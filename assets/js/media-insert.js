/**
 * Creates and handles a wp.media instance for Envira Galleries, allowing
 * the user to insert images from the WordPress Media Library into their Gallery
 */
jQuery( document ).ready( function( $ ) {

    // Select Files from Other Sources
    $( 'a.envira-media-library' ).on( 'click', function( e ) {

        // Prevent default action
        e.preventDefault();

        // If the wp.media.frames.envira instance already exists, reopen it
        if ( wp.media.frames.envira ) {
            wp.media.frames.envira.open();
            return;
        } else {
            // Create the wp.media.frames.envira instance (one time)
            wp.media.frames.envira = wp.media( {
                frame: 'post',
                title:  wp.media.view.l10n.insertIntoPost,
                button: {
                    text: wp.media.view.l10n.insertIntoPost,
                },
                multiple: true
            } );
        }

        // Mark existing Gallery images as selected when the modal is opened
        wp.media.frames.envira.on( 'open', function() {
            // Get any previously selected images
            var selection = wp.media.frames.envira.state().get( 'selection' );

            // Get images that already exist in the gallery, and select each one in the modal
            $( 'ul#envira-gallery-output li' ).each( function() {
                var attachment = wp.media.attachment( $( this ).attr( 'id' ) );
                selection.add( attachment ? [ attachment ] : [] );
            } );
        } );

        // Insert into Gallery Button Clicked
        wp.media.frames.envira.on( 'insert', function( selection ) {

            // Get state
            var state = wp.media.frames.envira.state(),
                images = [];

            // Iterate through selected images, building an images array
            selection.each( function( attachment ) {
                // Get the chosen options for this image (size, alignment, link type, link URL)
                var display = state.display( attachment ).toJSON();

                // Change the image link parameter based on the "Link To" setting the user chose in the media view
                switch ( display.link ) {
                    case 'none':
                        // Because users cry when their images aren't linked, we need to actually set this to the attachment URL
                        attachment.set( 'link', attachment.get( 'url' ) );
                        break;
                    case 'file':
                        attachment.set( 'link', attachment.get( 'url' ) );
                        break;
                    case 'post':
                        // Already linked to post by default
                        break;
                    case 'custom':
                        attachment.set( 'link', display.linkUrl );
                        break;
                }

                // Add the image to the images array
                images.push( attachment.toJSON() );
            }, this );

            // Make visible the "items are being added"
            $( document ).find('.envira-progress-adding-images').css('display', 'block');

            // Send the ajax request with our data to be processed.
            $.post(
                envira_gallery_metabox.ajax,
                {
                    action:     'envira_gallery_insert_images',
                    nonce:      envira_gallery_metabox.insert_nonce,
                    post_id:    envira_gallery_metabox.id,
                    // make this a JSON string so we can send larger amounts of data (images), otherwise max is around 20 by default for most server configs
                    images:     JSON.stringify(images),
                },
                function( response ) {
                    // Response should be a JSON success with the HTML for the image grid
                    if ( response && response.success ) {
                        // Set the image grid to the HTML we received
                        $( '#envira-gallery-output' ).html( response.success );

                        // Repopulate the Envira Gallery Image Collection
                        EnviraGalleryImagesUpdate( false );

                        $( document ).find('.envira-progress-adding-images').css('display', 'none');
                    }
                },
                'json'
            );

        } );

        wp.media.frames.envira.open();
        // Remove the 'Create Gallery' left hand menu item in the modal, as we don't
        // want users inserting galleries!
        $( 'div.media-menu a.media-menu-item:nth-child(2)' ).addClass( 'hidden' );
        $( 'div.media-menu a.media-menu-item:nth-child(4)' ).addClass( 'hidden' );
        return;

    } );

} );