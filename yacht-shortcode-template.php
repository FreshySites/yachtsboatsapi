<?php 
// get_header();

$key      		= get_option('boats_api_key');
$api_url    	= get_option('boats_api_key_url');
$plugin_chk   	= get_option('Activated_BoatsAPI');

if($key && $api_url && $plugin_chk){
	
global $wpdb;

if(isset($_GET['resultButton'])){
	$table 		= $wpdb->prefix.'boats';
	$sql 		= "SELECT * FROM $table WHERE status in ('Active','Sale Pending','On-Order') ";
	$criteria 	= "";

	if(isset($_GET['minLenght']) && $_GET['minLenght'] != '' &&  isset($_GET['maxLenght']) && $_GET['maxLenght'] != ''){
		$criteria .= " AND nominallength BETWEEN ".$_GET['minLenght']." AND ".$_GET['maxLenght'];
	}
	else if(isset($_GET['minLenght']) && $_GET['minLenght'] != ''){
		$criteria .= " AND nominallength >=".$_GET['minLenght'];
	}
	else if(isset($_GET['maxLenght']) && $_GET['maxLenght'] != ''){
		$criteria .= " AND nominallength <= ".$_GET['maxLenght'];
	}

	if(isset($_GET['inputPrice']) && $_GET['inputPrice'] != '' &&  isset($_GET['inputPriceMax']) && $_GET['inputPriceMax'] != ''){
		$criteria .= " AND price BETWEEN ".$_GET['inputPrice']." AND ".$_GET['inputPriceMax'];
	}
	else if(isset($_GET['inputPrice']) && $_GET['inputPrice'] != ''){
		$criteria .= " AND price >=".$_GET['inputPrice'];
	}
	else if(isset($_GET['inputPriceMax']) && $_GET['inputPriceMax'] != ''){
		$criteria .= " AND price <= ".$_GET['inputPriceMax'];
	}

	if(isset($_GET['inputYear']) && $_GET['inputYear'] != ''  &&  isset($_GET['inputYearMax']) && $_GET['inputYearMax'] != '' ){
		$criteria .= " AND year BETWEEN ".$_GET['inputYear']." AND ".$_GET['inputYearMax'];
	}
	else if(isset($_GET['inputYear']) && $_GET['inputYear'] != '' ){
		$criteria .= " AND year >=".$_GET['inputYear'];
	}
	else if(isset($_GET['inputYearMax']) && $_GET['inputYearMax'] != '' ){
		$criteria .= " AND year <= ".$_GET['inputYearMax'];
	}

	if(isset($_GET['make']) && $_GET['make'] != '' && $_GET['make'] != 'undefined'){
		$get_make = $_GET['make'];
		$criteria .= " AND make = '$get_make' ";
	}
	if(isset($_GET['fuel']) && $_GET['fuel'] != ''){
		$implode_fuelid = implode(",",$_GET['fuel']);
		$criteria .= " AND fuelid in($implode_fuelid)";
	}
	if(isset($_GET['type']) && $_GET['type'] != ''){
		$implode_categoryid = implode(",",$_GET['type']);
		$criteria .= " AND categoryid in($implode_categoryid)";
	}
	if(isset($_GET['hullid']) && $_GET['hullid'] != '' && $_GET['hullid'] != 'undefined'){
		$criteria .= " AND hullid = ".$_GET['hullid'];
	}
	if(isset($_GET['condition']) && $_GET['condition'] != ''){

		$implode_condition = implode(",", $_GET['condition']);
		$criteria .= " AND conditionid in ($implode_condition)";
	}
	if(isset($_GET['boatname']) && $_GET['boatname'] != '' ){
		$criteria .= " AND boatname LIKE '%".$_GET['boatname']."%'";
	}

	$s_boats_query = $sql.$criteria;
	// $search_boats_query = $wpdb->get_results($s_boats_query);
}

if (isset($_GET['page_no']) && $_GET['page_no']!="") {

	$page_no = $_GET['page_no'];
}else {
	$page_no = 1;
}

if (isset($_GET['view_all_boat']) && $_GET['view_all_boat'] = 'all'){

	$total_records_per_page = 99999;
}else{
	$total_records_per_page = 9;
}

if( !function_exists('returnRefinedURL')){
	function returnRefinedURL($url, $which_argument=false){ 
		return preg_replace('/'. ($which_argument ? '(\&|)'.$which_argument.'(\=(.*?)((?=&(?!amp\;))|$)|(.*?)\b)' : '(\?.*)').'/i' , '', $url);  
	}
}

$filters = '';
if( $_SERVER['QUERY_STRING']!='' ){
	if ( strpos($_SERVER['QUERY_STRING'], 'page_no') !== false ) {
	    $filters = returnRefinedURL( $_SERVER['QUERY_STRING'], 'page_no' );
	}else{
		$filters = '&'.$_SERVER['QUERY_STRING'];
	}
}else{
	$filters = '';
}

$offset = ($page_no-1) * $total_records_per_page;
$previous_page = $page_no - 1;
$next_page = $page_no + 1;
$adjacents = "2"; 
$table1 = $wpdb->prefix.'boats';
// $result_count= $wpdb->get_results("SELECT * FROM $table1 where status='Active' or status = 'On-Order' or status='Sale Pending' order by nominallength DESC");

if(isset($_GET['resultButton'])){
	$search_boats_query = $wpdb->get_results($s_boats_query);
	$total_records = $search_boats_query;
}else{
	$result_count= $wpdb->get_results("SELECT * FROM $table1 where status='Active' or status = 'On-Order' or status='Sale Pending' order by nominallength DESC");
	$total_records = $result_count;
}

$total_records = count($total_records);

$total_no_of_pages = ceil($total_records / $total_records_per_page);
$second_last = $total_no_of_pages - 1; // total page minus 1
?>

<?php 
global $wpdb;
$hull_materials = $wpdb->prefix.'boat_hull_materials';
$boat_conditions = $wpdb->prefix.'boat_conditions';
$boat_categories = $wpdb->prefix.'boat_categories';
$boat_fuels = $wpdb->prefix.'boat_fuel';
$makes = $wpdb->get_results( "SELECT distinct make FROM $table1 where status='Active' or status = 'On-Order' or status='Sale Pending' ORDER by make");
$hullmaterials = $wpdb->get_results( "SELECT id,title FROM $hull_materials where isactive=1 and title != '' and id in (select hullid from $table1 where hullid is not null and status in ('active','sale pending')) order by position");
$states = $wpdb->get_results( "SELECT distinct statecode FROM $table1 where statecode <> '' and statecode != 'Unknown' and status = 'active' order by statecode;");
$boatcategories = $wpdb->get_results( "SELECT id,title FROM $boat_categories where isactive = 1 LIMIT 2");
$boatconditions = $wpdb->get_results( "SELECT id,title FROM $boat_conditions where isactive = 1 LIMIT 2");
$boatfuels = $wpdb->get_results( "SELECT id,title FROM $boat_fuels where isactive = 1 LIMIT 2");
?>
<div class="main-content">
	<div class="row">
		<!-- Pagination  -->

		<style>
			.hidden-inputs{
				display: none;
			}
		</style>
		<?php
			$hero_image = '';
			if(get_option('hero_image')!=''){
				$hero_image = get_option('hero_image');
			}else{
				$hero_image = 'https://images.unsplash.com/photo-1589315751941-9b3c84b12c9a?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1647&q=80';
			}
		?>
		<!-- <div class="boats-banner-section" style="background-image: url('<?= $hero_image ?>')">
			<div class="row">
				<div class="container">
					<div class="plugin-banner">
						<div class="main-heading">
							<h1 class="page-heading">
							</h1>
						</div>
					</div>
				</div>
			</div>
		</div> -->
		<div class="search scroll-section">
			<div class="container">
				<div class="sub-heading-global">
					<h2>
						Search boats by filter
					</h2>
				</div>
				<form id="searchForm" method="get">
					<div class="row">
						<div class="col-md-5">
							<div class="form-group noUiGroup">
								<label>Length</label>
								<div id="lenght"></div>
								<p class="results">min <span id="span-lenght"></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;max <span id="span-lenght-max"></span></p>
								<div class="hidden-inputs">
									<input type="text" id="input-lenght" name="minLenght">
									<input type="text" id="input-lenght-max" name="maxLenght">
								</div>
							</div>
							<div class="form-group noUiGroup">
								<label>Price</label>
								<div id="price"></div>
								<p class="results">min <span id="span-price"></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;max <span id="span-price-max"></span></p>
								<div class="hidden-inputs">
									<input type="text" id="input-price" name="inputPrice">
									<input type="text" id="input-price-max" name="inputPriceMax">
								</div>
							</div>
							<div class="form-group noUiGroup">
								<label>Year</label>
								<div id="year"></div>
								<p class="results">min <span id="span-year"></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;max <span id="span-year-max"></span></p>
								<div class="hidden-inputs">
									<input type="text" id="input-year" name="inputYear">
									<input type="text" id="input-year-max" name="inputYearMax">
								</div>
							</div>
						</div>

						<div class="col-md-7">
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label>Make</label>
										<select id="make" name="make" class="form-control">
											<option value="">- Select -</option>
											<?php foreach($makes as $make){ ?>
												<option <?php if(isset($_GET['make'])){ if($make->make == $_GET['make']){ echo "selected";} } ?> value="<?php echo $make->make; ?>"><?php echo $make->make; ?></option>
											<?php } ?>

										</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label>	Hull Material</label>
										<select id="hullid" name="hullid" class="form-control">
											<option value="">- Select -</option>
											<?php foreach($hullmaterials as $hull){ ?>
												<option <?php if(isset($_GET['hullid'])){ if($hull->id == $_GET['hullid']){ echo "selected";} } ?> value="<?php echo $hull->id; ?>"><?php echo $hull->title; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>

							</div>
							<div class="row">
								<div class="col-sm-6 col-md-3">
									<div class="form-group radio-buttons">
										<label>Fuel Type</label>
										<ul class="type-radio sale-lease radio-buttons">
											<?php foreach($boatfuels as $fuel){ 
												if(isset($_GET['fuel'])){
												?>
												<li class="custom-checkRad">
													<input <?php if(in_array($fuel->id, $_GET['fuel']) ){ echo 'checked'; } ?> type="checkbox" name="fuel[]" id="<?php echo strtolower($fuel->title)."Rad"; ?>" value="<?php echo $fuel->id; ?>"><label for="<?php echo strtolower($fuel->title)."Rad"; ?>"><?php echo $fuel->title; ?></label>
												</li>
											<?php }else{ ?>
												<li class="custom-checkRad">
													<input type="checkbox" name="fuel[]" id="<?php echo strtolower($fuel->title)."Rad"; ?>" value="<?php echo $fuel->id; ?>"><label for="<?php echo strtolower($fuel->title)."Rad"; ?>"><?php echo $fuel->title; ?></label>
												</li>
											<?php
											} 
										}
											?>
										</ul>
									</div>
								</div>
								<div class="col-sm-6 col-md-3">
									<div class="form-group radio-buttons">
										<label>Sail or Power</label>
										<ul class="type-radio sale-lease radio-buttons">
											<?php foreach($boatcategories as $category){ 
												if(isset($_GET['type'])){
												?>
												<li class="custom-checkRad">
													<input <?php if(in_array($category->id, $_GET['type']) ){ echo 'checked'; } ?> type="checkbox" name="type[]" id="<?php echo strtolower($category->title)."Rad"; ?>" value="<?php echo $category->id; ?>"><label for="<?php echo strtolower($category->title)."Rad"; ?>"><?php echo $category->title; ?></label>
												</li>
											<?php }else{ ?>
												<li class="custom-checkRad">
													<input type="checkbox" name="type[]" id="<?php echo strtolower($category->title)."Rad"; ?>" value="<?php echo $category->id; ?>"><label for="<?php echo strtolower($category->title)."Rad"; ?>"><?php echo $category->title; ?></label>
												</li>
											<?php
											} 
										}
											?>
										</ul>
									</div>
								</div>
								<div class="col-sm-6 col-md-3">
									<div class="form-group radio-buttons">
										<label>Condition</label>
										<ul class="type-radio sale-lease radio-buttons">
											<?php foreach($boatconditions as $condition){ 
												if(isset($_GET['condition'])){
												?>
												<li class="custom-checkRad">
													<input <?php if(in_array($condition->id, $_GET['condition']) ){ echo 'checked'; } ?> type="checkbox" name="condition[]" id="<?php echo strtolower($condition->title)."Rad"; ?>" value="<?php echo $condition->id; ?>"><label for="<?php echo strtolower($condition->title)."Rad"; ?>"><?php echo $condition->title; ?></label>
												</li>
											<?php 
												}else{ ?>
													<li class="custom-checkRad">
														<input type="checkbox" name="condition[]" id="<?php echo strtolower($condition->title)."Rad"; ?>" value="<?php echo $condition->id; ?>"><label for="<?php echo strtolower($condition->title)."Rad"; ?>"><?php echo $condition->title; ?></label>
													</li>
											<?php }
										} 

										?>
										</ul>
									</div>
								</div>
								<div class="col-sm-6 col-md-3">
									<div class="text-right global-button-gray">
										<button class="btn-layout" name="resultButton" type="submit" id="resultButton" >Search <i class="fa fa-search" aria-hidden="true"></i></button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php 

		if(isset($_GET['minLenght']) && !empty($_GET['minLenght'])){

			$GetminLenght = $_GET['minLenght'];
		}else{

			$GetminLenght = 9;
		}

		if(isset($_GET['maxLenght']) && !empty($_GET['maxLenght'])){

			$GetmaxLenght = $_GET['maxLenght'];
		}else{

			$GetmaxLenght = 100;
		}


		if(isset($_GET['inputPrice']) && !empty($_GET['inputPrice'])){

			$GetinputPrice = $_GET['inputPrice'];
		}else{

			$GetinputPrice = 4000;
		}

		if(isset($_GET['inputPriceMax']) && !empty($_GET['inputPriceMax'])){

			$GetinputPriceMax = $_GET['inputPriceMax'];
		}else{

			$GetinputPriceMax = 1000000;
		}

		if(isset($_GET['inputYear']) && !empty($_GET['inputYear'])){

			$GetinputYear = $_GET['inputYear'];
		}else{

			$GetinputYear = 1959;
		}

		if(isset($_GET['inputYearMax']) && !empty($_GET['inputYearMax'])){

			$GetinputYearMax = $_GET['inputYearMax'];
		}else{

			$GetinputYearMax = 2021;
		}

		?>

		<div class="boat-listing-main">
			<div class="row">
				<div class="container">
					<div class="sub-heading-global">
					<h2>
						our listings
					</h2>
				</div>
					<div class="pagination-main">
<!-- 						<div class="global-button-gray">
							<a href="?view_all_boat=all" class="btn btn-primary">View All Boats</a>
						</div> -->
					<?php 
					global $wpdb;
					$boats1 = $wpdb->prefix.'boats';
					if(isset($_GET['resultButton'])){
						$wpdb->get_results( $s_boats_query );
					}else{
						$wpdb->get_results( "SELECT * FROM `$boats1`");
					}
					
					?>
					<div class="inner-headings">
					  <h2>Search Results</h2>
					  <p>Found <?= $wpdb->num_rows ?> Exclusive Listings!</p>
					</div>
						<div class="pagination-content">
							<ul class="pagination">
								<?php // if($page_no > 1){ echo "<li><a href='?page_no=1'>First Page</a></li>"; } ?>
								<li <?php if($page_no <= 1){ echo "class='disabled'"; } ?>>
									<a <?php if($page_no > 1){ echo "href='?page_no=$previous_page$filters'"; } ?>>Previous</a>
								</li>
								<?php 
								if ($total_no_of_pages <= 10){  	 
									for ($counter = 1; $counter <= $total_no_of_pages; $counter++){
										if ($counter == $page_no) {
											echo "<li class='active'><a>$counter</a></li>";	
										}else{
											echo "<li><a href='?page_no=$counter$filters'>$counter</a></li>";
										}
									}
								}
								elseif($total_no_of_pages > 10){

									if($page_no <= 4) {			
										for ($counter = 1; $counter < 8; $counter++){		 
											if ($counter == $page_no) {
												echo "<li class='active'><a>$counter</a></li>";	
											}else{
												echo "<li><a href='?page_no=$counter$filters'>$counter</a></li>";
											}
										}
										echo "<li><a>...</a></li>";
										echo "<li><a href='?page_no=$second_last$filters'>$second_last</a></li>";
										echo "<li><a href='?page_no=$total_no_of_pages$filters'>$total_no_of_pages</a></li>";
									}

									elseif($page_no > 4 && $page_no < $total_no_of_pages - 4) {		 
										echo "<li><a href='?page_no=1$filters'>1</a></li>";
										echo "<li><a href='?page_no=2$filters'>2</a></li>";
										echo "<li><a>...</a></li>";
										for ($counter = $page_no - $adjacents; $counter <= $page_no + $adjacents; $counter++) {			
											if ($counter == $page_no) {
												echo "<li class='active'><a>$counter</a></li>";	
											}else{
												echo "<li><a href='?page_no=$counter$filters'>$counter</a></li>";
											}                  
										}
										echo "<li><a>...</a></li>";
										echo "<li><a href='?page_no=$second_last$filters'>$second_last</a></li>";
										echo "<li><a href='?page_no=$total_no_of_pages$filters'>$total_no_of_pages</a></li>";      
									}
									else {
										echo "<li><a href='?page_no=1$filters'>1</a></li>";
										echo "<li><a href='?page_no=2$filters'>2</a></li>";
										echo "<li><a>...</a></li>";

										for ($counter = $total_no_of_pages - 6; $counter <= $total_no_of_pages; $counter++) {
											if ($counter == $page_no) {
												echo "<li class='active'><a>$counter</a></li>";	
											}else{
												echo "<li><a href='?page_no=$counter$filters'>$counter</a></li>";
											}                   
										}
									}
								}
								?>
								<li <?php if($page_no >= $total_no_of_pages){ echo "class='disabled'"; } ?>>
									<a <?php if($page_no < $total_no_of_pages) { echo "href='?page_no=$next_page$filters'"; } ?>>Next</a>
								</li>
								<?php if($page_no < $total_no_of_pages){
									echo "<li><a href='?page_no=$total_no_of_pages$filters'>Last <span><i class='far fa-angle-double-right'></i></span></a></li>";
								} ?>
							</ul>
						</div>
					</div>
					<!-- end Pagination -->
					<!-- Display baots -->
					
				
					<?php
					global $wpdb;
					$boats1 = $wpdb->prefix.'boats';
					$boats = $wpdb->get_results( "SELECT * FROM `$boats1` WHERE status='Active' or status = 'On-Order' or status='Sale Pending' order by nominallength DESC LIMIT $offset, $total_records_per_page");
						if(isset($_GET['resultButton'])){
							// $rs_boats = $search_boats_query; //previous code
							$rs_boats =  $wpdb->get_results($s_boats_query." order by nominallength DESC LIMIT $offset, $total_records_per_page"); //by SV
						}else{
							$rs_boats = $boats;
						}
						?>
						<div class="news-letter-main">
							<?php 
							// echo "<pre>";
							foreach ($rs_boats as $boat) {
								$boat_id = $boat->id;
								$imagess = $wpdb->prefix.'images';
								$images = $wpdb->get_results( "SELECT * FROM `$imagess` WHERE boatid = '$boat_id' LIMIT 1");

								// print_r($images); die();
								$image_url = $images[0]->{'url'};
								//$boaturl = get_site_url().'/exclusive-listing-detail?boat_id='.$boat_id; 
								$boaturl = get_permalink(get_option('individual_listing_option_name')) . '?boat_id=' . $boat_id; 
								?>
								<div class="col-md-4">
									<div class="inner-box-boarts">
										<div class="boat-img">
											<?php if(isset($boat->status) && $boat->status == "On-Order" ){?>
									        	<div class="ribbon" style="background-color: #A50101; color: #fff;">New Model</div>
									        <?php }else if(isset($boat->status) && $boat->status == "Sale Pending" ){ ?>
									        	<div class="ribbon" style="background-color: #CCCC00; color: #fff;"><?= $boat->status ?></div>
									        <?php
									        } ?>
											<a class="mls-image" href="<?php echo $boaturl ?>">
												<img src="<?php echo $image_url ?>">
											</a>
										</div>
										<div class="boats-inner-content">
											<a class="mls-image" href="<?php echo $boaturl ?>">
												<span class="address"><?php if($boat->city != "Unknown"){echo $boat->city.", ";} echo $boat->statecode; ?></span>
												<span class="total-tempure"><?php echo round($boat->nominallength,0); ?>'</span>
											</a>
										</div>
										<a class="mls-image" href="<?php echo $boaturl ?>" target="_blank">
											<span class="shadow"></span>
										</a>
										<div class="overloay-box">
											<a class="mls-image" href="<?php echo $boaturl ?>" target="_blank">
												<span class="year-model"><?php echo $boat->year; ?> <?php echo $boat->make; ?> <?php echo $boat->model; ?></span>
												<span class="address"><?php if($boat->city != "Unknown"){echo $boat->city.", ";} echo $boat->statecode; ?></span>
												<span class="total-tempure"><?php echo round($boat->nominallength,0); ?>'</span>
												<span class="price"><?php if($boat->price != 0){echo "$".number_format($boat->price,0,'',',');} ?></span>
											</a>
										</div>
									</div>
								</div>
							<?php }  ?>
							<!-- display baots end-->
						</div>
						<div class="pagination-main">
							<div class="global-button-gray">
									<!-- Pagination  -->
<!-- 								<a href="?view_all_boat=all" class="btn btn-primary">View All Boats</a> -->
							</div>
							<div class="pagination-content">
								<ul class="pagination">
									<?php // if($page_no > 1){ echo "<li><a href='?page_no=1'>First Page</a></li>"; } ?>
									<li <?php if($page_no <= 1){ echo "class='disabled'"; } ?>>
										<a <?php if($page_no > 1){ echo "href='?page_no=$previous_page$filters'"; } ?>>Previous</a>
									</li>
									<?php 
									if ($total_no_of_pages <= 10){  	 
										for ($counter = 1; $counter <= $total_no_of_pages; $counter++){
											if ($counter == $page_no) {
												echo "<li class='active'><a>$counter</a></li>";	
											}else{
												echo "<li><a href='?page_no=$counter$filters'>$counter</a></li>";
											}
										}
									}
									elseif($total_no_of_pages > 10){

										if($page_no <= 4) {			
											for ($counter = 1; $counter < 8; $counter++){		 
												if ($counter == $page_no) {
													echo "<li class='active'><a>$counter</a></li>";	
												}else{
													echo "<li><a href='?page_no=$counter$filters'>$counter</a></li>";
												}
											}
											echo "<li><a>...</a></li>";
											echo "<li><a href='?page_no=$second_last$filters'>$second_last</a></li>";
											echo "<li><a href='?page_no=$total_no_of_pages$filters'>$total_no_of_pages</a></li>";
										}

										elseif($page_no > 4 && $page_no < $total_no_of_pages - 4) {		 
											echo "<li><a href='?page_no=1$filters'>1</a></li>";
											echo "<li><a href='?page_no=2$filters'>2</a></li>";
											echo "<li><a>...</a></li>";
											for ($counter = $page_no - $adjacents; $counter <= $page_no + $adjacents; $counter++) {			
												if ($counter == $page_no) {
													echo "<li class='active'><a>$counter</a></li>";	
												}else{
													echo "<li><a href='?page_no=$counter$filters'>$counter</a></li>";
												}                  
											}
											echo "<li><a>...</a></li>";
											echo "<li><a href='?page_no=$second_last$filters'>$second_last</a></li>";
											echo "<li><a href='?page_no=$total_no_of_pages$filters'>$total_no_of_pages</a></li>";      
										}
										else {
											echo "<li><a href='?page_no=1$filters'>1</a></li>";
											echo "<li><a href='?page_no=2$filters'>2</a></li>";
											echo "<li><a>...</a></li>";

											for ($counter = $total_no_of_pages - 6; $counter <= $total_no_of_pages; $counter++) {
												if ($counter == $page_no) {
													echo "<li class='active'><a>$counter</a></li>";	
												}else{
													echo "<li><a href='?page_no=$counter$filters'>$counter</a></li>";
												}                   
											}
										}
									}
									?>
									<li <?php if($page_no >= $total_no_of_pages){ echo "class='disabled'"; } ?>>
										<a <?php if($page_no < $total_no_of_pages) { echo "href='?page_no=$next_page$filters'"; } ?>>Next</a>
									</li>
									<?php if($page_no < $total_no_of_pages){
										echo "<li><a href='?page_no=$total_no_of_pages$filters'>Last <span><i class='far fa-angle-double-right'></i></span></a></li>";
									} ?>
								</ul>
							</div>
						</div>
						<!-- end Pagination -->
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
// Lenght
var lenghtSlider = document.getElementById('lenght');
var GetminLenght = "<?php echo $GetminLenght; ?>";
var GetmaxLenght = "<?php echo  $GetmaxLenght;?>";
noUiSlider.create(lenghtSlider, {
	start: [ GetminLenght, GetmaxLenght ],
	connect: true,
	step: 1,
	range: {
		'min': 9,
		'max': 100
	},
	format: wNumb({
		decimals: 0,
		postfix: "'",
	})
});

var inputLenght = document.getElementById('input-lenght');
var inputLenghtMax = document.getElementById('input-lenght-max');
var spanLenght = document.getElementById('span-lenght');
var spanLenghtMax = document.getElementById('span-lenght-max');

lenghtSlider.noUiSlider.on('update', function( values, handle ) {

	var value = values[handle];
	console.log(value)
	if ( handle ) {
		inputLenghtMax.value = value.replace("'","");
		spanLenghtMax.innerHTML = value;
		if(value === "100'" ){
			inputLenghtMax.value = "100";
			spanLenghtMax.innerHTML = 'any';
		}
		else{
			inputLenghtMax.value = value.replace("'","");
			spanLenghtMax.innerHTML = value;
		}
	} else {

		if(value === "9'"){
			inputLenght.value = "";
			spanLenght.innerHTML = 'any';
		}
		else{
			inputLenght.value = value.replace("'","");
			spanLenght.innerHTML = value;
		}
	}
});


// Price
var priceSlider = document.getElementById('price');

var GetinputPrice = "<?php echo $GetinputPrice;?>";
GetinputPrice = GetinputPrice.trim();
var GetinputPriceMax = "<?php echo $GetinputPriceMax;?>";
GetinputPriceMax = GetinputPriceMax.trim();

noUiSlider.create(priceSlider, {
	start: [ GetinputPrice, GetinputPriceMax ],
	connect: true,
	step: 1000,
	range: {
		'min': [ 4000 ],
		'max': [ 1000000 ]
	},
	format: wNumb({
		decimals: 0,
		thousand: ',',
		prefix: '$',
	})
});

var inputPrice = document.getElementById('input-price');
var inputPriceMax = document.getElementById('input-price-max');
var spanPrice = document.getElementById('span-price');
var spanPricetMax = document.getElementById('span-price-max');
var priceFormat = wNumb({
	decimals: 0,
	thousand: ','
});

priceSlider.noUiSlider.on('update', function( values, handle ) {

	var value = values[handle];
	console.log(value);
	if ( handle ) {
		if(value === "$1,000,000" || value === '$1,000,000'){
			spanPricetMax.innerHTML = 'any';
			inputPriceMax.value = '';
		}
		else{
			spanPricetMax.innerHTML = value;
			inputPriceMax.value = priceFormat.from(value);
		}
	} else {

		if(value === "$4,000"){
			spanPrice.innerHTML = 'any';
			inputPrice.value = '';
		}
		else{
			spanPrice.innerHTML = value;
			inputPrice.value = priceFormat.from(value);
		}
	}
});

inputPrice.addEventListener('change', function(){
	priceSlider.noUiSlider.set([null, this.value]);
});

inputPriceMax.addEventListener('change', function(){
	priceSlider.noUiSlider.set([null, this.value]);
});

// Year
var currentTime = new Date();
var yearSlider = document.getElementById('year');

var GetinputYear = "<?php echo $GetinputYear;?>";
GetinputYear = GetinputYear.trim();
var GetinputYearMax = "<?php echo $GetinputYearMax; ?>";
GetinputYearMax = GetinputYearMax.trim();

noUiSlider.create(yearSlider, {
	start: [ GetinputYear, GetinputYearMax ],
	connect: true,
	step: 1,
	range: {
		'min': 1959,
		'max': currentTime.getFullYear()
	},
	format: wNumb({
		decimals: 0
	})
});

var inputYearMin = document.getElementById('input-year');
var inputYearMax = document.getElementById('input-year-max');
var spanYearMin = document.getElementById('span-year');
var spanYearMax = document.getElementById('span-year-max');

yearSlider.noUiSlider.on('update', function( values, handle ) {

	var value = values[handle];

	if ( handle ) {
		if(value === '2021'){
			spanYearMax.innerHTML = 'any';
			inputYearMax.value = '';
		}
		else{
			spanYearMax.innerHTML = value;
			inputYearMax.value = value;
		}
	} else {
		inputYearMin.value = value;
		if(value === '1959'){
			spanYearMin.innerHTML = 'any';
			inputYearMin.value = '';
		}
		else{
			spanYearMin.innerHTML = value;
			inputYearMin.value = value;
		}
	}
});

inputYearMin.addEventListener('change', function(){
	yearSlider.noUiSlider.set([null, this.value]);
});

inputYearMax.addEventListener('change', function(){
	yearSlider.noUiSlider.set([null, this.value]);
});
</script>
<?php
}else{
  echo "It seems plugin not activated OR Api key is not configured.";
}
?>
<?php  //get_footer(); ?>