<?php namespace IFCF7;

final class Admin_Table {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('init', [__CLASS__, 'register_shortcodes']);
		add_action('wp_enqueue_scripts', [__CLASS__, 'wp_enqueue_scripts']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// private
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	private static $tags = [];

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    private static function get_meta_query($atts){
		$meta_query = [];
		$meta_compare = strtoupper(trim($atts['meta_compare']));
		if(!in_array($meta_compare, ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'EXISTS', 'NOT EXISTS'])){ // - IN, NOT IN, BETWEEN, NOT BETWEEN
			$meta_compare = '=';
		}
		if('=' !== $meta_compare){
			$meta_query['compare'] = $meta_compare;
		}
		$meta_key = trim($atts['meta_key']);
		$meta_query['key'] = $meta_key;
		$meta_type = strtoupper(trim($atts['meta_type']));
		if(!in_array($meta_type, ['NUMERIC', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'TIME'])){ // - BINARY, SIGNED, UNSIGNED
			$meta_type = 'CHAR';
		}
		if('CHAR' !== $meta_type){
			$meta_query['type'] = $meta_type;
		}
		if(!in_array($meta_compare, ['EXISTS', 'NOT EXISTS'])){
			$meta_query['value'] = trim($atts['meta_value']);
		}
		if($meta_key){
			return $meta_query;
		} else {
			return [];
		}
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function admin_table_func($atts, $content = ''){
    	return self::render_admin_table($atts);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function register_shortcodes(){
		add_shortcode('ifcf7_admin_table', [__CLASS__, 'admin_table_func']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function render_admin_table($atts = []){
		if(is_numeric($atts)){ // backcompat
			$echo = false;
			$form_id = absint($atts);
		} else {
			$atts = shortcode_atts([
				'capability' => 'edit_others_posts',
				'echo' => 0, // backcompat
				'form_id' => 0,
				'hide_tags' => '',
				'hide_utms' => '',
				'meta_compare' => '=',
				'meta_key' => '',
				'meta_value' => '',
				'meta_type' => 'CHAR',
				'show_custom_fields' => '',
				'show_fields' => '',
				'show_tags' => '',
			], $atts, 'ifcf7_admin_table');
			$echo = ifwp_seems_true($atts['echo']);

			//$form_id = absint($atts['form_id']);
			$form_id = trim($atts['form_id']);
			if('all' === $form_id){
                $form_ids = get_posts([
                    'fields' => 'ids',
                    'post_status' => 'publish',
                    'post_type' => \WPCF7_ContactForm::post_type,
                    'posts_per_page' => -1,
                ]);
            } elseif(strpos($form_id, ',')){
                $form_ids = explode(',', $form_id);
                $form_ids = array_map('trim', $form_ids);
                $form_ids = array_filter($form_ids);
                $form_ids = array_map('absint', $form_ids);
                $form_ids = array_values($form_ids);
            } else {
                $form_id = absint($form_id);
                $form_ids = [$form_id];
            }
            $form_id = $form_ids[0];

			$hide_tags = array_filter(array_map('trim', explode(',', $atts['hide_tags'])));
			$hide_utms = ifwp_seems_true($atts['hide_utms']);
			$show_custom_fields = array_filter(array_map('trim', explode(',', $atts['show_custom_fields'])));
			$show_fields = array_filter(array_map('trim', explode(',', $atts['show_fields'])));
			$show_tags = array_filter(array_map('trim', explode(',', $atts['show_tags'])));
		}
		$html = '';

		//$contact_form = ifcf7_contact_form($form_id);
		$contact_forms = [];
        foreach($form_ids as $form_id){
            $contact_form = ifcf7_contact_form($form_id);
            if($contact_form){
                $contact_forms[$form_id] = $contact_form;
            }
        }

		if(false === $contact_form){
			$html .= '<div class="alert alert-danger" role="alert">' . __('The requested contact form was not found.', 'contact-form-7') . '</div>';
		} else {
			if(!current_user_can($atts['capability'])){
				$html .= '<div class="alert alert-danger" role="alert">' . __('Sorry, you are not allowed to view this item.') . ' ' . __('You need a higher level of permission.') . '</div>';
			} else {
				$post_status = ifcf7_get_post_status($contact_form);
				$post_type = ifcf7_get_post_type($contact_form);
				$args = [
					'meta_query' => [

						/*[
							'key' => 'original_contact_form_id',
							'value' => $contact_form->id(),
						],*/
						[
                            'compare' => 'IN',
							'key' => 'original_contact_form_id',
							'value' => array_keys($contact_forms),
						],

					],
					'order' => 'ASC',
					'post_status' => $post_status,
					'post_type' => $post_type,
					'posts_per_page' => -1,
				];
				$meta_query = self::get_meta_query($atts);
				if($meta_query){
					$args['meta_query'][] = $meta_query;
				}
				$args = apply_filters('ifcf7_render_admin_table_query_args', $args, $contact_form);
				$args['order'] = 'ASC'; // important
				$args['posts_per_page'] = -1;
				$query = ifwp_get_posts_query($args);
				if(!$query->post_count){
					$html .= '<div class="alert alert-info" role="alert">' . __('No posts found.') . '</div>';
				} else {
					$count = 1;

					/*$tags = $contact_form->scan_form_tags();
					if($show_tags){
						$tags = array_filter($tags, function($tag) use($show_tags){
							return (wpcf7_form_tag_supports($tag->type, 'name-attr') and in_array($tag->name, $show_tags));
						});
					} else {
						$tags = array_filter($tags, function($tag){
							return wpcf7_form_tag_supports($tag->type, 'name-attr');
						});
					}
					if($hide_tags){
						$tags = array_filter($tags, function($tag) use($hide_tags){
							return !in_array($tag->name, $hide_tags);
						});
					}*/

					$all_tags = [];
                    foreach($contact_forms as $contact_form){
                        $tags = $contact_form->scan_form_tags();
                        if($show_tags){
                            $tags = array_filter($tags, function($tag) use($show_tags){
                                return (wpcf7_form_tag_supports($tag->type, 'name-attr') and in_array($tag->name, $show_tags));
                            });
                        } else {
                            $tags = array_filter($tags, function($tag){
                                return wpcf7_form_tag_supports($tag->type, 'name-attr');
                            });
                        }
                        if($hide_tags){
                            $tags = array_filter($tags, function($tag) use($hide_tags){
                                return !in_array($tag->name, $hide_tags);
                            });
                        }
                        foreach($tags as $tag){
                            $all_tags[$tag->name] = $tag;
                        }
                    }
                    $tags = array_values($all_tags);

					/*$additional_data = apply_filters('ifcf7_render_admin_table_additional_data', [], $contact_form, $tags);
					if($additional_data and !ifwp_is_array_assoc($additional_data)){
						$additional_data = [];
					}*/

					$html .= '<div class="card bg-light ifcf7-admin-table-card">';
					$html .= '<div class="card-body overflow-hidden p-3">';
					$html .= '<table class="ifcf7-admin-table table table-striped table-bordered bg-white">';
					$html .= '<thead>';
					$html .= '<tr>';
					$html .= '<th>#</th>';
					$html .= '<th>ID</th>';
					foreach($tags as $tag){
						if(in_array($tag->name, self::$tags)){
							continue;
						}
						self::$tags[] = $tag->name;
						$label = $tag->name;
						$label = ifcf7_placeholder_value($tag, $label);
						$label = ifcf7_content_label_value($tag, $label);
						$html .= '<th class="text-nowrap">' . $label . '</th>';
					}
					$utm_params = ifwp_get_utm_params(false);
					if(!$hide_utms){
						foreach($utm_params as $param){
							$html .= '<th class="text-nowrap">UTM ' . $param . '</th>';
						}
					}
					$tags_arr = wp_list_pluck($tags, 'name');
					$show_custom_fields = array_filter($show_custom_fields, function($custom_field) use($tags_arr, $utm_params){
						if('utm_' === substr($custom_field, 0, 4)){
							return (!in_array($custom_field, $tags_arr) and !in_array(str_replace('utm_', '', $custom_field), array_keys($utm_params)));
						} else {
							return !in_array($custom_field, $tags_arr);
						}
					});
					foreach($show_custom_fields as $custom_field){
						$html .= '<th class="text-nowrap">' . $custom_field . '</th>';
					}
					$post_fields = ['post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count'];
					$show_fields = array_filter($show_fields, function($field) use($post_fields){
						return in_array($field, $post_fields, true);
					});
					foreach($show_fields as $field){
						$html .= '<th class="text-nowrap">' . $field . '</th>';
					}
					/*foreach(array_keys($additional_data) as $key){
						$html .= '<th class="text-nowrap">' . $key . '</th>';
					}*/
					$html .= '</tr>';
					$html .= '</thead>';
					$html .= '<tbody>';
					foreach($query->posts as $post){
						$html .= '<tr>';
						if(is_post_type_viewable($post_type)){
							$html .= '<td><a href="' . get_permalink($post->ID) . '" target="_blank">' . $count . '</a></td>';
						} else {
							$html .= '<td>' . $count . '</td>';
						}
						$html .= '<td><a href="' . get_edit_post_link($post->ID) . '" target="_blank">' . $post->ID . '</a></td>';
						$count ++;
						self::$tags = [];
						foreach($tags as $tag){
							if(in_array($tag->name, self::$tags)){
								continue;
							}
							self::$tags[] = $tag->name;
							if(wpcf7_form_tag_supports($tag->type, 'file-uploading')){
								$value = Storage::attachments_html('post', $post->ID, $tag->name, true);
							} else {
								if(wpcf7_form_tag_supports($tag->type, 'selectable-values')){
									if(ifcf7_has_pipes($tag)){
										if(metadata_exists('post', $post->ID, $tag->name . '_value')){
											$value = get_post_meta($post->ID, $tag->name . '_value', false); // backcompat
										} else {
											$value = get_post_meta($post->ID, 'raw_' . $tag->name, false);
										}
									} else {
										$value = get_post_meta($post->ID, $tag->name, false);
									}
									$value = implode(', ', $value);
								} else {
									if('tel' === $tag->basetype and $tag->has_option('intl')){
										if(metadata_exists('post', $post->ID, $tag->name . '_intl')){
											$value = get_post_meta($post->ID, $tag->name . '_intl', true); // backcompat
										} else {
											$value = get_post_meta($post->ID, 'iti_' . $tag->name, true);
										}
									} elseif('text' === $tag->basetype and $tag->has_option('iti')){
										$value = get_post_meta($post->ID, 'iti_' . $tag->name, true);
									} else {
										$value = get_post_meta($post->ID, $tag->name, true);
									}
									$value = maybe_serialize($value);
									$value = ifcf7_format_value($value, $tag);
								}
							}
							$value = apply_filters('ifcf7_render_admin_table_tag_value', $value, $contact_form, $tag, $post);
							$html .= '<td>' . $value . '</td>';
						}
						if(!$hide_utms){
							foreach($utm_params as $key => $value){
								$value = get_post_meta($post->ID, 'utm_' . $key, true);
								$value = apply_filters('ifcf7_render_admin_table_utm_value', $value, $contact_form, 'utm_' . $key, $post);
								$html .= '<td>' . $value . '</td>';
							}
						}
						foreach($show_custom_fields as $custom_field){
							$value = get_post_meta($post->ID, $custom_field, false);
							$value = implode(', ', $value);
							$value = apply_filters('ifcf7_render_admin_table_custom_field_value', $value, $contact_form, $custom_field, $post);
							$html .= '<td>' . $value . '</td>';
						}
						foreach($show_fields as $field){
							$value = $post->$field;
							$value = apply_filters('ifcf7_render_admin_table_field_value', $value, $contact_form, $field, $post);
							$html .= '<td>' . $value . '</td>';
						}
						/*foreach($additional_data as $value){
							$html .= '<td>' . $value . '</td>';
						}*/
						$html .= '</tr>';
					}
					$html .= '</tbody>';
					$html .= '</table>';
					$html .= '</div>';
					$html .= '</div>';
				}
			}
		}
		if($echo){
			echo $html;
		} else {
			return $html;
		}
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wp_enqueue_scripts(){
		global $post;
		if(is_archive()){
			return;
		}
		if(!$post){
			return;
		}
		if(!has_shortcode($post->post_content, 'ifcf7_admin_table')){
			return;
		}
		wp_enqueue_script('data-tables', 'https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js', ['jquery'], '1.12.1', true);
		wp_enqueue_script('data-tables-bootstrap-4', 'https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap4.min.js', ['data-tables'], '1.12.1', true);
		wp_enqueue_script('jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js', ['data-tables'], '3.10.0', true);
		wp_enqueue_script('pdfmake', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.5/pdfmake.min.js', ['data-tables'], '0.2.5', true);
		wp_enqueue_script('vfs_fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.5/vfs_fonts.min.js', ['pdfmake'], '0.2.5', true);
		wp_enqueue_script('data-tables-buttons', 'https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js', ['data-tables'], '2.2.3', true);
		wp_enqueue_script('data-tables-buttons-bootstrap-4', 'https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap4.min.js', ['data-tables'], '2.2.3', true);
		wp_enqueue_script('data-tables-buttons-html5', 'https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js', ['data-tables'], '2.2.3', true);
		wp_enqueue_script('data-tables-buttons-print', 'https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js', ['data-tables'], '2.2.3', true);
		wp_enqueue_script('data-tables-buttons-colvis', 'https://cdn.datatables.net/buttons/2.2.3/js/buttons.colVis.min.js', ['data-tables'], '2.2.3', true);
		ifwp_enqueue_asset('ifcf7-admin-table', 'admin-table.js', ['data-tables']);
		wp_add_inline_script('ifcf7-admin-table', 'ifcf7_admin_table.load();');
		wp_enqueue_style('data-tables-bootstrap-4', 'https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap4.min.css', [], '1.12.1');
		wp_enqueue_style('data-tables-buttons-bootstrap-4', 'https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap4.min.css', [], '2.2.3');
		ifwp_enqueue_asset('ifcf7-admin-table', 'admin-table.css', []);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
