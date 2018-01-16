/**
* Determines if the device supports touch, and if so adds a class to the body
* which Envira can then use
*
* @since 1.3.3
*/
jQuery(document).ready(function($) {
	var isTouchDevice = 'ontouchstart' in document.documentElement;
	if ( isTouchDevice ) {
		$('body').addClass( 'envira-touch' );
	}
});