var ifcf7_utilities = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    fix_fields: function(element){
		if('undefined' !== typeof(ifcf7_autoresize)){
			ifcf7_autoresize.init_fields(element);
		}
		if('undefined' !== typeof(bsCustomFileInput)){
			bsCustomFileInput.init();
		}
		if('undefined' !== typeof(ifcf7_conditional_fields)){
			ifcf7_conditional_fields.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_file)){
			ifcf7_file.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_floating_labels)){
			ifcf7_floating_labels.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_intl_tel_input)){
			ifcf7_intl_tel_input.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_intl_tel_input_floating_labels)){
			ifcf7_intl_tel_input_floating_labels.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_iti)){
            ifcf7_iti.init_fields(element);
        }
        if('undefined' !== typeof(ifcf7_iti_floating_labels)){
            ifcf7_iti_floating_labels.init_fields(element);
        }
		if('undefined' !== typeof(ifcf7_login_signup)){
            ifcf7_login_signup.init_fields(element);
        }
		if('undefined' !== typeof(ifcf7_pay)){
			ifcf7_pay.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_select2)){
			ifcf7_select2.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_select2_floating_labels)){
			ifcf7_select2_floating_labels.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_tinymce)){
			ifcf7_tinymce.init_fields(element);
		}
		if('undefined' !== typeof(ifcf7_uploaded_files)){
			ifcf7_uploaded_files.init_fields(element);
		}
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_form: function(element){
		jQuery(element).on({
			wpcf7mailsent: ifcf7_utilities.wpcf7mailsent,
			wpcf7reset: ifcf7_utilities.wpcf7reset,
            wpcf7submit: ifcf7_utilities.wpcf7submit,
		});
		jQuery(element).on('keydown', '*', function(event){
			event.stopPropagation();
			switch(event.which){
				case 13: // Enter
					if(!jQuery(this).is('textarea')){
						event.preventDefault();
					}
					break;
				case 32: // Space character
					if(jQuery(this).is('.wpcf7-submit')){
						event.preventDefault();
					}
					break;
			}
		});
		jQuery(element).find('.wpcf7-submit').on('click', function(){
			jQuery(this).addClass('disabled');
		});
		jQuery(element).find('.wpcf7-response-output').attr('role', 'alert');
		jQuery(element).find('.wpcf7-radio:not(.wpcf7-validates-as-required)').each(function(index, element){
			if(jQuery(element).find('.custom-control-input:checked').length){
				jQuery(element).attr('data-current-value', jQuery(element).find('.custom-control-input:checked').val());
			} else {
				jQuery(element).attr('data-current-value', '');
			}
			jQuery(element).find('.custom-control-input').on({
				change: function(){
					if(jQuery(this).prop('checked')){
						jQuery(this).parents('.wpcf7-radio').attr('data-current-value', jQuery(this).val());
					} else {
						jQuery(this).parents('.wpcf7-radio').attr('data-current-value', '');
					}
				},
				click: function(){
					if(jQuery(this).parents('.wpcf7-radio').attr('data-current-value') === jQuery(this).val()){
						jQuery(this).prop('checked', false).trigger('change').blur();
					}
				},
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_utilities.init_form(this);
			});
			$(document).on('wpcf7reset', '.wpcf7-form', function(event){
				ifcf7_utilities.fix_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	redirect_url: '',

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	submit_value: '',

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    wpcf7mailsent: function(event){
		ifcf7_utilities.redirect_url = event.detail.apiResponse.ifcf7.redirect_url;
		jQuery(event.target).find('.ifcf7_response_output').text(event.detail.apiResponse.message);
		if(jQuery(event.target).hasClass('ifcf7_button')){
			ifcf7_utilities.submit_value = jQuery(event.target).find('.ifcf7-submit-value').text();
			jQuery(event.target).find('.ifcf7-submit-value').text(event.detail.apiResponse.message);
		}
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    wpcf7reset: function(event){
		if('' === ifcf7_utilities.redirect_url){
			jQuery(event.target).find('.wpcf7-submit').removeClass('disabled');
			if(jQuery(event.target).hasClass('ifcf7_button')){
				jQuery(event.target).find('.ifcf7-submit-value').text(ifcf7_utilities.submit_value);
			}
		} else {
			jQuery(event.target).addClass('ifcf7_redirecting');
			jQuery(event.target).find('.ifcf7_response_output').prepend('<span class="float-right ifcf7-spinner ml-2"><span></span></span>');
            setTimeout(function(){
                jQuery(location).attr('href', ifcf7_utilities.redirect_url);
            }, 200);
		}
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    wpcf7submit: function(event){
		if('mail_sent' === event.detail.apiResponse.status){
			return;
		}
		jQuery(event.target).find('.wpcf7-submit').removeClass('disabled');
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
