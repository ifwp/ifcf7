<?php namespace IFCF7;

final class Delete_User {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('wpcf7_before_send_mail', [__CLASS__, 'wpcf7_before_send_mail'], 10, 3);
		add_filter('ifcf7_shortcode_tag_html', [__CLASS__, 'ifcf7_shortcode_tag_html'], 10, 5);
		add_filter('shortcode_atts_wpcf7', [__CLASS__, 'shortcode_atts_wpcf7'], 10, 3);
		add_filter('wpcf7_form_hidden_fields', [__CLASS__, 'wpcf7_form_hidden_fields']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static $deleted_user_id = 0;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_user_id($contact_form = null, $submission = null){
		if(null === $contact_form){
			$contact_form = wpcf7_get_current_contact_form();
		}
		if(null === $contact_form){
			return ifwp_error(__('The requested contact form was not found.', 'contact-form-7'));
		}
		if(!self::is_action($contact_form)){
			return ifwp_error(sprintf(__('%1$s is not of type %2$s.'), $type, 'delete_user'));
		}
		if(null === $submission){
			$submission = \WPCF7_Submission::get_instance();
		}
		$missing = [];
		if(null === $submission){
			$nonce = null;
			$user_id = $contact_form->shortcode_attr('user_id');
			if(empty($user_id)){
				$missing[] = 'user_id';
			}
		} else {
			$nonce = $submission->get_posted_data('ifcf7_nonce');
			if(empty($nonce)){
				$missing[] = 'ifcf7_nonce';
			}
			$user_id = $submission->get_posted_data('ifcf7_user_id');
			if(empty($user_id)){
				$missing[] = 'ifcf7_user_id';
			}
		}
		if($missing){
			return ifwp_error(sprintf(__('Missing parameter(s): %s'), ifwp_implode_and($missing)) . '.');
		}
		if(null !== $nonce and !wp_verify_nonce($nonce, 'ifcf7-edit-user_' . $user_id)){
			return ifwp_error(__('The link you followed has expired.'));
		}
		$user_id = self::sanitize_user_id($user_id);
		if(0 === $user_id){
			return ifwp_error(__('Invalid user ID.'));
		}
		if(!current_user_can('delete_user', $user_id)){
			$message = __('Sorry, you are not allowed to delete this user.') . ' ' . __('You need a higher level of permission.');
			return ifwp_error($message);
		}
		return $user_id;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_action($contact_form = null){
		return ('delete_user' === ifcf7_action($contact_form));
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_deleted_user_id(){
		return self::$deleted_user_id;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
		if(!self::is_action($contact_form)){
			return $output;
		}
		$user_id = self::get_user_id();
		if(is_wp_error($user_id)){
			return '<div class="alert alert-danger" role="alert">' . $user_id->get_error_message() . '</div>';
		}
		return $output;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function sanitize_user_id($user_id){
		$user = false;
		if(is_numeric($user_id)){
			$user = get_userdata($user_id);
		} else {
			if('current' === $user_id){
				if(is_user_logged_in()){
					$user = wp_get_current_user();
				}
			}
		}
		if(!$user){
			return 0;
		}
		return $user->ID;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function shortcode_atts_ifcf7_load_form($out, $pairs, $atts){
		$contact_form = ifcf7_contact_form($out['form_id']);
		if(null === $contact_form){
			return $out;
		}
		if(!self::is_action($contact_form)){
			return $out;
		}
		if(isset($atts['user_id'])){
			$user_id = self::sanitize_user_id($atts['user_id']);
			if($user_id){
				$out['user_id'] = $user_id;
				$out['user_nonce'] = wp_create_nonce('ifcf7-setup-user_' . $user_id);
				if(!current_user_can('delete_user', $user_id)){
					$out['disabled'] = 1;
				}
			} else {
				$out['disabled'] = 1;
			}
		}
		return $out;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function shortcode_atts_wpcf7($out, $pairs, $atts){
		$contact_form = ifcf7_contact_form($out['id']);
		if(null === $contact_form){
			return $out;
		}
		if(!self::is_action($contact_form)){
			return $out;
		}
		if(wp_doing_ajax()){
			$user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
			$user_nonce = isset($_POST['user_nonce']) ? $_POST['user_nonce'] : '';
			$user_nonce_valid = $user_id ? wp_verify_nonce($user_nonce, 'ifcf7-setup-user_' . $user_id) : false;
			if($user_nonce_valid){
				$out['user_id'] = $user_id;
			}
		} else {
			// old
			if(isset($atts['user_id'])){
				$out['user_id'] = $atts['user_id'];
			}
		}
		return $out;
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
		$user_id = self::get_user_id($contact_form, $submission);
		if(is_wp_error($user_id)){
			$abort = true; // prevent mail_sent and mail_failed actions
			$message = $user_id->get_error_message();
			$message = wp_strip_all_tags($message);
			$submission->set_response($message);
			$submission->set_status('aborted'); // try to prevent conflicts with other plugins
			return;
		}
		if(!function_exists('wp_delete_user')){
			require_once(ABSPATH . 'wp-admin/includes/user.php');
		}
		if(!wp_delete_user($user_id)){
			$abort = true; // prevent mail_sent and mail_failed actions
			$message = __('The user cannot be deleted.');
			$message = wp_strip_all_tags($message);
			$submission->set_response($message);
			$submission->set_status('aborted'); // try to prevent conflicts with other plugins
			return;
		}
		self::$deleted_user_id = $user_id;
		//Helper::mail($contact_form, $submission);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_hidden_fields($hidden_fields){
		if(!self::is_action()){
			return $hidden_fields;
		}
		$user_id = self::get_user_id();
		if(is_wp_error($user_id)){
			return $hidden_fields;
		}
		$hidden_fields['ifcf7_nonce'] = wp_create_nonce('ifcf7-edit-user_' . $user_id);
		$hidden_fields['ifcf7_user_id'] = $user_id;
		return $hidden_fields;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
