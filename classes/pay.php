<?php namespace IFCF7;

final class Pay {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        //add_action('after_setup_theme', [__CLASS__, 'add_data_options']);
		add_action('wpcf7_before_send_mail', [__CLASS__, 'wpcf7_before_send_mail'], 10, 3);
		add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_filter('ifcf7_form_types', [__CLASS__, 'ifcf7_form_types'], 11);
		add_filter('ifcf7_shortcode_tag_html', [__CLASS__, 'ifcf7_shortcode_tag_html'], 10, 5);
		add_filter('wpcf7_posted_data', [__CLASS__, 'wpcf7_posted_data'], 40);
		add_filter('wpcf7_validate_text*', [__CLASS__, 'wpcf7_validate_text'], 20, 2);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	//private static $cc_data = [];

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_action($contact_form = null){
		return ('payment_intent' === ifcf7_action($contact_form));
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    /*public static function add_data_options(){
		global $wp_locale;
		$cc_exp_m = $wp_locale->month;
		$cc_exp_m = array_map('ucfirst', $cc_exp_m);
		foreach($cc_exp_m as $key => $value){
			$cc_exp_m[$key] = $key . ' - ' . $value;
		}
		Data_Options::add_data_option('cc_exp_month', $cc_exp_m);
		$cc_exp_y = current_time('Y');
		$cc_exp_y = range($cc_exp_y, ($cc_exp_y + 15));
		//foreach($cc_exp_y as $key => $value){
			//unset($cc_exp_y[$key]);
			//$cc_exp_y[substr($value, -2)] = $value;
		//}
		$cc_exp_y = array_combine($cc_exp_y, $cc_exp_y);
		Data_Options::add_data_option('cc_exp_year', $cc_exp_y);
    }*/

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_form_types($form_types){
		if(!has_filter('ifcf7_payment_intent')){
			return $form_types;
		}
		$form_types['payment_intent'] = 'Payment intent';
		return $form_types;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
		if(!self::is_action($contact_form)){
			return $output;
		}
		if(!has_filter('ifcf7_payment_intent')){
			$error = current_user_can('manage_options') ? sprintf(__("Method '%s' not implemented. Must be overridden in subclass."), 'ifcf7_payment_intent') : __('Something went wrong.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		/*$fields = [
			'cc_csc' => 'number*',
			'cc_exp_month' => 'select*',
			'cc_exp_year' => 'select*',
			'cc_number' => 'number*',
		];*/
		$fields = [
			'ifcf7_cc_cvv' => 'text*',
			'ifcf7_cc_exp' => 'text*',
			'ifcf7_cc_num' => 'text*',
		];
		$missing = ifcf7_missing_fields($fields, $contact_form);
		/*$service = \WPCF7_RECAPTCHA::get_instance();
		if(!$service->is_active()){
			$missing[] = 'reCAPTCHA';
		}*/
		$tags = array_filter($contact_form->scan_form_tags(), function($tag){
			return wpcf7_form_tag_supports($tag->type, 'name-attr');
		});
		foreach($tags as $tag){
			if(!array_key_exists($tag->name, $fields)){
				continue;
			}
			if(in_array($tag->name, $missing)){
				continue;
			}
			if(!$tag->has_option('inputmode')){
				$missing[] = $tag->name . '[inputmode]';
			}
		}
		if($missing){
			$error = current_user_can('manage_options') ? sprintf(__('Missing parameter(s): %s'), ifwp_implode_and($missing)) . '.' : __('Something went wrong.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		$invalid = ifcf7_invalid_fields($fields, $contact_form);
		if($invalid){
			$error = current_user_can('manage_options') ? sprintf(__('Invalid parameter(s): %s'), ifwp_implode_and($invalid)) . '.' : __('Something went wrong.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		return $output;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_before_send_mail($contact_form, &$abort, $submission){
		if(!self::is_action($contact_form)){
			return;
		}
		if($abort or !$submission->is('init')){
			return; // prevent conflicts with other plugins
		}
		//$abort = true; // prevent mail_sent and mail_failed actions
		$title = 'Payment intent ' . uniqid();
		$name = sanitize_title($title);
		$post_id = wp_insert_post([
			'post_name' => $name,
			'post_status' => 'private',
			'post_title' => $title,
			'post_type' => 'ifcf7-payment-intent',
		], true);
		if(is_wp_error($post_id)){
			$abort = true; // prevent mail_sent and mail_failed actions
			$submission->set_response($post_id->get_error_message());
			$submission->set_status('aborted'); // try to prevent conflicts with other plugins
			return;
		}
		$stored = Storage::store_submission('insert', 'post', $post_id, $contact_form, $submission);
		if(is_wp_error($stored)){
			$abort = true; // prevent mail_sent and mail_failed actions
            $submission->set_response($stored->get_error_message());
            $submission->set_status('aborted'); // try to prevent conflicts with actions and other plugins
            return;
		}
		$cc_cvv = Helper::get_posted_data('ifcf7_cc_cvv');
		$cc_exp = Helper::get_posted_data('ifcf7_cc_exp');
		$cc_num = Helper::get_posted_data('ifcf7_cc_num');
		$cc_data = [
			'cc_cvv' => $cc_cvv,
			'cc_exp' => str_replace('/', '', $cc_exp),
			'cc_num' => str_replace(' ', '', $cc_num),
		];
		$payment_intent = new Payment_Intent($post_id);
		$payment_intent = apply_filters('ifcf7_payment_intent', $payment_intent, $cc_data, $contact_form, $submission);
		if($payment_intent instanceof Payment_Intent){
			$data = $payment_intent->get_data();
			$message = $payment_intent->get_message();
			$status = $payment_intent->get_status();
		} else {
			$data = $payment_intent;
			$message = __('Invalid object type.');
			$status = false;
			update_post_meta($post_id, 'ifcf7_payment_intent_data', $data);
			update_post_meta($post_id, 'ifcf7_payment_intent_message', $message);
			update_post_meta($post_id, 'ifcf7_payment_intent_status', $status);
		}
		if(false === $status){
			$abort = true; // prevent mail_sent and mail_failed actions
			$submission->set_response($message);
			$submission->set_status('aborted'); // try to prevent conflicts with other plugins
		}
		//do_action('ifcf7_pay', $payment_intent, $contact_form, $submission);
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		if(!has_filter('ifcf7_payment_intent')){
			return;
		}
		ifwp_enqueue_asset('ifcf7-pay', 'pay.js', ['jquery-inputmask']);
		wp_add_inline_script('ifcf7-pay', 'ifcf7_pay.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-pay', 'pay.css', ['contact-form-7']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_posted_data($posted_data){
		if(!self::is_action()){
			return $posted_data;
		}
		$fields = ['ifcf7_cc_cvv', 'ifcf7_cc_exp', 'ifcf7_cc_num'];
		foreach($fields as $field){
			if(isset($posted_data[$field])){
				//self::$cc_data[$field] = $posted_data[$field];
				unset($posted_data[$field]);
			}
		}
		return $posted_data;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_validate_text($result, $tag){
		if(!in_array($tag->name, ['ifcf7_cc_cvv', 'ifcf7_cc_exp', 'ifcf7_cc_num'])){
			return $result;
		}
		if(!self::is_action()){
			return $result;
		}
		$cc_cvv = Helper::get_posted_data('ifcf7_cc_cvv');
		$cc_exp = Helper::get_posted_data('ifcf7_cc_exp');
		$cc_num = Helper::get_posted_data('ifcf7_cc_num');
		$first2 = substr($cc_num, 0, 2);
		switch($tag->name){
			case 'ifcf7_cc_cvv':
				if($cc_cvv){
					$lenght = 3;
					if(34 == $first2 or 37 == $first2){
						$lenght = 4;
					}
					if($lenght < strlen($cc_cvv)){
						$message = wpcf7_get_message('number_too_small'); // invalid_too_short
						$message = wp_strip_all_tags($message);
						$result->invalidate($tag, wp_strip_all_tags($message));
						return $result;
					} elseif($lenght > strlen($cc_cvv)){
						$message = wpcf7_get_message('number_too_large'); // invalid_too_long
						$message = wp_strip_all_tags($message);
						$result->invalidate($tag, wp_strip_all_tags($message));
						return $result;
					}
				}
				break;
			case 'ifcf7_cc_exp':
				if($cc_exp){
					if(!preg_match('/(0[1-9]|1[012])\/[0-9]{2}/', $cc_exp)){
						$message = wpcf7_get_message('invalid_date');
						$message = wp_strip_all_tags($message);
						$result->invalidate($tag, wp_strip_all_tags($message));
						return $result;
					}
				}
				break;
			case 'ifcf7_cc_num':
				if($cc_num){
					if(!preg_match('/([12456789][0-9]{3} [0-9]{4} [0-9]{4} [0-9]{4}|3[47][0-9]{2} [0-9]{6} [0-9]{5}|3[01235689][0-9]{2} [0-9]{6} [0-9]{4})/', $cc_num)){
						$message = wpcf7_get_message('invalid_number');
						$message = wp_strip_all_tags($message);
						$result->invalidate($tag, wp_strip_all_tags($message));
						return $result;
					}
				}
				break;
			default:
				return $result;
		}
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
