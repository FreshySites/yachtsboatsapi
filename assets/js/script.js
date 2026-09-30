jQuery(document).on('click', '#queries_data', function(e){
	e.preventDefault();
	e.stopImmediatePropagation();

	jQuery('input[name="full_name"]').css('border-color', '#cccccc');
	jQuery('input[name="email"]').css('border-color', '#cccccc');
	jQuery('input[name="phone"]').css('border-color', '#cccccc');

	var full_name = jQuery('input[name="full_name"]').val();
	var email = jQuery('input[name="email"]').val();
	var phone = jQuery('input[name="phone"]').val();

	if(full_name && email && phone){
		jQuery('.response_msg').remove();
		var datastring = jQuery("#queries_form").serializeArray();
		jQuery.ajax({
		    type: "POST",
		    url: yacht_ajax_object.ajax_url,
		    data: { action:'saveQueriesFormData', data:datastring },
		    // dataType: "json",
		    success: function(data) {
		        console.log(data);
		        if(jQuery('.form-rowfull').hasClass('response_msg')){
		        	jQuery('.response_msg').text(data);
		        }else{
			        jQuery('<div class="form-rowfull response_msg"><p>'+data+'</p></div>').insertAfter('#queries_form .popup-image');
		        }
		    },
		    error: function() {
                if(jQuery('.form-rowfull').hasClass('response_msg')){
                	jQuery('.response_msg').text('Something went wrong! Please try again.');
                }else{
        	        jQuery('<div class="form-rowfull response_msg error"><p>Something went wrong! Please try again.</p></div>').insertAfter('#queries_form .popup-image');
                }
		    }
		});
		return false;
	}else{
		if(full_name==''){
			jQuery('input[name="full_name"]').css('border-color', 'red');
		}

		if(email==''){
			jQuery('input[name="email"]').css('border-color', 'red');
		}

		if(phone==''){
			jQuery('input[name="phone"]').css('border-color', 'red');
		}

        if(jQuery('.form-rowfull').hasClass('response_msg')){
        	jQuery('.response_msg').text('Please fill required fields.');
        }else{
	        jQuery('<div class="form-rowfull response_msg error"><p>Please fill required fields.</p></div>').insertAfter('#queries_form .popup-image');
        }
	}
});