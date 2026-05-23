<?php namespace IFCF7;

final class Floating_Labels {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 20, 5);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function file_html($html = '', $tag = null){
        $wrapper = $html->find('.wpcf7-form-control-wrap', 0);
        $wrapper->addClass('ifcf7-floating-labels');
        $input = $wrapper->find('input', 0);
        $input->addClass('form-control');
		/*if(!empty($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false){
			$input->addClass('form-control-iphone');
		}*/
        if(empty($input->id)){
            $input->id = $tag->name;
        }
        if($tag->has_option('multiple')){
            $input->multiple = 'multiple';
            $input->name .= '[]';
        }
		$placeholder = ifcf7_placeholder_value($tag);
        if(empty($placeholder)){
            $placeholder = __('Select Files');
        }
        $input->outertext = $input->outertext . '<label>' . $placeholder . '</label>';
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function select_html($html = '', $tag = null){
		$placeholder = ifcf7_placeholder_value($tag);
		$wrapper = $html->find('.wpcf7-form-control-wrap', 0);
		$wrapper->addClass('ifcf7-floating-labels');
		$select = $wrapper->find('select', 0);
		$select->addClass('custom-select');
		if(!empty($select->find('option[selected]', 0)->value)){
			$select->addClass('placeholder-hidden');
		}
		$option = $select->find('option', 0);
		$option->innertext = '';
		$select->outertext = $select->outertext . '<label>' . $placeholder . '</label>';
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function text_html($html = '', $tag = null){
		$placeholder = ifcf7_placeholder_value($tag);
		$wrapper = $html->find('.wpcf7-form-control-wrap', 0);
		$wrapper->addClass('ifcf7-floating-labels');
		$input = $wrapper->find('input', 0);
		$input->addClass('form-control');
		if('number' === $tag->basetype){
			$input->inputmode = 'numeric';
		}
		if($tag->has_option('inputmode')){
			$inputmode = $tag->get_option('inputmode', 'id', true);
			if(in_array($inputmode, ['none', 'text', 'decimal', 'numeric', 'tel', 'search', 'email', 'url'])){ // https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/inputmode#values
				$input->inputmode = $inputmode;
			}
		}
		$input->outertext = $input->outertext . '<label>' . $placeholder . '</label>';
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function textarea_html($html = '', $tag = null){
		$placeholder = ifcf7_placeholder_value($tag);
		$wrapper = $html->find('.wpcf7-form-control-wrap', 0);
		$textarea = $wrapper->find('textarea', 0);
		$textarea->addClass('form-control autoresize'); // autoresize
		$wrapper->addClass('ifcf7-floating-labels');
		$textarea->cols = null;
		$textarea->rows = null;
		$textarea->outertext = $textarea->outertext . '<label>' . $placeholder . '</label>';
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
		if(!$tag->has_option('floating')){
			return $html;
		}
		$placeholder = ifcf7_placeholder_value($tag);
		if(empty($placeholder)){
			return $html;
		}
        switch($type){
            case 'date':
            case 'email':
            case 'number':
            case 'password':
            case 'tel':
            case 'text':
            case 'url':
                $html = self::text_html($html_orig, $tag);
                break;
			case 'file':
                $html = self::file_html($html_orig, $tag);
                break;
            case 'select':
                $html = self::select_html($html_orig, $tag);
                break;
            case 'textarea':
                $html = self::textarea_html($html_orig, $tag);
                break;
        }
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7-floating-labels', 'floating-labels.js', ['contact-form-7', 'ifwp']);
		wp_add_inline_script('ifcf7-floating-labels', 'ifcf7_floating_labels.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-floating-labels', 'floating-labels.css', ['contact-form-7']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
