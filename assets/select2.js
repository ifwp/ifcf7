var ifcf7_select2 = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('select.form-control.ifcf7-select2').each(function(){
			var cssClass = '', placeholderValue = '';
			if(jQuery(this).children('option[value=""]').length){
				cssClass = jQuery(this).attr('class').split(/\s+/).filter(function(currentValue, index, arr){
					return arr.indexOf(currentValue) === index && currentValue.indexOf('form-control') === 0;
				}).join(' ');
				placeholderValue = jQuery(this).children('option[value=""]').first().text();
				jQuery(this).select2({
					allowClear: true,
					containerCssClass: cssClass,
					dropdownParent: jQuery(this).parent(),
					placeholder: placeholderValue,
					theme: 'bootstrap',
				});
			}
		});
		jQuery(element).find('.wpcf7-submit').on('click', function(){
			jQuery(element).find('select.form-control.ifcf7-select2').each(function(){
				jQuery(this).next('.select2-container').removeClass('is-invalid');
			});
		});
		jQuery(element).on('wpcf7invalid', function(event){
			jQuery.each(event.detail.apiResponse.invalid_fields, function(index, value){
				jQuery(element).find(value.into).each(function(){
					jQuery(this).children('.select2-container').addClass('is-invalid');
				});
			});
    	});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_select2.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
