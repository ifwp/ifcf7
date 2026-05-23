var ifcf7_login_signup = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		if(!jQuery(element).hasClass('ifcf7_login_signup')){
		   return;
		}
		jQuery(element).on('wpcf7cf_change_step', function(e, previousStep, currentStep){
			jQuery(e.currentTarget).find('input[type="hidden"][name="email_exists"]').val('').trigger('change');
			if(2 !== currentStep){
		        return;
		    }
			var user_email = jQuery(e.currentTarget).find('input[type="email"][name="user_email"]').val();
			jQuery(e.currentTarget).find('.ifcf7_login_signup_loading').html('<span class="ifcf7-spinner mb-3"><span></span></span>');
			if('' !== user_email){
				ifcf7_login_signup.submit_disabled = jQuery(element).find('.wpcf7-submit').is(':disabled');
				if(!ifcf7_login_signup.submit_disabled){
					jQuery(element).find('.wpcf7-submit').addClass('disabled');
				}
                jQuery.ajax({
                    beforeSend: function(xhr){
                        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                    },
                    data: {
                        user_email: user_email,
                    },
                    method: 'POST',
                    url: wpApiSettings.root + 'ifcf7/v1/user-email-exists',
                }).done(function(response){
                    jQuery(e.currentTarget).on('wpcf7cf_show_group', ifcf7_login_signup.wpcf7cf_show_group);
                    jQuery(e.currentTarget).find('input[type="hidden"][name="email_exists"]').val(response.user_email_exists).trigger('change');
                }).fail(function(response){
                    jQuery(e.currentTarget).find('.ifcf7_login_signup_loading').html('<div class="alert alert-danger" role="alert">' + response.responseJSON.message + '</div>');
                });
                jQuery(e.currentTarget).find('.ifcf7_login_signup_user_email').text(user_email);
            } else {
                jQuery(e.currentTarget).find('.ifcf7_login_signup_user_email').text('@');
            }
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_login_signup.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	submit_disabled: false,

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    wpcf7cf_show_group: function(e){
        jQuery(e.currentTarget).find('.ifcf7_login_signup_loading').html('');
    	jQuery(e.currentTarget).off('wpcf7cf_show_group', ifcf7_login_signup.wpcf7cf_show_group);
		if(!ifcf7_login_signup.submit_disabled){
			jQuery(e.currentTarget).find('.wpcf7-submit').removeClass('disabled');
		}
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
