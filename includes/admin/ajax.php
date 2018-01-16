<?php
/**
 * Handles all admin ajax interactions for the Envira Gallery plugin.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */

add_action( 'wp_ajax_envira_gallery_change_type', 'envira_gallery_ajax_change_type' );
/**
 * Changes the type of gallery to the user selection.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_change_type() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-change-type', 'nonce' );

    // Prepare variables.
    $post_id = absint( $_POST['post_id'] );
    $post    = get_post( $post_id );
    $type    = stripslashes( $_POST['type'] );

    // Retrieve the data for the type selected.
    ob_start();
    $instance = Envira_Gallery_Metaboxes::get_instance();
    $instance->images_display( $type, $post );
    $html = ob_get_clean();

    // Send back the response.
    echo json_encode( array( 'type' => $type, 'html' => $html ) );
    die;

}

add_action( 'wp_ajax_envira_gallery_change_preview', 'envira_gallery_ajax_change_preview' );
/**
 * Returns the output for the Preview Metabox for the given Gallery Type.
 *
 * @since 1.5.0
 */
function envira_gallery_ajax_change_preview() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-change-preview', 'nonce' );

    // Prepare variables.
    $post_id = absint( $_POST['post_id'] );
    $type    = stripslashes( $_POST['type'] );

    // Get the saved Gallery configuration.
    $data    = Envira_Gallery_Lite::get_instance()->get_gallery( $post_id );

    // Iterate through the POSTed Gallery configuration (which comprises of index based fields),
    // overwriting the above with the supplied values.  This gives us the most up to date,
    // unsaved configuration.
    foreach ( $_POST['data'] as $index => $field ) {

        // Skip if this isnt' a configuration field.
        if ( strpos( $field['name'], '_envira_gallery[' ) === false ) {
            continue;
        }

        // Extract the key from the field name.
        preg_match_all( "/\[([^\]]*)\]/", $field['name'], $matches );
        if ( ! isset( $matches[1] ) || count( $matches[1] ) == 0 ) {
            continue;
        }

        // Add this field key/value pair to the configuration
        $data['config'][ $matches[1][0] ] = $field['value'];

    }

    // Retrieve the preview for the type selected, using the now up-to-date gallery configuration.
    ob_start();
    do_action( 'envira_gallery_preview_' . $type, $data );
    $html = ob_get_clean();

    // Send back the response.
    echo json_encode( $html );
    die;

}

add_action( 'wp_ajax_envira_gallery_set_user_setting', 'envira_gallery_ajax_set_user_setting' );
/**
 * Stores a user setting for the logged in WordPress User
 *
 * @since 1.5.0
 */
function envira_gallery_ajax_set_user_setting() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-set-user-setting', 'nonce' );

    // Prepare variables.
    $name    = stripslashes( $_POST['name'] );
    $value   = stripslashes( $_POST['value'] );

    // Set user setting.
    set_user_setting( $name, $value );

    // Send back the response.
    wp_send_json_success();
    die();

}

