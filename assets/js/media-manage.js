/**
 * Handles:
 * - Selection and deselection of media in an Envira Gallery
 * - Toggling edit / delete button states when media is selected / deselected,
 * - Toggling the media list / grid view
 * - Storing the user's preferences for the list / grid view
 */

 // Setup some vars
var envira_gallery_output = '#envira-gallery-output',
    envira_gallery_shift_key_pressed = false,
    envira_gallery_last_selected_image = false;

jQuery( document ).ready( function( $ ) {

    // Toggle List / Grid View
    $( document ).on( 'click', 'nav.envira-tab-options a', function( e ) {

        e.preventDefault();

        // Get the view the user has chosen
        var envira_tab_nav          = $( this ).closest( '.envira-tab-options' ),
            envira_tab_view         = $( this ).data( 'view' ),
            envira_tab_view_style   = $( this ).data( 'view-style' );

        // If this view style is already displayed, don't do anything
        if ( $( envira_tab_view ).hasClass( envira_tab_view_style ) ) {
            return;
        }

        // Update the view class
        $( envira_tab_view ).removeClass( 'list' ).removeClass( 'grid' ).addClass( envira_tab_view_style );

        // Mark the current view icon as selected
        $( 'a', envira_tab_nav ).removeClass( 'selected' );
        $( this ).addClass( 'selected' );

        // Send an AJAX request to store this user's preference for the view
        // This means when they add or edit any other Gallery, the image view will default to this setting
        $.ajax( {
            url:      envira_gallery_metabox.ajax,
            type:     'post',
            dataType: 'json',
            data: {
                action:  'envira_gallery_set_user_setting',
                name:    'envira_gallery_image_view',
                value:   envira_tab_view_style,
                nonce:   envira_gallery_metabox.set_user_setting_nonce
            },
            success: function( response ) {
            },
            error: function( xhr, textStatus, e ) {
                // Inject the error message into the tab settings area
                $( envira_gallery_output ).before( '<div class="error"><p>' + textStatus.responseText + '</p></div>' );
            }
        } );

    } );

    // Toggle Select All / Deselect All
    $( document ).on( 'change', 'nav.envira-tab-options input', function( e ) {

        if ( $( this ).prop( 'checked' ) ) {
            $( 'li', $( envira_gallery_output ) ).addClass( 'selected' );
            $( 'nav.envira-select-options' ).fadeIn();
        } else {
            $( 'li', $( envira_gallery_output ) ).removeClass( 'selected' );
            $( 'nav.envira-select-options' ).fadeOut();
        }

    } );
	
    // Enable sortable functionality on images
	envira_gallery_sortable( $ );

    // When the Gallery Type is changed, reinitialise the sortable
    $( document ).on( 'enviraGalleryType', function() {

        if ( $( envira_gallery_output ).length > 0 ) {
            // Re-enable sortable functionality on images, now we're viewing the default gallery type
            envira_gallery_sortable( $ );
        }
        
    } );

    // Select / deselect images
    $( document ).on( 'click', 'ul#envira-gallery-output li.envira-gallery-image > img, li.envira-gallery-image > div, li.envira-gallery-image > a.check', function( e ) {

        // Prevent default action
        e.preventDefault();

        // Get the selected gallery item
        var gallery_item = $( this ).parent();

        if ( $( gallery_item ).hasClass( 'selected' ) ) {
            $( gallery_item ).removeClass( 'selected' );
            envira_gallery_last_selected_image = false;
        } else {
            
            // If the shift key is being held down, and there's another image selected, select every image between this clicked image
            // and the other selected image
            if ( envira_gallery_shift_key_pressed && envira_gallery_last_selected_image !== false ) {
                // Get index of the selected image and the last image
                var start_index = $( 'ul#envira-gallery-output li' ).index( $( envira_gallery_last_selected_image ) ),
                    end_index = $( 'ul#envira-gallery-output li' ).index( $( gallery_item ) ),
                    i = 0;

                // Select images within the range
                if ( start_index < end_index ) {
                    for ( i = start_index; i <= end_index; i++ ) {
                        $( 'ul#envira-gallery-output li:eq( ' + i + ')' ).addClass( 'selected' );
                    }
                } else {
                    for ( i = end_index; i <= start_index; i++ ) {
                        $( 'ul#envira-gallery-output li:eq( ' + i + ')' ).addClass( 'selected' );
                    }
                }
            }

            // Select the clicked image
            $( gallery_item ).addClass( 'selected' );
            envira_gallery_last_selected_image = $( gallery_item );

        }
        
        // Show/hide buttons depending on whether
        // any galleries have been selected
        if ( $( 'ul#envira-gallery-output > li.selected' ).length > 0 ) {
            $( 'nav.envira-select-options' ).fadeIn();
        } else {
            $( 'nav.envira-select-options' ).fadeOut();
        }
    } );

    // Determine whether the shift key is pressed or not
    $( document ).on( 'keyup keydown', function( e ) {
        envira_gallery_shift_key_pressed = e.shiftKey;
    } );

} );

/**
 * Enables sortable functionality on a grid of Envira Gallery Images
 *
 * @since 1.5.0
 */
function envira_gallery_sortable( $ ) {

    // Add sortable support to Envira Gallery Media items
    $( envira_gallery_output ).sortable( {
        containment: envira_gallery_output,
        items: 'li',
        cursor: 'move',
        forcePlaceholderSize: true,
        placeholder: 'dropzone',
        helper: function( e, item ) {

            // Basically, if you grab an unhighlighted item to drag, it will deselect (unhighlight) everything else
            if ( ! item.hasClass( 'selected' ) ) {
                item.addClass( 'selected' ).siblings().removeClass( 'selected' );
            }
            
            // Clone the selected items into an array
            var elements = item.parent().children( '.selected' ).clone();
            
            // Add a property to `item` called 'multidrag` that contains the 
            // selected items, then remove the selected items from the source list
            item.data( 'multidrag', elements ).siblings( '.selected' ).remove();
            
            // Now the selected items exist in memory, attached to the `item`,
            // so we can access them later when we get to the `stop()` callback
            
            // Create the helper
            var helper = $( '<li/>' );
            return helper.append( elements );

        },
        stop: function( e, ui ) {
            // Remove the helper so we just display the sorted items
            var elements = ui.item.data( 'multidrag' );
            ui.item.after(elements).remove();

            // Remove the selected class from everything
            $( 'li.selected', $( envira_gallery_output ) ).removeClass( 'selected' );
            
            // Send AJAX request to store the new sort order
            $.ajax( {
                url:      envira_gallery_metabox.ajax,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action:  'envira_gallery_sort_images',
                    order:   $( envira_gallery_output ).sortable( 'toArray' ).toString(),
                    post_id: envira_gallery_metabox.id,
                    nonce:   envira_gallery_metabox.sort
                },
                success: function( response ) {
                    // Repopulate the Envira Gallery Backbone Image Collection
                    EnviraGalleryImagesUpdate( false );
                    return;
                },
                error: function( xhr, textStatus, e ) {
                    // Inject the error message into the tab settings area
                    $( envira_gallery_output ).before( '<div class="error"><p>' + textStatus.responseText + '</p></div>' );
                }
            } );
        }
    } );

}