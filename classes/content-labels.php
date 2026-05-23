<?php namespace IFCF7;

final class Content_Labels {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 50, 5);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function form_tag_html($html, $tag, $type, $basetype, $html_orig){
		if(in_array($type, ['acceptance'])){
			return $html;
		}
		if(in_array($type, ['textarea']) and !$tag->has_option('content_as_label')){
			return $html;
		}
		$content = $tag->content ? ifwp_remove_whitespaces($tag->content) : '';
		if(!$content){
			return $html;
		}
		$html_orig = $html;
		if(in_array($type, ['textarea'])){
			$html_orig = ifwp_str_get_html($html_orig);
			if(is_wp_error($html_orig)){
				return $html;
			}
			$wrapper = $html_orig->find('.wpcf7-form-control-wrap', 0);
	        $textarea = $wrapper->find('textarea', 0);
			if(esc_textarea($content) === $textarea->innertext){
				$textarea->innertext = '';
			}
		}
		$html = '<label class="ifcf7-content-label"';
		$id = $tag->get_id_option();
		if($id){
			$html .= ' for="' . $id . '"';
		}
		$html .= '>' . $content . '</label>' . $html_orig;
		return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
