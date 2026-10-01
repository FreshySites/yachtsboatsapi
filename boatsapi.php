<?php
/*
Plugin Name: Yacht Importer
Plugin URI: https://github.com/FreshySites/yachtsboatsapi
Description: This plugin is used to fetch and store boats from API.
Version: 1.6.1
Author: Freshy (formerly WP Harbor)
Author URI: https://freshysites.com/
Text Domain: boatsapi
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit();
}

/* * */
define('YACHT_PLUGIN_SLUG', 'yachtsboatsapi');
define('YACHT_PLUGIN_FILE', plugin_basename(__FILE__));
define('YACHT_PLUGIN_VERSION', '1.6.1');
/* * */




define( 'BOATS_VERSION', '1.6.1' );
define( 'BOATS__MINIMUM_WP_VERSION', '4.0' );
define( 'BOATS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BOATS_DELETE_LIMIT', 100000 );

require_once( BOATS__PLUGIN_DIR . 'includes/class.boatapis.php' );
require_once( BOATS__PLUGIN_DIR . 'includes/class.tableslist.php' );
require_once( BOATS__PLUGIN_DIR . 'includes/class.createtables.php' );
require_once( BOATS__PLUGIN_DIR . 'includes/class.saveapidata.php' );


register_activation_hook( __FILE__, array( 'BoatsAPI', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'BoatsAPI', 'plugin_deactivation' ) );
add_action('admin_init', 'plugin_redirect');

add_action( 'init', array( 'BoatsAPI', 'init' ) );
add_action( 'init', array( 'BoatsAPITableList', 'init' ) );
add_action( 'init', array( 'BoatsAPICreateTables', 'init' ) );
add_action( 'init', array( 'SaveAllBoatsAPIData', 'init' ) );

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'add_plugin_page_settings_link');
function add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=boats_api' ) .
		'">' . __('Settings') . '</a>';
	return $links;
}

function plugin_redirect() {
    if (get_option('do_activation_redirect', false)) {
        delete_option('do_activation_redirect');
        exit(wp_redirect(admin_url( 'admin.php?page=boats_api' )));
    }
}

// register jquery and style on initialization

function boatsRegisterScript() {
	// Only run on yacht templates
	if ( is_page_template( 'yacht-template.php' ) || is_page_template( 'yacht-detail-template.php' ) ) {

		wp_register_script( 'nouisliderjs', plugins_url( '/nouislider/nouislider.min.js', __FILE__ ) );
		wp_register_script( 'script', plugins_url( '/assets/js/script.js', __FILE__ ), [], '1.0.0', true );
		wp_register_script( 'nouisliderjstw', plugins_url( '/nouislider/wNumb.js', __FILE__ ) );

		wp_register_style( 'nouislider', plugins_url( '/nouislider/nouislider.min.css', __FILE__ ), [], '1.0.0', 'all' );
		wp_register_style( 'custom', plugins_url( '/assets/custom.css', __FILE__ ), [], '1.0.2', 'all' );
		wp_register_style( 'bootstrap', plugins_url( '/assets/bootstrap.css', __FILE__ ), [], '1.0.0', 'all' );
	}
}
add_action( 'wp_enqueue_scripts', 'boatsRegisterScript' );

// add_action('init', 'boatsRegisterScript'); // old way prior to restricting with templates

// use the registered jquery and style above
add_action('wp_enqueue_scripts', 'boatsEnqueueStyle');

function boatsEnqueueStyle(){
	wp_enqueue_script('nouisliderjs');
	wp_enqueue_script('nouisliderjstw');
	wp_enqueue_script('script');

	wp_enqueue_style( 'nouislider' );
	wp_enqueue_style( 'custom' );
	wp_enqueue_style( 'bootstrap' );
	?>
	<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
	<?php
}

function boatsCustomScriptInAdmin($hook) {
    wp_register_script( 'boats_scripts', plugin_dir_url( __FILE__ ) . 'assets/admin/js/script.js', '', uniqid() );
    wp_enqueue_script('boats_scripts');

    wp_register_style( 'boats__css', plugin_dir_url( __FILE__ ) . 'assets/admin/css/style.css', false, uniqid() );
    wp_enqueue_style( 'boats__css' );
}

