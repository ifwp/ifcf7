<?php namespace IFCF7;

final class Login_Signup {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('rest_api_init', [__CLASS__, 'rest_api_init']);
		add_action('wpcf7_before_send_mail', [__CLASS__, 'wpcf7_before_send_mail'], 10, 3);
		add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_filter('ifcf7_form_types', [__CLASS__, 'ifcf7_form_types']);
		add_filter('ifcf7_shortcode_tag_html', [__CLASS__, 'ifcf7_shortcode_tag_html'], 10, 5);
		add_filter('wpcf7_messages', [__CLASS__, 'wpcf7_messages']);
		add_filter('wpcf7_validate', [__CLASS__, 'wpcf7_validate'], 20, 2);
		add_filter('wpcf7_validate_number*', [__CLASS__, 'wpcf7_validate_number'], 20, 2);
		add_filter('wpcf7_validate_password*', [__CLASS__, 'wpcf7_validate_password'], 20, 2);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static $user_email_exists = '';

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_role($contact_form = null){
		/*$role = $contact_form->pref('default_role');
		if('' === $role){
			return get_option('default_role');
		}
		if(!wp_roles()->is_role($role)){
			return get_option('default_role');
		}
		return $role;*/
		return get_option('default_role');
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_action($contact_form = null){
		return ('login_signup' === ifcf7_action($contact_form));
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_form_types($form_types){
		$form_types['login_signup'] = __('Log in') . ' / ' . __('Sign up');
		return $form_types;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
		if(!self::is_action($contact_form)){
			return $output;
		}
		$fields = [
			'email_exists' => 'hidden',
			'first_name' => 'text*',
			'last_name' => 'text*',
			'pass1' => 'password*',
			'user_email' => 'email*',
			'user_pass' => 'password*',
			'verification_code' => 'number*',
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
		$invalid = ifcf7_invalid_fields($fields, $contact_form);
		if($invalid){
			$error = current_user_can('manage_options') ? sprintf(__('Invalid parameter(s): %s'), implode(', ', $invalid)) . '.' : __('Something went wrong.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		if(is_user_logged_in() and !current_user_can('create_users')){
			$error = __('You are logged in already. No need to register again!') . ' / ' . __('Sorry, you are not allowed to create new users.') . ' ' . __('You need a higher level of permission.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		return $output;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function rest_api_init(){
		register_rest_route('ifcf7/v1', '/user-email-exists', [
			'callback' => [__CLASS__, 'user_email_exists'],
			'methods' => 'POST',
			'permission_callback' => '__return_true',
		]);
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function user_email_exists($request){
		$user_email = $request->get_param('user_email');
		$user_email_exists = email_exists($user_email) ? 'yes' : 'no';
		return [
			'user_email_exists' => $user_email_exists,
		];
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
		if(email_exists($user_email)){
			// login
			$user_pass = Helper::get_posted_data('user_pass');
			$user = ifwp_signon($user_email, $user_pass, true);
			if(is_wp_error($user)){
				$message = $user->get_error_message();
				$submission->set_response(wp_strip_all_tags($message));
				$submission->set_status('aborted'); // try to prevent conflicts with other plugins
				return;
			}
		} else {
			// signup
			$first_name = Helper::get_posted_data('first_name');
			$last_name = Helper::get_posted_data('last_name');
			$name = $first_name . ' ' . $last_name;
			$pass1 = Helper::get_posted_data('pass1');
			$user_id = wp_insert_user([
				'display_name' => $name,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'nickname' => $name,
				'role' => self::get_role($contact_form),
				'user_email' => $user_email,
				'user_login' => $user_email,
				'user_pass' => $pass1,
			]);
		}
		Helper::mail($contact_form, $submission);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7-login-signup', 'login-signup.js', ['contact-form-7']);
		wp_add_inline_script('ifcf7-login-signup', 'ifcf7_login_signup.load();');
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_messages($messages){
		$messages = array_merge($messages, [
			'invalid_verification_code' => [
				'description' => __('Verification code that the sender entered is invalid', 'contact-form-7'),
				'default' => __('The verification code is invalid.', 'contact-form-7'),
			],
		]);
		return $messages;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_validate($result, $tag){
		if(!self::is_action()){
			return $result;
		}
		if(!$result->is_valid()){
			return $result;
		}
		if(!isset($_POST['_wpcf7cf_steps'])){
			return $result;
		}
		$step_data = json_decode(stripslashes($_POST['_wpcf7cf_steps']));
		if(!in_array('user_email', $step_data->fieldsInCurrentStep)){
			return $result;
		}
		/*$name = 'user_email';
		$value = isset($_POST[$name]) ? trim(wp_unslash(strtr((string) $_POST[$name], "\n", ' '))) : '';*/
		$user_email = Helper::get_posted_data('user_email');
		if(email_exists($user_email)){
			return $result;
		}
		ifwp_session_start();
		$_SESSION['ifcf7_verification_code'] = random_int(100000, 999999);
		$sitename = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$to = $user_email;
		$subject = '[' . $sitename . '] Verification code';
		$message = 'Howdy ' . $user_email . ',';
		$message .= "\n\n";
		$message .= 'Your verification code is ' . $_SESSION['ifcf7_verification_code'] . '.';
		$message .= "\n\n";
		$message .= 'If you didn\'t request this code, no action is needed. You can ignore this email.';
		$message .= "\n\n";
		$message .= 'Regards,';
		$message .= "\n";
		$message .= 'All at ' . $sitename;
		$message .= "\n";
		$message .= home_url();
		wp_mail($to, $subject, $message);
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_validate_number($result, $tag){
		if(!self::is_action()){
			return $result;
		}
		if($tag->name !== 'verification_code'){
			return $result;
		}
		$value = (int) Helper::get_posted_data('verification_code');
		ifwp_session_start();
		if($value !== $_SESSION['ifcf7_verification_code']){
			$result->invalidate($tag, wpcf7_get_message('invalid_verification_code'));
		}
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_validate_password($result, $tag){
		if(!self::is_action()){
			return $result;
		}
		switch($tag->name){
			case 'pass1':
				$user_email = Helper::get_posted_data('user_email');
				if(email_exists($user_email)){
					return $result;
				}
				$pass1 = Helper::get_posted_data('pass1');
				if(false !== strpos(wp_unslash($pass1), '\\')){
					$message = __('<strong>Error</strong>: Passwords may not contain the character "\\".');
					$result->invalidate($tag, wp_strip_all_tags($message));
					return $result;
				}
				return $result;
				break;
			case 'user_pass':
				$user_email = Helper::get_posted_data('user_email');
				if(!email_exists($user_email)){
					return $result;
				}
				$user = get_user_by('email', $user_email);
				$user_pass = Helper::get_posted_data('user_pass');
				if(!wp_check_password($user_pass, $user->data->user_pass, $user->ID)){
					$message = sprintf(__('<strong>Error</strong>: The password you entered for the email address %s is incorrect.'), '<strong>' . $user_email . '</strong>');
					$result->invalidate($tag, wp_strip_all_tags($message));
					return $result;
				}
				return $result;
				break;
			default:
				return $result;
		}
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
