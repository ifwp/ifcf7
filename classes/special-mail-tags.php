<?php namespace IFCF7;

final class Special_Mail_Tags {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_filter('wpcf7_special_mail_tags', [__CLASS__, 'author'], 10, 3);
		add_filter('wpcf7_special_mail_tags', [__CLASS__, 'dynamic_author'], 10, 3);
		add_filter('wpcf7_special_mail_tags', [__CLASS__, 'dynamic_post'], 10, 3);
		add_filter('wpcf7_special_mail_tags', [__CLASS__, 'dynamic_user'], 10, 3);
		add_filter('wpcf7_special_mail_tags', [__CLASS__, 'post'], 10, 3);
		
		/*add_filter('wpcf7_special_mail_tags', [__CLASS__, 'referred_author'], 10, 3);
		add_filter('wpcf7_special_mail_tags', [__CLASS__, 'referred_post'], 10, 3);
		add_filter('wpcf7_special_mail_tags', [__CLASS__, 'referred_user'], 10, 3);*/
		
		add_filter('wpcf7_special_mail_tags', [__CLASS__, 'user'], 10, 3);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_object_meta($object = null, $key = '', $html = false){
		if($object instanceof \WP_Post){
			//wpcf7_post_related_smt
			switch($key){
				case 'id':
					return (string) $object->ID;
					break;
				case 'name':
					return $object->post_name;
					break;
				case 'title':
					return $html ? esc_html($object->post_title) : $object->post_title;
					break;
				case 'url':
					return get_permalink($object->ID);
					break;
				default:
					$type = 'post';
			}
		} elseif($object instanceof \WP_User){
			//get_the_author_meta
			/*if(in_array($key, ['login', 'pass', 'nicename', 'email', 'url', 'registered', 'activation_key', 'status'], true)){
				$key = 'user_' . $key;
			}*/
			//wpcf7_user_related_smt
			$primary_props = ['login', 'email', 'url'];
			if(in_array($key, $primary_props)){
				$key = 'user_' . $key;
			}
			$type = 'user';
		} else {
			return '';
		}
		if($object->__isset($key)){
			if(metadata_exists($type, $object->ID, $key)){
				$value = get_metadata($type, $object->ID, $key);
				$value = wpcf7_flat_join($value);
			} else {
				$value = $object->__get($key);
			}
			if($html){
				$value = esc_html($value);
				$value = wptexturize($value);
			}
			return trim($value);
		} else {
			return '';
		}
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_referred_post_id(){
		$referer_tag = Utilities::referer_tag();
		if(!$referer_tag){
			return 0;
		}
		$parsed = ifcf7_parse_referer_tag($referer_tag);
		if('post' !== $parsed['type']){
			return 0;
		}
		return $parsed['object_id'];
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_referred_user_id(){
		$referer_tag = Utilities::referer_tag();
		if(!$referer_tag){
			return 0;
		}
		$parsed = ifcf7_parse_referer_tag($referer_tag);
		if('user' !== $parsed['type']){
			return 0;
		}
		return $parsed['object_id'];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function author($output, $name, $html){
		if(!preg_match('/^ifcf7_author_(.+)$/', $name, $matches)){
			return $output;
		}
		$submission = ifcf7_get_current_submission();
		if(!$submission){
			return $output;
		}
		$post_id = (int) $submission->get_meta('container_post_id');
		if(!$post_id){
			return '';
		}
		$post = get_post($post_id);
		if(!$post->post_author){
			return '';
		}
		$user = get_userdata($post->post_author);
		return self::get_object_meta($user, $matches[1], $html);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function dynamic_author($output, $name, $html){
		if(!preg_match('/^ifcf7_dynamic_author_(.+)$/', $name, $matches)){
			return $output;
		}
		$post_id = self::get_referred_post_id();
		if(!$post_id){
			return '';
		}
		$post = get_post($post_id);
		if(!$post->post_author){
			return '';
		}
		$user = get_userdata($post->post_author);
		return self::get_object_meta($user, $matches[1], $html);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function dynamic_post($output, $name, $html){
		if(!preg_match('/^ifcf7_dynamic_post_(.+)$/', $name, $matches)){
			return $output;
		}
		$post_id = self::get_referred_post_id();
		if(!$post_id){
			return '';
		}
		$post = get_post($post_id);
		return self::get_object_meta($post, $matches[1], $html);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function dynamic_user($output, $name, $html){
		if(!preg_match('/^ifcf7_dynamic_user_(.+)$/', $name, $matches)){
			return $output;
		}
		$user_id = self::get_referred_user_id();
		if(!$user_id){
			return '';
		}
		$user = get_userdata($user_id);
		return self::get_object_meta($user, $matches[1], $html);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function post($output, $name, $html){
		if(!preg_match('/^ifcf7_post_(.+)$/', $name, $matches)){
			return $output;
		}
		$submission = ifcf7_get_current_submission();
		if(!$submission){
			return $output;
		}
		$post_id = (int) $submission->get_meta('container_post_id');
		if(!$post_id){
			return '';
		}
		$post = get_post($post_id);
		return self::get_object_meta($post, $matches[1], $html);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function user($output, $name, $html){
		if(!preg_match('/^ifcf7_user_(.+)$/', $name, $matches)){
			return $output;
		}
		$submission = ifcf7_get_current_submission();
		if(!$submission){
			return $output;
		}
		$user_id = (int) $submission->get_meta('current_user_id');
		if(!$user_id){
			return '';
		}
		$user = get_userdata($user_id);
		return self::get_object_meta($user, $matches[1], $html);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
