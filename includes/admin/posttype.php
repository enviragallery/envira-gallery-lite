<?php
/**
 * Posttype admin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Posttype_Admin {

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
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery_Lite::get_instance();
        $this->metabox = Envira_Gallery_Metaboxes::get_instance();

        // Update post type messages.
        add_filter( 'post_updated_messages', array( $this, 'messages' ) );

        // Force the menu icon to be scaled to proper size (for Retina displays).
        add_action( 'admin_head', array( $this, 'menu_icon' ) );

        // Add the Universal Header.
        add_action( 'in_admin_header', array( $this, 'admin_header' ), 100 );

    }

    /**
     * Outputs the Envira Gallery Header.
     *
     * @since 1.5.0
     */
    public function admin_header() {
        
        // Get the current screen, and check whether we're viewing the Envira or Envira Album Post Types.
        $screen = get_current_screen(); 
        if ( 'envira' !== $screen->post_type && 'envira_album' !== $screen->post_type ) {
            return;
        }

        // If here, we're on an Envira Gallery or Album screen, so output the header.
        $this->base->load_admin_partial( 'header', array(
            'logo' => plugins_url( 'assets/images/envira-logo.png', $this->base->file ),
        ) );

    }

    /**
     * Contextualizes the post updated messages.
     *
     * @since 1.0.0
     *
     * @global object $post    The current post object.
     * @param array $messages  Array of default post updated messages.
     * @return array $messages Amended array of post updated messages.
     */
    public function messages( $messages ) {

        global $post;

        // Contextualize the messages.
        $envira_messages = array(
            0  => '',
            1  => __( 'Envira gallery updated.', 'envira-gallery' ),
            2  => __( 'Envira gallery custom field updated.', 'envira-gallery' ),
            3  => __( 'Envira gallery custom field deleted.', 'envira-gallery' ),
            4  => __( 'Envira gallery updated.', 'envira-gallery' ),
            5  => isset( $_GET['revision'] ) ? sprintf( __( 'Envira gallery restored to revision from %s.', 'envira-gallery' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => __( 'Envira gallery published.', 'envira-gallery' ),
            7  => __( 'Envira gallery saved.', 'envira-gallery' ),
            8  => __( 'Envira gallery submitted.', 'envira-gallery' ),
            9  => sprintf( __( 'Envira gallery scheduled for: <strong>%1$s</strong>.', 'envira-gallery' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
            10 => __( 'Envira gallery draft updated.', 'envira-gallery' ),
        );
        $messages['envira'] = apply_filters( 'envira_gallery_messages', $envira_messages );

        return $messages;

    }

    /**
     * Forces the Envira menu icon width/height for Retina devices.
     *
     * @since 1.0.0
     */
    public function menu_icon() {

        ?>
        <style type="text/css">#menu-posts-envira .wp-menu-image img { width: 16px; height: 16px; }</style>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Posttype_Admin object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Posttype_Admin ) ) {
            self::$instance = new Envira_Gallery_Posttype_Admin();
        }

        return self::$instance;

    }



}

// Load the posttype admin class.
$envira_gallery_posttype_admin = Envira_Gallery_Posttype_Admin::get_instance();