<?php namespace IFCF7;

final class Reset_Password {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('login_form_resetpass', [__CLASS__, 'custom_form']);
        add_action('login_form_rp', [__CLASS__, 'custom_form']);
        add_action('parse_query', [__CLASS__, 'parse_query']);
		add_action('wpcf7_before_send_mail', [__CLASS__, 'wpcf7_before_send_mail'], 10, 3);
		add_filter('ifcf7_form_types', [__CLASS__, 'ifcf7_form_types']);
		add_filter('ifcf7_shortcode_tag_html', [__CLASS__, 'ifcf7_shortcode_tag_html'], 10, 5);
        add_filter('wpcf7_form_hidden_fields', [__CLASS__, 'wpcf7_form_hidden_fields']);
        add_filter('wpcf7_posted_data', [__CLASS__, 'wpcf7_posted_data']);
        add_filter('wpcf7_validate_password*', [__CLASS__, 'wpcf7_validate_password'], 20, 2);
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
		return ('reset_password' === ifcf7_action($contact_form));
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
		$form_types['reset_password'] = __('Reset Password');
        return $form_types;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
		if(!self::is_action($contact_form)){
			return $output;
		}
		$fields = [
			'pass1' => 'password*',
		];
		$missing = ifcf7_missing_fields($fields, $contact_form);
		$service = \WPCF7_RECAPTCHA::get_instance();
		if(!$service->is_active()){
			$missing[] = 'reCAPTCHA';
		}
		if($missing){
			$error = current_user_can('manage_options') ? sprintf(__('Missing parameter(s): %s'), implode(', ', $missing)) . '.' : __('Something went wrong.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		$fields['pass2'] = 'password*';
		$invalid = ifcf7_invalid_fields($fields, $contact_form);
		if($invalid){
			$error = current_user_can('manage_options') ? sprintf(__('Invalid parameter(s): %s'), implode(', ', $invalid)) . '.' : __('Something went wrong.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		if(!self::$post_id){ // __('Invalid post ID.')
			$error = current_user_can('manage_options') ? sprintf(__('Missing parameter(s): %s'), 'Page ID') . '.' : __('Something went wrong.');
            return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
        }
        if(!in_the_loop()){
			$error = current_user_can('manage_options') ? sprintf(__('Missing parameter(s): %s'), 'The Loop') . '.' : __('Something went wrong.');
            return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
        }
        if(self::$post_id !== get_the_ID()){
			$error = current_user_can('manage_options') ? sprintf(__('Invalid parameter(s): %s'), 'Page ID') . '.' : __('Something went wrong.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
        }
		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
        if(isset($_COOKIE[$rp_cookie]) and 0 < strpos($_COOKIE[$rp_cookie], ':')){
            // fake
        } else {
			//$error = current_user_can('manage_options') ? __('Invalid cookie.') : __('Something went wrong.');
			$error = __( 'Cookie check failed' );
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		if(is_user_logged_in()){
			$error = __('You are logged in already. No need to register again!');
			return '<div class="alert alert-warning" role="alert">' . $error . '</div>';
		}
		return $output;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function parse_query($query){
        if(!self::$post_id){
            return;
        }
        if(self::$post_id !== $query->queried_object_id){
    		return;
    	}
		if(!self::is_valid_post_id(self::$post_id)){
			return;
		}
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
		if(isset($_GET['key'])){
			$action = 'resetpass';
		}
		if(isset($_GET['checkemail'])){
			$action = 'checkemail';
		}
		if(!in_array($action, ['resetpass', 'rp'])){
			return;
		}
        $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
    	if(isset($_GET['key'], $_GET['login'])){
    		$value = sprintf('%s:%s', wp_unslash($_GET['login']), wp_unslash($_GET['key']));
    		setcookie($rp_cookie, $value, 0, '/', COOKIE_DOMAIN, is_ssl(), true);
    		wp_safe_redirect(remove_query_arg(['key', 'login']));
    		exit;
    	}
        if(isset($_COOKIE[$rp_cookie]) and 0 < strpos($_COOKIE[$rp_cookie], ':')){
    		list($rp_login, $rp_key) = explode(':', wp_unslash($_COOKIE[$rp_cookie]), 2);
    		$user = check_password_reset_key($rp_key, $rp_login);
    	} else {
    		$user = false;
    	}
        if(!$user or is_wp_error($user)){
    		setcookie($rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/', COOKIE_DOMAIN, is_ssl(), true);
    		if($user and $user->get_error_code() === 'expired_key'){
				$error = 'expiredkey';
    		} else {
				$error = 'invalidkey';
    		}
			$redirect = '';
			if(isset($_GET['redirect_to']) and '' !== $_GET['redirect_to']){
				$redirect = esc_url_raw($_GET['redirect_to']);
			}
			$location = wp_lostpassword_url($redirect);
			$location = add_query_arg('ifcf7_error', $error, $location);
			wp_safe_redirect($location);
    		exit;
    	}
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
		$pass1 = Helper::get_posted_data('pass1');
		$pass2 = Helper::get_posted_data('pass2');
		$posted_rp_key = Helper::get_posted_data('rp_key');
        $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
        if(isset($_COOKIE[$rp_cookie]) and 0 < strpos($_COOKIE[$rp_cookie], ':')){
            list($rp_login, $rp_key) = explode(':', wp_unslash($_COOKIE[$rp_cookie]), 2);
            $user = check_password_reset_key($rp_key, $rp_login);
            if(!hash_equals($rp_key, $posted_rp_key)){
				$user = false;
			}
        } else {
            $user = false;
        }
        if(!$user or is_wp_error($user)){
            setcookie($rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/', COOKIE_DOMAIN, is_ssl(), true);
            if($user and $user->get_error_code() === 'expired_key'){
				$message = __('<strong>Error</strong>: Your password reset link has expired. Please request a new link below.');
				$message = ifwp_first_p($message);
				$message = wp_strip_all_tags($message);
                $submission->set_response($message);
            } else {
				$message = __('<strong>Error</strong>: Your password reset link appears to be invalid. Please request a new link below.');
				$message = ifwp_first_p($message);
				$message = wp_strip_all_tags($message);
                $submission->set_response($message);
            }
            $submission->set_status('aborted'); // try to prevent conflicts with other plugins
			return;
        }
        reset_password($user, $pass1);
        setcookie($rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/', COOKIE_DOMAIN, is_ssl(), true);
		if(!is_user_logged_in()){
			$user = ifwp_signon_without_password($user->user_email);
			if(is_wp_error($user)){
				$message = $user->get_error_message();
				$message = wp_strip_all_tags($message);
                $submission->set_response($message);
				$submission->set_status('aborted'); // try to prevent conflicts with other plugins
				return;
			}
		}
		Helper::mail($contact_form, $submission); // __('Your password has been reset.')
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_hidden_fields($hidden_fields){
		if(!self::is_action()){
			return $hidden_fields;
		}
        $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
        if(isset($_COOKIE[$rp_cookie]) and 0 < strpos($_COOKIE[$rp_cookie], ':')){
            list($rp_login, $rp_key) = explode(':', wp_unslash($_COOKIE[$rp_cookie]), 2);
            $hidden_fields['rp_key'] = $rp_key;
        }
		return $hidden_fields;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_posted_data($posted_data){
		if(!self::is_action()){
			return $posted_data;
		}
		$fields = ['pass1', 'pass2', 'rp_key'];
		foreach($fields as $field){
			if(isset($posted_data[$field])){
				unset($posted_data[$field]);
			}
		}
		return $posted_data;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_validate_password($result, $tag){
        if(!in_array($tag->name, ['pass1', 'pass2'])){
			return $result;
		}
		if(!self::is_action()){
			return $result;
		}
		$pass1 = Helper::get_posted_data('pass1');
		$pass2 = Helper::get_posted_data('pass2');
        switch($tag->name){
            case 'pass1':
                if(false !== strpos(wp_unslash($pass1), '\\')){
                    $message = __('<strong>Error</strong>: Passwords may not contain the character "\\".');
					$message = wp_strip_all_tags($message);
                    $result->invalidate($tag, $message);
                    return $result;
                }
                break;
            case 'pass2':
                if($pass1 !== $pass2){
                    //$message = __('<strong>Error</strong>: Passwords don&#8217;t match. Please enter the same password in both password fields.');
					$message = __('<strong>Error</strong>: The passwords do not match.');
                    $message = wp_strip_all_tags($message);
                    $result->invalidate($tag, $message);
                    return $result;
                }
                break;
        }
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
