<?php namespace IFCF7;

final class Utilities {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('wp_enqueue_scripts', [__CLASS__, 'wp_enqueue_scripts']);
        add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
        add_filter('pre_delete_post', [__CLASS__, 'pre_delete_post'], 10, 3);
		add_filter('wpcf7_autop_or_not', [__CLASS__, 'wpcf7_autop_or_not']);
		add_filter('wpcf7_display_message', [__CLASS__, 'wpcf7_display_message'], 10, 2);
		add_filter('wpcf7_feedback_response', [__CLASS__, 'wpcf7_feedback_response'], 10, 2);
		add_filter('wpcf7_form_class_attr', [__CLASS__, 'wpcf7_form_class_attr']);
		add_filter('wpcf7_form_elements', [__CLASS__, 'wpcf7_form_elements'], 11);
		add_filter('wpcf7_form_hidden_fields', [__CLASS__, 'wpcf7_form_hidden_fields']);
		add_filter('wpcf7_remote_ip_addr', [__CLASS__, 'wpcf7_remote_ip_addr']);
        add_filter('wpcf7_validate_password', [__CLASS__, 'wpcf7_password_validation_filter'], 10, 2);
        add_filter('wpcf7_validate_password*', [__CLASS__, 'wpcf7_password_validation_filter'], 10, 2);
        add_filter('wpcf7_verify_nonce', 'is_user_logged_in');
		if(version_compare(WPCF7_VERSION, '5.5.6.1', '>')){
			add_action('wpcf7_swv_create_schema', [__CLASS__, 'wpcf7_swv_add_checkbox_rules'], 10, 2);
			remove_action('wpcf7_swv_create_schema', 'wpcf7_swv_add_checkbox_rules');
		} else { // backcompat
			add_filter('wpcf7_validate_radio*', 'wpcf7_checkbox_validation_filter', 10, 2);
			remove_filter('wpcf7_validate_radio', 'wpcf7_checkbox_validation_filter');
		}
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function redirect_url(){
		$contact_form = wpcf7_get_current_contact_form();
        if(null === $contact_form){
            return '';
        }
		$submission = \WPCF7_Submission::get_instance();
		if(null === $submission){
			return '';
		}
		$url = self::redirect_raw_to_url($contact_form, $submission);
		$url = apply_filters('ifcf7_redirect_url', $url, $contact_form, $submission);
		if(!wpcf7_is_url($url)){
			return '';
		}
		$referer_tag = self::referer_tag($contact_form, $submission);
		if($referer_tag){
			$url = add_query_arg('ifcf7_referer', $referer_tag, $url);
		}
		return $url;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function redirect_raw_to_url($contact_form = null, $submission = null){
		$redirect_url = self::redirect_raw_url($contact_form, $submission);
		switch($redirect_url){
			case 'same':
				return $submission->get_meta('url');
				break;
			case 'another':
				$another_url = ifcf7_meta_pref('ifcf7_another_url', $contact_form);
				if(!wpcf7_is_url($another_url)){
					$another_url = home_url();
				}
				return $another_url;
				break;
			case 'dynamic':
				$fallback_url = ifcf7_meta_pref('ifcf7_fallback_url', $contact_form);
				if(!wpcf7_is_url($fallback_url)){
					$fallback_url = home_url();
				}
				$inserted_post_id = Storage::get_inserted_post_id();
				$inserted_user_id = Storage::get_inserted_user_id();
				$updated_post_id = Storage::get_updated_post_id();
				$updated_user_id = Storage::get_updated_user_id();
				if($inserted_post_id){
					if(!is_post_type_viewable(get_post_type($inserted_post_id))){
						return $fallback_url;
					}
					return get_permalink($inserted_post_id);
				} elseif($inserted_user_id){
					return get_author_posts_url($inserted_user_id);
				} elseif($updated_post_id){
					if(!is_post_type_viewable(get_post_type($updated_post_id))){
						return $fallback_url;
					}
					return get_permalink($updated_post_id);
				} elseif($updated_user_id){
					return get_author_posts_url($updated_user_id);
				} else { // delete or anything else
					return $fallback_url;
				}
				break;
			case 'contact_form':
				return '';
				break;
			default:
				return '';
		}
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function redirect_raw_url($contact_form = null, $submission = null){
		$redirect = ifcf7_meta_pref('ifcf7_redirect', $contact_form);
		if(!$redirect){
			$redirect = 'no';
		}
		if('no' === $redirect){
            return '';
        }
		$redirect_to = $submission->get_posted_data('ifcf7_redirect_to');
		if(wpcf7_is_url($redirect_to)){
			return $redirect_to;
		}
		$redirect_url = ifcf7_meta_pref('ifcf7_redirect_url', $contact_form);
		if(!$redirect_url){
			$redirect_url = 'same';
		}
		return $redirect_url;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_inserted_post_id($verify_nonce = true){
		$referer_tag = ifcf7_parse_referer_tag();
		if(!$referer_tag){
			return 0;
		}
		if('insert' !== $referer_tag['action'] or 'post' !== $referer_tag['type']){
			return 0;
		}
		if($verify_nonce and !$referer_tag['nonce_valid']){
			return ifwp_error(__('The link you followed has expired.'));
		}
		return $referer_tag['object_id'];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_inserted_user_id($verify_nonce = true){
		$referer_tag = ifcf7_parse_referer_tag();
		if(!$referer_tag){
			return 0;
		}
		if('insert' !== $referer_tag['action'] or 'user' !== $referer_tag['type']){
			return 0;
		}
		if($verify_nonce and !$referer_tag['nonce_valid']){
			return ifwp_error(__('The link you followed has expired.'));
		}
		return $referer_tag['object_id'];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_updated_post_id($verify_nonce = true){
		$referer_tag = ifcf7_parse_referer_tag();
		if(!$referer_tag){
			return 0;
		}
		if('update' !== $referer_tag['action'] or 'post' !== $referer_tag['type']){
			return 0;
		}
		if($verify_nonce and !$referer_tag['nonce_valid']){
			return ifwp_error(__('The link you followed has expired.'));
		}
		return $referer_tag['object_id'];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_updated_user_id($verify_nonce = true){
		$referer_tag = ifcf7_parse_referer_tag();
		if(!$referer_tag){
			return 0;
		}
		if('update' !== $referer_tag['action'] or 'user' !== $referer_tag['type']){
			return 0;
		}
		if($verify_nonce and !$referer_tag['nonce_valid']){
			return ifwp_error(__('The link you followed has expired.'));
		}
		return $referer_tag['object_id'];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function pre_delete_post($delete, $post, $force_delete){
		if('wpcf7_contact_form' !== $post->post_type){
			return $delete;
		}
		if(!$force_delete){
			return $delete;
		}
		return wp_trash_post($post->ID);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function referer_tag($contact_form = null, $submission = null){
		$contact_form = wpcf7_get_current_contact_form();
        if(null === $contact_form){
            return '';
        }
		$submission = \WPCF7_Submission::get_instance();
		if(null === $submission){
			return '';
		}
		$action = '';
		$deleted_post_id = Delete_Post::get_deleted_post_id();
		$deleted_user_id = Delete_User::get_deleted_user_id();
		$id = 0;
		$inserted_post_id = Storage::get_inserted_post_id();
		$inserted_user_id = Storage::get_inserted_user_id();
		$type = '';
		$updated_post_id = Storage::get_updated_post_id();
		$updated_user_id = Storage::get_updated_user_id();
		if($inserted_post_id){
			$action = 'insert';
			$id = $inserted_post_id;
			$type = 'post';
		} elseif($inserted_user_id){
			$action = 'insert';
			$id = $inserted_user_id;
			$type = 'user';
		} elseif($updated_post_id){
			$action = 'update';
			$id = $updated_post_id;
			$type = 'post';
		} elseif($updated_user_id){
			$action = 'update';
			$id = $updated_user_id;
			$type = 'user';
		} elseif($deleted_post_id){
			$action = 'delete';
			$id = $deleted_post_id;
			$type = 'post';
		} elseif($deleted_user_id){
			$action = 'delete';
			$id = $deleted_user_id;
			$type = 'user';
		} else {
			return '';
		}
		return $action[0] . '-c' . $submission->get_meta('container_post_id') . '-f' . $contact_form->id() . '-n' . wp_create_nonce('ifcf7-' . $action . '-' . $type . '_' . $id) . '-' . $type[0] . $id;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wp_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7', 'functions.js', ['jquery']);
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_autop_or_not($autop){
        $contact_form = wpcf7_get_current_contact_form();
        if(null === $contact_form){
            return $autop;
        }
        return $contact_form->is_true('autop'); // TODO: add additional setting
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7-utilities', 'utilities.js', ['contact-form-7']);
		wp_add_inline_script('ifcf7-utilities', 'ifcf7_utilities.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-utilities', 'utilities.css', ['contact-form-7']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_display_message($message, $status){
		if('spam' !== $status){
			return $message;
		}
		$submission = ifcf7_get_current_submission();
		if(!$submission){
			return $message;
		}
		$spam_log = $submission->get_spam_log();
		$invalid = wp_list_pluck($spam_log, 'agent');
		if($invalid){
			return sprintf(__('Invalid parameter(s): %s'), implode(', ', $invalid)) . '. ' . ifwp_last_p(__('An error has occurred. Please reload the page and try again.'));
		}
		return $message;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_feedback_response($response, $result){
		$feedback = [
			'redirect_url' => '',
			'referer_tag' => '',
		];
		if('mail_sent' === $response['status']){
			$feedback['redirect_url'] = self::redirect_url();
			$feedback['referer_tag'] = self::referer_tag();
		}
		$response['ifcf7'] = apply_filters('ifcf7_feedback_response', $feedback, $response, $result);
        return $response;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_class_attr($class = ''){
		$action = ifcf7_action();
		$class .= ' ifcf7_' . $action;
		$button = ifcf7_meta_pref('ifcf7_button');
		if(!$button){
			$button = 'no';
		}
		if('yes' === $button){
            $class .= ' ifcf7_button';
        }
		return $class;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_elements($form = ''){
		$contact_form = wpcf7_get_current_contact_form();
        if(null === $contact_form){
            return '<div class="alert alert-danger" role="alert">' . __('Something went wrong.') . '</div>';
        }
		$form = do_shortcode($form);
		$class = 'ifcf7';
		$hide = ifcf7_meta_pref('ifcf7_hide', $contact_form);
		if(!$hide){
			$hide = 'yes';
		}
		if('yes' === $hide){
            $class .= ' ifcf7_hide';
        }
		$hide_messages = ifcf7_meta_pref('ifcf7_hide_messages', $contact_form);
		if(!$hide_messages){
			$hide_messages = 'no';
		}
		if('yes' === $hide_messages){
            $class .= ' ifcf7_hide_messages';
        }
		$html = '<div class="' . $class . '">';
		$html .= '<div class="ifcf7_form_elements">' . $form . '</div>';
		$html .= '<div class="alert alert-success ifcf7_response_output" role="alert"></div>';
		$html .= '</div>';
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_hidden_fields($hidden_fields){
        $inserted_post_id = self::get_inserted_post_id();
        if(!empty($inserted_post_id) and !is_wp_error($inserted_post_id)){
            $hidden_fields['ifcf7_inserted_post_id'] = $inserted_post_id;
        }
        $inserted_user_id = self::get_inserted_user_id();
        if(!empty($inserted_user_id) and !is_wp_error($inserted_user_id)){
            $hidden_fields['ifcf7_inserted_user_id'] = $inserted_user_id;
        }
        $updated_post_id = self::get_updated_post_id();
        if(!empty($updated_post_id) and !is_wp_error($updated_post_id)){
            $hidden_fields['ifcf7_updated_post_id'] = $updated_post_id;
        }
        $updated_user_id = self::get_updated_user_id();
        if(!empty($updated_user_id) and !is_wp_error($updated_user_id)){
            $hidden_fields['ifcf7_updated_user_id'] = $updated_user_id;
        }
        if(!empty($_GET['redirect_to']) and wpcf7_is_url($_GET['redirect_to'])){
			$hidden_fields['ifcf7_redirect_to'] = $_GET['redirect_to'];
		}
		$current_utm_params = ifwp_get_current_utm_params();
		foreach(ifwp_get_utm_params() as $param){
			if(isset($current_utm_params[$param])){
				$hidden_fields['utm_' . $param] = $current_utm_params[$param];
			}
		}
		return $hidden_fields;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_password_validation_filter($result, $tag){
		$name = $tag->name;
		$value = isset($_POST[$name]) ? trim(wp_unslash(strtr((string) $_POST[$name], "\n", ' '))) : '';
		if('password' === $tag->basetype and $tag->is_required() and '' === $value){
			$result->invalidate($tag, wpcf7_get_message('invalid_required'));
		}
		if('' !== $value){
			$maxlength = $tag->get_maxlength_option();
			$minlength = $tag->get_minlength_option();
			if($maxlength and $minlength and $maxlength < $minlength){
				$maxlength = $minlength = null;
			}
			$code_units = wpcf7_count_code_units($value);
			if(false !== $code_units){
				if($maxlength and $maxlength < $code_units){
					$result->invalidate($tag, wpcf7_get_message('invalid_too_long'));
				} elseif($minlength and $code_units < $minlength){
					$result->invalidate($tag, wpcf7_get_message('invalid_too_short'));
				}
			}
		}
		return $result;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_remote_ip_addr($ip_addr){
		$ip = ifwp_remote_ip();
		if($ip){
			return $ip;
		}
        return $ip_addr;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_swv_add_checkbox_rules($schema, $contact_form){
		$tags = $contact_form->scan_form_tags([
			'type' => ['checkbox*', 'radio*'],
		]);
		foreach($tags as $tag){
			$schema->add_rule(wpcf7_swv_create_rule('required', [
				'error' => wpcf7_get_message('invalid_required'),
				'field' => $tag->name,
			]));
		}
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
