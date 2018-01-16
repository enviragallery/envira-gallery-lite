<?php
/**
 * Common admin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Common_Admin {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Holds the metabox class object.
     *
     * @since 1.3.1
     *
     * @var object
     */
    public $metabox;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery_Lite::get_instance();

        // Handle any necessary DB upgrades.
        add_action( 'admin_init', array( $this, 'db_upgrade' ) );

        // Load admin assets.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        // Delete any gallery association on attachment deletion. Also delete any extra cropped images.
        add_action( 'delete_attachment', array( $this, 'delete_gallery_association' ) );
        add_action( 'delete_attachment', array( $this, 'delete_cropped_image' ) );

        // Ensure gallery display is correct when trashing/untrashing galleries.
        add_action( 'wp_trash_post', array( $this, 'trash_gallery' ) );
        add_action( 'untrash_post', array( $this, 'untrash_gallery' ) );

        // Delete attachments, if setting enabled, when a gallery is permanently deleted
        add_action( 'before_delete_post', array( $this, 'delete_gallery' ) );

    }

    /**
     * Handles any necessary DB upgrades for Envira.
     *
     * @since 1.0.0
     */
    public function db_upgrade() {

        // Upgrade to allow captions (v1.1.6).
        $captions = get_option( 'envira_gallery_116' );
        if ( ! $captions ) {
            $galleries = Envira_Gallery_Lite::get_instance()->_get_galleries();
            if ( $galleries ) {
                foreach ( $galleries as $gallery ) {
                    foreach ( (array) $gallery['gallery'] as $id => $item ) {
                        $gallery['gallery'][$id]['caption'] = ! empty( $item['title'] ) ? $item['title'] : '';
                        update_post_meta( $gallery['id'], '_eg_gallery_data', $gallery );
                        Envira_Gallery_Common::get_instance()->flush_gallery_caches( $gallery['id'], $gallery['config']['slug'] );
                    }
                }
            }

            update_option( 'envira_gallery_116', true );
        }

        // 1.2.1: Convert all non-Envira Post Type galleries into Envira CPT galleries.
        $cptGalleries = get_option( 'envira_gallery_121' );
        if ( ! $cptGalleries ) {
            // Get Post Types, excluding our own
            // We don't use post_status => 'any', as this doesn't include CPTs where exclude_from_search = true.
            $postTypes = get_post_types( array(
                'public' => true,
            ) );
            $excludedPostTypes = array( 'envira', 'envira_album', 'attachment' );
            foreach ( $postTypes as $key=>$postType ) {
                if ( in_array( $postType, $excludedPostTypes ) ) {
                    unset( $postTypes[ $key ] );
                }
            }

            // Get all Posts that have _eg_gallery_data set
            $inPostGalleries = new WP_Query( array(
                'post_type'     => $postTypes,
                'post_status'   => 'any',
                'posts_per_page'=> -1,
                'meta_query'    => array(
                    array(
                        'key'       => '_eg_gallery_data',
                        'compare'   => 'EXISTS',
                    ),
                )
            ) );

            // Check if any Posts with galleries exist
            if ( count( $inPostGalleries->posts ) > 0 ) {
                $migrated_galleries = 0;

                // Iterate through Posts with Galleries
                foreach ( $inPostGalleries->posts as $post ) {
                    // Check if this is an Envira or Envira Album CPT
                    // If so, skip it
                    if ( $post->post_type == 'envira' || $post->post_type == 'envira_album' ) {
                        continue;
                    }

                    // Get metadata
                    $data = get_post_meta( $post->ID, '_eg_gallery_data', true);
                    $in = get_post_meta( $post->ID, '_eg_in_gallery', true);

                    // Check if there is at least one image in the gallery
                    // Some Posts save Envira config data but don't have images - we don't want to migrate those,
                    // as we would end up with blank Envira CPT galleries
                    if ( ! isset( $data['gallery'] ) || ! is_array( $data['gallery']) ) {
                        continue;
                    }

                    // If here, we need to create a new Envira CPT
                    $cpt_args = array(
                        'post_title'    => ( !empty( $data['config']['title'] ) ? $data['config']['title'] : $post->post_title ),
                        'post_status'   => $post->post_status,
                        'post_type'     => 'envira',
                        'post_author'   => $post->post_author,
                    );
                    if ( ! empty( $data['config']['slug'] ) ) {
                        $cpt_args['post_name'] = $data['config']['slug'];
                    }
                    $enviraGalleryID = wp_insert_post( $cpt_args );

                    // Check gallery creation was successful
                    if ( is_wp_error( $enviraGalleryID ) ) {
                        // @TODO how to handle errors?
                        continue;
                    }

                    // Get Envira Gallery Post
                    $enviraPost = get_post( $enviraGalleryID );

                    // Map the title and slug of the post object to the custom fields if no value exists yet.
                    $data['config']['title'] = trim( strip_tags( $enviraPost->post_title ) );
                    $data['config']['slug']  = sanitize_text_field( $enviraPost->post_name );

                    // Store post metadata
                    update_post_meta( $enviraGalleryID, '_eg_gallery_data', $data );
                    update_post_meta( $enviraGalleryID, '_eg_in_gallery', $in );
                    update_post_meta( $enviraGalleryID, '_eg_gallery_old', $post->ID );
                    if ( ! empty( $data['config']['slug'] ) ) {
                        update_post_meta( $enviraGalleryID, '_eg_gallery_old_slug', $data['config']['slug'] );
                    }

                    // Remove post metadata from the original Post
                    delete_post_meta( $post->ID, '_eg_gallery_data' );
                    delete_post_meta( $post->ID, '_eg_in_gallery' );

                    // Search for the envira shortcode in the Post content, and change its ID to the new Envira Gallery ID
                    if ( has_shortcode ( $post->post_content, 'envira-gallery' ) ) {
                        $pattern = get_shortcode_regex();
                        if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) ) {
                            foreach ( $matches[2] as $key => $shortcode ) {
                                if ( $shortcode == 'envira-gallery' ) {
                                    // Found an envira-gallery shortcode
                                    // Change the ID
                                    $originalShortcode = $matches[0][ $key ];
                                    $replacementShortcode = str_replace( 'id="' . $post->ID . '"', 'id="' . $enviraGalleryID . '"', $originalShortcode );
                                    $post->post_content = str_replace( $originalShortcode, $replacementShortcode, $post->post_content );
                                    wp_update_post( $post );
                                }
                            }
                        }
                    }

                    // Store a relationship between the gallery and this Post
                    update_post_meta( $post->ID, '_eg_gallery_id', $enviraGalleryID );

                    // Increment the counter
                    $migrated_galleries++;
                }

                // Display a one time admin notice so the user knows their in-page galleries were migrated.
                if ( $migrated_galleries > 0 ) {
                    add_action( 'admin_notices', array( $this, 'notice_galleries_migrated' ) );
                }
            }

            // Force the tags addon to convert any tags to the new CPT system for any galleries that have been converted to Envira post type.
            delete_option( 'envira_tags_taxonomy_migrated' );

            // Mark upgrade as complete
            update_option( 'envira_gallery_121', true );
        }
    }
    /**
     * Displays a notice on screen when a user upgrades from Lite to Pro or Lite to Lite 1.5.x,
     * telling them that their in-page galleries have been migrated.
     *
     * @since 1.5.0
     */
    public function notice_galleries_migrated() {

        ?>
        <div class="notice updated">
            <p><?php _e( '<strong>Envira Gallery:</strong> Your existing in-page Galleries can now be found by clicking on Envira Gallery in the WordPress Admin menu.', 'envira-gallery' ); ?></p>
        </div>
        <?php

    }

    /**
     * Loads styles for all Envira-based Administration Screens.
     *
     * @since 1.3.1
     *
     * @return null Return early if not on the proper screen.
     */
    public function admin_styles() {

        // Get current screen.
        $screen = get_current_screen();

        // Bail if we're not on the Envira Post Type screen.
        if ( 'envira' !== $screen->post_type && 'envira_album' !== $screen->post_type ) {
            return;
        }

        // Load necessary admin styles.
        wp_register_style( $this->base->plugin_slug . '-admin-style', plugins_url( 'assets/css/admin.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-admin-style' );

        // Fire a hook to load in custom admin styles.
        do_action( 'envira_gallery_admin_styles' );

    }

    /**
     * Loads scripts for all Envira-based Administration Screens.
     *
     * @since 1.3.5
     *
     * @return null Return early if not on the proper screen.
     */
    public function admin_scripts() {

        // Get current screen.
        $screen = get_current_screen();

        // Bail if we're not on the Envira Post Type screen.
        if ( 'envira' !== $screen->post_type && 'envira_album' !== $screen->post_type ) {
            return;
        }

        // Load necessary admin scripts
        wp_register_script( $this->base->plugin_slug . '-admin-script', plugins_url( 'assets/js/min/admin-min.js', $this->base->file ), array( 'jquery' ), $this->base->version );
        wp_enqueue_script( $this->base->plugin_slug . '-admin-script' );
        wp_localize_script(
            $this->base->plugin_slug . '-admin-script',
            'envira_gallery_admin',
            array(
                'ajax'                  => admin_url( 'admin-ajax.php' ),
                'dismiss_notice_nonce'  => wp_create_nonce( 'envira-gallery-dismiss-notice' ),
            )
        );

        // Fire a hook to load in custom admin scripts.
        do_action( 'envira_gallery_admin_scripts' );

    }

    /**
     * Deletes the Envira gallery association for the image being deleted.
     *
     * @since 1.0.0
     *
     * @param int $attach_id The attachment ID being deleted.
     */
    public function delete_gallery_association( $attach_id ) {

        $has_gallery = get_post_meta( $attach_id, '_eg_has_gallery', true );

        // Only proceed if the image is attached to any Envira galleries.
        if ( ! empty( $has_gallery ) ) {
            foreach ( (array) $has_gallery as $post_id ) {
                // Remove the in_gallery association.
                $in_gallery = get_post_meta( $post_id, '_eg_in_gallery', true );
                if ( ! empty( $in_gallery ) ) {
                    if ( ( $key = array_search( $attach_id, (array) $in_gallery ) ) !== false ) {
                        unset( $in_gallery[$key] );
                    }
                }

                update_post_meta( $post_id, '_eg_in_gallery', $in_gallery );

                // Remove the image from the gallery altogether.
                $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
                if ( ! empty( $gallery_data['gallery'] ) ) {
                    unset( $gallery_data['gallery'][$attach_id] );
                }

                // Update the post meta for the gallery.
                update_post_meta( $post_id, '_eg_gallery_data', $gallery_data );

                // Flush necessary gallery caches.
                Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id, ( ! empty( $gallery_data['config']['slug'] ) ? $gallery_data['config']['slug'] : '' ) );
            }
        }

    }

    /**
     * Removes any extra cropped images when an attachment is deleted.
     *
     * @since 1.0.0
     *
     * @param int $post_id The post ID.
     * @return null        Return early if the appropriate metadata cannot be retrieved.
     */
    public function delete_cropped_image( $post_id ) {

        // Get attachment image metadata.
        $metadata = wp_get_attachment_metadata( $post_id );

        // Return if no metadata is found.
        if ( ! $metadata ) {
            return;
        }

        // Return if we don't have the proper metadata.
        if ( ! isset( $metadata['file'] ) || ! isset( $metadata['image_meta']['resized_images'] ) ) {
            return;
        }

        // Grab the necessary info to removed the cropped images.
        $wp_upload_dir  = wp_upload_dir();
        $pathinfo       = pathinfo( $metadata['file'] );
        $resized_images = $metadata['image_meta']['resized_images'];

        // Loop through and deleted and resized/cropped images.
        foreach ( $resized_images as $dims ) {
            // Get the resized images filename and delete the image.
            $file = $wp_upload_dir['basedir'] . '/' . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $dims . '.' . $pathinfo['extension'];

            // Delete the resized image.
            if ( file_exists( $file ) ) {
                @unlink( $file );
            }
        }

    }

    /**
     * Trash a gallery when the gallery post type is trashed.
     *
     * @since 1.0.0
     *
     * @param $id   The post ID being trashed.
     * @return null Return early if no gallery is found.
     */
    public function trash_gallery( $id ) {

        $gallery = get_post( $id );

        // Flush necessary gallery caches to ensure trashed galleries are not showing.
        Envira_Gallery_Common::get_instance()->flush_gallery_caches( $id );

        // Return early if not an Envira gallery.
        if ( 'envira' !== $gallery->post_type ) {
            return;
        }

        // Check some gallery data exists
        $gallery_data = get_post_meta( $id, '_eg_gallery_data', true );
        if ( empty( $gallery_data ) ) {
            return;
        }

        // Set the gallery status to inactive.
        $gallery_data['status'] = 'inactive';
        update_post_meta( $id, '_eg_gallery_data', $gallery_data );

        // Allow other addons to run routines when a Gallery is trashed
        do_action( 'envira_gallery_trash', $id, $gallery_data );

    }

    /**
     * Untrash a gallery when the gallery post type is untrashed.
     *
     * @since 1.0.0
     *
     * @param $id   The post ID being untrashed.
     * @return null Return early if no gallery is found.
     */
    public function untrash_gallery( $id ) {

        $gallery = get_post( $id );

        // Flush necessary gallery caches to ensure untrashed galleries are showing.
        Envira_Gallery_Common::get_instance()->flush_gallery_caches( $id );

        // Return early if not an Envira gallery.
        if ( 'envira' !== $gallery->post_type ) {
            return;
        }

        // Set the gallery status to inactive.
        $gallery_data = get_post_meta( $id, '_eg_gallery_data', true );
        if ( empty( $gallery_data ) ) {
            return;
        }

        if ( isset( $gallery_data['status'] ) ) {
            unset( $gallery_data['status'] );
        }

        update_post_meta( $id, '_eg_gallery_data', $gallery_data );

        // Allow other addons to run routines when a Gallery is untrashed
        do_action( 'envira_gallery_untrash', $id, $gallery_data );

    }

    /**
    * Fired when a gallery is about to be permanently deleted from Trash
    *
    * Checks if the media_delete setting is enabled, and if so safely deletes
    * media that isn't being used elsewhere on the site
    *
    * @since 1.3.6.1
    *
    * @param int $post_id Post ID
    * @return null
    */
    public function delete_gallery( $id ) {

        // Check if the media_delete setting is enabled
        $media_delete = false;
        if ( $media_delete != '1' ) {
            return;
        }

        // Get post
        $gallery = get_post( $id );

        // Flush necessary gallery caches to ensure untrashed galleries are showing.
        Envira_Gallery_Common::get_instance()->flush_gallery_caches( $id );

        // Return early if not an Envira gallery.
        if ( 'envira' !== $gallery->post_type ) {
            return;
        }

        // Get attached media
        $media = get_attached_media( 'image', $id );
        if ( ! is_array( $media ) ) {
            return;
        }

        // Iterate through media, deleting
        foreach ( $media as $image ) {
            wp_delete_attachment( $image->ID );
        }

    }

    /**
     * Called whenever an upgrade button / link is displayed in Lite, this function will
     * check if there's a shareasale ID specified.
     *
     * There are three ways to specify an ID, ordered by highest to lowest priority
     * - add_filter( 'envira_gallery_shareasale_id', function() { return 1234; } );
     * - define( 'ENVIRA_GALLERY_SHAREASALE_ID', 1234 );
     * - get_option( 'envira_gallery_shareasale_id' ); (with the option being in the wp_options table)
     *
     * If an ID is present, returns the ShareASale link with the affiliate ID, and tells
     * ShareASale to then redirect to enviragallery.com/lite
     *
     * If no ID is present, just returns the enviragallery.com/lite URL with UTM tracking.
     *
     * @since 1.5.0
     */
    public function get_upgrade_link() {

        // Check if there's a constant.
        $shareasale_id = '';
        if ( defined( 'ENVIRA_GALLERY_SHAREASALE_ID' ) ) {
            $shareasale_id = ENVIRA_GALLERY_SHAREASALE_ID;
        }

        // If there's no constant, check if there's an option.
        if ( empty( $shareasale_id ) ) {
            $shareasale_id = get_option( 'envira_gallery_shareasale_id', '' );
        }

        // Whether we have an ID or not, filter the ID.
        $shareasale_id = apply_filters( 'envira_gallery_shareasale_id', $shareasale_id );

        // If at this point we still don't have an ID, we really don't have one!
        // Just return the standard upgrade URL.
        if ( empty( $shareasale_id ) ) {
            return 'http://enviragallery.com/lite/?utm_source=liteplugin&utm_medium=link&utm_campaign=WordPress';
        }

        // If here, we have a ShareASale ID
        // Return ShareASale URL with redirect.
        return 'http://www.shareasale.com/r.cfm?u=' . $shareasale_id . '&b=566240&m=51693&afftrack=&urllink=enviragallery%2Ecom%2Flite%2F';

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Common_Admin object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Common_Admin ) ) {
            self::$instance = new Envira_Gallery_Common_Admin();
        }

        return self::$instance;

    }

}

// Load the common admin class.
$envira_gallery_common_admin = Envira_Gallery_Common_Admin::get_instance();