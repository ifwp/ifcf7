<?php namespace IFCF7;

final class Visibility {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_filter('ifcf7_mb', [__CLASS__, 'ifcf7_mb'], 10, 2);
		add_filter('ifcf7_shortcode_tag_html', [__CLASS__, 'ifcf7_shortcode_tag_html'], 10, 5);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_mb($meta_box, $current_post_id){
		$check_zone_info = true;
		$current_offset = get_option('gmt_offset');
        $timezone_format = _x('Y-m-d H:i:s', 'timezone date format');
        $tzstring = get_option('timezone_string');
        if(false !== strpos($tzstring, 'Etc/GMT')){ // Remove old Etc mappings. Fallback to gmt_offset.
            $tzstring = '';
        }
        if(empty($tzstring)){ // Create a UTC+- zone if no timezone string exists.
            $check_zone_info = false;
            if(0 == $current_offset){
                $tzstring = 'UTC+0';
            } elseif($current_offset < 0){
                $tzstring = 'UTC' . $current_offset;
            } else {
                $tzstring = 'UTC+' . $current_offset;
            }
        }
		$custom_html = '<p>';
		$custom_html .= __('Timezone') . ': <code>' . $tzstring . '</code>.';
        $custom_html .= '</p>';
		$custom_html .= '<p>';
        $custom_html .= __('Date Format') . ': <code>' . get_option('date_format') . '</code>. ' . __('Preview:') . ' <code>' . date_i18n(get_option('date_format')) . '</code>.';
        $custom_html .= '</p>';
		$custom_html .= '<p>';
        $custom_html .= __('Time Format') . ': <code>' . get_option('time_format') . '</code>. ' . __('Preview:') . ' <code>' . date_i18n(get_option('time_format')) . '</code>.';
        $custom_html .= '</p>';
        $custom_html .= '<p class="timezone-info">';
        $custom_html .= '<span id="utc-time">';
        $custom_html .= sprintf(__('Universal time is %s.'), '<code>' . date_i18n($timezone_format, false, true) . '</code>');
        $custom_html .= '</span>';
        if(get_option('timezone_string') || !empty($current_offset)){
            $custom_html .= ' <span id="local-time">';
            $custom_html .= sprintf(__('Local time is %s.'), '<code>' . date_i18n($timezone_format) . '</code>');
            $custom_html .= '</span>';
        }
        $custom_html .= '</p>';
        if($check_zone_info && $tzstring){
            $custom_html .= '<p>';
            $now = new \DateTime('now', new \DateTimeZone($tzstring));
            $dst = (bool) $now->format('I');
            if($dst){
                $custom_html .= __('This timezone is currently in daylight saving time.');
            } else {
                $custom_html .= __('This timezone is currently in standard time.');
            }
            $custom_html .= '</p>';
			$custom_html .= '<p>';
            if(in_array($tzstring, timezone_identifiers_list(), true)){
                $transitions = timezone_transitions_get(timezone_open($tzstring), time());
                if(!empty($transitions[1])){
                    $message = $transitions[1]['isdst'] ? __('Daylight saving time begins on: %s.') : __('Standard time begins on: %s.');
                    $custom_html .= sprintf($message, '<code>' . wp_date(__('F j, Y') . ' ' . __('g:i a'), $transitions[1]['ts']) . '</code>');
                } else {
                    $custom_html .= __('This timezone does not observe daylight saving time.');
                }
            }
            $custom_html .= '</p>';
        }
        $custom_html .= '<a href="' . admin_url('options-general.php') . '">' . str_replace('&larr; ', '', sprintf(_x('&larr; Go to %s', 'site'), __('General Settings'))) . '</a>.';
		$meta_box['tabs']['visibility'] = [
            'icon' => 'dashicons-visibility',
            'label' => __('Visibility', 'fl-builder'),
        ];
        $meta_box['fields'][] = [
            'columns' => 12,
            'id' => 'ifcf7_visibility',
            'inline' => true,
            'name' => __('Close'),
            'options' => [
                'before_date' => 'Before date',
                'after_date' => 'After date',
                'after_submissions' => 'After submissions',
            ],
            'tab' => 'visibility',
            'type' => 'checkbox_list',
		];
        $meta_box['fields'][] = [
            'columns' => 12,
            'id' => 'ifcf7_visibility_before_date',
            'js_options' => [
                'showSecond' => true,
                'timeFormat' => 'HH:mm:ss',
            ],
            'name' => 'Open on',
			'placeholder' => __('Date and time'),
            'required' => true,
            'save_format' => 'Y-m-d H:i:s',
            'tab' => 'visibility',
            'type' => 'datetime',
            'visible' => ['ifcf7_visibility', 'contains', 'before_date'],
        ];
        $meta_box['fields'][] = [
            'columns' => 12,
            'desc' => 'Translators: 1: ' . __('Date') . ', 2: ' . __('Time') . '.',
            'id' => 'ifcf7_visibility_before_date_message',
            'name' => 'Opening message',
			'placeholder' => 'This form will be open on %s at %s.',
            'tab' => 'visibility',
            'type' => 'text',
            'visible' => ['ifcf7_visibility', 'contains', 'before_date'],
        ];
        $meta_box['fields'][] = [
            'columns' => 12,
            'id' => 'ifcf7_visibility_after_date',
            'js_options' => [
                'showSecond' => true,
                'timeFormat' => 'HH:mm:ss',
            ],
            'name' => 'Close on',
			'placeholder' => __('Date and time'),
            'required' => true,
            'save_format' => 'Y-m-d H:i:s',
            'tab' => 'visibility',
            'type' => 'datetime',
            'visible' => ['ifcf7_visibility', 'contains', 'after_date'],
        ];
        
        $meta_box['fields'][] = [
            'columns' => 12,
            'desc' => 'Translators: 1: ' . __('Date') . ', 2: ' . __('Time') . '.',
            'id' => 'ifcf7_visibility_after_date_message',
            'name' => 'Closing message',
			'placeholder' => 'This form was closed on %s at %s.',
            'tab' => 'visibility',
            'type' => 'text',
            'visible' => ['ifcf7_visibility', 'contains', 'after_date'],
        ];
        
        $meta_box['fields'][] = [
            'columns' => 12,
            'id' => 'ifcf7_visibility_after_submissions',
            'name' => 'Maximum number of submissions',
            'required' => true,
            'tab' => 'visibility',
            'type' => 'number',
            'visible' => ['ifcf7_visibility', 'contains', 'after_submissions'],
        ];
        $meta_box['fields'][] = [
            'columns' => 12,
            'desc' => 'Translators: Number of submissions.',
            'id' => 'ifcf7_visibility_after_submissions_message',
            'name' => 'Sold out message',
            'placeholder' => 'This form was closed after %d submissions.',
            'tab' => 'visibility',
            'type' => 'text',
            'visible' => ['ifcf7_visibility', 'contains', 'after_submissions'],
        ];
        $meta_box['fields'][] = [
            'tab' => 'visibility',
            'type' => 'divider',
        ];
        $meta_box['fields'][] = [
            'std' => $custom_html,
            'tab' => 'visibility',
            'type' => 'custom_html',
        ];
		return $meta_box;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifcf7_shortcode_tag_html($output, $tag, $attr, $m, $contact_form){
        $ifcf7_visibility = ifcf7_meta_pref('ifcf7_visibility', $contact_form, false);
        if(in_array('before_date', $ifcf7_visibility)){
            $hide_before_date = ifcf7_meta_pref('ifcf7_visibility_before_date', $contact_form);
            $timestamp = strtotime($hide_before_date);
			if(current_time('timestamp') < $timestamp){
                $message = ifcf7_meta_pref('ifcf7_visibility_before_date_message', $contact_form);
				if(empty($message)){
					$message = 'This form will be open on %s at %s.';
				}
				$date = date_i18n(get_option('date_format'), $timestamp);
				$time = date_i18n(get_option('time_format'), $timestamp); // here
				$message = ifwp_prepare($message, $date, $time);
				return apply_filters('ifcf7_visibility_before_date_message', '<div class="alert alert-info" role="alert">' . $message . '</div>', $contact_form);
			}
        }
        if(in_array('after_date', $ifcf7_visibility)){
            $hide_after_date = ifcf7_meta_pref('ifcf7_visibility_after_date', $contact_form);
            $timestamp = strtotime($hide_after_date);
			if(current_time('timestamp') > $timestamp){
                $message = ifcf7_meta_pref('ifcf7_visibility_after_date_message', $contact_form);
				if(empty($message)){
					$message = 'This form was closed on %s at %s.';
				}
				$date = date_i18n(get_option('date_format'), $timestamp);
				$time = date_i18n(get_option('time_format'), $timestamp); // here
				$message = ifwp_prepare($message, $date, $time);
				return apply_filters('ifcf7_visibility_after_date_message', '<div class="alert alert-info" role="alert">' . $message . '</div>', $contact_form);
			}
        }
        if(in_array('after_submissions', $ifcf7_visibility)){
            $hide_after_submissions = (int) ifcf7_meta_pref('ifcf7_visibility_after_submissions', $contact_form);
            $query = ifwp_get_posts_query([
				'fields' => 'ids',
				'meta_query' => [
					[
						'key' => 'original_contact_form_id',
						'value' => $contact_form->id(),
					],
				],
				'post_status' => ifcf7_get_post_status($contact_form),
				'post_type' => ifcf7_get_post_type($contact_form),
                'posts_per_page' => $hide_after_submissions,
			]);
            $count = apply_filters('ifcf7_visibility_after_submissions', $query->post_count, $contact_form);
            if($count >= $hide_after_submissions){
                $message = ifcf7_meta_pref('ifcf7_visibility_after_submissions_message', $contact_form);
				if(empty($message)){
					$message = 'This form was closed after %d submissions.';
				}
				$message = ifwp_prepare($message, $hide_after_submissions);
				return apply_filters('ifcf7_visibility_after_submissions_message', '<div class="alert alert-info" role="alert">' . $message . '</div>', $contact_form);
			}
        }
		return $output;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
