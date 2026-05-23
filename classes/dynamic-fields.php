<?php namespace IFCF7;

final class Dynamic_Fields {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('init', [__CLASS__, 'register_shortcodes']);
		add_filter('wpcf7_contact_form_property_form', [__CLASS__, 'wpcf7_contact_form_property_form'], 10, 2);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    //private static $dynamic_fields = [];
	private static $contact_form = null, $post_id = 0;

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function add_dynamic_field($name = '', $data = ''){
        if(!wpcf7_is_name($name)){
            return;
        }
		//self::$dynamic_fields[$name] = $data;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function dynamic_field_func($atts, $content = ''){
		$atts = shortcode_atts([
            'name' => '',
        ], $atts, 'ifcf7_dynamic_field');
		$data = '';
		$name = $atts['name'];
		/*if(isset(self::$dynamic_fields[$name])){
			$data = (string) self::$dynamic_fields[$name];
		}*/
		$data = apply_filters('ifcf7_dynamic_field_' . $name, $content, self::$contact_form, self::$post_id);
		return $data;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function register_shortcodes(){
		add_shortcode('ifcf7_dynamic_field', [__CLASS__, 'dynamic_field_func']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_contact_form_property_form($val, $contact_form){
		global $shortcode_tags;
		if(is_admin() and !wp_doing_ajax()){
			return $val;
		}
		if(!has_shortcode($val, 'ifcf7_dynamic_field')){
			return $val;
		}
		self::$contact_form = $contact_form;
		if(in_the_loop()){
			self::$post_id = get_the_ID();
		} elseif(isset($_POST['_wpcf7'], $_POST['_wpcf7_container_post']) and (int) $_POST['_wpcf7'] === $contact_form->id()){
			self::$post_id = (int) $_POST['_wpcf7_container_post'];
		}
		$tmp = $shortcode_tags;
		remove_all_shortcodes();
		self::register_shortcodes();
		$val = do_shortcode($val);
		$shortcode_tags = $tmp;
		return $val;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
