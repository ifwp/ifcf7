<?php namespace IFCF7;

final class Helper {

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static $meta_data = [], $posted_data = [];

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function sanitize_posted_data($value){
        if(is_array($value)){
			$value = array_map([__CLASS__, 'sanitize_posted_data'], $value);
		} elseif(is_string($value)){
			$value = wp_check_invalid_utf8($value);
			$value = wp_kses_no_null($value);
		}
		return $value;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function send_mail($contact_form = null){
        if(null === $contact_form){
            $contact_form = wpcf7_get_current_contact_form();
        }
        if(null === $contact_form){
            return false;
        }
        $skip_mail = self::skip_mail($contact_form);
        if($skip_mail){
        	return true;
        }
        $result = \WPCF7_Mail::send($contact_form->prop('mail'), 'mail');
        if(!$result){
            return false;
        }
        $additional_mail = [];
    	if($mail_2 = $contact_form->prop('mail_2') and $mail_2['active']){
    		$additional_mail['mail_2'] = $mail_2;
    	}
    	$additional_mail = apply_filters('wpcf7_additional_mail', $additional_mail, $contact_form);
    	foreach($additional_mail as $name => $template){
    		\WPCF7_Mail::send($template, $name);
    	}
    	return true;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function setup_meta_data(){
        if(!empty(self::$meta_data)){
            return;
        }
        $contact_form = wpcf7_get_current_contact_form();
        if(null === $contact_form){
            return;
        }
        $submission = \WPCF7_Submission::get_instance();
        if(null === $submission){
            return;
        }
        self::$meta_data = [
            'contact_form_id' => $contact_form->id(),
            'contact_form_locale' => $contact_form->locale(),
            'contact_form_name' => $contact_form->name(),
            'contact_form_title' => $contact_form->title(),
            'container_post_id' => $submission->get_meta('container_post_id'),
            'current_user_id' => $submission->get_meta('current_user_id'),
            'remote_ip' => $submission->get_meta('remote_ip'),
            'remote_port' => $submission->get_meta('remote_port'),
            'submission_response' => $submission->get_response(),
            'submission_status' => $submission->get_status(),
            'timestamp' => $submission->get_meta('timestamp'),
            'unit_tag' => $submission->get_meta('unit_tag'),
            'url' => $submission->get_meta('url'),
            'user_agent' => $submission->get_meta('user_agent'),
        ];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function setup_posted_data(){
        if(!empty(self::$posted_data)){
            return;
        }
        $posted_data = array_filter((array) $_POST, function($key){
			return '_' !== substr($key, 0, 1);
		}, ARRAY_FILTER_USE_KEY);
        self::$posted_data = self::sanitize_posted_data($posted_data);
		/*$submission = \WPCF7_Submission::get_instance();
        if(empty($submission)){
            return;
        }
		self::$posted_data = array_merge(self::$posted_data, $submission->get_posted_data());*/
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function skip_mail($contact_form = null){
        if(null === $contact_form){
            $contact_form = wpcf7_get_current_contact_form();
        }
        if(null === $contact_form){
            return false;
        }
        $skip_mail = ($contact_form->in_demo_mode() or $contact_form->is_true('skip_mail') or !empty($contact_form->skip_mail));
        $skip_mail = apply_filters('wpcf7_skip_mail', $skip_mail, $contact_form);
        return (bool) $skip_mail;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function get_meta_data($name = ''){
        if(empty(self::$meta_data)){
            self::setup_meta_data();
        }
        if('' === $name){
            return self::$meta_data;
        }
        if(!isset($name, self::$meta_data)){
            return '';
        }
        return self::$meta_data[$name];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function get_posted_data($name = ''){
    	if(empty(self::$posted_data)){
            self::setup_posted_data();
        }
        if('' === $name){
            return self::$posted_data;
        }
        if(!isset($name, self::$posted_data)){
            return '';
        }
        return self::$posted_data[$name];
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function mail($contact_form = null, $submission = null){
        if(empty($contact_form)){
    		$contact_form = wpcf7_get_current_contact_form();
    	}
        if(empty($contact_form)){
    		return;
    	}
        if(empty($submission)){
            $submission = \WPCF7_Submission::get_instance();
        }
        if(empty($submission)){
            return;
        }
        $response = $submission->get_response();
        $status = $submission->get_status();
        if(self::skip_mail($contact_form)){
            if(empty($response)){
                $response = $contact_form->message('mail_sent_ok');
            }
            $status = 'mail_sent';
    	} else {
    		if(self::send_mail($contact_form)){
				if(empty($response)){
					$response = $contact_form->message('mail_sent_ok');
				}
    			$status = 'mail_sent';
    		} else {
				if(empty($response)){
					$response = $contact_form->message('mail_sent_ng');
				}
                $status = 'mail_failed';
    		}
    	}
        $submission->set_response(wp_strip_all_tags($response));
        $submission->set_status($status);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
