<?php
/**
 * Posttype class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Posttype {

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

        // Build the labels for the post type.
        $labels =  array(
            'name'               => __( 'Envira Galleries', 'envira-gallery' ),
            'singular_name'      => __( 'Envira Gallery', 'envira-gallery' ),
            'add_new'            => __( 'Add New', 'envira-gallery' ),
            'add_new_item'       => __( 'Add New Envira Gallery', 'envira-gallery' ),
            'edit_item'          => __( 'Edit Envira Gallery', 'envira-gallery' ),
            'new_item'           => __( 'New Envira Gallery', 'envira-gallery' ),
            'view_item'          => __( 'View Envira Gallery', 'envira-gallery' ),
            'search_items'       => __( 'Search Envira Galleries', 'envira-gallery' ),
            'not_found'          => __( 'No Envira galleries found.', 'envira-gallery' ),
            'not_found_in_trash' => __( 'No Envira galleries found in trash.', 'envira-gallery' ),
            'parent_item_colon'  => '',
            'menu_name'          => __( 'Envira Gallery', 'envira-gallery' ),
        );
        $labels = apply_filters( 'envira_gallery_post_type_labels', $labels );

        // Build out the post type arguments.
        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'exclude_from_search' => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_admin_bar'   => true,
            'rewrite'             => false,
			'query_var'           => false,
			'show_in_rest'        => true,
			'rest_base'           => 'envira-gallery',
            'menu_position'       => apply_filters( 'envira_gallery_post_type_menu_position', 247 ),
            'menu_icon'           => plugins_url( 'assets/css/images/menu-icon@2x.png', $this->base->file ),
            'supports'            => array( 'title' ),
        );

        // Filter arguments.
        $args = apply_filters( 'envira_gallery_post_type_args', $args );

        // Register the post type with WordPress.
        register_post_type( 'envira', $args );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Posttype object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Posttype ) ) {
            self::$instance = new Envira_Gallery_Posttype();
        }

        return self::$instance;

    }

}

// Load the posttype class.
$envira_gallery_posttype = Envira_Gallery_Posttype::get_instance();