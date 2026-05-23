<?php namespace IFCF7;

final class Additional_Settings {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_after_setup_theme(){
		if(!defined('RWMB_VER')){
			ifwp_tgmpa([
				[
					'name' => 'Meta Box',
					'required' => true,
					'slug' => 'meta-box',
				],
			], [
				'id' => 'ifcf7',
				'menu' => 'ifcf7-install-plugins',
				'parent_slug'  => 'plugins.php',
				'strings' => [
					'menu_title' => 'IFCF7',
					'page_title' => __('One or more required modules are missing'),
				],
			]);
		} else {
			add_action('init', function(){
				$missing = [];
				if(!class_exists('MB_Conditional_Logic')){
					$missing[] = '<a href="https://metabox.io/plugins/meta-box-conditional-logic/" target="_blank">Meta Box Conditional Logic</a>';
				}
				if($missing){
					ifwp_add_admin_notice(__('One or more required modules are missing') . ': ' . implode($missing) . '.', 'error');
				}
				$missing = [];
				if(!class_exists('MB_Tabs')){
					$missing[] = '<a href="https://metabox.io/plugins/meta-box-tabs/" target="_blank">Meta Box Tabs</a>';
				}
				if($missing){
					ifwp_add_admin_notice(__('One or more recommended modules are missing') . ': ' . implode($missing) . '.', 'error');
				}
			});
		}
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_filter('redirect_post_location', [__CLASS__, 'redirect_post_location'], 10, 2);
		add_filter('register_post_type_args', [__CLASS__, 'register_post_type_args'], 10, 2);
		add_filter('rwmb_meta_boxes', [__CLASS__, 'rwmb_meta_boxes']);
		add_filter('wpcf7_editor_panels', [__CLASS__, 'wpcf7_editor_panels']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static $active_tab = 0, $fields = [];

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function admin_table($data = []){
		$html = '';
		if(!$data){
			return $html;
		}
		$html .= '<table class="fixed striped widefat wp-list-table">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th scope="col">' . __('Key') . '</th>';
		$html .= '<th scope="col">' . __('Value') . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		foreach($data as $key => $value){
			$html .= '<tr>';
			$html .= '<td>' . $key . '</td>';
			$html .= '<td>' . $value . '</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function current_post_id(){
		if(self::is_editing()){
			$current_post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
		} else {
			$current_post_id = isset($_POST['post_ID']) ? absint($_POST['post_ID']) : 0;
		}
		return $current_post_id;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_default_value($post_id = 0, $index_key = ''){
		$value = get_post_meta($post_id, $index_key, true);
		if(!self::$fields){
			$meta_box_registry = rwmb_get_registry('meta_box');
			$meta_box = $meta_box_registry->get('ifcf7_mb');
			self::$fields = ifwp_list_pluck($meta_box->meta_box['fields'], 'id');
		}
		if(!isset(self::$fields[$index_key])){
			return '';
		}
		$field = self::$fields[$index_key];
		if(!$value and isset($field['std'])){
			$value = $field['std'];
		}
		return $value;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function get_key_value($post_id = 0, $index_key = '', $default_value_key = ''){
		if(!self::$fields){
			$meta_box_registry = rwmb_get_registry('meta_box');
			$meta_box = $meta_box_registry->get('ifcf7_mb');
			self::$fields = ifwp_list_pluck($meta_box->meta_box['fields'], 'id');
		}
		if(!isset(self::$fields[$index_key])){
			return [];
		}
		$field = self::$fields[$index_key];
		$value_raw = self::get_default_value($post_id, $index_key);
		if($value_raw and isset($field['options'], $field['options'][$value_raw])){
			$value = $field['options'][$value_raw];
		} else {
			$value = $value_raw;
		}
		if(!$value_raw and $default_value_key and 'std' !== $default_value_key and isset($field[$default_value_key])){
			$value = $field[$default_value_key];
		}
		return [
			'key' => $field['name'],
			'value' => $value,
			'value_raw' => $value_raw,
		];
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function is_editing(){
		global $pagenow;
		if(!is_admin()){
			return false;
		}
		if('post.php' !== $pagenow){
			return false;
		}
		if(!isset($_GET['_wpnonce'], $_GET['action'], $_GET['active-tab'], $_GET['post'])){
			return false;
		}
		if('edit' !== $_GET['action']){
			return false;
		}
		if('wpcf7_contact_form' !== get_post_type($_GET['post'])){
			return false;
		}
		return wp_verify_nonce($_GET['_wpnonce'], 'ifcf7-edit_post-' . $_GET['post']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static function render_admin_table($id = 0){
		$data = [];
		if(!$id){
			return;
		}
		$field = self::get_key_value($id, 'ifcf7_action');
		$data[$field['key']] = $field['value'];
		if('contact_form' === $field['value_raw']){
			$field = self::get_key_value($id, 'ifcf7_save');
			$data[$field['key']] = $field['value'];
			if('yes' === $field['value_raw']){
				$field = self::get_key_value($id, 'ifcf7_post_type');
				$data[$field['key']] = $field['value'];
				$field = self::get_key_value($id, 'ifcf7_post_status');
				$data[$field['key']] = $field['value'];
			}
		}
		$field = self::get_key_value($id, 'ifcf7_redirect');
		$data[$field['key']] = $field['value'];
		if('yes' === $field['value_raw']){
			$field = self::get_key_value($id, 'ifcf7_redirect_url');
			$data[$field['key']] = $field['value'];
			switch($field['value_raw']){
				case 'another':
					$field = self::get_key_value($id, 'ifcf7_another_url');
					$data[$field['key']] = $field['value'];
					break;
				case 'dynamic':
					$field = self::get_key_value($id, 'ifcf7_fallback_url', 'placeholder');
					$data[$field['key']] = $field['value'];
					break;
			}
		}
		$field = self::get_key_value($id, 'ifcf7_button');
		$data[$field['key']] = $field['value'];
		if('yes' !== $field['value_raw']){
			$field = self::get_key_value($id, 'ifcf7_hide');
			$data[$field['key']] = $field['value'];
		}
		return self::admin_table($data);
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function redirect_post_location($location, $post_id){
		if('wpcf7_contact_form' !== get_post_type($post_id)){
			return $location;
		}
		$referer = wp_get_referer();
		if(false === $referer){
			return $location;
		}
		return add_query_arg('message', 1, $referer);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function register_post_type_args($args, $post_type){
		if('wpcf7_contact_form' !== $post_type){
			return $args;
		}
		if(!self::is_editing()){
			return $args;
		}
		$args['show_in_menu'] = false;
		$args['show_ui'] = true;
		$args['supports'] = ['title'];
		return $args;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function rwmb_meta_boxes($meta_boxes){
		if(self::is_editing()){
			$contact_form = ifcf7_contact_form(self::current_post_id());
			if($contact_form){
				$meta_boxes[] = [
					'fields' => [
						[
							'std' => '<style>span.shortcode { display: block; margin: 2px 0; } span.shortcode input { background: transparent; border: none; box-shadow: none; color: #fff; font-size: 12px; margin: 0; padding: 4px 8px; }</style><p class="description"><label for="wpcf7-shortcode">' . esc_html(__('Copy this shortcode and paste it into your post, page, or text widget content:', 'contact-form-7')) . '</label><span class="shortcode wp-ui-highlight"><input type="text" id="wpcf7-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="' . esc_attr($contact_form->shortcode()) . '" /></span></p>',
							'type' => 'custom_html',
						],
					],
					'id' => 'ifcf7_shortcode_mb',
					'post_types' => 'wpcf7_contact_form',
					'style' => 'seamless',
					'title' => 'Shortcode',
				];
			}
		}
		$meta_box = [
			'fields' => [
				[
					'columns' => 12,
					'id' => 'ifcf7_action',
					'name' => '<span class="dashicons dashicons-admin-settings"></span> ' . __('Action'),
					'options' => apply_filters('ifcf7_form_types', [
						'contact_form' => __('Contact Form', 'contact-form-7'),
						'edit_post' => __('Edit Post'),
						'edit_user' => __('Edit User'),
						'delete_post' => __('Move to Trash'),
						'delete_user' => __('Delete Users'),
						//'button' => __('Button', 'fl-builder'),
					]),
					'required' => true,
					'std' => 'contact_form',
					'tab' => 'general',
					'type' => 'select_advanced',
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_save',
					'name' => '<span class="dashicons dashicons-database-add"></span> ' . 'Save submitted messages',
					'options' => [
						'yes' => esc_html__('Yes'),
						'no' => esc_html__('No'),
					],
					'required' => true,
					'std' => 'yes',
					'tab' => 'general',
					'type' => 'radio',
					'visible' => ['ifcf7_action', 'contact_form'],
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_post_type',
					'name' => '<span class="dashicons dashicons-admin-post"></span> ' . rtrim(__('Type of post.'), '.'),
					'options' => wp_list_pluck(get_post_types([], 'objects'), 'label', 'name'),
					'required' => true,
					'std' => 'ifcf7-submission',
					'type' => 'select_advanced',
					'tab' => 'general',
					'visible' => [
						['ifcf7_action', 'contact_form'],
						['ifcf7_save', 'yes'],
					],
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_post_status',
					'name' => '<span class="dashicons dashicons-post-status"></span> ' . __('Status'),
					'options' => array_reverse(get_post_statuses()),
					'required' => true,
					'std' => 'publish',
					'tab' => 'general',
					'type' => 'radio',
					'visible' => [
						['ifcf7_action', 'contact_form'],
						['ifcf7_save', 'yes'],
					],
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_redirect',
					'name' => '<span class="dashicons dashicons-external"></span> ' . 'Redirect after submissions',
					'options' => [
						'no' => esc_html__('No'),
						'yes' => esc_html__('Yes'),
					],
					'required' => true,
					'std' => 'no',
					'tab' => 'general',
					'type' => 'radio',
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_redirect_url',
					'name' => '<span class="dashicons dashicons-admin-site-alt3"></span> ' . 'Redirect URL',
					'options' => [
						'same' => 'Same URL as the form’s URL',
						'another' => 'Another URL',
						'dynamic' => 'Dynamic URL (inserted or updated post or user)',
					],
					'required' => true,
					'std' => 'same',
					'tab' => 'general',
					'type' => 'radio',
					'visible' => ['ifcf7_redirect', 'yes'],
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_another_url',
					'name' => '<span class="dashicons dashicons-admin-links"></span> ' . 'Another URL',
					'required' => true,
					'tab' => 'general',
					'type' => 'url',
					'visible' => ['ifcf7_redirect_url', 'another'],
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_fallback_url',
					'name' => '<span class="dashicons dashicons-admin-links"></span> ' . 'Fallback URL',
					'placeholder' => 'Same URL as the form’s URL (insert or update) or Site Address (delete)',
					'tab' => 'general',
					'type' => 'url',
					'visible' => ['ifcf7_redirect_url', 'dynamic'],
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_button',
					'name' => '<span class="dashicons dashicons-button"></span> ' . 'Show as button',
					'options' => [
						'yes' => esc_html__('Yes'),
						'no' => esc_html__('No'),
					],
					'required' => true,
					'std' => 'no',
					'tab' => 'general',
					'type' => 'radio',
					'visible' => ['ifcf7_action', 'in', ['contact_form', 'edit_post', 'edit_user', 'delete_post', 'delete_user']],
				],
				[
					'columns' => 12,
					'id' => 'ifcf7_hide',
					'name' => '<span class="dashicons dashicons-hidden"></span> ' . 'Hide after submissions',
					'options' => [
						'yes' => esc_html__('Yes'),
						'no' => esc_html__('No'),
					],
					'required' => true,
					'std' => 'yes',
					'tab' => 'general',
					'type' => 'radio',
					'hidden' => ['ifcf7_button', '=', 'yes'],
				],
			],
			'id' => 'ifcf7_mb',
			'post_types' => 'wpcf7_contact_form',
			'tab_style' => 'left',
			'tabs' => [
                'general' => [
                    'icon' => 'dashicons-admin-generic',
                    'label' => __('Additional settings', 'contact-form-7'),
                ],
			],
			'title' => 'Improvements and Fixes for Contact Form 7',
		];
		$meta_box = apply_filters('ifcf7_mb', $meta_box, self::current_post_id());
		$meta_boxes[] = $meta_box;
		if(self::is_editing()){
			$url = add_query_arg([
				'action' => 'edit',
				'active-tab' => $_GET['active-tab'],
				'page' => 'wpcf7',
				'post' => $_GET['post'],
			], admin_url('admin.php'));
			$meta_boxes[] = [
				'context' => 'side',
				'fields' => [
					[
						'std' => '<style>#delete-action, #minor-publishing, .page-title-action { display: none !important; } #major-publishing-actions { border-top: 0 !important; } .rwmb-label { width: 200px; } h3 { margin-bottom: 4px; }</style><a href="' . $url . '">' . str_replace('&larr; ', '', sprintf(_x('&larr; Go to %s', 'site'), 'IFCF7')) . '</a>.',
						'type' => 'custom_html',
					],
				],
				'id' => 'ifcf7_go_back_mb',
				'post_types' => 'wpcf7_contact_form',
				'priority' => 'low',
				'title' => __('Go back'),
			];
		}
		return $meta_boxes;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_editor_panel($contact_form){
		//$additional_settings = 0;
		//$title = $additional_settings ? sprintf('Improvements and Fixes for Contact Form 7 (%d)', $additional_settings) : 'Improvements and Fixes for Contact Form 7';
		$title = 'Improvements and Fixes for Contact Form 7';
		$html = '<h2>' . $title . '</h2>';
		$html .= '<fieldset>';
		$html .= '<legend>';
		$id = $contact_form->id();
		if($id){
			$url = add_query_arg([
				'action' => 'edit',
				'active-tab' => self::$active_tab,
				'post' => $id,
			], admin_url('post.php'));
			$nonce_url = wp_nonce_url($url, 'ifcf7-edit_post-' . $id);
			$html .= '<a href="' . $nonce_url . '">' . __('Edit This') . '</a>.';
		} else {
			$html .= __('Save Changes') . '.';
		}
		$html .= '</legend>';
		if($id){
			$html .= self::render_admin_table($id);
		}
		$html .= '</fieldset>';
		echo $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_editor_panels($panels){
		if(isset($panels['ifcf7'])){
		   return $panels;
		}
		self::$active_tab = count($panels);
		$panels['ifcf7'] = [
		   'callback' => [__CLASS__, 'wpcf7_editor_panel'],
		   'title' => 'IFCF7',
		];
		return $panels;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
