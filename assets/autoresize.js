var ifcf7_autoresize = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('textarea.autoresize').each(function(){
			jQuery(this).data({
				'border': jQuery(this).outerHeight() - jQuery(this).innerHeight(),
				'element': jQuery(this).height(),
				'padding': jQuery(this).innerHeight() - jQuery(this).height(),
			});
			ifcf7_autoresize.textarea(this);
		});
		jQuery(document).on(ifwp.page_visibility_event(), function(){
			jQuery(element).find('textarea.autoresize').each(function(){
				ifcf7_autoresize.textarea(this);
			});
		});
		jQuery(element).find('textarea.autoresize').on('input propertychange', function(){
            ifcf7_autoresize.textarea(this);
        });
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_autoresize.init_fields(this);
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    textarea: function(textarea){
		jQuery(textarea).height(parseInt(jQuery(textarea).data('element'))).height(textarea.scrollHeight - parseInt(jQuery(textarea).data('padding')));
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
