<?php namespace IFCF7;

final class ITI_Floating_Labels {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 40, 5);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function text_html($html = '', $tag = null){
		$placeholder = ifcf7_placeholder_value($tag);
		$wrapper = $html->find('.wpcf7-form-control-wrap', 0);
		$wrapper->addClass('ifcf7-iti-floating-labels');
		if($tag->has_option('no_flags')){
			$wrapper->addClass('ifcf7-iti-no-flags');
		}
		$input = $wrapper->find('input', 0);
		$input->addClass('form-control ifcf7-iti');
		if(!$tag->has_option('no_flags') and $tag->has_option('no_dial_code')){
			$input->addClass('ifcf7-iti-no-dial-code');
		}
		$input->inputmode = 'numeric';
		$iti_value = apply_filters('ifcf7_iti_value', '', $tag->name);
		if(!empty($iti_value)){
			$input->value = $iti_value;
		}
		$iti_json_value = apply_filters('ifcf7_iti_json_value', '', $tag->name);
		$input->outertext = $input->outertext . '<input type="hidden" value="' . $input->value . '" name="iti_' . $input->name . '" class="ifcf7-iti-hidden"><input class="ifcf7-iti-json" name="iti_json_' . $input->name . '" type="hidden" value="' . $iti_json_value . '"><label>' . $placeholder . '</label>';
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function form_tag_html($html, $tag, $type, $basetype, $html_orig){
		$html_orig = ifwp_str_get_html($html_orig);
		if(is_wp_error($html_orig)){
			return $html;
		}
		if('text' !== $type){
			return $html;
		}
		if(!$tag->has_option('floating') or !$tag->has_option('iti')){
			return $html;
		}
		$html = self::text_html($html_orig, $tag);
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7-iti-floating-labels', 'iti-floating-labels.js', ['ifcf7-iti']);
		wp_add_inline_script('ifcf7-iti-floating-labels', 'ifcf7_iti_floating_labels.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-iti-floating-labels', 'iti-floating-labels.css', ['ifcf7-iti']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
