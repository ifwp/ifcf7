var ifcf7_load_form = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$(document).on('click', '.ifcf7-load_form', function(e){
				e.preventDefault();
                var data = {};
    			$.each(this.attributes, function(index, attribute){
    				if(attribute.name.indexOf('data-ifcf7-') > -1 && '' !== attribute.value){
    					data[attribute.name.replace('data-ifcf7-', '')] = attribute.value;
    				}
    			});
				if('undefined' !== typeof(data.form_id)){
                    var title = 'Untitled';
                    if('undefined' !== typeof(data.title)){
                        if('' !== data.title){
                            title = data.title;
                        }
                        delete data.title;
                    }
					ifcf7.load_form(data);
					$('#ifcf7-modal_label').text(title);
					if('undefined' !== typeof(data.modal_size)){
						var modal_sizes = ['sm', 'lg', 'xl'];
						$('#ifcf7-modal > .modal-dialog').removeClass('modal-sm modal-lg modal-xl');
                        if(modal_sizes.includes(data.modal_size)){
                            $('#ifcf7-modal > .modal-dialog').addClass('modal-' + data.modal_size);
                        }
                        delete data.modal_size;
                    }
					$('#ifcf7-modal').modal('show');
				}
			});
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load_form: function(args){
        if('object' !== typeof(args) || null === args){
            return false;
        }
        if('undefined' === typeof(args.form_id) || 0 >= parseInt(args.form_id)){
            return false;
        }
        var target = '#ifcf7-modal_container';
        if('undefined' !== typeof(args.target)){
            if(jQuery(args.target).length){
                target = args.target;
            }
            delete args.target;
        }
        var url = ifcf7_load_form_object.ajaxurl;
        args.action = 'ifcf7_load_form';
        url = ifwp.add_query_args(ifwp.get_query_args(), url);
        jQuery(target).html('<span class="ifcf7-spinner"><span></span></span>');
        jQuery.post(url, args, function(response){
            jQuery(target).html(response);
            if(jQuery(target + ' .wpcf7-form').length){
                var $form = jQuery(target + ' .wpcf7-form').eq(0);
                wpcf7.init($form[0]); // register all the necessary events on the Contact Form 7 form
                if('undefined' !== typeof(wpcf7cf)){
                    wpcf7cf.initForm($form); // register all the necessary events for the conditional logic to work
                }
                ifcf7_utilities.init_form($form[0]); // register all the necessary events for IFCF7
                ifcf7_utilities.fix_fields($form[0]); // register all the necessary fixes for IFCF7
                //ifcf7_load_form.init_form($form[0]);
                //jQuery(target).trigger('ifcf7_form_loaded', args);
            } else {
                //jQuery(target).trigger('ifcf7_form_not_loaded', args);
            }
        });
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