add_action('admin_enqueue_scripts', 'boatsCustomScriptInAdmin');

function dequeue_dequeue_plugin_style(){
    wp_dequeue_style( 'style' ); //Name of Style ID.
}
add_action( 'wp_enqueue_scripts', 'dequeue_dequeue_plugin_style', 999 ); 


// Begin the code for custom template of the plugin page
class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		} 

		return self::$instance;

	} 

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);


		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);


		// Add your templates to this array.
		$this->templates = array(
			'yacht-template.php' => 'Yacht Plugin Template',
			'yacht-detail-template.php' => 'Yacht Detail Plugin Template',
		);

	} 

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	} 

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		
		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 

		$file = plugin_dir_path( __FILE__ ). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

} 
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

// add_action( 'admin_init', 'mytheme_admin_init' );
// function mytheme_admin_init() {
// 	if ( ! get_option( 'mytheme_installed' ) ) {
// 		$new_page_id = wp_insert_post( array(
// 			'post_title'     => 'Exclusive Listings',
// 			'post_type'      => 'page',
// 			'post_name'      => 'exclusive-listings',
// 			'comment_status' => 'closed',
// 			'ping_status'    => 'closed',
// 			'post_content'   => '',
// 			'post_status'    => 'publish',
// 			'post_author'    => get_user_by( 'id', 1 )->user_id,
// 			'menu_order'     => 0,
//             // Assign page template
// 			'page_template'  => 'yacht-template.php'
// 		)
// 	);
// 		update_option( 'mytheme_installed', true );
// 	}
// }

function boats_api_register_settings() {
   add_option( 'boats_api_option_name', '');
   add_option( 'boats_api_key', '');
   add_option( 'boats_api_key_url', '');
   add_option( 'hero_image', '');      
   add_option( 'archive_page_option_name', '');      
   add_option( 'individual_listing_option_name', '');
   register_setting( 'boats_api_options_group', 'boats_api_option_name', 'boats_api_callback' );
   register_setting( 'boats_api_options_group', 'boats_api_key', 'boats_api_callback' );
   register_setting( 'boats_api_options_group', 'boats_api_key_url', 'boats_api_callback' );
   register_setting( 'boats_api_options_group', 'hero_image', 'boats_api_callback' );      register_setting( 'boats_api_options_group', 'archive_page_option_name', 'boats_api_callback' );      register_setting( 'boats_api_options_group', 'individual_listing_option_name', 'boats_api_callback' );
}
add_action( 'admin_init', 'boats_api_register_settings' );

function boats_api_register_options_page() {
  add_options_page('Boats API Setting Page', 'Yacht Importer', 'manage_options', 'boats_api', 'boats_api_options_page');
}
add_action('admin_menu', 'boats_api_register_options_page');

