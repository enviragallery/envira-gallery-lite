/**
 * Hooks into the global Plupload instance ('uploader'), which is set when includes/admin/metaboxes.php calls media_form()
 * We hook into this global instance and apply our own changes during and after the upload.
 *
 * @since 1.3.1.3 
 */
(function( $ ) {
    $(function() {

        if ( typeof uploader !== 'undefined' ) {

            // Change "Select Files" button in the pluploader to "Select Files from Your Computer"
            $( 'input#plupload-browse-button' ).val( envira_gallery_metabox.uploader_files_computer );

            // Set a custom progress bar
            var envira_bar      = $( '#envira-gallery .envira-progress-bar' ),
                envira_progress = $( '#envira-gallery .envira-progress-bar div.envira-progress-bar-inner' ),
                envira_status   = $( '#envira-gallery .envira-progress-bar div.envira-progress-bar-status' ),
                envira_output   = $( '#envira-gallery-output' ),
                envira_error    = $( '#envira-gallery-upload-error' ),
                envira_file_count = 0;

            // Uploader has initialized
            uploader.bind( 'Init', function( up ) {

                // Fade in the uploader, as it's hidden with CSS so the user doesn't see elements reposition on screen and look messy.
                $( '#drag-drop-area' ).fadeIn();
                $( 'a.envira-media-library.button' ).fadeIn();

            } );

            // Files Added for Uploading
            uploader.bind( 'FilesAdded', function ( up, files ) {

                // Hide any existing errors
                $( envira_error ).html( '' );

                // Get the number of files to be uploaded
                envira_file_count = files.length;

                // Set the status text, to tell the user what's happening
                $( '.uploading .current', $( envira_status ) ).text( '1' );
                $( '.uploading .total', $( envira_status ) ).text( envira_file_count );
                $( '.uploading', $( envira_status ) ).show();
                $( '.done', $( envira_status ) ).hide();

                // Fade in the upload progress bar
                $( envira_bar ).fadeIn( "fast", function() {
                    $( 'p.max-upload-size' ).css('padding-top', '10px');
                });

                

            } );

            // File Uploading - show progress bar
            uploader.bind( 'UploadProgress', function( up, file ) {

                // Update the status text
                $( '.uploading .current', $( envira_status ) ).text( ( envira_file_count - up.total.queued ) + 1 );

                // Update the progress bar
                $( envira_progress ).css({
                    'width': up.total.percent + '%'
                });

            });

            // File Uploaded - AJAX call to process image and add to screen.
            uploader.bind( 'FileUploaded', function( up, file, info ) {

                // AJAX call to Envira to store the newly uploaded image in the meta against this Gallery
                $.post(
                    envira_gallery_metabox.ajax,
                    {
                        action:  'envira_gallery_load_image',
                        nonce:   envira_gallery_metabox.load_image,
                        id:      info.response,
                        post_id: envira_gallery_metabox.id
                    },
                    function(res){
                        // Prepend or append the new image to the existing grid of images,
                        // depending on the media_position setting
                        switch ( envira_gallery_metabox.media_position ) {
                            case 'before':
                                $(envira_output).prepend(res);
                                break;
                            case 'after':
                            default:
                                $(envira_output).append(res);
                                break;
                        }

                        // Repopulate the Envira Gallery Image Collection
                        EnviraGalleryImagesUpdate( false );

                    },
                    'json'
                );
            });

            // Files Uploaded
            uploader.bind( 'UploadComplete', function() {

                // Update status
                $( '.uploading', $( envira_status ) ).hide();
                $( '.done', $( envira_status ) ).show();

                // Hide Progress Bar
                setTimeout( function() {
                    $( envira_bar ).fadeOut( "fast", function() {
                        $( 'p.max-upload-size' ).css('padding-top', '0');
                    });
                }, 1000 );

            });

            // File Upload Error
            uploader.bind('Error', function(up, err) {

                // Show message
                $('#envira-gallery-upload-error').html( '<div class="error fade"><p>' + err.file.name + ': ' + err.message + '</p></div>' );
                up.refresh();

            });

        }

    });
})( jQuery );