add_action( 'wp_ajax_envira_gallery_load_image', 'envira_gallery_ajax_load_image' );
/**
 * Loads an image into a gallery.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_load_image() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-load-image', 'nonce' );

    // Prepare variables.
    $id      = absint( $_POST['id'] );
    $post_id = absint( $_POST['post_id'] );

    // Set post meta to show that this image is attached to one or more Envira galleries.
    $has_gallery = get_post_meta( $id, '_eg_has_gallery', true );
    if ( empty( $has_gallery ) ) {
        $has_gallery = array();
    }

    $has_gallery[] = $post_id;
    update_post_meta( $id, '_eg_has_gallery', $has_gallery );

    // Set post meta to show that this image is attached to a gallery on this page.
    $in_gallery = get_post_meta( $post_id, '_eg_in_gallery', true );
    if ( empty( $in_gallery ) ) {
        $in_gallery = array();
    }

    $in_gallery[] = $id;
    update_post_meta( $post_id, '_eg_in_gallery', $in_gallery );

    // Set data and order of image in gallery.
    $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
    if ( empty( $gallery_data ) ) {
        $gallery_data = array();
    }

    // If no gallery ID has been set, set it now.
    if ( empty( $gallery_data['id'] ) ) {
        $gallery_data['id'] = $post_id;
    }

    // Set data and update the meta information.
    $gallery_data = envira_gallery_ajax_prepare_gallery_data( $gallery_data, $id );
    update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

    // Run hook before building out the item.
    do_action( 'envira_gallery_ajax_load_image', $id, $post_id );

    // Build out the individual HTML output for the gallery image that has just been uploaded.
    $html = Envira_Gallery_Metaboxes::get_instance()->get_gallery_item( $id, $gallery_data['gallery'][$id], $post_id );

    // Allow addons to filter the HTML output
    $html = apply_filters( 'envira_gallery_ajax_get_gallery_item_html', $html, $gallery_data, $id, $post_id );

    // Flush the gallery cache.
    Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id );

    echo json_encode( $html );
    die;

}

add_action( 'wp_ajax_envira_gallery_insert_images', 'envira_gallery_ajax_insert_images' );
/**
 * Inserts one or more images from the Media Library into a gallery.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_insert_images() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-insert-images', 'nonce' );

    // Prepare variables.
    $images = array();

    if ( isset( $_POST['images'] ) ) {
        $images = json_decode( stripslashes( $_POST['images'] ), true );
    }

    // Get the Envira Gallery ID
    $post_id = absint( $_POST['post_id'] );

    // Grab and update any gallery data if necessary.
    $in_gallery = get_post_meta( $post_id, '_eg_in_gallery', true );
    if ( empty( $in_gallery ) ) {
        $in_gallery = array();
    }

    // Set data and order of image in gallery.
    $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
    if ( empty( $gallery_data ) ) {
        $gallery_data = array();
    }

    // If no gallery ID has been set, set it now.
    if ( empty( $gallery_data['id'] ) ) {
        $gallery_data['id'] = $post_id;
    }

    // Loop through the images and add them to the gallery.
    foreach ( (array) $images as $i => $image ) {

        // If the image is already in the gallery, lets skip it since we don't want to override the image metadata settings
        if ( in_array( $image['id'], $in_gallery ) ) {
            continue;
        }

        // Update the attachment image post meta first.
        $has_gallery = get_post_meta( $image['id'], '_eg_has_gallery', true );
        if ( empty( $has_gallery ) ) {
            $has_gallery = array();
        }

        $has_gallery[] = $post_id;
        update_post_meta( $image['id'], '_eg_has_gallery', $has_gallery );

        // Now add the image to the gallery for this particular post.
        $in_gallery[] = $image['id'];
        $gallery_data = envira_gallery_ajax_prepare_gallery_data( $gallery_data, $image['id'], $image );
    }

    // Update the gallery data.
    update_post_meta( $post_id, '_eg_in_gallery', $in_gallery );
    update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

    // Run hook before finishing.
    do_action( 'envira_gallery_ajax_insert_images', $images, $post_id );

    // Flush the gallery cache.
    Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id );

    // Return a HTML string comprising of all gallery images, so the UI can be updated
    $html = '';
    foreach ( (array) $gallery_data['gallery'] as $id => $data ) {
        $html .= Envira_Gallery_Metaboxes::get_instance()->get_gallery_item( $id, $data, $post_id );
    }

    // Output JSON and exit
    echo json_encode( array( 'success' => $html ) );
    die;

}

add_action( 'wp_ajax_envira_gallery_sort_images', 'envira_gallery_ajax_sort_images' );
/**
 * Sorts images based on user-dragged position in the gallery.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_sort_images() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-sort', 'nonce' );

    // Prepare variables.
    $order        = explode( ',', $_POST['order'] );
    $post_id      = absint( $_POST['post_id'] );
    $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );

    // Copy the gallery config, removing the images
    // Stops config from getting lost when sorting + not clicking Publish/Update
    $new_order = $gallery_data;
    unset( $new_order['gallery'] );
    $new_order['gallery'] = array();

    // Loop through the order and generate a new array based on order received.
    foreach ( $order as $id ) {
        $new_order['gallery'][$id] = $gallery_data['gallery'][$id];
    }

    // Update the gallery data.
    update_post_meta( $post_id, '_eg_gallery_data', $new_order );

    // Flush the gallery cache.
    Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id );

    echo json_encode( true );
    die;

}

add_action( 'wp_ajax_envira_gallery_remove_image', 'envira_gallery_ajax_remove_image' );
/**
 * Removes an image from a gallery.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_remove_image() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-remove-image', 'nonce' );

    // Prepare variables.
    $post_id      = absint( $_POST['post_id'] );
    $attach_id    = absint( $_POST['attachment_id'] );
    $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
    $in_gallery   = get_post_meta( $post_id, '_eg_in_gallery', true );
    $has_gallery  = get_post_meta( $attach_id, '_eg_has_gallery', true );

    // Unset the image from the gallery, in_gallery and has_gallery checkers.
    unset( $gallery_data['gallery'][ $attach_id ] );

    if ( ( $key = array_search( $attach_id, (array) $in_gallery ) ) !== false ) {
        unset( $in_gallery[ $key ] );
    }

    if ( ( $key = array_search( $post_id, (array) $has_gallery ) ) !== false ) {
        unset( $has_gallery[ $key ] );
    }

    // Update the gallery data.
    update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );
    update_post_meta( $post_id, '_eg_in_gallery', $in_gallery );
    update_post_meta( $attach_id, '_eg_has_gallery', $has_gallery );

    // Run hook before finishing the reponse.
    do_action( 'envira_gallery_ajax_remove_image', $attach_id, $post_id );

    // Flush the gallery cache.
    Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id );

    echo json_encode( true );
    die;

}

add_action( 'wp_ajax_envira_gallery_remove_images', 'envira_gallery_ajax_remove_images' );
/**
 * Removes multiple images from a gallery.
 *
 * @since 1.3.2.4
 */
