jQuery(document).on('click', '#import_yachts_boats', function(e){
	e.preventDefault();
	e.stopImmediatePropagation();
	e.stopPropagation();
	var loader = jQuery(this).closest("td").find('.loadersmall');
	loader.css('display', 'block');
	jQuery('#message').css('display', 'none');
	jQuery('#message .response_msg').text('');
	let adminUrl = window.location.href.split('wp-admin')[0]+'wp-admin/admin-ajax.php';
	jQuery.ajax({
        type:"POST",
        url: adminUrl,		//'/wp-admin/admin-ajax.php',
        data:{ action: 'importYachtsBoats' },
        success:function(res){
            loader.css('display', 'none');
            jQuery('#message').css('display', 'block');
            jQuery('#message .response_msg').text(res);
        }
    });
});

// jQuery(document).on('click', '#validate_activation_key', function(e){
// 	e.preventDefault();
// 	e.stopImmediatePropagation();
// 	e.stopPropagation();
	
// 	if(jQuery("#yacht_plugin_activation_key").val().length < 1 ) return false;

// 	var loader = jQuery(this).closest("td").find('.loadersmall');
// 	loader.css('display', 'block');
// 	jQuery('#message').css('display', 'none');
// 	jQuery('#message .response_msg').text('');
// 	let adminUrl = window.location.href.split('wp-admin')[0]+'wp-admin/admin-ajax.php';
// 	jQuery.ajax({
//         type:"POST",
//         url: adminUrl,		//'/wp-admin/admin-ajax.php',
//         data:{ action: 'validateActivationKey', key: jQuery("#yacht_plugin_activation_key").val() },
//         success:function(res){
//             loader.css('display', 'none');
//             // jQuery('#message').css('display', 'block');
//             // jQuery('#message .response_msg').text(res);
//         }
//     });
// });

jQuery(document).ready(function($){
	var custom_uploader
	, click_elem = jQuery('.wpse-228085-upload')
	, target = jQuery('#hero_image')

	click_elem.click(function(e) {
		e.preventDefault();
		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		//Extend the wp.media object
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});
		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			console.log(attachment.url);
			target.val(attachment.url);
		});
		//Open the uploader dialog
		custom_uploader.open();
	});    
});
