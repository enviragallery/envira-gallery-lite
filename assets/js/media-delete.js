jQuery( document ).ready( function( $ ) {

	/**
    * Delete Multiple Images
    */
    $( document ).on( 'click', 'a.envira-gallery-images-delete', function( e ) {

        e.preventDefault();

        // Bail out if the user does not actually want to remove the image.
        var confirm_delete = confirm(envira_gallery_metabox.remove_multiple);
        if ( ! confirm_delete ) {
            return false;
        }

        // Build array of image attachment IDs
        var attach_ids = [];
        $( 'ul#envira-gallery-output > li.selected' ).each( function() {
            attach_ids.push( $( this ).attr( 'id' ) );
        } );

        // Send an AJAX request to delete the selected items from the Gallery
        var attach_id = $( this ).parent().attr( 'id' );
        $.ajax( {
            url:      envira_gallery_metabox.ajax,
            type:     'post',
            dataType: 'json',
            data: {
                action:        'envira_gallery_remove_images',
                attachment_ids:attach_ids,
                post_id:       envira_gallery_metabox.id,
                nonce:         envira_gallery_metabox.remove_nonce
            },
            success: function( response ) {
                // Remove each image
                $( 'ul#envira-gallery-output > li.selected' ).remove();

                // Hide Select Options
                $( 'nav.envira-select-options' ).fadeOut();

                // Refresh the modal view to ensure no items are still checked if they have been removed.
                $( '.envira-gallery-load-library' ).attr( 'data-envira-gallery-offset', 0 ).addClass( 'has-search' ).trigger( 'click' );

                // Repopulate the Envira Gallery Image Collection
                EnviraGalleryImagesUpdate( false );
            },
            error: function( xhr, textStatus, e ) {
                // Inject the error message into the tab settings area
                $( envira_gallery_output ).before( '<div class="error"><p>' + textStatus.responseText + '</p></div>' );
            }
        } );

    } );

    /**
    * Delete Single Image
    */
    $( document ).on( 'click', '#envira-gallery-main .envira-gallery-remove-image', function( e ) {
        
        e.preventDefault();

        // Bail out if the user does not actually want to remove the image.
        var confirm_delete = confirm( envira_gallery_metabox.remove );
        if ( ! confirm_delete ) {
            return;
        }

        // Send an AJAX request to delete the selected items from the Gallery
        var attach_id = $( this ).parent().attr( 'id' );
        $.ajax( {
            url:      envira_gallery_metabox.ajax,
            type:     'post',
            dataType: 'json',
            data: {
                action:        'envira_gallery_remove_image',
                attachment_id: attach_id,
                post_id:       envira_gallery_metabox.id,
                nonce:         envira_gallery_metabox.remove_nonce
            },
            success: function( response ) {
                $( '#' + attach_id ).fadeOut( 'normal', function() {
                    $( this ).remove();

                    // Refresh the modal view to ensure no items are still checked if they have been removed.
                    $( '.envira-gallery-load-library' ).attr( 'data-envira-gallery-offset', 0 ).addClass( 'has-search' ).trigger( 'click' );

                    // Repopulate the Envira Gallery Image Collection
                    EnviraGalleryImagesUpdate( false );
                } );
            },
            error: function( xhr, textStatus, e ) {
                // Inject the error message into the tab settings area
                $( envira_gallery_output ).before( '<div class="error"><p>' + textStatus.responseText + '</p></div>' );
            }
        } );
    } );

} );