function boats_api_options_page()
{
?>



<div>
<?php //screen_icon(); ?>
	<div class="setting-main-heading">
		<h2>Yacht Importer</h2>
		<div class="import_yachts">
			<div id="message" class="updated notice is-dismissible">
				<p class="response_msg"></p>
			</div>
		</div>
	</div>

<form method="post" action="options.php">
	<?php settings_fields( 'boats_api_options_group' ); ?>
	<h3>API Settings</h3>
	<p><?php echo "<a href='https://github.com/FreshySites/yachtsboatsapi/blob/main/README.md' class='documentatation-link' target='_blank'>Read Documentation</a>";  ?> </p>

	<table style="width: 100%;">
		<tr valign="top">
		<th scope="row">
			<label for="boats_api_option_name">Enter Email for inquiries</label>
		</th>
		<td>
			<input type="text" id="boats_api_option_name" name="boats_api_option_name" value="<?php echo get_option('boats_api_option_name'); ?>" />
		</td>
		</tr>
		
		<tr valign="top">
		<th scope="row">
			<label for="boats_api_key_url">Enter API Key URL</label>
		</th>
		<td>
			<input type="text" id="boats_api_key_url" name="boats_api_key_url" value="<?php echo get_option('boats_api_key_url'); ?>" />
		</td>
		</tr>
			
		<tr valign="top">
		<th scope="row">
			<label for="boats_api_key">Enter Boats API Key</label>
		</th>
		<td>
			<input type="text" id="boats_api_key" name="boats_api_key" value="<?php echo get_option('boats_api_key'); ?>" />
			<input type="button" class="button button-primary" name="import_yachts_boats" id="import_yachts_boats" value="Import Yachts">
					<div class="loder_imp">
						<div class="loadersmall"></div>
					</div>
		</td>

		</tr>

		<tr valign="top">
		<th scope="row">
			<label for="hero_image">Upload Hero Image</label>
		</th>
		<td>
			<input type="text" name="hero_image" id="hero_image" value="<?php echo get_option('hero_image'); ?>" />
			<button class="button wpse-228085-upload">Upload</button>
		</td>
		</tr>
 	</table>		
	
	<h3>Pages Setup</h3>		
	
	<table style="width: 100%;">
	
	
		<tr valign="top" style="display:none;">
			<th scope="row">
			<label for="archive_page_option_name">Select Archive Page</label>
			</th>
			<td>
				<?php
					$pages = get_pages();
					echo '<select id="archive_page_option_name" name="archive_page_option_name" class="select2-pages" style="width: 300px;">';
					echo '<option value="">Select a Page</option>';
					foreach ($pages as $page) {
						$selected = ($page->ID == get_option('archive_page_option_name')) ? 'selected' : '';
						echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
					}					
					echo '</select>';				
				?>							
			</td>		
		</tr>						
		<tr valign="top">			
			<th scope="row">				
				<label for="individual_listing_option_name">Select Listing Detail Page</label>
			</th>
			<td>
			<?php
				$pages = get_pages();
				echo '<select id="individual_listing_option_name" name="individual_listing_option_name" class="select2-pages" style="width: 300px;">';
				echo '<option value="">Select a Page</option>';
				foreach ($pages as $page) {
					$selected = ($page->ID == get_option('individual_listing_option_name')) ? 'selected' : '';
					echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
				}
				echo '</select>';
				?>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td><?php  submit_button('Save Changes', 'primary', 'save_api_form'); ?>
			</td>
		</tr>			
	</table>
  
</form>

	
<?php
$key 		= get_option('boats_api_key');
$api_url 	= get_option('boats_api_key_url');

if( $key && $api_url ){ ?>

<?php
}


?>

  <h2>Yacht Inquiries</h2>
	<div class="yacht-inquiries-table">
  <table class="widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th id="cb" class="manage-column column-cb check-column" scope="col">ID</th> 
			<th id="columnname" class="manage-column column-columnname" scope="col">Boat ID</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Boat Name</th>
			<th id="columnname" class="manage-column column-columnname num" scope="col">Your Name</th> 
			<th id="columnname" class="manage-column column-columnname num" scope="col">Email</th> 
			<th id="columnname" class="manage-column column-columnname num" scope="col">Phone</th> 
			<th id="columnname" class="manage-column column-columnname num" scope="col">Company</th> 
			<th id="columnname" class="manage-column column-columnname num" scope="col">Message</th> 
		</tr>
	</thead>

	<tfoot>
		<tr>
			<th class="manage-column column-cb check-column" scope="col">ID</th>
			<th class="manage-column column-columnname" scope="col">Boat ID</th>
			<th class="manage-column column-columnname" scope="col">Boat Name</th>
			<th class="manage-column column-columnname" scope="col">Your Name</th>
			<th class="manage-column column-columnname num" scope="col">Email</th>
			<th class="manage-column column-columnname num" scope="col">Phone</th>
			<th class="manage-column column-columnname num" scope="col">Company</th>
			<th class="manage-column column-columnname num" scope="col">Message</th>
		</tr>
	</tfoot>
	  
	<?php
	global $wpdb;
	$table = $wpdb->prefix.'yacht_queries';
	$result = $wpdb->get_results ( "SELECT * FROM $table ");
	$queries_data = $result;
	foreach($queries_data as $inquery){
	    $id 			= $inquery->id;
	    $boatid 		= $inquery->boatid;
	    $customer_info 	= unserialize($inquery->customer_info);
	    ?>
		<tbody>
		  <tr class="alternate">
		      <th class="check-column" scope="row"><?= $id ?></th>
		      <td class="column-columnname"><?= $boatid ?></td>
		      <td class="column-columnname"><?= $customer_info['boat-name'] ?></td>
		      <td class="column-columnname"><?= $customer_info['full_name'] ?></td>
		      <td class="column-columnname"><?= $customer_info['email'] ?></td>
		      <td class="column-columnname"><?= $customer_info['phone'] ?></td>
		      <td class="column-columnname"><?= $customer_info['company'] ?></td>
		      <td class="column-columnname"><?= $customer_info['message'] ?></td>
		  </tr>
		  </tr>
		</tbody>

	<?php
	}
	?>

  </table>
	</div>
  </div>



<?php
}



