<?php namespace IFCF7;

final class Bootstrap_4 {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 10, 5);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function checkbox_html($html = '', $tag = null){
        $type = in_array($tag->basetype, ['checkbox', 'radio']) ? $tag->basetype : 'checkbox';
		$type = ('checkbox' === $type and $tag->has_option('switch')) ? 'switch' : $type;
		foreach($html->find('.wpcf7-list-item') as $li){
			$li->addClass('custom-control custom-' . $type);
			if($tag->has_option('inline')){
                $li->addClass('custom-control-inline');
            }
			$input = $li->find('input', 0);
			$input->addClass('custom-control-input');
            $id = $tag->name . '_' . str_replace('-', '_', sanitize_title($input->value));
			$input->id = $id;
			$label = $li->find('.wpcf7-list-item-label', 0);
			$label->addClass('custom-control-label');
			$label->for = $id;
			$label->tag = 'label';
			$freetext = '';
            if($li->hasClass('has-free-text')){
                $freetext = $li->find('.wpcf7-free-text', 0);
                $freetext->addClass('form-control mt-1');
                $free_text_value = apply_filters('ifcf7_free_text_value', '', $tag->name);
                if(!empty($free_text_value)){
                    $freetext->value = $free_text_value;
                }
            }
			$li->innertext = $input->outertext . $label->outertext . $freetext;
		}
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function file_html($html = '', $tag = null){
        $wrapper = $html->find('.wpcf7-form-control-wrap', 0);
        $wrapper->addClass('custom-file');
        $input = $wrapper->find('input', 0);
        $input->addClass('custom-file-input');
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
        $input->outertext = $input->outertext . '<label class="custom-file-label text-truncate" for="' . $input->id . '" data-browse="' . __('Select') . '">' . $placeholder . '</label>';
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function range_html($html = '', $tag = null){
        $wrapper = $html->find('.wpcf7-form-control-wrap', 0);
        $range = $wrapper->find('range', 0);
        $range->addClass('form-control-range');
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function select_html($html = '', $tag = null){
        $wrapper = $html->find('.wpcf7-form-control-wrap', 0);
        $select = $wrapper->find('select', 0);
		$select->addClass('custom-select');
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function submit_html($html = '', $tag = null){
        $submit = $html->find('input', 0);
        $submit->addClass('btn');
		$has_class = false;
		preg_match_all('/btn-[A-Za-z][-A-Za-z0-9_:.]*/', (string) $submit->class, $matches);
		$matches = array_filter($matches[0], function($match){
			return !in_array($match, ['btn-block', 'btn-lg', 'btn-sm']);
		});
		if($matches){
			$has_class = true;
		}
        if(!$has_class){
            $submit->addClass('btn-primary');
        }
		if($submit->hasClass('has-spinner')){
			$submit->removeClass('has-spinner');
		}
		$submit->tag = 'button';
		$submit->addClass('d-inline-flex align-items-center justify-content-center');
		$fa_icon = ifcf7_fa_icon($tag);
		$value = '<span class="ifcf7-submit-value">' . $submit->value . '</span>';
		if($fa_icon){
			if($tag->has_option('icon_only')){
				$submit->innertext = ifcf7_fa_icon($tag, '');
			} else {
				if($tag->has_option('icon_right')){
					$fa_icon = ifcf7_fa_icon($tag, 'right');
					$submit->innertext = $value . $fa_icon;
				} else {
					$submit->innertext = $fa_icon . $value;
				}
			}
		} else {
			$submit->innertext = $value;
		}
		$submit->innertext = apply_filters('ifcf7_submit_button_innertext', $submit->innertext, $tag);
		$submit->innertext = $submit->innertext . '<span class="ifcf7-spinner ml-2"><span></span></span>';
		$submit->outertext = $submit->outertext . '</button>';
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function text_html($html = '', $tag = null){
        $wrapper = $html->find('.wpcf7-form-control-wrap', 0);
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
        return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function textarea_html($html = '', $tag = null){
        $wrapper = $html->find('.wpcf7-form-control-wrap', 0);
        $textarea = $wrapper->find('textarea', 0);
        $textarea->addClass('form-control');
		if($tag->has_option('autoresize')){
			$textarea->addClass('autoresize');
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
        switch($type){
            case 'acceptance':
            case 'checkbox':
            case 'radio':
                $html = self::checkbox_html($html_orig, $tag);
                break;
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
            case 'range':
                $html = self::range_html($html_orig, $tag);
                break;
            case 'select':
                $html = self::select_html($html_orig, $tag);
                break;
            case 'submit':
                $html = self::submit_html($html_orig, $tag);
                break;
            case 'textarea':
                $html = self::textarea_html($html_orig, $tag);
                break;
        }
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
        wp_enqueue_script('bs-custom-file-input', 'https://cdn.jsdelivr.net/npm/bs-custom-file-input@1.3.4/dist/bs-custom-file-input.min.js', ['jquery'], '1.3.4', true);
        wp_add_inline_script('bs-custom-file-input', 'jQuery(function(){ bsCustomFileInput.init(); });');
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
