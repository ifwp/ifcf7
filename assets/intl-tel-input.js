var ifcf7_intl_tel_input = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('.ifcf7-intl-tel-input').each(function(){
			jQuery(this).intlTelInput({
				preferredCountries: ifcf7_intl_tel_input.preferred_countries,
				utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.16/js/utils.js',
			});
		});
		jQuery(element).find('.ifcf7-intl-tel-input').on('blur countrychange focus input propertychange', function(){ // jQuery(element).find('.ifcf7-intl-tel-input').on('countrychange input propertychange', function(){
			//var phone_number = jQuery(this).intlTelInput('getNumber');
			var phoneData = {
				countryData: {},
				numberFormat: {},
				numberType: -1,
			};
			if(jQuery(this).intlTelInput('isValidNumber')){
				phoneData.countryData = jQuery(this).intlTelInput('getSelectedCountryData');
				phoneData.numberFormat.e164 = jQuery(this).intlTelInput('getNumber', intlTelInputUtils.numberFormat.E164);
				phoneData.numberFormat.international = jQuery(this).intlTelInput('getNumber', intlTelInputUtils.numberFormat.INTERNATIONAL);
				phoneData.numberFormat.national = jQuery(this).intlTelInput('getNumber', intlTelInputUtils.numberFormat.NATIONAL);
				phoneData.numberFormat.rfc3966 = jQuery(this).intlTelInput('getNumber', intlTelInputUtils.numberFormat.RFC3966);
				phoneData.numberType = jQuery(this).intlTelInput('getNumberType');
				//console.log(phoneData);
				/*console.log(jQuery(this).intlTelInput('getNumber', intlTelInputUtils.numberFormat.E164));
				console.log(jQuery(this).intlTelInput('getNumber', intlTelInputUtils.numberFormat.INTERNATIONAL));
				console.log(jQuery(this).intlTelInput('getNumber', intlTelInputUtils.numberFormat.NATIONAL));
				console.log(jQuery(this).intlTelInput('getNumber', intlTelInputUtils.numberFormat.RFC3966));
				console.log(jQuery(this).intlTelInput('getNumberType'));
				console.log(jQuery(this).intlTelInput('getSelectedCountryData'));*/
				//jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-intl-tel-input-hidden').val(jQuery(this).intlTelInput('getNumber'));
				jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-intl-tel-input-hidden').val(phoneData.numberFormat.e164);
				jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-intl-tel-input-json').val(JSON.stringify(phoneData));
			} else {
				jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-intl-tel-input-hidden').val('');
				jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-intl-tel-input-json').val('');
			}
			//jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-intl-tel-input-hidden').val(phone_number);
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_intl_tel_input.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	preferred_countries: [],

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