function enqueueAjaxUrl() {

    wp_enqueue_script( 'ajax-script', plugins_url('/assets/js/script.js', __FILE__), array('jquery') );
    wp_enqueue_script( 'ajax-admin-script', plugin_dir_url( __FILE__ ).'assets/admin/js/script.js', array('jquery') );

    wp_localize_script( 'ajax-script', 'yacht_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    wp_localize_script( 'ajax-admin-script', 'yacht_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'enqueueAjaxUrl' );

add_action('wp_ajax_saveQueriesFormData', 'saveQueriesFormData');
add_action('wp_ajax_nopriv_saveQueriesFormData', 'saveQueriesFormData');

function saveQueriesFormData()
{
	$all_queires_data = $_POST;
	$form_data = $all_queires_data['data'];

	$yachts_queury = array();
	foreach ($form_data as $key => $value) {
		$yachts_queury[$value['name']] = $value['value'];
	}

	$searialize_data = serialize($yachts_queury);

	if(!empty($yachts_queury)){
		global $wpdb;
		$nameTbl = $wpdb->prefix.'yacht_queries';
		$result = $wpdb->insert($nameTbl, array(
		        'boatid' => $yachts_queury['boat-id'],
		        'customer_info' =>$searialize_data,
		    ),array(
		        '%s',
		        '%s',
		        '%s'
		    )
		);

		if($result==1){
			echo "You query has been submitted successfully!";

			$to 			= get_option('boats_api_option_name');
			$head_new_usr 	= array('Content-Type: text/html; charset=UTF-8');
			$subject_new_usr = "New Query for ".$yachts_queury['boat-name'];
			$new_user_msg = '';

			$new_user_msg .= "<html><body>";
			$new_user_msg .= "<p>Hi,</p>";
			$new_user_msg .= "<p>";
			$new_user_msg .= "Username: ".$yachts_queury['full_name']. " <br>";
			$new_user_msg .= "Email: ".$yachts_queury['email']. " <br>";
			$new_user_msg .= "Phone: ".$yachts_queury['phone']." <br>";
			$new_user_msg .= "Your Company: ".$yachts_queury['company']." <br>";
			$new_user_msg .= "Yacht URL: ".$yachts_queury['boat-url']." <br>";
			$new_user_msg .= "Message: ".$yachts_queury['message']." <br>";
			$new_user_msg .= "<br>";
			$new_user_msg .= "</p>";
			$new_user_msg .= "<p>Thanks!</p>";
			$new_user_msg .= "</html></body>";

			wp_mail( $to, $subject_new_usr, $new_user_msg, $head_new_usr );
			wp_mail( $yachts_queury['email'], 'Your Query for '.$yachts_queury['boat-name'], $new_user_msg, $head_new_usr );

		}else{
			echo "Something went wrong! Please try again.";
		}
	}

	die();
}


add_action('wp_ajax_importYachtsBoats', 'importYachtsBoats');
add_action('wp_ajax_nopriv_importYachtsBoats', 'importYachtsBoats');

function importYachtsBoats()
{
	$key 			= get_option('boats_api_key');
	$api_url 		= get_option('boats_api_key_url');
	$plugin_chk 	= get_option('Activated_BoatsAPI');

	if( $key && $api_url && $plugin_chk ){
		require_once( BOATS__PLUGIN_DIR . 'ImportBoats/import.php' );
	}else{
		echo "It seems you have not configured API Key and API Key URL.";
	}

	die();
}

function validateActivationKey() {
	$hooks = array( 'isa_add_every_day', 'validate_activation_key_cron' );
	$crons = _get_cron_array();
	if ( ! is_array( $crons ) ) {
		return;
	}
	foreach ( $crons as $timestamp => $cron ) {
		foreach ( $hooks as $hook ) {
			if ( ! isset( $cron[ $hook ] ) ) {
				continue;
			}
			foreach ( $cron[ $hook ] as $event ) {
				wp_unschedule_event( $timestamp, $hook, $event['args'] );
			}
		}
	}
}
add_action( 'isa_add_every_day', 'validateActivationKey' );
add_action( 'validate_activation_key_cron', 'validateActivationKey' );

add_action('admin_enqueue_scripts', function(){
    /*
    if possible try not to queue this all over the admin by adding your settings GET page val into next
    if( empty( $_GET['page'] ) || "my-settings-page" !== $_GET['page'] ) { return; }
    */
    wp_enqueue_media();
});



// [yacht-listings type="Power,Sail" fuel="diesel,unleaded" condition="Used"]

// Case-sensitive (as per DB Values)
// Comma-separated only (,)


function yacht_listings($atts = []) { 

	$atts = shortcode_atts([
		'type'      => '', // e.g. Power,Sail
		'fuel'      => '', // e.g. diesel,unleaded
		'condition' => '', // e.g. Used
	], $atts, 'yacht-listings');

  
	ob_start();
	
	// -------------------------------------------------
	// Apply shortcode attributes as default filters
	// ONLY if GET params are not already present
	// -------------------------------------------------

	if (empty($_GET['type']) && !empty($atts['type'])) {
		$_GET['type'] = array_map('trim', explode(',', $atts['type']));
	}

	if (empty($_GET['fuel']) && !empty($atts['fuel'])) {
		$_GET['fuel'] = array_map('trim', explode(',', $atts['fuel']));
	}

	if (empty($_GET['condition']) && !empty($atts['condition'])) {
		$_GET['condition'] = array_map('trim', explode(',', $atts['condition']));
	}
	
	
	$has_filters =
		!empty($_GET['type']) ||
		!empty($_GET['fuel']) ||
		!empty($_GET['condition']) ||
		!empty($_GET['make']) ||
		!empty($_GET['hullid']) ||
		!empty($_GET['boatname']) ||
		!empty($_GET['minLenght']) ||
		!empty($_GET['maxLenght']) ||
		!empty($_GET['inputPrice']) ||
		!empty($_GET['inputPriceMax']) ||
		!empty($_GET['inputYear']) ||
		!empty($_GET['inputYearMax']);

	if ($has_filters && !isset($_GET['resultButton'])) {
		$_GET['resultButton'] = '1';
	}
	
	?>
	
	<link rel="stylesheet" href="<?php echo plugins_url( '/nouislider/nouislider.min.css', __FILE__ ); ?>" />
    <link rel="stylesheet" href="<?php echo plugins_url( '/assets/custom.css', __FILE__ ); ?>" />
    <link rel="stylesheet" href="<?php echo plugins_url( '/assets/bootstrap.css', __FILE__ ); ?>" />

    <script src="<?php echo plugins_url( '/nouislider/nouislider.min.js', __FILE__ ); ?>"></script>
    <script src="<?php echo plugins_url( '/nouislider/wNumb.js', __FILE__ ); ?>"></script>
    <script src="<?php echo plugins_url( '/assets/js/script.js', __FILE__ ); ?>"></script>
	
	
	<div class="yacht-listings-wrapper">
	<?php
	include plugin_dir_path( __FILE__ ) . 'yacht-shortcode-template.php';
	?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode('yacht-listings', 'yacht_listings');



/****************CRON JOB WORK*****************/



/* // old code
// Add a new schedule interval for daily cron jobs
add_filter( 'cron_schedules', 'isa_add_every_day' );
function isa_add_every_day( $schedules ) {
    $schedules['every_day'] = array(
        'interval'  => 86400, // 86400 seconds in a day
        'display'   => __( 'Every Day', 'textdomain' )
    );
    return $schedules;
}
// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'isa_add_every_day' ) ) {
    wp_schedule_event( time(), 'every_day', 'isa_add_every_day' );
}
// Hook into that action that’ll fire every day
add_action( 'isa_add_every_day', 'validateActivationKey' );
*/

/* // old code
add_filter('cron_schedules', 'boatsCroneSchedule');
function boatsCroneSchedule($schedules)
{
    $schedules['every_six_h'] = array('interval' => 21600, 'display' => 'Every Six Hours');
    return $schedules;
}

add_action('boatsCroneSchedule', 'dailyImportBoatsCronJob');
*/


/*
// new code with wp_cron method 
// Register custom interval
add_filter( 'cron_schedules', function($schedules) {
    $schedules['every_day'] = [
        'interval' => 86400,
        'display'  => __( 'Every Day', 'textdomain' )
    ];
    return $schedules;
});

// Schedule the event if not already scheduled
if ( ! wp_next_scheduled( 'validate_activation_key_cron' ) ) {
    wp_schedule_event( time(), 'every_day', 'validate_activation_key_cron' );
}

// Hook the function
add_action( 'validate_activation_key_cron', 'validateActivationKey' );


// Register 6 hour schedule
add_filter('cron_schedules', function($schedules) {
    $schedules['every_six_h'] = [
        'interval' => 21600,
        'display'  => 'Every Six Hours'
    ];
    return $schedules;
});

// Schedule the import event
if ( ! wp_next_scheduled( 'import_boats_cron' ) ) {
    wp_schedule_event( time(), 'every_six_h', 'import_boats_cron' );
}

// Hook it
add_action('import_boats_cron', 'dailyImportBoatsCronJob');

*/




// Run dailyImportBoatsCronJob once every 6 hours
function maybe_run_import_boats() {
    $last_run = get_option('yacht_plugin_last_import_time', 0);
    $now      = current_time('timestamp');

    if ( ($now - $last_run) > 6 * HOUR_IN_SECONDS ) {
        ob_start();
        dailyImportBoatsCronJob();
        ob_end_clean(); // discard any output

        update_option('yacht_plugin_last_import_time', $now);
		
    }
}
add_action('wp_footer', 'maybe_run_import_boats');







function dailyImportBoatsCronJob()
{
    require_once( BOATS__PLUGIN_DIR . 'ImportBoats/import.php' );
}







/**************** Detail Template Shortcode *****************/


function yacht_detail_shortcode( $atts, $content = null ) {
    ob_start();
    
        include('yacht-detail-shortcode.php');
    
    return ob_get_clean();
}
add_shortcode( 'yacht_detail_shortcode', 'yacht_detail_shortcode' );






function cleanup_yacht_pdfs() {
    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'] . '/yacht_pdfs/';

    if (!is_dir($dir)) {
        return;
    }

    // Delete PDFs older than 24 hours (60 * 60 = 3600 seconds = 1 hour)
    $files = glob($dir . '*.pdf');
    if (!empty($files)) {
        foreach ($files as $file) {
            if (filemtime($file) < (time() - 3600)) {
                unlink($file);
            }
        }
    }
}
add_action('yacht_pdf_cleanup_event', 'cleanup_yacht_pdfs');



function schedule_yacht_pdf_cleanup() {
    if (!wp_next_scheduled('yacht_pdf_cleanup_event')) {
        wp_schedule_event(time(), 'daily', 'yacht_pdf_cleanup_event');
    }
}
add_action('wp', 'schedule_yacht_pdf_cleanup');




?>
