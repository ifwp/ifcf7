<?php namespace IFCF7;

final class File {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_filter('ifcf7_form_tag_html', [__CLASS__, 'form_tag_html'], 20, 5);
		add_filter('ifwp_handle_upload_file_name', [__CLASS__, 'ifwp_handle_upload_file_name']);
		add_filter('wpcf7_validate_file*', [__CLASS__, 'wpcf7_validate_file'], 20, 2);
		add_filter('wpcf7_validate_file', [__CLASS__, 'wpcf7_validate_file'], 20, 2);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function add_file($file_id = 0){
        if('attachment' !== get_post_type($file_id)){
			return '';
		}
		$html = '<span data-ifcf7_file_id="' . $file_id . '" class="ifcf7_file_container border bg-white rounded py-2 px-3 d-flex align-items-center mb-2 mr-2">';
		$html .= '<span class="ifcf7_file_delete dashicons dashicons-no text-danger mr-1" style="cursor: pointer;" title="Delete"></span>';
		$html .= '<span class="ifcf7_file_name text-truncate"><a href="' . wp_get_attachment_url($file_id) . '" target="_blank">' . get_the_title($file_id) . '</a></span>';
		$html .= '</span>';
		return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function file_html($html = '', $tag = null){
        $wrapper = $html->find('.wpcf7-form-control-wrap', 0);
        //$wrapper->addClass('custom-file');
		$wrapper->addClass('ifcf7_file_upload border bg-light pt-2 pl-2 rounded d-flex flex-wrap align-items-center');
        $input = $wrapper->find('input', 0);
        //$input->addClass('custom-file-input');
		$input->addClass('d-none');
        if(empty($input->id)){
            $input->id = $tag->name;
        }
        /*if($tag->has_option('multiple')){
            $input->multiple = 'multiple';
            $input->name .= '[]';
        }*/
		if($tag->has_option('multiple')){
            $input->addClass('ifcf7_multiple');
        }
		$placeholder = ifcf7_placeholder_value($tag);
        if(empty($placeholder)){
            $placeholder = __('Select Files');
        }
		$file_html = '';
		$file_value = apply_filters('ifcf7_file_value', '', $tag->name);
		if($file_value){
			$file_ids = json_decode($file_value, true);
			if($file_ids){
				foreach($file_ids as $file_id){
					$file_html .= self::add_file($file_id);
				}
			}
		}
		
		if(wp_is_mobile()){
			//$input->outertext = '<label class="text-primary border rounded p-2 mb-0 bg-white mb-2 mr-2" for="' . $input->id . '">' . $placeholder . $input->outertext . '</label>';
		} else {
			//$input->outertext = '<div class="d-flex align-items-center mb-2 mr-2"><span class="dashicons dashicons-cloud-upload mr-1"></span><span>' . __('Drop files to upload') . '</span><span class="mx-2">' . _x('or', 'Uploader: Drop files here - or - Select Files') .  '</span><label class="border rounded py-2 px-3 mb-0 bg-white d-flex align-items-center" for="' . $input->id . '"><span class="dashicons dashicons-plus mr-1"></span><span>' . $placeholder . '</span>' . $input->outertext . '</label></div>';
			//$input->outertext = '<div class="d-flex align-items-center mb-2 mr-2"><span class="dashicons dashicons-cloud-upload mr-1"></span><span>' . __('Drop files to upload') . '</span><span class="mx-2">' . _x('or', 'Uploader: Drop files here - or - Select Files') .  '</span><label class="border rounded py-2 px-3 mb-0 bg-white" for="' . $input->id . '"><span>' . $placeholder . '</span>' . $input->outertext . '</label></div>';
		}
		
		//$input->outertext = $file_html . '<input class="ifcf7-file-hidden" name="raw_' . $input->name . '" type="hidden" value="' . $file_value . '"><label class="d-flex align-items-center border rounded py-2 px-3 mb-0 bg-white mb-2 mr-2" for="' . $input->id . '" style="cursor: pointer;"><span class="dashicons dashicons-cloud-upload mr-1"></span><span>' . $placeholder . '</span>' . $input->outertext . '<input name="ifcf7_nonce_' . $input->name . '" type="hidden" value="' . wp_create_nonce('ifcf7-upload-file_' . $input->name) . '"></label>';
		$input->outertext = $file_html . '<input class="ifcf7-file-hidden" name="raw_' . $input->name . '" type="hidden" value="' . $file_value . '"><label class="d-flex align-items-center border rounded py-2 px-3 mb-0 bg-white mb-2 mr-2 text-primary" for="' . $input->id . '" style="cursor: pointer;"><span class="dashicons dashicons-cloud-upload mr-1"></span><span>' . $placeholder . '</span>' . $input->outertext . '</label>';
        //$input->outertext = $input->outertext . '<label class="custom-file-label text-truncate" for="' . $input->id . '" data-browse="' . __('Select') . '">' . $placeholder . '</label>';
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
		if('file' !== $type){
			return $html;
		}
		if(!$tag->has_option('beta')){
			return $html;
		}
		if(!current_user_can('upload_files')){
			return $html;
		}
		$html = self::file_html($html_orig, $tag);
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_handle_upload_file_name($filename){
		$filename = wpcf7_canonicalize($filename, [
			'strto' => 'lower', // as-is
		]);
		$filename = wpcf7_antiscript_file_name($filename);
		return $filename;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
        wp_enqueue_script('wp-api');
		ifwp_enqueue_asset('ifcf7-file', 'file.js', ['contact-form-7']);
		wp_add_inline_script('ifcf7-file', 'ifcf7_file.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
        wp_enqueue_style('dashicons');
		ifwp_enqueue_asset('ifcf7-file', 'file.css', ['contact-form-7']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_validate_file($result, $tag){
		if(!$tag->has_option('beta')){
			return $result;
		}
		/*$value = Helper::get_posted_data($tag->name);
		$iti_value = Helper::get_posted_data('iti_' . $tag->name);
		if('' !== $value and !preg_match('/^\+[1-9]\d{1,14}$/', $iti_value)){
			$result->invalidate($tag, wp_strip_all_tags(__('The telephone number is invalid.', 'contact-form-7')));
			return $result;
		}*/
		return $result;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
