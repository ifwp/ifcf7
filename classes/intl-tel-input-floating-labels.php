<?php namespace IFCF7;

final class Intl_Tel_Input_Floating_Labels {

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

	private static function tel_html($html = '', $tag = null){
		$placeholder = ifcf7_placeholder_value($tag);
		$wrapper = $html->find('.wpcf7-form-control-wrap', 0);
		$wrapper->addClass('ifcf7-intl-tel-input-floating-labels');
		$input = $wrapper->find('input', 0);
		$input->addClass('form-control ifcf7-intl-tel-input');
		$input->inputmode = 'numeric';
		$iti_value = apply_filters('ifcf7_iti_value', '', $tag->name);
		if(!empty($iti_value)){
			$input->value = $iti_value;
		}
		$iti_json_value = apply_filters('ifcf7_iti_json_value', '', $tag->name);
		$input->outertext = $input->outertext . '<input type="hidden" value="' . $input->value . '" name="iti_' . $input->name . '" class="ifcf7-intl-tel-input-hidden"><input class="ifcf7-intl-tel-input-json" name="iti_json_' . $input->name . '" type="hidden" value="' . $iti_json_value . '"><label>' . $placeholder . '</label>';
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
		if('tel' !== $type){
			return $html;
		}
		if(!$tag->has_option('floating') or !$tag->has_option('intl')){
			return $html;
		}
		$html = self::tel_html($html_orig, $tag);
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7-intl-tel-input-floating-labels', 'intl-tel-input-floating-labels.js', ['ifcf7-intl-tel-input-jquery']);
		wp_add_inline_script('ifcf7-intl-tel-input-floating-labels', 'ifcf7_intl_tel_input_floating_labels.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-intl-tel-input-floating-labels', 'intl-tel-input-floating-labels.css', ['ifcf7-intl-tel-input']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
