jq = jQuery.noConflict();
jq(function($){
	$('form[id^=shortcalc-form-]').submit(function(arg, brg){
		var formId = $(this).attr('id');
		var formName = formId.replace(/shortcalc-form-/,'');
		var templateName = 'calculator-result-' + formName;
		var resultId = 'shortcalc-form-result-' + formName;

		//calculator_type in $_POST that is formName
		var data = {
			'action': 'get_calculator_result',
			'calculator_name': formName,
			'parameters': {}
		};

		var inputs = $(this).find('input, select');
		for (var i=0; i<inputs.length; i++){
			var elmt = inputs[i];
			data.parameters[$(elmt).attr('name')] = $(elmt).val();
		}

		$.post(ajax_object.ajax_url, data, function(response) {
			var template = wp.template( templateName );
			$('#' + resultId).html( template( { result: response } ) );
		});

		return false;
	});
});
