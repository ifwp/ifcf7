<?php namespace IFCF7;

final class Edit_Post {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('wpcf7_before_send_mail', [__CLASS__, 'wpcf7_before_send_mail'], 10, 3);
		add_filter('ifcf7_file_value', [__CLASS__, 'ifcf7_file_value'], 10, 2);
		add_filter('ifcf7_free_text_value', [__CLASS__, 'ifcf7_free_text_value'], 10, 2);
		add_filter('ifcf7_iti_value', [__CLASS__, 'ifcf7_iti_value'], 10, 2);
		add_filter('ifcf7_iti_json_value', [__CLASS__, 'ifcf7_iti_json_value'], 10, 2);
		add_filter('ifcf7_shortcode_tag_html', [__CLASS__, 'ifcf7_shortcode_tag_html'], 10, 5);
		add_filter('shortcode_atts_ifcf7_load_form', [__CLASS__, 'shortcode_atts_ifcf7_load_form'], 10, 3);
		add_filter('shortcode_atts_wpcf7', [__CLASS__, 'shortcode_atts_wpcf7'], 10, 3);
		add_filter('wpcf7_form_elements', [__CLASS__, 'wpcf7_form_elements']);
		add_filter('wpcf7_form_hidden_fields', [__CLASS__, 'wpcf7_form_hidden_fields']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static $in_the_loop = false;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_post_id($contact_form = null, $submission = null){
		if(null === $contact_form){
			$contact_form = wpcf7_get_current_contact_form();
		}
		if(null === $contact_form){
			return ifwp_error(__('The requested contact form was not found.', 'contact-form-7'));
		}
		if(!self::is_action($contact_form)){
			return ifwp_error(sprintf(__('%1$s is not of type %2$s.'), $type, 'edit_post'));
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
		if(!current_user_can('edit_post', $post_id)){
			$message = __('Sorry, you are not allowed to edit this post.') . ' ' . __('You need a higher level of permission.');
			return ifwp_error($message);
		}
		if('trash' === get_post_status($post_id)){
			return ifwp_error(ifwp_first_p(__('You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.')));
		}
		return $post_id;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_action($contact_form = null){
		return ('edit_post' === ifcf7_action($contact_form));
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_file_value($value, $name){
		if('' !== $value){
			return $value;
		}
		if(!self::is_action()){
			return $value;
		}
		$post_id = self::get_post_id();
		if(is_wp_error($post_id)){
			return $value;
		}
		return get_post_meta($post_id, 'raw_' . $name, true);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_free_text_value($value, $name){
		if('' !== $value){
			return $value;
		}
		if(!self::is_action()){
			return $value;
		}
		$post_id = self::get_post_id();
		if(is_wp_error($post_id)){
			return $value;
		}
		return get_post_meta($post_id, $name . '_free_text', true);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_iti_value($value, $name){
		if('' !== $value){
			return $value;
		}
		if(!self::is_action()){
			return $value;
		}
		$post_id = self::get_post_id();
		if(is_wp_error($post_id)){
			return $value;
		}
		return get_post_meta($post_id, 'iti_' . $name, true);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_iti_json_value($value, $name){
		if('' !== $value){
			return $value;
		}
		if(!self::is_action()){
			return $value;
		}
		$post_id = self::get_post_id();
		if(is_wp_error($post_id)){
			return $value;
		}
		return esc_attr(get_post_meta($post_id, 'iti_json_' . $name, true));
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
		if(!self::is_action($contact_form)){
			return $output;
		}
		if(!in_the_loop()){
			return '<div class="alert alert-danger" role="alert">Loop hasn\'t started or ended.</div>';
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
				if(!current_user_can('edit_post', $post_id)){
					$out['disabled'] = 1;
				} elseif('trash' === get_post_status($post_id)){
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
		//$response = 'post' === get_post_type($post_id) ? __('Post updated.') : __('Item updated.');
		//$submission->set_response($response);
		//Helper::mail($contact_form, $submission);
		$stored = Storage::store_submission('update', 'post', $post_id, $contact_form, $submission);
		if(is_wp_error($stored)){
            $abort = true; // prevent mail_sent and mail_failed actions
			$message = $stored->get_error_message();
			$message = wp_strip_all_tags($message);
			$submission->set_response($message);
            $submission->set_status('aborted'); // try to prevent conflicts with actions and other plugins
            return;
        }
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_elements($elements){
		global $wp_query;
		if(!self::is_action()){
			return $elements;
		}
		$post_id = self::get_post_id();
		if(is_wp_error($post_id)){
			return $elements;
		}
		wp_reset_postdata(); // after replacing all form tags
		$wp_query->in_the_loop = self::$in_the_loop;
		return $elements;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_hidden_fields($hidden_fields){
		global $post, $wp_query;
		if(!self::is_action()){
			return $hidden_fields;
		}
		$post_id = self::get_post_id();
		if(is_wp_error($post_id)){
			return $hidden_fields;
		}
		$hidden_fields['ifcf7_nonce'] = wp_create_nonce('ifcf7-edit-post_' . $post_id);
		$hidden_fields['ifcf7_post_id'] = $post_id;
		$post = get_post($post_id);
		setup_postdata($post); // before replacing all form tags
		self::$in_the_loop = $wp_query->in_the_loop;
		if(!$wp_query->in_the_loop){
			$wp_query->in_the_loop = true;
		}
		return $hidden_fields;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
