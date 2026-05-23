var ifcf7_tinymce = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		jQuery(element).find('.ifcf7-tinymce').each(function(){
			var editor = null, placeholderValue = '', readonly = false;
			if(jQuery(this).attr('placeholder')){
				placeholderValue = jQuery(this).attr('placeholder');
			}
			editor = jQuery(this).tinymce();
			readonly = jQuery(this).prop('readonly');
			if('undefined' !== typeof(editor)){ // v6
				if(null !== editor){ // v5
					editor.remove();
				}
			}
			jQuery(this).tinymce({
				autoresize_bottom_margin: 0,
				branding: false,
				content_style: 'body { font-size: 16px; }',
				//icons: 'bootstrap',
				deprecation_warnings: false,
				menubar: false,
				min_height: 133,
				mobile: {
					toolbar_mode: 'floating',
				},
				placeholder: placeholderValue,
				plugins: ifcf7_tinymce_object.plugins,
				readonly: readonly,
				setup: function(editor){
					editor.on('blur', function(e){
						jQuery(editor.getContainer()).removeClass('focused');
					});
					editor.on('change', function(){
						editor.save();
					});
					editor.on('focus', function(e){
						jQuery(editor.getContainer()).addClass('focused');
					});
					editor.on('init', function(e){
						jQuery(editor.getContainer()).parent().removeClass('d-none').next().addClass('d-none');
					});
				},
				//skin: 'bootstrap',
				statusbar: false,
				toolbar: readonly ? false : ifcf7_tinymce_object.toolbar,
				toolbar_groups: ifcf7_tinymce_object.toolbar_groups,
				toolbar_location: 'bottom',
				toolbar_mode: 'floating',
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_tinymce.init_fields(this);
			});
			$(document).on('focusin', function(e){
				if($(e.target).closest('.tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root').length){
					e.stopImmediatePropagation();
				}
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
