<?php namespace IFCF7;

final class Image_Select {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 20, 5);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function checkbox_html($html = '', $tag = null){
		$form_control = $html->find('.wpcf7-form-control', 0);
		$form_control->addClass('d-block ifcf7-image-select-form-control');
		foreach($form_control->find('.wpcf7-list-item') as $li){
			$li->addClass('d-inline-flex ifcf7-image-select mb-3 mt-0 mx-0 position-relative');
			$input = $li->find('input', 0);
			$input->addClass('ifcf7-image-select-input position-absolute');
            $id = $tag->name . '_' . str_replace('-', '_', sanitize_title($input->value));
			$input->id = $id;
			$label = $li->find('.wpcf7-list-item-label', 0);
			$label->addClass('d-block ifcf7-image-select-label mb-0 mw-100 pr-3 w-100');
			$label->for = $id;
			$label->tag = 'label';
			$title = $label->innertext;
			$attachment_id = apply_filters('ifcf7_image_select_attachment_id', 0, $title, $tag);
			if($attachment_id and wp_attachment_is_image($attachment_id)){
				$src = wp_get_attachment_thumb_url($attachment_id);
			} else {
				$src = 'https://via.placeholder.com/150?text=' . urlencode($title);
			}
			$label->innertext = '<img alt="' . $title . '" class="d-block img-thumbnail mx-auto" src="' . $src . '" title="' . $title . '">';
			if($tag->has_option('use_label_element')){
				//$label->innertext .= '<small class="form-text text-center text-muted text-truncate">' . $title . '</small>';
				$label->innertext .= '<small class="form-text text-center text-muted">' . $title . '</small>';
			}
			$freetext = '';
            if($li->hasClass('has-free-text')){
                $freetext = $li->find('.wpcf7-free-text', 0);
                $freetext->addClass('form-control mt-1');
                $free_text_value = apply_filters('ifcf7_free_text_value', '', $tag->name);
                if($free_text_value){
                    $freetext->value = $free_text_value;
                }
            }
			$li->innertext = $input->outertext . $label->outertext . $freetext;
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
		if(!in_array($type, ['checkbox', 'radio'])){
			return $html;
		}
		if(!$tag->has_option('image_select')){
			return $html;
		}
		$html = self::checkbox_html($html_orig, $tag);
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-image-select', 'image-select.css', ['contact-form-7']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
