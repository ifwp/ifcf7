<?php namespace IFCF7;

final class Delete_Post {

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

    private static $deleted_post_id = 0;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_post_id($contact_form = null, $submission = null){
		if(null === $contact_form){
			$contact_form = wpcf7_get_current_contact_form();
		}
		if(null === $contact_form){
			return ifwp_error(__('The requested contact form was not found.', 'contact-form-7'));
		}
		if(!self::is_action($contact_form)){
			return ifwp_error(sprintf(__('%1$s is not of type %2$s.'), $type, 'delete_post'));
		}
		if(null === $submission){
			$submission = \WPCF7_Submission::get_instance();
		}
		$missing = [];
		if(null === $submission){
			$nonce = null;
			$post_id = $contact_form->shortcode_attr('post_id');
			if(empty($post_id)){
				$missing[] = 'post_id';
			}
		} else {
			$nonce = $submission->get_posted_data('ifcf7_nonce');
			if(empty($nonce)){
				$missing[] = 'ifcf7_nonce';
			}
			$post_id = $submission->get_posted_data('ifcf7_post_id');
			if(empty($post_id)){
				$missing[] = 'ifcf7_post_id';
			}
		}
		if($missing){
			return ifwp_error(sprintf(__('Missing parameter(s): %s'), ifwp_implode_and($missing)) . '.');
		}
		if(null !== $nonce and !wp_verify_nonce($nonce, 'ifcf7-edit-post_' . $post_id)){
			return ifwp_error(__('The link you followed has expired.'));
		}
		$post_id = self::sanitize_post_id($post_id);
		if(0 === $post_id){
			return ifwp_error(__('Invalid post ID.'));
		}
		if(!current_user_can('delete_post', $post_id)){
			$message = __('Sorry, you are not allowed to delete this post.') . ' ' . __('You need a higher level of permission.');
			return ifwp_error($message);
		}
		if('trash' === get_post_status($post_id)){
			return ifwp_error(__('The post has already been deleted.'));
		}
		return $post_id;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_action($contact_form = null){
		return ('delete_post' === ifcf7_action($contact_form));
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_deleted_post_id(){
		return self::$deleted_post_id;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
		if(!self::is_action($contact_form)){
			return $output;
		}
		$post_id = self::get_post_id();
		if(is_wp_error($post_id)){
			return '<div class="alert alert-danger" role="alert">' . $post_id->get_error_message() . '</div>';
		}
		return $output;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function sanitize_post_id($post_id){
		$post = null;
		if(is_numeric($post_id)){
			$post = get_post($post_id);
		} else {
			if('current' === $post_id){
				if(in_the_loop()){
					$post = get_post();
				}
			}
		}
		if(null === $post){
			return 0;
		}
		return $post->ID;
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
		if(isset($atts['post_id'])){
			$post_id = self::sanitize_post_id($atts['post_id']);
			if($post_id){
				$out['post_id'] = $post_id;
				$out['post_nonce'] = wp_create_nonce('ifcf7-setup-post_' . $post_id);
				if(!current_user_can('delete_post', $post_id)){
					$out['disabled'] = 1;
				} elseif('trash' === get_post_status($post_id)){
					$out['disabled'] = 1;
				}
			} else {
				
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
			$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
			$post_nonce = isset($_POST['post_nonce']) ? $_POST['post_nonce'] : '';
			$post_nonce_valid = $post_id ? wp_verify_nonce($post_nonce, 'ifcf7-setup-post_' . $post_id) : false;
			if($post_nonce_valid){
				$out['post_id'] = $post_id;
			}
		} else {
			// old
			if(isset($atts['post_id'])){
				$out['post_id'] = $atts['post_id'];
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
		$post_id = self::get_post_id($contact_form, $submission);
		if(is_wp_error($post_id)){
			$abort = true; // prevent mail_sent and mail_failed actions
			$message = $post_id->get_error_message();
			$message = wp_strip_all_tags($message);
			$submission->set_response($message);
			$submission->set_status('aborted'); // try to prevent conflicts with other plugins
			return;
		}
		if(!wp_trash_post($post_id)){
			$abort = true; // prevent mail_sent and mail_failed actions
			$message = __('Sorry, the post could not be deleted.');
			$message = wp_strip_all_tags($message);
			$submission->set_response($message);
			$submission->set_status('aborted'); // try to prevent conflicts with other plugins
			return;
		}
		self::$deleted_post_id = $post_id;
		//Helper::mail($contact_form, $submission);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_hidden_fields($hidden_fields){
		if(!self::is_action()){
			return $hidden_fields;
		}
		$post_id = self::get_post_id();
		if(is_wp_error($post_id)){
			return $hidden_fields;
		}
		$hidden_fields['ifcf7_nonce'] = wp_create_nonce('ifcf7-edit-post_' . $post_id);
		$hidden_fields['ifcf7_post_id'] = $post_id;
		return $hidden_fields;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