function envira_gallery_ajax_remove_images() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-remove-image', 'nonce' );

    // Prepare variables.
    $post_id      = absint( $_POST['post_id'] );
    $attach_ids   = (array) $_POST['attachment_ids'];
    $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
    $in_gallery   = get_post_meta( $post_id, '_eg_in_gallery', true );

    foreach ( (array) $attach_ids as $attach_id ) {
        $has_gallery  = get_post_meta( $attach_id, '_eg_has_gallery', true );

        // Unset the image from the gallery, in_gallery and has_gallery checkers.
        unset( $gallery_data['gallery'][$attach_id] );

        if ( ( $key = array_search( $attach_id, (array) $in_gallery ) ) !== false ) {
            unset( $in_gallery[$key] );
        }

        if ( ( $key = array_search( $post_id, (array) $has_gallery ) ) !== false ) {
            unset( $has_gallery[$key] );
        }

        // Update the attachment data.
        update_post_meta( $attach_id, '_eg_has_gallery', $has_gallery );
    }

    // Update the gallery data
    update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );
    update_post_meta( $post_id, '_eg_in_gallery', $in_gallery );

    // Run hook before finishing the reponse.
    do_action( 'envira_gallery_ajax_remove_images', $attach_id, $post_id );

    // Flush the gallery cache.
    Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id );

    echo json_encode( true );
    die;

}

add_action( 'wp_ajax_envira_gallery_save_meta', 'envira_gallery_ajax_save_meta' );
/**
 * Saves the metadata for an image in a gallery.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_save_meta() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-save-meta', 'nonce' );

    // Prepare variables.
    $post_id      = absint( $_POST['post_id'] );
    $attach_id    = absint( $_POST['attach_id'] );
    $meta         = $_POST['meta'];
    $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );

    if ( isset( $meta['title'] ) ) {
        $gallery_data['gallery'][ $attach_id ]['title'] = trim( $meta['title'] );
    }

    if ( isset( $meta['alt'] ) ) {
        $gallery_data['gallery'][ $attach_id ]['alt'] = trim( esc_html( $meta['alt'] ) );
    }

    if ( isset( $meta['link'] ) ) {
        $gallery_data['gallery'][ $attach_id ]['link'] = esc_url( $meta['link'] );
    }

    if ( isset( $meta['link_new_window'] ) ) {
        $gallery_data['gallery'][ $attach_id ]['link_new_window'] = trim( $meta['link_new_window'] );
    }

    if ( isset( $meta['caption'] ) ) {
        $gallery_data['gallery'][ $attach_id ]['caption'] = trim( $meta['caption'] );
    }

    // Allow filtering of meta before saving.
    $gallery_data = apply_filters( 'envira_gallery_ajax_save_meta', $gallery_data, $meta, $attach_id, $post_id );

    // Update the gallery data.
    update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

    // Flush the gallery cache.
    Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id );

    // Done
    wp_send_json_success();
    die;

}

add_action( 'wp_ajax_envira_gallery_save_bulk_meta', 'envira_gallery_ajax_save_bulk_meta' );
/**
 * Saves the metadata for multiple images in a gallery (bulk edit).
 *
 * @since 1.4.2.2
 */
