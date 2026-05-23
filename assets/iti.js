var ifcf7_iti = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('.ifcf7-iti').each(function(){
			var $this = jQuery(this);
			jQuery(this).intlTelInput({
				autoPlaceholder: 'aggressive',
				customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData){
					$this.inputmask({
						mask: selectedCountryPlaceholder.replace(/[0-9]/g, '9').replace(/[^9\(\)]/g, ' ').replace(/\s+/g, ' ').trim(),
						showMaskOnHover: false,
					});
					return '';
				},
				preferredCountries: ifcf7_intl_tel_input.preferred_countries,
				separateDialCode: !jQuery(this).hasClass('ifcf7-iti-no-dial-code'),
				utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.16/js/utils.js',
			});
		});
		jQuery(element).find('.ifcf7-iti').on('countrychange', function(){
			jQuery(this).val('');
		});
		jQuery(element).find('.ifcf7-iti').on('blur countrychange focus input propertychange', function(){
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
				jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-iti-hidden').val(phoneData.numberFormat.e164).trigger('change');
				jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-iti-json').val(JSON.stringify(phoneData)).trigger('change');
			} else {
				jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-iti-hidden').val('').trigger('change');
				jQuery(this).parents('.wpcf7-form-control-wrap').find('.ifcf7-iti-json').val('').trigger('change');
			}
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_iti.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	preferred_countries: [],

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
