var ifcf7_intl_tel_input_floating_labels = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('.ifcf7-intl-tel-input-floating-labels > .iti > input').each(function(){
			ifcf7_intl_tel_input_floating_labels.tel(this);
			jQuery(this).on('blur countrychange focus input propertychange', function(){ // jQuery(this).on('input propertychange', function(){
				ifcf7_intl_tel_input_floating_labels.tel(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_intl_tel_input_floating_labels.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	tel: function(tel){
		if(jQuery(tel).val() === ''){
			jQuery(tel).parent('.iti').removeClass('placeholder-hidden');
		} else {
			jQuery(tel).parent('.iti').addClass('placeholder-hidden');
		}
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
