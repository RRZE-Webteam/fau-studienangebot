/* 
 * AJAX for studienangebot-database
 */

jQuery(document).ready(function($) {	
	$('#studienangebot *').change(function() {
		// Show loading spinner
		$('#loading').fadeIn(300);
		
		// Get results and replace content
		$.get($(this).parents('form').attr('action'), $(this).parents('form').serialize(), function(data) {
			$('#studienangebot-result').replaceWith($(data).find('#studienangebot-result'));
			$('#loading').fadeOut(300);
		});
	});	
    }
);