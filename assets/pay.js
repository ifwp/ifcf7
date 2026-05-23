var ifcf7_pay = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('input[name="ifcf7_cc_cvv"]').inputmask({
			clearIncomplete: true,
			oncomplete: function(){
				if(Inputmask.isValid(jQuery(this).val(), {
					regex: '[0-9]{3}',
				})){
					jQuery(this).blur();
				}
			},
			regex: '[0-9]{3}',
		});
		jQuery(element).find('input[name="ifcf7_cc_exp"]').inputmask({
			clearIncomplete: true,
			oncomplete: function(){
				if(Inputmask.isValid(jQuery(this).val(), {
					regex: '(0[1-9]|1[012])/[0-9]{2}',
				})){
					if(!jQuery(element).find('input[name="ifcf7_cc_cvv"]').val()){
						jQuery(element).find('input[name="ifcf7_cc_cvv"]').focus();
					} else {
						jQuery(this).blur();
					}
				}
			},
			regex: '(0[1-9]|1[012])/[0-9]{2}',
		});
		jQuery(element).find('input[name="ifcf7_cc_num"]').inputmask({
			clearIncomplete: true,
			oncomplete: function(){
				if(Inputmask.isValid(jQuery(this).val(), {
					regex: '([12456789][0-9]{3} [0-9]{4} [0-9]{4} [0-9]{4}|3[47][0-9]{2} [0-9]{6} [0-9]{5}|3[01235689][0-9]{2} [0-9]{6} [0-9]{4})',
				})){
					if(!jQuery(element).find('input[name="ifcf7_cc_exp"]').val()){
						jQuery(element).find('input[name="ifcf7_cc_exp"]').focus();
					} else if(!jQuery(element).find('input[name="ifcf7_cc_cvv"]').val()){
						jQuery(element).find('input[name="ifcf7_cc_cvv"]').focus();
					} else {
						jQuery(this).blur();
					}
				}
			},
			regex: '([12456789][0-9]{3} [0-9]{4} [0-9]{4} [0-9]{4}|3[47][0-9]{2} [0-9]{6} [0-9]{5}|3[01235689][0-9]{2} [0-9]{6} [0-9]{4})',
		}).on('change', function(){
			var value = jQuery(this).val().substr(0, 2);
			jQuery('input[name="ifcf7_cc_cvv"]').inputmask({
				clearIncomplete: true,
				oncomplete: function(){
					if(Inputmask.isValid(jQuery(this).val(), {
						regex: (34 == value || 37 == value) ? '[0-9]{4}' : '[0-9]{3}',
					})){
						jQuery(this).blur();
					}
				},
				regex: (34 == value || 37 == value) ? '[0-9]{4}' : '[0-9]{3}',
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_pay.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
