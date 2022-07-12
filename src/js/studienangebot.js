/*
 * AJAX for studienangebot-database
 */
 function elementIsFixed(element) {
     var isFixed = false;
     if (jQuery(element).css("position") == "fixed") {
             isFixed = true;
     }
     return isFixed;
 }

 function addCollapseToggle() {
 	jQuery('button[data-toggle=collapse]').each(function(index){
 		jQuery('#' + jQuery(this).data('target')).hide();
 		jQuery(this).click(function() {
 			jQuery('#' + jQuery(this).data('target')).slideToggle();
 		});
 	});
 }

 function showFilterOnSmallViewport() {
 	if(jQuery(window).width() <= 768){
 		if(!jQuery('#studienangebot-filter').length) {
			var toggleButtonText = 'Filter ein/ausblenden';
			if(jQuery('#studienangebot').attr('data-filtertoggle')) {
				toggleButtonText = jQuery('#studienangebot').data('filtertoggle');
			}
 			jQuery('#studienangebot').before('<p id="studienangebot-filter"><button data-toggle="collapse" data-target="studienangebot">' + toggleButtonText + '</button></p>');
 			jQuery('.collapse.xs-collapsed').show();
 			addCollapseToggle();
 		}
 	} else {
 		if(jQuery('#studienangebot-filter').length) {
 			jQuery('#studienangebot-filter').remove();
 			jQuery('.collapse.xs-collapsed').show();
			jQuery('#studienangebot').show();
 		}
 	}
 }

jQuery(document).ready(function($) {
	$('#studienangebot *').change(function() {
		// Show loading spinner
		$('#loading').fadeIn(300);

		// Get results and replace content
		$.get($(this).parents('form').attr('action'), $(this).parents('form').serialize(), function(data) {
			$('#studienangebot-result').replaceWith($(data).find('#studienangebot-result'));
			$('#loading').fadeOut(300);
			var topOffset = 20;
			var isFixed = elementIsFixed('#header');
			if (isFixed) {
				topOffset = $('#header').outerHeight(true) + 20;
			}
			$('html, body').animate({ scrollTop: ($("#studienangebot-result").offset().top - topOffset)}, 'slow');
		});
	});

	showFilterOnSmallViewport();
	$(window).resize(function(){
		showFilterOnSmallViewport();
	});
});