function envira_gallery_ajax_save_bulk_meta() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-save-meta', 'nonce' );

    // Prepare variables.
    $post_id      = absint( $_POST['post_id'] );
    $image_ids    = $_POST['image_ids'];
    $meta         = $_POST['meta'];

    // Check the required variables exist.
    if ( empty( $post_id ) ) {
        wp_send_json_error();
    }
    if ( empty( $image_ids ) || ! is_array( $image_ids ) ) {
        wp_send_json_error();
    }
    if ( empty( $meta ) || ! is_array( $meta ) ) {
        wp_send_json_error();
    }

    // Get gallery.
    $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
    if ( empty( $gallery_data ) || ! is_array( $gallery_data ) ) {
        wp_send_json_error();
    }

    // Iterate through gallery images, updating the metadata.
    foreach ( $image_ids as $image_id ) {
        // If the image isn't in the gallery, something went wrong - so skip this image.
        if ( ! isset( $gallery_data['gallery'][ $image_id ] ) ) {
            continue;
        }

        // Update image metadata.
        if ( isset( $meta['title'] ) && ! empty( $meta['title'] ) ) {
            $gallery_data['gallery'][ $image_id ]['title'] = trim( $meta['title'] );
        }

        if ( isset( $meta['alt'] ) && ! empty( $meta['alt'] )  ) {
            $gallery_data['gallery'][ $image_id ]['alt'] = trim( esc_html( $meta['alt'] ) );
        }

        if ( isset( $meta['link'] ) && ! empty( $meta['link'] )  ) {
            $gallery_data['gallery'][ $image_id ]['link'] = esc_url( $meta['link'] );
        }

        if ( isset( $meta['link_new_window'] ) && ! empty( $meta['link_new_window'] )  ) {
            $gallery_data['gallery'][ $image_id ]['link_new_window'] = trim( $meta['link_new_window'] );
        }

        if ( isset( $meta['caption'] ) && ! empty( $meta['caption'] )  ) {
            $gallery_data['gallery'][ $image_id ]['caption'] = trim( $meta['caption'] );
        }

        // Allow filtering of meta before saving.
        $gallery_data = apply_filters( 'envira_gallery_ajax_save_bulk_meta', $gallery_data, $meta, $image_id, $post_id );
    }

    // Update the gallery data.
    update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

    // Flush the gallery cache.
    Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id );

    // Done
    wp_send_json_success();
    die;

}

