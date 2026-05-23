<?php namespace IFCF7;

final class Conditional_Fields {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
		add_action('admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts']);
		add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
		add_filter('wpcf7_form_elements', [__CLASS__, 'wpcf7_form_elements'], 20);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function admin_enqueue_scripts($hook_suffix){
		if(false === strpos($hook_suffix, 'wpcf7')){
			return;
		}
		ifwp_enqueue_asset('ifcf7-conditional-fields', 'conditional-fields-admin.css', ['contact-form-7-admin']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function wpcf7_form_elements($form = ''){
		$html = ifwp_str_get_html($form);
		if(is_wp_error($html)){
			return $form;
		}
		$multistep = $html->find('.wpcf7cf_multistep', 0);
		if($multistep){
			$next = $multistep->find('.wpcf7cf_next', 0);
			$next->addClass('btn btn-primary d-inline-flex align-items-center justify-content-center');
			$next->innertext = $next->innertext . '<span class="ifcf7-spinner ml-2"><span></span></span>';
			$prev = $multistep->find('.wpcf7cf_prev', 0);
			$prev->addClass('btn btn-outline-primary');
			$controls = $multistep->find('.wpcf7cf_step_controls', 0);
			$controls->addClass('mb-3 mt-0');
			$submit = $html->find('.wpcf7-submit', 0);
			if($submit->hasClass('btn-lg')){
				$next->addClass('btn-lg');
				$prev->addClass('btn-lg');
			}
			if($submit->hasClass('btn-sm')){
				$next->addClass('btn-sm');
				$prev->addClass('btn-sm');
			}
		}
		$repeater = $html->find('.wpcf7cf_repeater', 0);
		if($repeater){
			$add = $repeater->find('.wpcf7cf_add', 0);
			$add->addClass('btn btn-outline-success');
			$remove = $repeater->find('.wpcf7cf_remove', 0);
			$remove->addClass('btn btn-outline-danger');
			$controls = $repeater->find('.wpcf7cf_repeater_controls', 0);
			$controls->addClass('mt-0 mb-3');
			$sub = $repeater->find('.wpcf7cf_repeater_sub', 0);
			$sub->addClass('mb-0');
			$submit = $html->find('.wpcf7-submit', 0);
			if($submit->hasClass('btn-lg')){
				$add->addClass('btn-lg');
				$remove->addClass('btn-lg');
			}
			if($submit->hasClass('btn-sm')){
				$add->addClass('btn-sm');
				$remove->addClass('btn-sm');
			}
		}
        return $html;
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7-conditional-fields', 'conditional-fields.js', ['contact-form-7']);
		wp_add_inline_script('ifcf7-conditional-fields', 'ifcf7_conditional_fields.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-conditional-fields', 'conditional-fields.css', ['contact-form-7']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
