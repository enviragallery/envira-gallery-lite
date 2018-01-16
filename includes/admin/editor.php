<?php
/**
 * Editor class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Editor {

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
     * Flag to determine if media modal is loaded.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $loaded = false;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery_Lite::get_instance();

        // Add a custom media button to the editor.
        add_filter( 'media_buttons_context', array( $this, 'media_button' ) );
        add_action( 'save_post', array( $this, 'save_gallery_ids' ), 9999 );
        add_action( 'before_delete_post', array( $this, 'remove_gallery_ids' ) );

    }

    /**
     * Adds a custom gallery insert button beside the media uploader button.
     *
     * @since 1.0.0
     *
     * @param string $buttons  The media buttons context HTML.
     * @return string $buttons Amended media buttons context HTML.
     */
    public function media_button( $buttons ) {

        // Enqueue styles.
        wp_register_style( $this->base->plugin_slug . '-admin-style', plugins_url( 'assets/css/admin.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-admin-style' );

        wp_register_style( $this->base->plugin_slug . '-editor-style', plugins_url( 'assets/css/editor.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-editor-style' );

        // Enqueue the gallery / album selection script
        wp_enqueue_script( $this->base->plugin_slug . '-gallery-select-script', plugins_url( 'assets/js/min/gallery-select-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_localize_script( $this->base->plugin_slug . '-gallery-select-script', 'envira_gallery_select', array(
            'get_galleries_nonce' => wp_create_nonce( 'envira-gallery-editor-get-galleries' ),
            'modal_title'           => __( 'Insert', 'envira-gallery' ),
            'insert_button_label'   => __( 'Insert', 'envira-gallery' ),
        ) );

        // Enqueue the script that will trigger the editor button.
        wp_enqueue_script( $this->base->plugin_slug . '-editor-script', plugins_url( 'assets/js/min/editor-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_localize_script( $this->base->plugin_slug . '-gallery-select-script', 'envira_gallery_editor', array(
            'modal_title'           => __( 'Insert', 'envira-gallery' ),
            'insert_button_label'   => __( 'Insert', 'envira-gallery' ),
        ) );

        // Create the media button.
        $button = '<a id="envira-media-modal-button" href="#" class="button envira-gallery-choose-gallery" data-action="gallery" title="' . esc_attr__( 'Add Gallery', 'envira-gallery' ) . '" >
            <span class="envira-media-icon"></span> ' .
             __( 'Add Gallery', 'envira-gallery' ) . 
        '</a>';

        // Filter the button.
        $button = apply_filters( 'envira_gallery_media_button', $button, $buttons );

        // Append the button.
        return $buttons . $button;

    }

    /**
     * Checks for the existience of any Envira Gallery shortcodes in the Post's content,
     * storing this Post's ID in each Envira Gallery.
     *
     * This allows Envira's WP_List_Table to tell the user which Post(s) the Gallery is
     * included in.
     *
     * @since 1.3.3.6
     *
     * @param int $post_id Post ID
     */
    public function save_gallery_ids( $post_id ) {

        $this->update_gallery_post_ids( $post_id, false );

    }

    /**
     * Removes the given Post ID from all Envira Galleries that contain the Post ID
     *
     * @since 1.3.3.6
     *
     * @param int $post_id Post ID
     */
    public function remove_gallery_ids( $post_id ) {

        $this->update_gallery_post_ids( $post_id, true );

    }

    /**
     * Checks for Envira Gallery shortcodes in the given content.
     *
     * If found, adds or removes those shortcode IDs to the given Post ID
     *
     * @since 1.3.3.6
     *
     * @param int $post_id Post ID
     * @param bool $remove Remove Post ID from Gallery Meta (false)
     * @return bool
     */
    private function update_gallery_post_ids( $post_id, $remove = false ) {

        // Get post
        $post = get_post( $post_id );
        if ( ! $post ) {
            return;
        }

        // Don't do anything if we are saving a Gallery or Album
        if ( in_array( $post->post_type, array( 'envira', 'envira_album' ) ) ) {
            return;
        }

        // Don't do anything if this is a Post Revision
        if ( wp_is_post_revision( $post ) ) {
            return false;
        }

        // Check content for shortcodes
        if ( ! has_shortcode( $post->post_content, 'envira-gallery' ) ) {
            return false;
        }

        // Content has Envira shortcode(s)
        // Extract them to get Gallery IDs
        $pattern = '\[(\[?)(envira\-gallery)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
        if ( ! preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) ) {
            return false;
        }
        if ( ! is_array( $matches[3] ) ) {
            return false;
        }

        // Iterate through shortcode matches, extracting the gallery ID and storing it in the meta
        $gallery_ids = array();
        foreach ( $matches[3] as $shortcode ) {
            // Grab ID
            $gallery_ids[] = preg_replace( "/[^0-9]/", "", $shortcode ); 
        }

        // Check we found gallery IDs
        if ( ! $gallery_ids ) {
            return false;
        }

        // Iterate through each gallery
        foreach ( $gallery_ids as $gallery_id ) {
            // Get Post IDs this Gallery is included in
            $post_ids = get_post_meta( $gallery_id, '_eg_in_posts', true );
            if ( ! is_array( $post_ids ) ) {
                $post_ids = array();
            }

            
            if ( $remove ) {
                // Remove the Post ID
                if ( isset( $post_ids[ $post_id ] ) ) {
                    unset( $post_ids[ $post_id ] );
                }
            } else {
                // Add the Post ID
                $post_ids[ $post_id ] = $post_id;
            }

            // Save
            update_post_meta( $gallery_id, '_eg_in_posts', $post_ids );
        }

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Editor object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Editor ) ) {
            self::$instance = new Envira_Gallery_Editor();
        }

        return self::$instance;

    }

}

// Load the editor class.
$envira_gallery_editor = Envira_Gallery_Editor::get_instance();