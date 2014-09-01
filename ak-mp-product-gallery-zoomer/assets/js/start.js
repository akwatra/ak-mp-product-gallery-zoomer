// var $ = jQuery.noConflict(true)
 
 //CloudZoom.quickStart({zoomPosition: 'inside',zoomFlyOut: false,autoInside: 550});
 CloudZoom.quickStart();

// Initialize the slider.

if (jQuery) {
		//alert("jquery is loaded");
	(function($){

		jQuery('#slider1').Thumbelina({
			$bwdBut:jQuery('#slider1 .left'),    // Selector to left button.
			$fwdBut:jQuery('#slider1 .right')    // Selector to right button.
		});
				 
    })( jQuery );

} else if($){
	
//alert("Not loaded");

		$('#slider1').Thumbelina({
			$bwdBut:$('#slider1 .left'),    // Selector to left button.
			$fwdBut:$('#slider1 .right')    // Selector to right button.
		});
				 
}
