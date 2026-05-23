<?php namespace IFCF7;

final class ACE {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts']);
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
		if(wp_is_mobile()){
			return;
		}
		wp_enqueue_script('ace', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.min.js', ['wpcf7-admin'], '1.4.12', true);
		wp_enqueue_script('ace-language-tools', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.min.js', ['ace'], '1.4.12', true);
		ifwp_enqueue_asset('ifcf7-ace', 'ace.css', ['contact-form-7-admin']);
		ifwp_enqueue_asset('ifcf7-ace', 'ace.js', ['ace-language-tools']);
		wp_add_inline_script('ifcf7-ace', 'ifcf7_ace.load();');
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