add_action( 'wp_ajax_envira_gallery_refresh', 'envira_gallery_ajax_refresh' );
/**
 * Refreshes the DOM view for a gallery.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_refresh() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-refresh', 'nonce' );

    // Prepare variables.
    $post_id = absint( $_POST['post_id'] );
    $gallery = '';

    // Grab all gallery data.
    $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );

    // If there are no gallery items, don't do anything.
    if ( empty( $gallery_data ) || empty( $gallery_data['gallery'] ) ) {
        echo json_encode( array( 'error' => true ) );
        die;
    }

    // Loop through the data and build out the gallery view.
    foreach ( (array) $gallery_data['gallery'] as $id => $data ) {
        $gallery .= Envira_Gallery_Metaboxes::get_instance()->get_gallery_item( $id, $data, $post_id );
    }

    echo json_encode( array( 'success' => $gallery ) );
    die;

}

add_action( 'wp_ajax_envira_gallery_load_gallery_data', 'envira_gallery_ajax_load_gallery_data' );
/**
 * Retrieves and return gallery data for the specified ID.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_load_gallery_data() {

    // Prepare variables and grab the gallery data.
    $gallery_id   = absint( $_POST['post_id'] );
    $gallery_data = get_post_meta( $gallery_id, '_eg_gallery_data', true );

    // Send back the gallery data.
    echo json_encode( $gallery_data );
    die;

}

add_action( 'wp_ajax_envira_gallery_install_addon', 'envira_gallery_ajax_install_addon' );
/**
 * Installs an Envira addon.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_install_addon() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-install', 'nonce' );

    // Install the addon.
    if ( isset( $_POST['plugin'] ) ) {
        $download_url = $_POST['plugin'];
        global $hook_suffix;

        // Set the current screen to avoid undefined notices.
        set_current_screen();

        // Prepare variables.
        $method = '';
        $url    = add_query_arg(
            array(
                'page' => 'envira-gallery-settings'
            ),
            admin_url( 'admin.php' )
        );
        $url = esc_url( $url );

        // Start output bufferring to catch the filesystem form if credentials are needed.
        ob_start();
        if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, null ) ) ) {
            $form = ob_get_clean();
            echo json_encode( array( 'form' => $form ) );
            die;
        }

        // If we are not authenticated, make it happen now.
        if ( ! WP_Filesystem( $creds ) ) {
            ob_start();
            request_filesystem_credentials( $url, $method, true, false, null );
            $form = ob_get_clean();
            echo json_encode( array( 'form' => $form ) );
            die;
        }

        // We do not need any extra credentials if we have gotten this far, so let's install the plugin.
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once plugin_dir_path( Envira_Gallery::get_instance()->file ) . 'includes/admin/skin.php';

        // Create the plugin upgrader with our custom skin.
        $installer = new Plugin_Upgrader( $skin = new Envira_Gallery_Skin() );
        $installer->install( $download_url );

        // Flush the cache and return the newly installed plugin basename.
        wp_cache_flush();
        if ( $installer->plugin_info() ) {
            $plugin_basename = $installer->plugin_info();
            echo json_encode( array( 'plugin' => $plugin_basename ) );
            die;
        }
    }

    // Send back a response.
    echo json_encode( true );
    die;

}

add_action( 'wp_ajax_envira_gallery_activate_addon', 'envira_gallery_ajax_activate_addon' );
/**
 * Activates an Envira addon.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_activate_addon() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-activate', 'nonce' );

    // Activate the addon.
    if ( isset( $_POST['plugin'] ) ) {
        $activate = activate_plugin( $_POST['plugin'] );

        if ( is_wp_error( $activate ) ) {
            echo json_encode( array( 'error' => $activate->get_error_message() ) );
            die;
        }
    }

    echo json_encode( true );
    die;

}

add_action( 'wp_ajax_envira_gallery_deactivate_addon', 'envira_gallery_ajax_deactivate_addon' );
/**
 * Deactivates an Envira addon.
 *
 * @since 1.0.0
 */
function envira_gallery_ajax_deactivate_addon() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-deactivate', 'nonce' );

    // Deactivate the addon.
    if ( isset( $_POST['plugin'] ) ) {
        $deactivate = deactivate_plugins( $_POST['plugin'] );
    }

    echo json_encode( true );
    die;

}

/**
 * Helper function to prepare the metadata for an image in a gallery.
 *
 * @since 1.0.0
 *
 * @param array $gallery_data   Array of data for the gallery.
 * @param int   $id             The attachment ID to prepare data for.
 * @param array $image          Attachment image. Populated if inserting from the Media Library
 * @return array $gallery_data Amended gallery data with updated image metadata.
 */
