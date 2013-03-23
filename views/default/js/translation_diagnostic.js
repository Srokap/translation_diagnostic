elgg.provide('elgg.translation_diagnostic');

/**
 * Register the autocomplete input.
 */
elgg.translation_diagnostic.init = function() {
	$('.elgg-form-translation-diagnostic-select').submit(function(){
		$('#translation-diagnostic-loader').removeClass('hidden');
		$('#translation-diagnostic-result').html('');
		elgg.get('ajax/view/translation_diagnostic/analysis', {
			data: $(this).serialize(),
			success: function(data){
				$('#translation-diagnostic-result').html(data);
			},
			error: function(jqXHR, textStatus, errorThrown){
				elgg.register_error(elgg.echo('translation_diagnostic:error:request'));
			},
			complete: function(){
				$('#translation-diagnostic-loader').addClass('hidden');
			}
		});
		
		return false;
	});
};

elgg.register_hook_handler('init', 'system', elgg.translation_diagnostic.init);