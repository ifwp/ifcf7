<?php namespace IFCF7;

final class Data_Options {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_filter('ifcf7_image_select_attachment_id', [__CLASS__, 'ifcf7_image_select_attachment_id'], 10, 3);
        add_filter('wpcf7_form_tag_data_option', [__CLASS__, 'add_data_options'], 11, 3); // Fix for Conditional Fields PRO.
		add_filter('wpcf7_posted_data', [__CLASS__, 'fix_posted_data'], 30); // after pipes
        add_filter('wpcf7_posted_data_checkbox', [__CLASS__, 'fix_selectable_values'], 30, 3);
        add_filter('wpcf7_posted_data_checkbox*', [__CLASS__, 'fix_selectable_values'], 30, 3);
        add_filter('wpcf7_posted_data_radio', [__CLASS__, 'fix_selectable_values'], 30, 3);
        add_filter('wpcf7_posted_data_radio*', [__CLASS__, 'fix_selectable_values'], 30, 3);
        add_filter('wpcf7_posted_data_select', [__CLASS__, 'fix_selectable_values'], 30, 3);
        add_filter('wpcf7_posted_data_select*', [__CLASS__, 'fix_selectable_values'], 30, 3);
		self::default_data_options();
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static $additional_data = [], $data_options = [], $data_queries = [], $data_term_queries = [], $data_user_queries = [];

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function default_data_options(){
		$countries = ifcf7_intl_countries();
		self::$data_options['intl_countries'] = wp_list_pluck($countries, 0, 1);
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function add_data_option($option = '', $data = []){
        if(!wpcf7_is_name($option) or !is_array($data)){
            return;
        }
		if(ifwp_is_array_assoc($data)){
			self::$data_options[$option] = $data;
		} else {
			self::$data_options[$option] = array_combine($data, $data);
		}
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function add_data_options($data, $options, $args){
        foreach($options as $option){
			switch(true){
				case preg_match('/^posts\.(.+)$/', $option, $matches):
					$match = $matches[1];
					if(isset(self::$data_queries[$match])){
						if(!self::$data_queries[$match]['posts']){
							self::$data_queries[$match]['posts'] = get_posts(self::$data_queries[$match]['args']);
						}
						if(self::$data_queries[$match]['posts']){
							$data = (array) $data + wp_list_pluck(self::$data_queries[$match]['posts'], 'post_title', 'ID');
						}
					}
					break;
				case preg_match('/^terms\.(.+)$/', $option, $matches):
					$match = $matches[1];
					if(isset(self::$data_term_queries[$match])){
						if(!self::$data_term_queries[$match]['terms']){
							self::$data_term_queries[$match]['terms'] = get_terms(self::$data_term_queries[$match]['args']);
						}
						if(self::$data_term_queries[$match]['terms']){
							$data = (array) $data + wp_list_pluck(self::$data_term_queries[$match]['terms'], 'name', 'term_id');
						}
					}
					break;
				case preg_match('/^users\.(.+)$/', $option, $matches):
					$match = $matches[1];
					if(isset(self::$data_user_queries[$match])){
						if(!self::$data_user_queries[$match]['users']){
							self::$data_user_queries[$match]['users'] = get_users(self::$data_user_queries[$match]['args']);
						}
						if(self::$data_user_queries[$match]['users']){
							$data = (array) $data + wp_list_pluck(self::$data_user_queries[$match]['users'], 'display_name', 'ID');
						}
					}
					break;
				default:
					if(isset(self::$data_options[$option]) and ifwp_is_array_assoc(self::$data_options[$option])){
						$data = (array) $data + self::$data_options[$option];
					}
			}
        }
		return $data;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function add_data_query($option = '', $args = []){
        if(!wpcf7_is_name($option) or !is_array($args)){
            return;
        }
		if(isset($args['fields'])){
			unset($args['fields']);
		}
        self::$data_queries[$option] = [
			'args' => $args,
			'images' => [],
			'posts' => [],
		];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function add_data_term_query($option = '', $args = []){
        if(!wpcf7_is_name($option) or !is_array($args)){
            return;
        }
		if(isset($args['fields'])){
			unset($args['fields']);
		}
        self::$data_term_queries[$option] = [
			'args' => $args,
			'images' => [],
			'terms' => [],
		];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function add_data_user_query($option = '', $args = []){
        if(!wpcf7_is_name($option) or !is_array($args)){
            return;
        }
		if(isset($args['fields'])){
			unset($args['fields']);
		}
        self::$data_user_queries[$option] = [
			'args' => $args,
			'images' => [],
			'users' => [],
		];
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function fix_posted_data($posted_data){
		if(empty(self::$additional_data)){
			return $posted_data;
		}
		$posted_data = array_merge((array) $posted_data, self::$additional_data);
		return $posted_data;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function fix_selectable_values($value, $value_orig, $tag){
		if(!wpcf7_form_tag_supports($tag->type, 'selectable-values')){
            return $value;
        }
		$value_orig = (array) $value_orig;
        $data = $tag->get_data_option();
        if(!$data){
            return $value;
        }
        $data_flip = array_flip($data);
        if(is_array($value)){
            $label = [];
            foreach($value as $key => $v){
                $label[$key] = $v;
                if(isset($data_flip[$v])){
                    $value[$key] = $data_flip[$v];
                }
            }
        } else {
            $label = $value;
            if(isset($data_flip[$value])){
                $value = $data_flip[$value];
            }
        }
        self::$additional_data['raw_' . $tag->name] = $value;
        return $label;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_image_select_attachment_id($attachment_id, $title, $tag){
		$options = (array) $tag->get_option('data');
		foreach($options as $option){
			switch(true){
				case preg_match('/^posts\.(.+)$/', $option, $matches):
					$match = $matches[1];
					if(isset(self::$data_queries[$match])){
						if(!self::$data_queries[$match]['images']){
							foreach(self::$data_queries[$match]['posts'] as $post){
								self::$data_queries[$match]['images'][$post->post_title] = (int) get_post_thumbnail_id($post);
							}
						}
						if(isset(self::$data_queries[$match]['images'][$title])){
							$attachment_id = self::$data_queries[$match]['images'][$title];
						}
					}
					break;
				case preg_match('/^terms\.(.+)$/', $option, $matches):
					$match = $matches[1];
					if(isset(self::$data_term_queries[$match])){
						if(!self::$data_term_queries[$match]['images']){
							foreach(self::$data_term_queries[$match]['terms'] as $term){
								$thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
								if(!$thumb_id){
									$thumb_id = get_term_meta($term->term_id, 'taxonomy_thumbnail_id', true);
								}
								self::$data_term_queries[$match]['images'][$term->name] = (int) $thumb_id;
							}
						}
						if(isset(self::$data_term_queries[$match]['images'][$title])){
							$attachment_id = self::$data_term_queries[$match]['images'][$title];
						}
					}
					break;
				case preg_match('/^users\.(.+)$/', $option, $matches):
					$match = $matches[1];
					if(isset(self::$data_user_queries[$match])){
						if(!self::$data_user_queries[$match]['images']){
							foreach(self::$data_user_queries[$match]['users'] as $user){
								$thumb_id = get_user_meta($user->ID, 'thumbnail_id', true);
								self::$data_user_queries[$match]['images'][$user->display_name] = (int) $thumb_id;
							}
						}
						if(isset(self::$data_user_queries[$match]['images'][$title])){
							$attachment_id = self::$data_user_queries[$match]['images'][$title];
						}
					}
					break;
			}
		}
		return $attachment_id;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
