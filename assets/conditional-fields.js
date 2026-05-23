var ifcf7_conditional_fields = {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	init_fields: function(element){
		if(jQuery(element).find('.wpcf7cf_multistep').length && jQuery(element).children('.wpcf7-response-output').length){
			jQuery(element).children('.wpcf7-response-output').insertAfter(jQuery(element).find('.wpcf7cf_multistep')); // fix
		}
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	load: function(){
		jQuery(function($){
			$('.wpcf7-form').each(function(){
				ifcf7_conditional_fields.init_fields(this);
			});
			$(document).on('wpcf7cf_repeater_added', 'form', function(e){
				ifcf7_utilities.fix_fields(e.target);
			});
			if('undefined' !== typeof(window.wpcf7cf)){
				window.wpcf7cf.updateMultistepState = ifcf7_conditional_fields.updateMultistepState;
			}
		});
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	updateMultistepState: function(multistep){
		if (multistep == null) return; // update hidden input field

		var stepsData = {
			currentStep: multistep.currentStep,
			numSteps: multistep.numSteps,
			fieldsInCurrentStep: multistep.getFieldsInStep(multistep.currentStep)
		};
		multistep.form.$input_steps.val(JSON.stringify(stepsData)); // update Buttons

		multistep.$btn_prev.removeClass('disabled').attr('disabled', false);
		multistep.$btn_next.removeClass('disabled').attr('disabled', false);

		if (multistep.currentStep == multistep.numSteps) {
			multistep.$btn_next.addClass('disabled').attr('disabled', true);
		}

		if (multistep.currentStep == 1) {
			multistep.$btn_prev.addClass('disabled').attr('disabled', true);
		} // replace next button with submit button on last step.
		// TODO: make this depend on a setting


		//var $submit_button = multistep.form.$form.find('input[type="submit"]:last').eq(0);
		var $submit_button = multistep.form.$form.find('.wpcf7-submit:last').eq(0);
		var $ajax_loader = multistep.form.$form.find('.wpcf7-spinner').eq(0);
		$submit_button.detach().prependTo(multistep.$btn_next.parent());
		//$ajax_loader.detach().prependTo(multistep.$btn_next.parent());
		$ajax_loader.detach();

		/*if(!$submit_button.children('.wpcf7-spinner').length){
			$submit_button.append('<span class="wpcf7-spinner"></span>');
		}*/
		if(!$submit_button.children('.ifcf7-spinner').length){
			$submit_button.append('<span class="ifcf7-spinner"><span></span></span>');
		}

		if (multistep.currentStep == multistep.numSteps) {
			//multistep.$btn_next.hide();
			multistep.$btn_next.removeClass('d-inline-flex').addClass('d-none');
			//$submit_button.show();
			$submit_button.removeClass('d-none').addClass('d-inline-flex');
		} else {
			//$submit_button.hide();
			$submit_button.removeClass('d-inline-flex').addClass('d-none');
			//multistep.$btn_next.show();
			multistep.$btn_next.removeClass('d-none').addClass('d-inline-flex');
		} // update dots


		var $dots = multistep.$dots.find('.dot');
		$dots.removeClass('active').removeClass('completed');

		for (var step = 1; step <= multistep.numSteps; step++) {
			if (step < multistep.currentStep) {
				$dots.eq(step - 1).addClass('completed');
			} else if (step == multistep.currentStep) {
				$dots.eq(step - 1).addClass('active');
			}
		}
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

};
