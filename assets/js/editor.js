/* ==========================================================
 * editor.js
 * http://enviragallery.com/
 *
 * This file can be used by 3rd party plugins to integrate
 * with their custom field systems. It allows the selection
 * process to be standardized so that 3rd party plugins can
 * trigger modal selection windows and receive the corresponding
 * selected data objects.
 *
 * Using this file requires two actions for the 3rd party plugin.
 *
 * 1. This file should be enqueued on the page where the field resides.
 *
 * 2. You must add the class ".envira-gallery-modal-trigger" to the
 *    option/dropdown/button that will trigger the modal.
 *
 * 3. You must add the data-action="gallery" or data-action="album" attribute
 *    to the option/dropdown/button that will trigger the modal.
 *
 * 4. Attaching to a global event that is fired once the data for the
 *    selection has been retrieved. You should listen on the document
 *    object for the "enviraGalleryModalData" event, like this:
 *
 *    jQuery( document ).on( 'enviraGalleryModalData', function( e ) { 
 *        console.log( e.action );            // 'gallery' or 'album'
 *        console.log( e.multiple );          // Whether the user could select multiple Galleries / Albums (true|false)
 *        console.log( e.items );             // An array of Galleries or Albums
 *        console.log( e.insert_options );    // An object of the Insert Options the user chose
 *    } );
 *
 *    This will give you access to the entire array of galleries or albums that
 *    the user has selected, including ID, title and slug.
 *
 *    Please note that Envira Gallery 1.5.0 and Envira Albums 1.3.0 introduced
 *    support for selecting multiple Galleries / Albums in the Backbone modal.
 */ 
jQuery( document ).ready( function( $ ) {

    // Open the "Add Gallery" / "Add Album" modal
    $( document ).on( 'click', 'a.envira-gallery-choose-gallery, a.envira-albums-choose-album, .envira-gallery-modal-trigger', function( e ) {

        // Prevent default action
        e.preventDefault();

        // Get the action
        var action = $( this ).data( 'action' );

        // Define the modal's view
        EnviraGalleryModalWindow.content( new EnviraGallerySelectionView( {
            action:             action, // gallery|album
            multiple:           true,   // Allow multiple Galleries / Albums to be selected
            modal_title:        envira_gallery_editor.modal_title,
            insert_button_label:envira_gallery_editor.insert_button_label
        } ) );

        // Open the modal window
        EnviraGalleryModalWindow.open();

    } );

} );