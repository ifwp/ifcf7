<?php namespace IFCF7;

final class Load_Form {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('init', [__CLASS__, 'init']);
		add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wp_ajax_ifcf7_load_form', [__CLASS__, 'load_form']);
		add_action('wp_ajax_nopriv_ifcf7_load_form', [__CLASS__, 'load_form']);
		add_action('wp_footer', [__CLASS__, 'wp_footer']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function init(){
        add_shortcode('ifcf7_load_form', [__CLASS__, 'load_form_func']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function load_form(){
		global $post, $wp_query;
		$form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
		$contact_form = ifcf7_contact_form($form_id);
		if(null === $contact_form){
			echo '<div class="alert alert-danger" role="alert">' . __('The requested contact form was not found.', 'contact-form-7') . '</div>';
			die;
		}
		$content = '[contact-form-7 id="' . $form_id . '"';
		$html_class = isset($_POST['html_class']) ? $_POST['html_class'] : '';
		$html_id = isset($_POST['html_id']) ? $_POST['html_id'] : '';
		if($html_class){
			$content .= ' html_class="' . $html_class . '"';
		}
		if($html_id){
			$content .= ' html_id="' . $html_id . '"';
		}
		$content .= ']';
		$container_post_id = isset($_POST['container_post_id']) ? absint($_POST['container_post_id']) : 0;
		$container_post_nonce = isset($_POST['container_post_nonce']) ? $_POST['container_post_nonce'] : '';
		$container_post_nonce_valid = $container_post_id ? wp_verify_nonce($container_post_nonce, 'ifcf7-setup-container_post_' . $container_post_id) : false;
		if($container_post_nonce_valid and !in_the_loop()){
			$post = get_post($container_post_id);
			setup_postdata($post);
			$in_the_loop = $wp_query->in_the_loop;
			if(!$wp_query->in_the_loop){
				$wp_query->in_the_loop = true;
			}
			echo do_shortcode($content);
			wp_reset_postdata();
			$wp_query->in_the_loop = $in_the_loop;
		} else {
			echo do_shortcode($content);
		}
		die;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function load_form_func($atts, $content = ''){
		$atts = shortcode_atts([
			'class' => '',
			'disabled' => 0,
            'form_id' => 0,
			'hide_disabled' => 0,
			'html_class' => '',
			'html_id' => '',
			'link' => 0,
			'title' => 'Untitled',
        ], $atts, 'ifcf7_load_form');
		$content = trim($content);
		if(!$content){
			return '';
		}
		$contact_form = ifcf7_contact_form($atts['form_id']);
		if(!$contact_form){
			return '';
		}
		$class = ifwp_remove_whitespaces($atts['class']);
		unset($atts['class']);
		$disabled = ifwp_seems_true($atts['disabled']);
		unset($atts['disabled']);
		$hide_disabled = ifwp_seems_true($atts['hide_disabled']);
		unset($atts['hide_disabled']);
		$link = ifwp_seems_true($atts['link']);
		unset($atts['link']);
		if(in_the_loop()){
			$post = get_post();
			$atts['container_post_id'] = $post->ID;
			$atts['container_post_nonce'] = wp_create_nonce('ifcf7-setup-container_post_' . $post->ID);
		}
		$classes = explode(' ', ifwp_remove_whitespaces($class));
		if(!in_array('ifcf7-load_form', $classes)){
			$classes[] = 'ifcf7-load_form';
		}
		if(!in_array('dropdown-item', $classes) and !$link){
			if(!in_array('btn', $classes)){
				$classes[] = 'btn';
			}
			$has_class = false;
			preg_match_all('/btn-[A-Za-z][-A-Za-z0-9_:.]*/', $class, $matches);
			$matches = array_filter($matches[0], function($match){
				return !in_array($match, ['btn-block', 'btn-lg', 'btn-sm']);
			});
			if($matches){
				$has_class = true;
			}
			if(!$has_class){
				$classes[] = 'btn-primary';
			}
		}
		if($disabled){
			if($hide_disabled){
				return '';
			}
			if(!in_array('disabled', $classes)){
				$classes[] = 'disabled';
			}
		}
		$html = '<a href="#" title="' . $atts['title'] . '" class="' . implode(' ', $classes) . '"';
		if(!$atts['html_class']){
			unset($atts['html_class']);
		}
		if(!$atts['html_id']){
			unset($atts['html_id']);
		}
		foreach($atts as $key => $value){
			$html .= ' data-ifcf7-' . $key . '="' . trim($value) . '"';
		}
		if(!in_array('dropdown-item', $classes) and !$link){
			$html .= ' role="button"';
		}
		if($disabled){
			$html .= ' aria-disabled="true" tabindex="-1"';
		}
		$html .= '>';
		$html .= $content;
		$html .= '</a>';
    	return $html;
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wp_footer(){
		$class = 'modal-dialog modal-dialog-centered';
		$class = apply_filters('ifcf7_modal_class', $class);
		ob_start(); ?>
		<style>
			.modal.fade {
				background-color: rgba(0,0,0,0);
				transition: opacity 0.15s linear, background-color 0.15s linear;
			}
			.modal.fade.show {
			    background-color: rgba(0,0,0,.5);
			}
		</style>
		<div class="modal fade" id="ifcf7-modal" data-backdrop="false" data-keyboard="false" tabindex="-1" aria-labelledby="ifcf7-modal_label" aria-hidden="true">
			<div class="<?php echo $class; ?>">
				<div class="modal-content shadow-lg">
					<div class="modal-header">
						<h5 class="modal-title" id="ifcf7-modal_label">Untitled</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="bg-light modal-body" style="border-bottom-right-radius: calc(0.3rem - 1px); border-bottom-left-radius: calc(0.3rem - 1px);">
						<div id="ifcf7-modal_container"></div>
					</div>
				</div>
			</div>
		</div><?php
		$out = ob_get_clean();
		$out = apply_filters('ifcf7_modal', $out);
		echo $out;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7-load-form', 'load-form.js', ['contact-form-7']);
		wp_localize_script('ifcf7-load-form', 'ifcf7_load_form_object', [
			'ajaxurl' => admin_url('admin-ajax.php'),
		]);
		wp_add_inline_script('ifcf7-load-form', 'ifcf7_load_form.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
