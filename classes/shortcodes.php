<?php namespace IFCF7;

final class Shortcodes {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('init', [__CLASS__, 'register_shortcodes']);
		add_action('wpcf7_before_send_mail', [__CLASS__, 'wpcf7_before_send_mail'], 11, 3);
		add_filter('do_shortcode_tag', [__CLASS__, 'do_shortcode_tag'], 10, 4);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function do_shortcode_tag($output, $tag, $attr, $m){
		if('contact-form-7' !== $tag){
            return $output;
        }
        $contact_form = wpcf7_get_current_contact_form();
        if(null === $contact_form){
            return $output;
        }
        $output = apply_filters('ifcf7_shortcode_tag_html', $output, $tag, $attr, $m, $contact_form);
		if(isset($_GET['fl_builder'])){
			$output = '<div class="alert alert-info ifcf7_fl_builder_message" role="alert">';
			$output .= $tag;
			foreach($attr as $key => $value){
				$output .= ' ' . $key . '="' . $value . '"';
			}
			$output .= '</div>';
		}
        return $output;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function if_func($atts, $content = ''){
        $atts = shortcode_atts([
            'compare' => '=',
    		'key' => '',
            'special' => '',
    		'type' => 'CHAR',
    		'value' => '',
        ], $atts, 'ifcf7_if');
    	extract($atts);
        if(!in_array($compare, ['!=', '<', '<=', '=', '>', '>=', 'EXISTS', 'LIKE', 'NOT EXISTS', 'NOT LIKE', 'NOT REGEXP', 'REGEXP'])){
            return '';
        }
        if(!in_array($type, ['CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'NUMERIC', 'TIME'])){
            return '';
        }
        switch($special){
            case 'author':
                $posted_data = Special_Mail_Tags::author('', 'ifcf7_author_' . $key, false);
                break;
            case 'dynamic_author':
                $posted_data = Special_Mail_Tags::dynamic_author('', 'ifcf7_dynamic_author_' . $key, false);
                break;
            case 'dynamic_post':
                $posted_data = Special_Mail_Tags::dynamic_post('', 'ifcf7_dynamic_post_' . $key, false);
                break;
            case 'dynamic_user':
                $posted_data = Special_Mail_Tags::dynamic_user('', 'ifcf7_dynamic_user_' . $key, false);
                break;
            case 'post':
                $posted_data = Special_Mail_Tags::post('', 'ifcf7_post_' . $key, false);
                break;
            case 'user':
                $posted_data = Special_Mail_Tags::user('', 'ifcf7_user_' . $key, false);
                break;
            default:
                $submission = ifcf7_get_current_submission();
                if(!$submission){
                    return '';
                }
                $posted_data = $submission->get_posted_data($key);
                if(null === $posted_data){
                    $posted_data = '';
                } else {
        			$posted_data = wpcf7_flat_join($posted_data);
        		}
        }
        /*$submission = ifcf7_get_current_submission();
        if(!$submission){
			return '';
		}*/
        $content = array_filter(explode('[ifcf7_else]', $content, 2));
        $content_false = isset($content[1]) ? $content[1] : '';
        $content_true = $content[0];
        /*$posted_data = $submission->get_posted_data($key);
        if(null === $posted_data){
            $posted_data = '';
        } else {
			$posted_data = wpcf7_flat_join($posted_data);
		}*/
        if('' === $posted_data){
            switch($compare){
                case 'EXISTS':
                    /*if('' !== $posted_data){
                        return $content_true;
                    } else {
                        return $content_false;
                    }*/
					return $content_false;
                    break;
                case 'NOT EXISTS':
					return $content_true;
                    /*if('' === $posted_data){
                        return $content_true;
                    } else {
                        return $content_false;
                    }*/
                    break;
                default:
                    return '';
            }
        } else {
            switch($type){
                case 'DATE':
                    $posted_data = strtotime(date_i18n('Y-m-d', strtotime($posted_data)) . ' 00:00:00');
                    $value = strtotime(date_i18n('Y-m-d', strtotime($value)) . ' 00:00:00');
                    break;
                case 'DATETIME':
                    $posted_data = strtotime($posted_data);
                    $value = strtotime($value);
                    break;
                case 'DECIMAL':
                    $posted_data = (float) $posted_data;
                    $value = (float) $value;
                    break;
                case 'NUMERIC':
                    $posted_data = (int) $posted_data;
                    $value = (int) $value;
                    break;
                case 'TIME':
                    $posted_data = strtotime('1970-01-01 ' . date_i18n('H:i:s', strtotime($posted_data)));
                    $value = strtotime('1970-01-01 ' . date_i18n('H:i:s', strtotime($value)));
                    break;
                default:
                    $posted_data = (string) $posted_data;
                    $value = (string) $value;
            }
            switch($compare){
                case '!=':
                    if($posted_data !== $value){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                case '<':
                    if($posted_data < $value){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                case '<=':
                    if($posted_data <= $value){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                case '=':
                    if($posted_data === $value){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                case '>':
                    if($posted_data > $value){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                case '>=':
                    if($posted_data >= $value){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
				case 'EXISTS':
					return $content_true;
                    break;
                case 'NOT EXISTS':
					return $content_false;
                    break;
                case 'LIKE':
                    if(false !== strpos($posted_data, $value)){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                case 'NOT LIKE':
                    if(false === strpos($posted_data, $value)){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                case 'NOT REGEXP':
                    if(0 === preg_match($value, $posted_data)){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                case 'REGEXP':
                    if(1 === preg_match($value, $posted_data)){
                        return $content_true;
                    } else {
                        return $content_false;
                    }
                    break;
                default:
                    return '';
            }
        }
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function register_shortcodes(){
		add_shortcode('ifcf7_if', [__CLASS__, 'if_func']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_before_send_mail($contact_form, &$abort, $submission){
		global $shortcode_tags;
		if($abort or !$submission->is('init')){
			return; // prevent conflicts with other plugins
		}
		$tmp = $shortcode_tags;
		remove_all_shortcodes();
		self::register_shortcodes();
		$mail = $contact_form->prop('mail');
		if(has_shortcode($mail['body'], 'ifcf7_if')){
			$mail['body'] = do_shortcode($mail['body']);
		}
		if(has_shortcode($mail['subject'], 'ifcf7_if')){
			$mail['subject'] = do_shortcode($mail['subject']);
		}
		$mail_2 = $contact_form->prop('mail_2');
		if(has_shortcode($mail_2['body'], 'ifcf7_if')){
			$mail_2['body'] = do_shortcode($mail_2['body']);
		}
		if(has_shortcode($mail_2['subject'], 'ifcf7_if')){
			$mail_2['subject'] = do_shortcode($mail_2['subject']);
		}
		$contact_form->set_properties([
			'mail' => $mail,
			'mail_2' => $mail_2,
		]);
		$shortcode_tags = $tmp;
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
