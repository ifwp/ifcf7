<?php namespace IFCF7;

final class Storage {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('_wp_put_post_revision', [__CLASS__, 'wp_put_post_revision']);
		add_action('init', [__CLASS__, 'init']);
        add_action('rest_api_init', [__CLASS__, 'rest_api_init']);
        add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_action('wpcf7_mail_failed', [__CLASS__, 'wpcf7_mail_failed'], 20);
		add_action('wpcf7_mail_sent', [__CLASS__, 'wpcf7_mail_sent'], 20);
        add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 30, 5);
		add_filter('wpcf7_before_send_mail', [__CLASS__, 'wpcf7_before_send_mail'], 20, 3);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static $inserted_post_id = 0, $inserted_user_id = 0, $updated_post_id = 0, $updated_user_id = 0;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_post_excerpt($contact_form = null, $submission = null){
		$field = apply_filters('ifcf7_post_excerpt_field', 'post_excerpt', $contact_form, $submission);
		$excerpt = $submission->get_posted_data($field);
		if($excerpt){
			return $excerpt;
		}
        return '';
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_post_content($contact_form = null, $submission = null){
		$field = apply_filters('ifcf7_post_content_field', 'post_content', $contact_form, $submission);
		$content = $submission->get_posted_data($field);
		if($content){
			return $content;
		}
        return '';
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_post_status($contact_form = null, $submission = null){
		return ifcf7_get_post_status($contact_form);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_post_title($contact_form = null, $submission = null){
		$field = apply_filters('ifcf7_post_title_field', 'post_title', $contact_form, $submission);
		$title = $submission->get_posted_data($field);
		if($title){
			return $title;
		}
		return '';
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_post_type($contact_form = null){
        return ifcf7_get_post_type($contact_form);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_action($contact_form = null){
		return ('contact_form' === ifcf7_action($contact_form));
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function update_response($response = '', $status = ''){
        $post_id = 0;
		$user_id = 0;
		if(!empty(self::$inserted_post_id)){
			$post_id = self::$inserted_post_id;
		} elseif(!empty(self::$updated_post_id)){
			$post_id = self::$updated_post_id;
		} elseif(!empty(self::$inserted_user_id)){
			$user_id = self::$inserted_user_id;
		} elseif(!empty(self::$updated_user_id)){
			$user_id = self::$updated_user_id;
		}
        if(!empty($post_id)){
            update_post_meta($post_id, 'submission_response', $response);
            update_post_meta($post_id, 'submission_status', $status);
        } elseif(!empty($user_id)){
            update_user_meta($user_id, 'submission_response', $response);
            update_user_meta($user_id, 'submission_status', $status);
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function attachments_html($meta_type = '', $object_id = 0, $meta_key = '', $delete_action = false, $before_input = false){
		$html = '';
		if(!in_array($meta_type, ['post', 'user'])){
			return $html;
		}
		if(empty($object_id)){
			return $html;
		}
		$attachment_ids = get_metadata($meta_type, $object_id, 'uploaded_id_' . $meta_key);
		if(empty($attachment_ids)){
			$attachment_ids = get_metadata($meta_type, $object_id, $meta_key . '_id'); // backcompat
			if(empty($attachment_ids)){
				return $html;
			}
		}
		$html .= '<div class="ifcf7-attachments-container' . ($before_input ? ' ifcf7-before-input' : '') . '" data-ifcf7-key="' . $meta_key . '" data-ifcf7-id="' . $object_id . '" data-ifcf7-message="' . __('Deleting...') . '" data-ifcf7-type="' . $meta_type . '">';
		foreach($attachment_ids as $attachment_id){
			$metadata = wp_prepare_attachment_for_js($attachment_id);
			if(empty($metadata)){
				continue;
			}
			$html .= '<div class="align-items-center d-flex ifcf7-attachment-container" data-ifcf7-id="' . $attachment_id . '">';
			$url = $metadata['icon'];
			if('image' === $metadata['type']){
				if(!empty($metadata['sizes']['thumbnail'])){
					$url = $metadata['sizes']['thumbnail']['url'];
				} else {
					$url = $metadata['sizes']['full']['url'];
				}
			}
			$html .= '<div class="align-items-center border d-flex justify-content-center overflow-hidden p-1 rounded-sm" style="height: 50px; width: 50px; min-width: 50px;">';
			$html .= '<img src="' . $url . '" style="height: auto; max-height: 100%; max-width: 100%; width: auto;">';
			$html .= '</div>';
			$html .= '<div class="flex-grow-1 pl-2">';
			$html .= '<div><a class="ifcf7-attachment-link text-dark' . ('image' === $metadata['type'] ? ' ifcf7-attachment-image-link' : '') . '" href="' . $metadata['url'] . '" target="_blank">' . $metadata['name'] . '</a></div>';
			//$html .= '<div class="small text-muted">' . $metadata['filesizeHumanReadable'] . ' &bull; ' . $metadata['dateFormatted'];
			$html .= '<div class="small text-muted">' . $metadata['filesizeHumanReadable'];
			/*if($edit_action){
				if(!empty($metadata['editLink'])){
					$html .= ' &bull; <a href="' . $metadata['editLink'] . '" class="text-dark ifcf7-edit-attachment" target="_blank">' . __('Edit', 'fl-builder') . '</a>';
				}
			}*/
			if($delete_action){
				if(!empty($metadata['nonces']['delete'])){
					$html .= ' &bull; <a href="#" class="text-danger ifcf7-delete-attachment" data-ifcf7-nonce="' . $metadata['nonces']['delete'] . '">' . __('Delete', 'fl-builder') . '</a>';
				}
			}
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
		}
		$html .= '</div>';
		return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function form_tag_html($html, $tag, $type, $basetype, $html_orig){
		if('file' !== $type){
			return $html;
		}
        $contact_form = wpcf7_get_current_contact_form();
    	if(null === $contact_form){
    		return $html;
    	}
        $meta_type = '';
    	$object_id = 0;
    	$post_id = $contact_form->shortcode_attr('post_id');
    	if(!empty($post_id) and 'edit_post' === ifcf7_action($contact_form)){
    		$meta_type = 'post';
    		$object_id = Edit_Post::sanitize_post_id($post_id);
    	}
    	$user_id = $contact_form->shortcode_attr('user_id');
    	if(!empty($user_id) and 'edit_user' === ifcf7_action($contact_form)){
    		$meta_type = 'user';
    		$object_id = Edit_User::sanitize_user_id($user_id);
    	}
    	/*if(!in_array($meta_type, ['post', 'user'])){
    		return $html;
    	}*/
		if(!$meta_type or !$object_id){
    		return $html;
    	}
		$attachments_html = self::attachments_html($meta_type, $object_id, $tag->name, true, true);
        return $attachments_html . $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_inserted_post_id(){
		return self::$inserted_post_id;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_inserted_user_id(){
		return self::$inserted_user_id;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_updated_post_id(){
		return self::$updated_post_id;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_updated_user_id(){
		return self::$updated_user_id;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function init(){
        register_post_type('ifcf7-submission', [
            'labels' => ifwp_post_type_labels('Submitted message', 'Submitted messages', false),
            'show_in_admin_bar' => false,
            'show_in_menu' => 'wpcf7',
            'show_ui' => true,
            'supports' => ['custom-fields', 'title'],
        ]);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function store_submission($action = '', $meta_type = '', $object_id = 0, $contact_form = null, $submission = null){
		if(!in_array($action, ['insert', 'update'])){
			return ifwp_error(sprintf(__('Invalid parameter(s): %s'), 'action') . '.');
		}
		if(!in_array($meta_type, ['post', 'user'])){
			return ifwp_error(sprintf(__('Invalid parameter(s): %s'), 'meta_type') . '.');
		}
		if('post' === $meta_type){
            $post = get_post($object_id);
            if(empty($post)){
                return ifwp_error(__('Invalid post ID.'));
            }
		} elseif('user' === $meta_type){
            $user = get_userdata($object_id);
            if(empty($user)){
                return ifwp_error(__('Invalid user ID.'));
            }
		}
		if(null === $contact_form){
			$contact_form = wpcf7_get_current_contact_form();
		}
		if(null === $contact_form){
			return ifwp_error(__('The requested contact form was not found.', 'contact-form-7'));
		}
		if(null === $submission){
			$submission = \WPCF7_Submission::get_instance();
		}
		if(null === $submission){
			return ifwp_error(sprintf(__('%s (Invalid)'), 'WPCF7_Submission') . '.');
		}
        if('insert' === $action){
            if('post' === $meta_type){
                self::$inserted_post_id = $object_id;
            } elseif('user' === $meta_type){
                self::$inserted_user_id = $object_id;
            }
        } elseif('update' === $action){
            if('post' === $meta_type){
                self::$updated_post_id = $object_id;
            } elseif('user' === $meta_type){
                self::$updated_user_id = $object_id;
            }
        }
        if('post' === $meta_type){
            $the_post = wp_is_post_revision($object_id);
            if($the_post){
                $object_id = $the_post; // Make sure meta is added to the post, not a revision.
            }
        }
		if('insert' === $action){
            foreach(Helper::get_meta_data() as $key => $value){
                add_metadata($meta_type, $object_id, 'original_' . $key, $value, true);
            }
        } elseif('update' === $action){
            if('post' === $meta_type){
				$postarr = [
					'ID' => $object_id,
				];
				$content = self::get_post_content($contact_form, $submission);
				if($content){
					$postarr['post_content'] = $content;
				}
				$excerpt = self::get_post_excerpt($contact_form, $submission);
				if($excerpt){
					$postarr['post_excerpt'] = $excerpt;
				}
				$title = self::get_post_title($contact_form, $submission);
				if($title){
					$postarr['post_title'] = $title;
				}
				add_filter('wp_save_post_revision_post_has_changed', '__return_true');
				$post_id = wp_update_post($postarr, true);
				remove_filter('wp_save_post_revision_post_has_changed', '__return_true');
				if(is_wp_error($post_id)){
					self::$updated_post_id = 0;
					return $post_id;
				}
			} elseif('user' === $meta_type){
                $user_id = wp_update_user([
					'ID' => $object_id,
				]);
				if(is_wp_error($user_id)){
					self::$updated_user_id = 0;
					return $user_id;
				}
            }
        }
		foreach(Helper::get_meta_data() as $key => $value){
			update_metadata($meta_type, $object_id, $key, $value);
		}
		$posted_data = apply_filters('ifcf7_storage_data', $submission->get_posted_data(), $contact_form, $submission);
        foreach($posted_data as $key => $value){
            if(is_array($value)){
                delete_metadata($meta_type, $object_id, $key);
                foreach($value as $single){
                    add_metadata($meta_type, $object_id, $key, $single);
                }
            } else {
                update_metadata($meta_type, $object_id, $key, $value);
            }
        }
        if('post' === $meta_type){
            $post_id = $object_id;
        } else {
            $post_id = 0;
        }
		$path = apply_filters('ifcf7_upload_path', '', $contact_form, $submission);
		$uploaded_error = new \WP_Error;
        foreach($submission->uploaded_files() as $key => $value){
            foreach((array) $value as $single){
                $attachment_id = ifwp_upload($single, $post_id, $path);
                if(!is_wp_error($attachment_id)){
                    //add_metadata($meta_type, $object_id, $key . '_id', $attachment_id);
					add_metadata($meta_type, $object_id, 'uploaded_id_' . $key, $attachment_id);
					//add_metadata($meta_type, $object_id, 'uploaded_error_' . $key, '');
                } else {
					//add_metadata($meta_type, $object_id, 'uploaded_id_' . $key, 0);
					add_metadata($meta_type, $object_id, 'uploaded_error_' . $key, $attachment_id->get_error_message());
					$uploaded_error->merge_from($attachment_id);
				}
            }
            delete_metadata($meta_type, $object_id, $key); // hash_strings
        }
		if($uploaded_error->has_errors()){
			return $uploaded_error;
		}
        if('post' === $meta_type){
			// ifcf7_insert_post_{post_type}, ifcf7_update_post_{post_type}
            do_action('ifcf7_' . $action . '_post_' . $post->post_type, $object_id, $contact_form, $submission);
        } else {
            foreach($user->roles as $role){
				// ifcf7_insert_user_{role}, ifcf7_update_user_{role}
                do_action('ifcf7_' . $action . '_user_' . $role, $object_id, $contact_form, $submission);
            }
        }
		// ifcf7_insert_post, ifcf7_insert_user, ifcf7_update_post, ifcf7_update_user
		do_action('ifcf7_' . $action . '_' . $meta_type, $object_id, $contact_form, $submission);
		// ifcf7_insert_post_from_{contact_form_id}, ifcf7_insert_user_from_{contact_form_id}, ifcf7_update_post_from_{contact_form_id}, ifcf7_update_user_from_{contact_form_id}
		do_action('ifcf7_' . $action . '_' . $meta_type . '_from_' . $contact_form->id(), $object_id, $contact_form, $submission);
		do_action('ifcf7_store_submission', $action, $meta_type, $object_id, $contact_form, $submission);
		return true;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function rest_api_init(){
        register_rest_route('ifcf7/v1', '/uploaded-files/(?P<meta_type>post|user)/(?P<object_id>\d+)/(?P<meta_key>[\w-]+)/(?P<meta_value>\d+)', [
    		'callback' => function($request){
    			$meta_type = $request->get_param('meta_type');
    			$object_id = $request->get_param('object_id');
    			$meta_key = $request->get_param('meta_key');
    			$meta_value = $request->get_param('meta_value');
                $post = wp_delete_attachment($meta_value);
                if(empty($post)){
                    return [
        				'ifcf7_status' => 0,
        			];
                }
				delete_metadata($meta_type, $object_id, $meta_key . '_id', $meta_value); // backcompat
    			return [
    				'ifcf7_status' => (int) delete_metadata($meta_type, $object_id, 'uploaded_id_' . $meta_key, $meta_value),
    			];
    		},
    		'methods' => 'DELETE',
    		'permission_callback' => function($request){
    			$object_id = $request->get_param('object_id');
    			$meta_value = $request->get_param('meta_value');
    			$nonce = $request->get_param('ifcf7_nonce');
    			return (current_user_can('delete_post', $meta_value) and current_user_can('edit_post', $object_id) and wp_verify_nonce($nonce, 'delete-post_' . $meta_value));
    		},
    	]);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wp_put_post_revision($revision_id){
        if(!self::$updated_post_id){
			return;
		}
		$revision = get_post($revision_id);
		if(self::$updated_post_id !== (int) $revision->post_parent){
			return;
		}
		$metadata = get_post_meta($revision->post_parent);
		foreach($metadata as $key => $value){
			if('_' === substr($key, 0, 1)){
				continue;
			}
			foreach((array) $value as $single){
				add_metadata('post', $revision->ID, $key, $single);
			}
		}
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_before_send_mail($contact_form, &$abort, $submission){
		if(!self::is_action($contact_form)){
			return; // just contact forms
		}
		$save = ifcf7_meta_pref('ifcf7_save', $contact_form);
		if(!$save){
			$save = 'yes';
		}
        if('no' === $save or $contact_form->is_true('do_not_store')){
            return;
        }
        if(!$submission->is('init')){
            return; // prevent conflicts with actions and other plugins
        }
		$title = self::get_post_title($contact_form, $submission);
		if(!$title){
			$title = 'Submission ' . uniqid();
		}
		$name = sanitize_title($title);
		$post_id = wp_insert_post([
            'post_content' => self::get_post_content($contact_form, $submission),
			'post_excerpt' => self::get_post_excerpt($contact_form, $submission),
			'post_name' => $name,
			'post_status' => self::get_post_status($contact_form, $submission),
			'post_title' => $title,
			'post_type' => self::get_post_type($contact_form, $submission),
		], true);
        if(is_wp_error($post_id)){
            $abort = true; // prevent mail_sent and mail_failed actions
            $submission->set_response($post_id->get_error_message());
            $submission->set_status('aborted'); // try to prevent conflicts with actions and other plugins
            return;
        }
        self::store_submission('insert', 'post', $post_id, $contact_form, $submission);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
        wp_enqueue_script('wp-api');
		ifwp_enqueue_asset('ifcf7-uploaded-files', 'uploaded-files.js', ['wp-api']);
		wp_add_inline_script('ifcf7-uploaded-files', 'ifcf7_uploaded_files.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-uploaded-files', 'uploaded-files.css', ['contact-form-7']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_mail_failed($contact_form){
        $submission = \WPCF7_Submission::get_instance();
        if(null === $submission){
            $response = $contact_form->message('mail_sent_ng');
            $status = 'mail_failed';
        } else {
            $response = $submission->get_response();
            $status = $submission->get_status();
        }
		self::update_response($response, $status);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_mail_sent($contact_form){
        $submission = \WPCF7_Submission::get_instance();
        if(null === $submission){
            $response = $contact_form->message('mail_sent_ok');
            $status = 'mail_sent';
        } else {
            $response = $submission->get_response();
            $status = $submission->get_status();
        }
		self::update_response($response, $status);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
