<?php namespace IFCF7;

final class Pipes {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_filter('wpcf7_posted_data', [__CLASS__, 'fix_posted_data'], 20); // after free text
        add_filter('wpcf7_posted_data_checkbox', [__CLASS__, 'fix_selectable_values'], 20, 3);
        add_filter('wpcf7_posted_data_checkbox*', [__CLASS__, 'fix_selectable_values'], 20, 3);
        add_filter('wpcf7_posted_data_radio', [__CLASS__, 'fix_selectable_values'], 20, 3);
        add_filter('wpcf7_posted_data_radio*', [__CLASS__, 'fix_selectable_values'], 20, 3);
        add_filter('wpcf7_posted_data_select', [__CLASS__, 'fix_selectable_values'], 20, 3);
        add_filter('wpcf7_posted_data_select*', [__CLASS__, 'fix_selectable_values'], 20, 3);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static $additional_data = [], $data_options = [];

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
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
        if(ifcf7_has_pipes($tag)){
        	self::$additional_data['raw_' . $tag->name] = $value;
        }
        return $value_orig;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
