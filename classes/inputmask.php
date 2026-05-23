<?php namespace IFCF7;

final class Inputmask {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	public static function ifwp_plugins_loaded(){
        add_action('wpcf7_enqueue_scripts', [__CLASS__, 'wpcf7_enqueue_scripts']);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	//
	// public
	//
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public static function wpcf7_enqueue_scripts(){
		//wp_enqueue_script('jquery-inputmask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7/jquery.inputmask.min.js', ['contact-form-7'], '5.0.7', true);
		//wp_enqueue_script('jquery-inputmask-binding', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7/bindings/inputmask.binding.min.js', ['jquery-inputmask'], '5.0.7', true);
		//wp_enqueue_script('jquery-inputmask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/4.0.9/jquery.inputmask.bundle.min.js', ['contact-form-7'], '4.0.9', true);
		wp_enqueue_script('jquery-inputmask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8-beta.17/jquery.inputmask.min.js', ['contact-form-7'], '5.0.8-beta.17', true);
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