function envira_gallery_ajax_prepare_gallery_data( $gallery_data, $id, $image = false ) {

    // Get attachment
    $attachment = get_post( $id );

    // Depending on whether we're inserting from the Media Library or not, prepare the image array
    if ( ! $image ) {
        $url        = wp_get_attachment_image_src( $id, 'full' );
        $alt_text   = get_post_meta( $id, '_wp_attachment_image_alt', true );
        $new_image = array(
            'status'  => 'active',
            'src'     => isset( $url[0] ) ? esc_url( $url[0] ) : '',
            'title'   => get_the_title( $id ),
            'link'    => ( isset( $url[0] ) ? esc_url( $url[0] ) : '' ),
            'alt'     => ! empty( $alt_text ) ? $alt_text : '',
            'caption' => ! empty( $attachment->post_excerpt ) ? $attachment->post_excerpt : '',
            'thumb'   => ''
        );
    } else {
        $new_image = array(
            'status'  => 'active',
            'src'     => ( isset( $image['src'] ) ? $image['src'] : $image['url'] ),
            'title'   => $image['title'],
            'link'    => $image['link'],
            'alt'     => $image['alt'],
            'caption' => $image['caption'],
            'thumb'   => '',
        );
    }

    // Allow Addons to possibly add metadata now
    $image = apply_filters( 'envira_gallery_ajax_prepare_gallery_data_item', $new_image, $image, $id, $gallery_data );

    // If gallery data is not an array (i.e. we have no images), just add the image to the array
    if ( ! isset( $gallery_data['gallery'] ) || ! is_array( $gallery_data['gallery'] ) ) {
        $gallery_data['gallery'] = array();
        $gallery_data['gallery'][ $id ] = $image;
    } else {
		
		// Add image, this will default to the end of the array
        $gallery_data['gallery'][ $id ] = $image;
       
    }

    // Filter and return
    $gallery_data = apply_filters( 'envira_gallery_ajax_item_data', $gallery_data, $attachment, $id, $image );

    return $gallery_data;

}

/**
 * Called whenever a notice is dismissed in Envira Gallery or its Addons.
 *
 * Updates a key's value in the options table to mark the notice as dismissed,
 * preventing it from displaying again
 *
 * @since 1.3.5
 */
function envira_gallery_ajax_dismiss_notice() {

    // Run a security check first.
    check_admin_referer( 'envira-gallery-dismiss-notice', 'nonce' );

    // Deactivate the notice
    if ( isset( $_POST['notice'] ) ) {
        // Init the notice class and mark notice as deactivated
        $notice = Envira_Gallery_Notice_Admin::get_instance();
        $notice->dismiss( $_POST['notice'] );

        // Return true
        echo json_encode( true );
        die;
    }

    // If here, an error occured
    echo json_encode( false );
    die;

}
add_action( 'wp_ajax_envira_gallery_ajax_dismiss_notice', 'envira_gallery_ajax_dismiss_notice' );

/**
 * Returns the media link (direct image URL) for the given attachment ID
 *
 * @since 1.4.1.4
 */
add_action( 'wp_ajax_envira_gallery_get_attachment_links', 'envira_gallery_get_attachment_links' );
function envira_gallery_get_attachment_links() {

    // Check nonce
    check_ajax_referer( 'envira-gallery-save-meta', 'nonce' );

    // Get required inputs
    $attachment_id = absint( $_POST['attachment_id'] );

    // Return the attachment's links
    wp_send_json_success( array(
        'media_link'      => wp_get_attachment_url( $attachment_id ),
        'attachment_page' => get_attachment_link( $attachment_id ),
    ) );

}

/**
 * Returns Galleries, with an optional search term
 *
 * @since 1.5.0
 */
