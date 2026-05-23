var ifcf7_iti_floating_labels = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	countrychange: function(tel){
		jQuery(tel).parent('.iti').parent('.ifcf7-iti-floating-labels').children('label').css('padding-left', jQuery(tel).css('padding-left'));
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('.ifcf7-iti-floating-labels > .iti > input').each(function(){
			if(!jQuery(this).hasClass('ifcf7-iti-no-dial-code')){
				ifcf7_iti_floating_labels.countrychange(this);
				jQuery(this).on('countrychange', function(){
					ifcf7_iti_floating_labels.countrychange(this);
				});
			}
			ifcf7_iti_floating_labels.tel(this);
			jQuery(this).on('blur countrychange focus input propertychange', function(){
				ifcf7_iti_floating_labels.tel(this);
			});
			jQuery(this).on('focus', function(){
				jQuery(this).parent('.iti').addClass('placeholder-hidden');
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_iti_floating_labels.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	tel: function(tel){
		if(jQuery(tel).is(':focus')){
			jQuery(tel).parent('.iti').addClass('placeholder-hidden');
		} else {
			if(jQuery(tel).val() === ''){
				jQuery(tel).parent('.iti').removeClass('placeholder-hidden');
			} else {
				jQuery(tel).parent('.iti').addClass('placeholder-hidden');
			}
		}
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
