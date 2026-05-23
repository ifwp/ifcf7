<?php namespace IFCF7;

final class Autoresize {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
		add_action('wpcf7_enqueue_styles', [__CLASS__, 'wpcf7_enqueue_styles']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		ifwp_enqueue_asset('ifcf7-autoresize', 'autoresize.js', ['contact-form-7', 'ifwp']);
		wp_add_inline_script('ifcf7-autoresize', 'ifcf7_autoresize.load();');
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_styles(){
		ifwp_enqueue_asset('ifcf7-autoresize', 'autoresize.css', ['contact-form-7']);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
