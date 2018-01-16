/**
 * Handles:
 * - Copy to Clipboard functionality
 * - Dismissable Notices
 *
 * @since 1.5.0
 */
jQuery( document ).ready( function( $ ) {

    $("#screen-meta-links").prependTo("#envira-header-temp");
    $("#screen-meta").prependTo("#envira-header-temp");

	/**
    * Copy to Clipboard
    */
    if ( typeof Clipboard !== 'undefined' ) {
        $( document ).on( 'click', '.envira-clipboard', function( e ) {
            var envira_clipboard = new Clipboard('.envira-clipboard');
            e.preventDefault();
        } );
    }

	/**
    * Dismissable Notices
    * - Sends an AJAX request to mark the notice as dismissed
    */
    $( 'div.envira-notice' ).on( 'click', '.notice-dismiss', function( e ) {

        e.preventDefault();

        $( this ).closest( 'div.envira-notice' ).fadeOut();

        // If this is a dismissible notice, it means we need to send an AJAX request
        if ( $( this ).hasClass( 'is-dismissible' ) ) {
            $.post(
                envira_gallery_admin.ajax,
                {
                	action: 'envira_gallery_ajax_dismiss_notice',
                	nonce: 	envira_gallery_admin.dismiss_notice_nonce,
                	notice: $( this ).parent().data( 'notice' )
                },
                function( response ) {
    			},
                'json'
            );
        }

    } );

});