add_action( 'wp_ajax_envira_gallery_editor_get_galleries', 'envira_gallery_editor_get_galleries' );
function envira_gallery_editor_get_galleries() {

    // Check nonce
    check_admin_referer( 'envira-gallery-editor-get-galleries', 'nonce' );

    // Get POSTed fields
    $search         = (bool) $_POST['search'];
    $search_terms   = sanitize_text_field( $_POST['search_terms'] );
    $prepend_ids    = stripslashes_deep( $_POST['prepend_ids'] );

    // Get galleries
    $instance = Envira_Gallery_Lite::get_instance();
    $galleries = $instance->get_galleries( false, true, ( $search ? $search_terms : '' ) );

    // Build array of just the data we need.
    foreach ( ( array ) $galleries as $gallery ) {
        // Get the thumbnail of the first image
        if ( isset( $gallery['gallery'] ) && ! empty( $gallery['gallery'] ) ) {
            // Get the first image
            reset( $gallery['gallery'] );
            $key = key( $gallery['gallery'] );
            $thumbnail = wp_get_attachment_image_src( $key, 'thumbnail' );
        }

        if ( ! empty( $gallery['config']['title'] ) ) {
            $gallery_title = $gallery['config']['title'];
        } else {
            $gallery_title = false;
        }

        // Check to make sure variables are there
        $gallery_id = false;
        $gallery_config_slug = false;

        if ( isset( $gallery['id'] ) ) {
            $gallery_id = $gallery['id'];
        }
        if ( isset( $gallery['config']['slug'] ) ) {
            $gallery_config_slug = $gallery['config']['slug'];
        }

        // Add gallery to results
        $results[] = array(
            'id'        => $gallery_id,
            'slug'      => $gallery_config_slug,
            'title'     => $gallery_title,
            'thumbnail' => ( ( isset( $thumbnail ) && is_array( $thumbnail ) ) ? $thumbnail[0] : '' ),
            'action'    => 'gallery', // Tells the editor modal whether this is a Gallery or Album for the shortcode output
        );
    }

    // If any prepended Gallery IDs were specified, get them now
    // These will typically be a Defaults Gallery, which wouldn't be included in the above get_galleries() call
    if ( is_array( $prepend_ids ) && count( $prepend_ids ) > 0 ) {
        $prepend_results = array();

        // Get each Gallery
        foreach ( $prepend_ids as $gallery_id ) {
            // Get gallery
            $gallery = get_post_meta( $gallery_id, '_eg_gallery_data', true );

            // Get gallery first image
            if ( isset( $gallery['gallery'] ) && ! empty( $gallery['gallery'] ) ) {
                // Get the first image
                reset( $gallery['gallery'] );
                $key = key( $gallery['gallery'] );
                $thumbnail = wp_get_attachment_image_src( $key, 'thumbnail' );
            }

            // Add gallery to results
            $prepend_results[] = array(
                'id'        => $gallery['id'],
                'slug'      => $gallery['config']['slug'],
                'title'     => $gallery['config']['title'],
                'thumbnail' => ( ( isset( $thumbnail ) && is_array( $thumbnail ) ) ? $thumbnail[0] : '' ),
                'action'    => 'gallery', // Tells the editor modal whether this is a Gallery or Album for the shortcode output
            );
        }

        // Add to results
        if ( is_array( $prepend_results ) && count( $prepend_results ) > 0 ) {
            $results = array_merge( $prepend_results, $results );
        }
    }

    // Return galleries
    wp_send_json_success( $results );

}

/**
 * Moves media (images) from one Gallery to another
 *
 * @since 1.5.0.3
 */
add_action( 'wp_ajax_envira_gallery_move_media', 'envira_gallery_move_media' );
function envira_gallery_move_media() {

    // Check nonce
    check_admin_referer( 'envira-gallery-move-media', 'nonce' );

    // Get POSTed fields
    $from_gallery_id    = absint( $_POST['from_gallery_id'] );
    $to_gallery_id      = absint( $_POST['to_gallery_id'] );
    $image_ids          = $_POST['image_ids'];

    if ( ! $from_gallery_id ) {
        wp_send_json_error( __( 'The From Gallery ID has not been specified.', 'envira-gallery' ) );
    }
    if ( ! $to_gallery_id ) {
        wp_send_json_error( __( 'The From Gallery ID has not been specified.', 'envira-gallery' ) );
    }
    if ( count( $image_ids ) == 0 ) {
        wp_send_json_error( __( 'No images were selected to be moved between Galleries.', 'envira-gallery' ) );
    }

    // Get from and to Galleries
    $from_gallery   = Envira_Gallery::get_instance()->get_gallery( $from_gallery_id );
    $to_gallery     = Envira_Gallery::get_instance()->get_gallery( $to_gallery_id );

    // Iterate through each image ID, adding the image to $to_gallery, then removing from $from_gallery
    foreach ( $image_ids as $image_id ) {
        // Check the image exists in $from_gallery
        // If not, skip this image
        if ( ! isset( $from_gallery['gallery'][ $image_id ] ) ) {
            continue;
        }

        // Copy the image to $to_gallery
        $to_gallery['gallery'][ $image_id ] = $from_gallery['gallery'][ $image_id ];

        // Remove the image from $from_gallery
        unset( $from_gallery['gallery'][ $image_id ] );
    }

    // Save both Galleries
    update_post_meta( $from_gallery_id, '_eg_gallery_data', $from_gallery );
    update_post_meta( $to_gallery_id, '_eg_gallery_data', $to_gallery );

    // Return success
    wp_send_json_success();

}
