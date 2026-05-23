<?php namespace IFCF7;

final class Button {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_filter('ifcf7_shortcode_tag_html', [__CLASS__, 'ifcf7_shortcode_tag_html'], 10, 5);
		//add_filter('wpcf7_skip_spam_check', [__CLASS__, 'wpcf7_skip_spam_check'], 10, 2);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function is_action($contact_form = null){
		return ('button' === ifcf7_action($contact_form));
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
		if(!self::is_action($contact_form)){
			return $output;
		}
		$invalid = [];
		$tags = wp_list_pluck($contact_form->scan_form_tags(), 'type', 'name');
		foreach($tags as $name => $type){
    		if(!in_array($type, ['hidden', 'submit'])){
				$invalid[] = $name;
			}
    	}
		if($invalid){
			$error = current_user_can('manage_options') ? sprintf(__('Invalid parameter(s): %s'), implode(', ', $invalid)) . '.' : __('Something went wrong.');
			return '<div class="alert alert-danger" role="alert">' . $error . '</div>';
		}
		return $output;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    /*public static function wpcf7_skip_spam_check($skip_spam_check, $submission){
		if(!self::is_action()){
			$skip_spam_check = true;
		}
		$contact_form = wpcf7_get_current_contact_form();
        if(null === $contact_form){
            return $skip_spam_check;
        }
		
		if('button' === ifcf7_action($contact_form)){
			$skip_spam_check = true;
		}
		return $skip_spam_check;
    }*/

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
