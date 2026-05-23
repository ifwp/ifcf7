var ifcf7_file = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	add_id: function(element, file_id){
		var hidden = jQuery(element).parents('.ifcf7_file_upload').find('.ifcf7-file-hidden');
		var current_val = hidden.val();
		if('' === current_val){
			current_val = '[]';
		}
		current_val = jQuery.parseJSON(current_val);
		var index = current_val.indexOf(parseInt(file_id));
		if(-1 === index){
			current_val.push(parseInt(file_id));
			hidden.val(JSON.stringify(current_val)).trigger('change');
			return true;
		} else {
			return false;
		}
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	//delete: function(element, file){
	delete: function(element){
		var parent = jQuery(element).parent();
		var file_id = parent.data('ifcf7_file_id');
		var file_name = '';
		var file_saved = false;
		var input_element = parent.parent('.ifcf7_file_upload').children('label').children('input');
		if(parent.children('.ifcf7_file_name').children('a').length){
			file_name = parent.children('.ifcf7_file_name').children('a').text();
			file_saved = true;
		} else {
			file_name = parent.children('.ifcf7_file_name').text();
			file_saved = false;
		}
		parent.html('Deleting...');
        jQuery.ajax({
			beforeSend: function(xhr){
				xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
			},
			method: 'DELETE',
			url: wpApiSettings.root + 'wp/v2/media/' + file_id + '?force=true',
		}).done(function(response){
			ifcf7_file.remove_id(parent, file_id);
			parent.remove();
			//console.log(response);
		}).fail(function(response){
			if(file_saved){
				//parent.addClass('text-danger').html(file_name);
				alert(response.responseJSON.message);
				ifcf7_file.remove_id(parent, file_id);
				parent.remove();
			} else {
				alert(response.responseJSON.message);
				ifcf7_file.remove_id(parent, file_id);
				parent.remove();
				/*parent.addClass('text-danger').html(file_name);
				alert(response.responseJSON.message);*/
			}
			//console.log(response);
		}).always(function(response){
			//console.log(response);
			/*if(0 === ifcf7_file.get_ids(jQuery(element).parent().next('label').children('input')[0]).length){
				jQuery(element).parent().next('label').addClass('d-flex').removeClass('d-none');
			}*/
			if(0 === ifcf7_file.get_ids(input_element).length){
				jQuery(input_element).parent('label').addClass('d-flex').removeClass('d-none');
			}
		});
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	get_ids: function(element){
		var hidden = jQuery(element).parents('.ifcf7_file_upload').find('.ifcf7-file-hidden');
		var current_val = hidden.val();
		if('' === current_val){
			current_val = '[]';
		}
		current_val = jQuery.parseJSON(current_val);
		return current_val;
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	handle_delete: function(element){
		var parent = jQuery(element).parent();
		var file_name = '';
		if(parent.children('.ifcf7_file_name').children('a').length){
			file_name = parent.children('.ifcf7_file_name').children('a').text();
			if(confirm('Are you sure you want to delete ' + file_name + '? This action cannot be undone.')){
				ifcf7_file.delete(element);
			}
		} else {
			//file_name = parent.children('.ifcf7_file_name').text();
			ifcf7_file.delete(element);
		}
		//ifcf7_file.delete(element, response);
		/*if(confirm('Are you sure you want to delete ' + file_name + '? This action cannot be undone.')){
			ifcf7_file.delete(element);
		}*/
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	handle_upload: function(element){
        if(0 === element.files.length){
            return;
        }
        jQuery(element).attr('disabled', 'disabled');
		jQuery(element).parent('label').addClass('text-muted').removeClass('text-primary');
        jQuery.each(element.files, function(index, file){
            ifcf7_file.upload(element, file);
        });
		jQuery(element).val('').trigger('change').removeAttr('disabled');
		jQuery(element).parent('label').addClass('text-primary').removeClass('text-muted');
		if(!jQuery(element).hasClass('ifcf7_multiple')){
			jQuery(element).parent('label').addClass('d-none').removeClass('d-flex');
		}
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
        jQuery(element).find('.ifcf7_file_upload').each(function(index, element){
            jQuery(element).find('input[type="file"]').on('change', function(event){
                event.preventDefault();
                ifcf7_file.handle_upload(this);
            });
			jQuery(element).find('.ifcf7_file_delete').on('click', function(event){
				event.preventDefault();
				ifcf7_file.handle_delete(this);
			});
        });
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
        jQuery(function($){
			$('.wpcf7-form').each(function(index, element){
				ifcf7_file.init_fields(element);
			});
		});
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	remove_id: function(element, file_id){
		var hidden = jQuery(element).parents('.ifcf7_file_upload').find('.ifcf7-file-hidden');
		var current_val = hidden.val();
		if('' === current_val){
			current_val = '[]';
		}
		current_val = jQuery.parseJSON(current_val);
		var index = current_val.indexOf(parseInt(file_id));
		if(-1 !== index){
			current_val.splice(index, 1);
			hidden.val(JSON.stringify(current_val)).trigger('change');
			return true;
		} else {
			return false;
		}
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	upload: function(element, file){
        var fileData = jQuery('<span>'), formData = new FormData();
        	formData.append('file', file);
        	formData.append('title', file.name);
		fileData.addClass('ifcf7_file_container border rounded py-2 px-3 d-flex align-items-center mb-2 mr-2').html('<span class="ifcf7_file_name text-truncate">' + file.name + '</span>').prependTo(jQuery(element).parents('.ifcf7_file_upload'));
        jQuery.ajax({
			beforeSend: function(xhr){
				xhr.setRequestHeader('Content-Disposition', 'attachment; filename="' + file.name + '"');
				xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
			},
		    contentType: false,
			data: formData,
			enctype: 'multipart/form-data',
			method: 'POST',
			processData: false,
			url: wpApiSettings.root + 'wp/v2/media',
			xhr: function(){
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(event){
                    if(event.lengthComputable){
                        var percent_complete = (event.loaded / event.total) * 100;
						fileData.css('background', 'linear-gradient(90deg, rgba(255,255,255,1) 0%, rgba(255,255,255,1) ' + Math.ceil(percent_complete) + '%, rgba(248,249,250,1) ' + Math.ceil(percent_complete) + '%, rgba(248,249,250,1) 100%)');
                        //console.log(Math.ceil(percent_complete));
						if(100 === Math.ceil(percent_complete)){
							/*if(0 === fileData.children('.wpcf7-spinner-container').length){
								fileData.append('<span class="wpcf7-spinner-container"><span class="bg-secondary d-block ml-2 mr-0 visible wpcf7-spinner"></span></span>');
							}*/
							if(0 === fileData.children('.ifcf7-spinner').length){
								fileData.append('<span class="ifcf7-spinner ml-2"><span class="bg-secondary"></span></span>');
							}
						}
                    }
                }, false);
                return xhr;
            },
		}).done(function(response){
			var fileDelete = jQuery('<span>');
			fileData.attr('data-ifcf7_file_id', response.id);
			//fileData.children('.ifcf7_file_name').html('<a href="' + response.link + '" target="_blank">' + response.title.rendered + '</a>');
			//fileData.prepend('<span class="ifcf7_file_delete dashicons dashicons-no text-danger mr-1" style="cursor: pointer;"></span>');
			fileDelete.addClass('ifcf7_file_delete dashicons dashicons-no text-danger mr-1').css('cursor', 'pointer').attr('title', 'Delete').prependTo(fileData);
			fileDelete.on('click', function(event){
				event.preventDefault();
				ifcf7_file.handle_delete(this);
				/*if(confirm('Are you sure you want to delete ' + response.title.raw + '? This action cannot be undone.')){
					ifcf7_file.delete(this, response);
				}*/
			});
			//console.log(response);
			ifcf7_file.add_id(element, response.id);
		}).fail(function(response){
			fileData.children('span').addClass('text-danger');
			alert(response.responseJSON.message);
			//console.log(response);
		}).always(function(response){
			/*if(0 !== fileData.children('.wpcf7-spinner-container').length){
				fileData.children('.wpcf7-spinner-container').remove();
			}*/
			if(0 !== fileData.children('.ifcf7-spinner').length){
				fileData.children('.ifcf7-spinner').remove();
			}
			// validate
			/*if(0 === element.files.length){
				jQuery(element).parent('label').addClass('d-flex').removeClass('d-none');
			}*/
			if(0 === ifcf7_file.get_ids(element).length){
				jQuery(element).parent('label').addClass('d-flex').removeClass('d-none');
			}
			/*if(0 === element.files.length){
				if(!jQuery(element).hasClass('ifcf7_multiple')){
					jQuery(element).parent('label').addClass('d-none').removeClass('d-flex');
				}
			}*/
		});
    },

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
