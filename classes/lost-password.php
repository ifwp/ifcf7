<?php namespace IFCF7;

final class Lost_Password {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('login_form_lostpassword', [__CLASS__, 'custom_form']);
        add_action('login_form_retrievepassword', [__CLASS__, 'custom_form']);
		add_action('wpcf7_before_send_mail', [__CLASS__, 'wpcf7_before_send_mail'], 10, 3);
		add_filter('ifcf7_form_types', [__CLASS__, 'ifcf7_form_types']);
		add_filter('ifcf7_shortcode_tag_html', [__CLASS__, 'ifcf7_shortcode_tag_html'], 10, 5);
		add_filter('wpcf7_posted_data', [__CLASS__, 'wpcf7_posted_data']);
		add_filter('wpcf7_validate_email*', [__CLASS__, 'wpcf7_validate_email'], 20, 2);
		add_filter('wpcf7_validate_text*', [__CLASS__, 'wpcf7_validate_text'], 20, 2);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static $post_id = 0;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function has_shortcode($content, $tag){
		if(false === strpos($content, '[')){
	        return [];
	    }
	    if(shortcode_exists($tag)){
	        preg_match_all('/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER);
	        if(empty($matches)){
	            return [];
	        }
	        foreach($matches as $shortcode){
	            if($tag === $shortcode[2]){
	                return shortcode_parse_atts($shortcode[3]);
	            } elseif(!empty($shortcode[5]) and $attr = self::has_shortcode($shortcode[5], $tag)){
	                return $attr;
	            }
	        }
	    }
	    return [];
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_action($contact_form = null){
		return ('lost_password' === ifcf7_action($contact_form));
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_valid_post_id($post_id = 0){
		if(!$post_id){
            return false;
        }
		$post = get_post($post_id);
		if(!$post){
			return false;
		}
		$attr = self::has_shortcode($post->post_content, 'contact-form-7');
		if(!$attr){
			return false;
		}
		$contact_form = ifcf7_contact_form($attr['id']);
		if(!$contact_form){
			return false;
		}
		return self::is_action($contact_form);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function custom_form(){
        if(!self::$post_id){
            return;
        }
		if(!self::is_valid_post_id(self::$post_id)){
			return;
		}
        $url = get_permalink(self::$post_id);
    	if($_GET){
    		$url = add_query_arg($_GET, $url);
    	}
    	wp_safe_redirect($url);
    	exit;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function custom_page($post_id = 0){
        $post = get_post($post_id);
        if(!$post or 'page' !== $post->post_type or 'publish' !== $post->post_status){
            return;
        }
        self::$post_id = $post->ID;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function ifcf7_form_types($form_types = ''){
		$form_types['lost_password'] = __('Lost Password');
        return $form_types;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
		if(!self::is_action($contact_form)){
			return $output;
		}
		$error = '';
		if(isset($_GET['ifcf7_error'])){
			if('invalidkey' === $_GET['ifcf7_error']){
				$message = __('<strong>Error</strong>: Your password reset link appears to be invalid. Please request a new link below.');
			} elseif('expiredkey' === $_GET['ifcf7_error']){
				$message = __('<strong>Error</strong>: Your password reset link has expired. Please request a new link below.');
			} else {
                $message = __('<strong>Error</strong>: Your password reset link appears to be invalid. Please request a new link below.');
                $message = ifwp_last_p($message);
                $message = __('Something went wrong.') . ' ' . $message;
            }
			$error = '<div class="alert alert-danger" role="alert">' . $message . '</div>';
		}
		$missing = [];
		$service = \WPCF7_RECAPTCHA::get_instance();
        $tags = wp_list_pluck($contact_form->scan_form_tags(), 'type', 'name');
		if(!$service->is_active()){
			$missing[] = 'reCAPTCHA';
		}
		if(!isset($tags['user_email']) and !isset($tags['user_login'])){
			$missing[] = 'user_login';
		}
		if($missing){
			$message = current_user_can('manage_options') ? sprintf(__('Missing parameter(s): %s'), implode(', ', $missing)) . '.' : __('Something went wrong.');
			$error .= '<div class="alert alert-danger" role="alert">' . $message . '</div>';
			return $error;
		}
        if(isset($tags['user_email'], $tags['user_login'])){
			$message = current_user_can('manage_options') ? __('Duplicated username or email address.') : __('Something went wrong.');
			$error .= '<div class="alert alert-danger" role="alert">' . $message . '</div>';
			return $error;
		}
        $fields = [
			'user_email' => 'email*',
            'user_login' => 'text*',
		];
		$invalid = ifcf7_invalid_fields($fields, $contact_form);
		if($invalid){
			$message = current_user_can('manage_options') ? sprintf(__('Invalid parameter(s): %s'), implode(', ', $invalid)) . '.' : __('Something went wrong.');
			$error .= '<div class="alert alert-danger" role="alert">' . $message . '</div>';
			return $error;
		}
		if(!self::$post_id){ // __('Invalid post ID.')
			$message = current_user_can('manage_options') ? sprintf(__('Missing parameter(s): %s'), 'Page ID') . '.' : __('Something went wrong.');
            $error .= '<div class="alert alert-danger" role="alert">' . $message . '</div>';
			return $error;
        }
        /*if(!in_the_loop()){
			$message = current_user_can('manage_options') ? sprintf(__('Missing parameter(s): %s'), 'The Loop') . '.' : __('Something went wrong.');
			$error .= '<div class="alert alert-danger" role="alert">' . $message . '</div>';
			return $error;
        }
        if(self::$post_id !== get_the_ID()){
			$message = current_user_can('manage_options') ? sprintf(__('Invalid parameter(s): %s'), 'Page ID') . '.' : __('Something went wrong.');
			$error .= '<div class="alert alert-danger" role="alert">' . $message . '</div>';
			return $error;
        }*/
		if(is_user_logged_in()){
			$message = __('You are logged in already. No need to register again!');
			$error .= '<div class="alert alert-warning" role="alert">' . $message . '</div>';
			return $error;
		}
		$error .= $output;
		return $error;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_before_send_mail($contact_form, &$abort, $submission){
        if(!self::is_action($contact_form)){
			return;
		}
		if($abort or !$submission->is('init')){
			return; // prevent conflicts with other plugins
		}
		$abort = true; // prevent mail_sent and mail_failed actions
		$user_email = Helper::get_posted_data('user_email');
		$user_login = Helper::get_posted_data('user_login');
		if(!$user_login){
			$user_login = $user_email;
		}
		$result = retrieve_password($user_login);
		if(is_wp_error($result)){
			$message = $result->get_error_message();
			$submission->set_response(wp_strip_all_tags($message));
			$submission->set_status('aborted'); // try to prevent conflicts with other plugins
			return;
		}
        Helper::mail($contact_form, $submission);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_posted_data($posted_data){
		if(!self::is_action()){
			return $posted_data;
		}
		$fields = ['user_email', 'user_login'];
		foreach($fields as $field){
			if(isset($posted_data[$field])){
				unset($posted_data[$field]);
			}
		}
		return $posted_data;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_validate_email($result, $tag){
		if('user_email' !== $tag->name){
			return $result;
		}
		if(!self::is_action()){
			return $result;
		}
        $user_email = Helper::get_posted_data('user_email');
		if(!email_exists($user_email)){
			$message = __('Unknown email address. Check again or try your username.');
			$message = ifwp_first_p($message);
			$message = wp_strip_all_tags($message);
			$result->invalidate($tag, $message);
			return $result;
		}
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_validate_text($result, $tag){
		if($tag->name !== 'user_login'){
			return $result;
		}
		if(!self::is_action()){
			return $result;
		}
        $user_login = Helper::get_posted_data('user_login');
		if(wpcf7_is_email($user_login)){
			$message = __('Unknown email address. Check again or try your username.');
			$user = get_user_by('email', $user_login);
		} else {
			$message = __('Unknown username. Check again or try your email address.');
			$user = get_user_by('login', $user_login);
		}
		if(!$user){
            $message = ifwp_first_p($message);
			$message = wp_strip_all_tags($message);
			$result->invalidate($tag, wp_strip_all_tags($message));
			return $result;
		}
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
