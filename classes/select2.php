<?php namespace IFCF7;

final class Select2 {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 30, 5);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function select_html($html = '', $tag = null){
		$wrapper = $html->find('.wpcf7-form-control-wrap', 0);
		$select = $wrapper->find('select', 0);
		$select->addClass('custom-select form-control ifcf7-select2');
		if($tag->has_option('free_text')){
			$select->{'data-tags'} = 'true';
		}
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
		if(!$tag->has_option('select2')){
			return $html;
		}
		$placeholder = ifcf7_placeholder_value($tag);
		if(empty($placeholder)){
			return $html;
		}
		if('select' !== $type){
			return $html;
		}
		$html = self::select_html($html_orig, $tag);
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js', ['contact-form-7'], '4.0.13', true);
		ifwp_enqueue_asset('ifcf7-select2', 'select2.js', ['select2']);
		wp_add_inline_script('ifcf7-select2', 'ifcf7_select2.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css', ['contact-form-7'], '4.0.13');
		wp_enqueue_style('select2-theme-bootstrap4', 'https://cdn.jsdelivr.net/npm/select2-theme-bootstrap4@1.0.1/dist/select2-bootstrap.min.css', ['select2'], '1.0.1');
		ifwp_enqueue_asset('ifcf7-select2', 'select2.css', ['select2-theme-bootstrap4']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
