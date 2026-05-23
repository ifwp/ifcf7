<?php namespace IFCF7;

final class TinyMCE {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		if(!defined('IFCF7_TINY_API_KEY')){
			return;
		}
		if(isset($_GET['fl_builder'])){
			return;
		}
		add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
        add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 30, 5);
		add_filter('wpcf7_mail_tag_replaced_textarea', [__CLASS__, 'revert_html'], 10, 4);
		add_filter('wpcf7_mail_tag_replaced_textarea*', [__CLASS__, 'revert_html'], 10, 4);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function textarea_html($html = '', $tag = null){
		$wrapper = $html->find('.wpcf7-form-control-wrap', 0);
		$wrapper->addClass('d-none');
		$textarea = $wrapper->find('textarea', 0);
		$textarea->addClass('form-control ifcf7-tinymce');
		$loading_message = apply_filters('ifcf7_field_loading_message', __('Field Loading...', 'fl-builder'), $tag);
		$html .= '<div class="alert bg-white border clearfix ifcf7-tinymce-loading-message text-secondary ' . $tag->name . '" role="alert"><span class="float-right ifcf7-spinner ml-2"><span></span></span>' . $loading_message . '</div>';
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
		if('textarea' !== $type){
			return $html;
		}
		if(!$tag->has_option('tinymce')){
			return $html;
		}
		$html = self::textarea_html($html_orig, $tag);
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function revert_html($replaced, $submitted, $html, $mail_tag){
		$form_tag = $mail_tag->corresponding_form_tag();
		if(!$form_tag->has_option('tinymce')){
			return $replaced;
		}
		$replaced = $submitted;
		if(null !== $replaced){
			$replaced = wpcf7_flat_join($submitted);
		}
		return $replaced;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		wp_enqueue_script('tinymce', 'https://cdn.tiny.cloud/1/' . IFCF7_TINY_API_KEY . '/tinymce/5.10.5/tinymce.min.js', ['contact-form-7', 'jquery'], '5.10.5', true);
		wp_enqueue_script('jquery-tinymce', 'https://cdn.tiny.cloud/1/' . IFCF7_TINY_API_KEY . '/tinymce/5.10.5/jquery.tinymce.min.js', ['tinymce'], '5.10.5', true);
		ifwp_enqueue_asset('ifcf7-tinymce', 'tinymce.js', ['jquery-tinymce']);
		$object = [
			'plugins' => 'autoresize lists',
			'toolbar' => 'textgroup aligngroup listgroup insertgroup',
			'toolbar_groups' => [
				'aligngroup' => [
					'icon' => 'align-left',
					'items' => 'alignleft aligncenter alignright alignjustify',
					'tooltip' => 'Align',
				],
				'listgroup' => [
					'icon' => 'unordered-list',
					'items' => 'bullist numlist indent outdent',
					'tooltip' => 'Lists',
				],
				'textgroup' => [
					'icon' => 'format',
					'items' => 'bold italic underline strikethrough',
					'tooltip' => 'Text',
				],
			],
		];
		$object = apply_filters('ifcf7_tinymce_object', $object);
		wp_localize_script('ifcf7-tinymce', 'ifcf7_tinymce_object', $object);
		wp_add_inline_script('ifcf7-tinymce', 'ifcf7_tinymce.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-tinymce', 'tinymce.css', ['contact-form-7']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
