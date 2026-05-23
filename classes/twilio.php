<?php namespace IFCF7;

final class Twilio {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('wpcf7_mail_sent', [__CLASS__, 'wpcf7_mail_sent']);
		add_filter('ifcf7_mb', [__CLASS__, 'ifcf7_mb'], 10, 2);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function is_active(){
		return (defined('IFCF7_TWILIO_ACCOUNT_SID') and defined('IFCF7_TWILIO_AUTH_TOKEN'));
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function seems_e164($number = ''){
		return preg_match('/^\+[1-9]\d{1,14}$/', $number);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function send($from = '', $to = '', $message = ''){
		$missing = [];
		if(!$from){
			$missing[] = 'from';
		}
		if(!$to){
			$missing[] = 'to';
		}
		if(!$message){
			$missing[] = 'message';
		}
		if($missing){
			$error = current_user_can('manage_options') ? sprintf(__('Missing parameter(s): %s'), ifwp_implode_and($missing)) . '.' : __('Something went wrong.');
			return ifwp_error($error);
		}
		$invalid = [];
		if(!self::seems_e164($from)){
			$invalid[] = 'from';
		}
		if(!self::seems_e164($to)){
			$invalid[] = 'to';
		}
		if($invalid){
			$error = current_user_can('manage_options') ? sprintf(__('Invalid parameter(s): %s'), ifwp_implode_and($invalid)) . '.' : __('Something went wrong.');
			return ifwp_error($error);
		}
		$url = 'https://api.twilio.com/2010-04-01/Accounts/' . IFCF7_TWILIO_ACCOUNT_SID . '/Messages.json';
		$args = [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode(IFCF7_TWILIO_ACCOUNT_SID . ':' . IFCF7_TWILIO_AUTH_TOKEN),
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
			'body' => [
				'Body' => $message,
				'From' => $from,
				'To' => $to,
			],
			'timeout' => 10,
		];
		return ifwp_remote_post($url, $args);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_mb($meta_box, $current_post_id){
		if(!self::is_active()){
			return $meta_box;
		}
		/*if(!$current_post_id){
			return $meta_box;
		}
		$contact_form = ifcf7_contact_form($current_post_id);
		if(!$contact_form){
			return $meta_box;
		}
		$tags = $contact_form->scan_form_tags([
			'basetype' => ['tel'],
		]);
		$tags = array_filter($tags, function($tag){
			return $tag->has_option('intl');
		});
		$sms_to = [];
		foreach($tags as $tag){
			$label = $tag->name;
			$label = ifcf7_placeholder_value($tag, $label);
			$label = ifcf7_content_label_value($tag, $label);
			$sms_to[$tag->name] = $label;
		}
		$sms_to['ifcf7_sms_other'] = 'Another phone number';*/
		$meta_box['tabs']['sms'] = [
      'label' => 'SMS',
      'icon' => 'dashicons-smartphone',
    ];
		$meta_box['fields'][] = [
			'columns' => 12,
			'id' => 'ifcf7_sms_from',
			'name' => __('From', 'contact-form-7'),
			//'pattern' => '^\+[1-9]\d{1,14}$',
			//'pattern' => '^\[[A-Za-z][-A-Za-z0-9_:.]*\]$',
			'pattern' => '^(?:\+[1-9]\d{1,14})|(?:\[[A-Za-z][-A-Za-z0-9_:.]*\])$',
			'placeholder' => defined('IFCF7_TWILIO_FROM') ? IFCF7_TWILIO_FROM : '',
			'type' => 'text',
			'tab' => 'sms',
		];
		$meta_box['fields'][] = [
			'columns' => 12,
			'id' => 'ifcf7_sms_to',
			'name' => __('To', 'contact-form-7'),
			'type' => 'text',
			'tab' => 'sms',
		];
		$meta_box['fields'][] = [
			'columns' => 12,
			'id' => 'ifcf7_sms_message',
			'limit' => 160,
			'name' => __('Message body', 'contact-form-7'),
			'tab' => 'sms',
			'type' => 'textarea',
		];
		$meta_box['fields'][] = [
			'columns' => 12,
			'id' => 'ifcf7_sms_2',
			'name' => 'SMS (2)',
			'tab' => 'sms',
			'type' => 'switch',
		];
		$meta_box['fields'][] = [
			'columns' => 12,
			'id' => 'ifcf7_sms_2_from',
			'name' => __('From', 'contact-form-7'),
			//'pattern' => '^\+[1-9]\d{1,14}$',
			//'pattern' => '^\[[A-Za-z][-A-Za-z0-9_:.]*\]$',
			'pattern' => '^(?:\+[1-9]\d{1,14})|(?:\[[A-Za-z][-A-Za-z0-9_:.]*\])$',
			'placeholder' => defined('IFCF7_TWILIO_FROM') ? IFCF7_TWILIO_FROM : '',
			'type' => 'text',
			'tab' => 'sms',
			'visible' => ['ifcf7_sms_2', 1],
		];
		$meta_box['fields'][] = [
			'columns' => 12,
			'id' => 'ifcf7_sms_2_to',
			'name' => __('To', 'contact-form-7'),
			'type' => 'text',
			'tab' => 'sms',
			'visible' => ['ifcf7_sms_2', 1],
		];
		$meta_box['fields'][] = [
			'columns' => 12,
			'id' => 'ifcf7_sms_2_message',
			'limit' => 160,
			'name' => __('Message body', 'contact-form-7'),
			'tab' => 'sms',
			'type' => 'textarea',
			'visible' => ['ifcf7_sms_2', 1],
		];
		return $meta_box;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_mail_sent($contact_form){
		if(!self::is_active()){
			return;
		}
		$submission = ifcf7_get_current_submission();
		if(!$submission){
			return;
		}
		$from = get_post_meta($contact_form->id(), 'ifcf7_sms_from', true);
		if($from){
			$from = wpcf7_mail_replace_tags($from);
		} else {
			if(defined('IFCF7_TWILIO_FROM')){
				$from = IFCF7_TWILIO_FROM;
			}
		}
		$to = get_post_meta($contact_form->id(), 'ifcf7_sms_to', true);
		if($to){
			$to = wpcf7_mail_replace_tags($to);
		}
		$message = get_post_meta($contact_form->id(), 'ifcf7_sms_message', true);
		if($message){
			$message = wpcf7_mail_replace_tags($message);
		}
		if($from and $to and $message){
			$response = self::send($from, $to, $message);
			if(is_wp_error($response)){
				$submission->set_response($response->get_error_message());
				$submission->set_status('mail_failed');
			}
		}
		if(!get_post_meta($contact_form->id(), 'ifcf7_sms_2', true)){
			return;
		}
		$from = get_post_meta($contact_form->id(), 'ifcf7_sms_2_from', true);
		if($from){
			$from = wpcf7_mail_replace_tags($from);
		} else {
			if(defined('IFCF7_TWILIO_FROM')){
				$from = IFCF7_TWILIO_FROM;
			}
		}
		$to = get_post_meta($contact_form->id(), 'ifcf7_sms_2_to', true);
		if($to){
			$to = wpcf7_mail_replace_tags($to);
		}
		$message = get_post_meta($contact_form->id(), 'ifcf7_sms_2_message', true);
		if($message){
			$message = wpcf7_mail_replace_tags($message);
		}
		if($from and $to and $message){
			$response = self::send($from, $to, $message);
			if(is_wp_error($response)){
				$submission->set_response($response->get_error_message());
				$submission->set_status('mail_failed');
			}
		}
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
