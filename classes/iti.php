<?php namespace IFCF7;

final class ITI {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 30, 5);
		add_filter('wpcf7_validate_text*', [__CLASS__, 'wpcf7_validate_text'], 20, 2);
		add_filter('wpcf7_validate_text', [__CLASS__, 'wpcf7_validate_text'], 20, 2);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function text_html($html = '', $tag = null){
        $wrapper = $html->find('.wpcf7-form-control-wrap', 0);
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
        $input->outertext = $input->outertext . '<input class="ifcf7-iti-hidden" name="iti_' . $input->name . '" type="hidden" value="' . $input->value . '"><input class="ifcf7-iti-json" name="iti_json_' . $input->name . '" type="hidden" value="' . $iti_json_value . '">';
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
		if(!$tag->has_option('iti')){
			return $html;
		}
		$html = self::text_html($html_orig, $tag);
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		wp_enqueue_script('intl-tel-input-jquery', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.16/js/intlTelInput-jquery.min.js', ['contact-form-7'], '17.0.16', true);
		ifwp_enqueue_asset('ifcf7-iti', 'iti.js', ['intl-tel-input-jquery', 'jquery-inputmask']);
		$countries = [];
		$country = ifwp_remote_country();
		if($country){
			$countries[] = $country;
		}
		$countries = apply_filters('ifcf7_intl_tel_input_preferred_countries', $countries);
		$countries = array_map('strtolower', $countries);
		if($countries){
			wp_add_inline_script('ifcf7-iti', 'ifcf7_iti.preferred_countries = ' . wp_json_encode($countries) . ';');
		}
		wp_add_inline_script('ifcf7-iti', 'ifcf7_iti.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		wp_enqueue_style('intl-tel-input', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.16/css/intlTelInput.css', ['contact-form-7'], '17.0.16');
		ifwp_enqueue_asset('ifcf7-iti', 'iti.css', ['intl-tel-input']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_validate_text($result, $tag){
		if(!$tag->has_option('iti')){
			return $result;
		}
		$value = Helper::get_posted_data($tag->name);
		$iti_value = Helper::get_posted_data('iti_' . $tag->name);
		if('' !== $value and !preg_match('/^\+[1-9]\d{1,14}$/', $iti_value)){
			$result->invalidate($tag, wp_strip_all_tags(__('The telephone number is invalid.', 'contact-form-7')));
			return $result;
		}
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
