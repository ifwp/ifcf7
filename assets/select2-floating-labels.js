var ifcf7_select2_floating_labels = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('select.form-control.ifcf7-select2.ifcf7-select2-floating-labels').each(function(){
			var placeholderValue = '';
			if(!jQuery(this).prop('multiple')){
				if(jQuery(this).children('option[value=""]').length){
					if(jQuery(this).next('.select2').hasClass('select2-container--bootstrap')){
						placeholderValue = jQuery(this).children('option[value=""]').first().text();
						jQuery(this).next('.select2').addClass('ifcf7-select2-floating-labels-container').children('.selection').append('<label>' + placeholderValue + '</label>');
						ifcf7_select2_floating_labels.select(this);
						jQuery(this).on('change', function(){
							ifcf7_select2_floating_labels.select(this);
						});
					}
				}
			}
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_select2_floating_labels.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	select: function(select){
		if(jQuery(select).val() === ''){
			jQuery(select).removeClass('placeholder-hidden');
		} else {
			jQuery(select).addClass('placeholder-hidden');
		}
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
