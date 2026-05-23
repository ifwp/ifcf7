var ifcf7_floating_labels = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('.ifcf7-floating-labels > select').each(function(){
			ifcf7_floating_labels.select(this);
		});
		jQuery(document).on(ifwp.page_visibility_event(), function(){
			jQuery(element).find('.ifcf7-floating-labels > select').each(function(){
				ifcf7_floating_labels.select(this);
			});
		});
		jQuery(element).find('.ifcf7-floating-labels > select').on('change', function(){
            ifcf7_floating_labels.select(this);
        });
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_floating_labels.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    select: function(select){
        if(jQuery(select).val() == ''){
            jQuery(select).removeClass('placeholder-hidden');
        } else {
            jQuery(select).addClass('placeholder-hidden');
        }
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
