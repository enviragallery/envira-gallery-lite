<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Common {

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

    }

    /**
     * Helper method for retrieving columns.
     *
     * @since 1.0.0
     *
     * @return array Array of column data.
     */
    public function get_columns() {

        $columns = array(
            array(
                'value' => '0',
                'name'  => __( 'Automatic', 'envira-gallery' )
            ),
            array(
                'value' => '1',
                'name'  => __( 'One Column (1)', 'envira-gallery' )
            ),
            array(
                'value' => '2',
                'name'  => __( 'Two Columns (2)', 'envira-gallery' )
            ),
            array(
                'value' => '3',
                'name'  => __( 'Three Columns (3)', 'envira-gallery' )
            ),
            array(
                'value' => '4',
                'name'  => __( 'Four Columns (4)', 'envira-gallery' )
            ),
            array(
                'value' => '5',
                'name'  => __( 'Five Columns (5)', 'envira-gallery' )
            ),
            array(
                'value' => '6',
                'name'  => __( 'Six Columns (6)', 'envira-gallery' )
            )
        );

        return apply_filters( 'envira_gallery_columns', $columns );

    }

    /**
     * Helper method for retrieving gallery themes.
     *
     * @since 1.0.0
     *
     * @return array Array of gallery theme data.
     */
    public function get_gallery_themes() {

        $themes = array(
            array(
                'value' => 'base',
                'name'  => __( 'Base', 'envira-gallery' ),
                'file'  => $this->base->file
            )
        );

        return apply_filters( 'envira_gallery_gallery_themes', $themes );

    }

    /**
     * Helper method for retrieving justified gallery themes.
     *
     * @since 1.1.1
     *
     * @return array Array of gallery theme data.
     */
    public function get_justified_gallery_themes() {

        $themes = array(
            array(
                'value' => 'normal',
                'name'  => __( 'Normal', 'envira-gallery' ),
                'file'  => $this->base->file
            )
        );

        return apply_filters( 'envira_gallery_justified_gallery_themes', $themes );

    }

    /**
     * Helper method for retrieving display description options.
     *
     * @since 1.3.7.3
     *
     * @return array Array of description placement options.
     */
    public function get_display_description_options() {

        $descriptions = array(
            array(
                'name'  => __( 'Do not display', 'envira-gallery' ),
                'value' => 0,
            ),
            array(
                'name'  => __( 'Display above galleries', 'envira-gallery' ),
                'value' => 'above',
            ),
            array(
                'name'  => __( 'Display below galleries', 'envira-gallery' ),
                'value' => 'below',
            ),
        );

        return apply_filters( 'envira_gallery_display_description_options', $descriptions );

    }

    /**
     * Helper method for retrieving display sorting options.
     *
     * @since 1.3.8
     *
     * @return array Array of sorting options
     */
    public function get_sorting_options() {

        $options = array(
            array(
                'name'  => __( 'No Sorting', 'envira-gallery' ),
                'value' => 0,
            ),
            array(
                'name'  => __( 'Random', 'envira-gallery' ),
                'value' => 1, // Deliberate, as we map the 'random' config key which was a true/false
            ),
            array(
                'name'  => __( 'Published Date', 'envira-gallery' ),
                'value' => 'date',
            ),
            array(
                'name'  => __( 'Filename', 'envira-gallery' ),
                'value' => 'src',
            ),
            array(
                'name'  => __( 'Title', 'envira-gallery' ),
                'value' => 'title',
            ),
            array(
                'name'  => __( 'Caption', 'envira-gallery' ),
                'value' => 'caption',
            ),
            array(
                'name'  => __( 'Alt', 'envira-gallery' ),
                'value' => 'alt',
            ),
            array(
                'name'  => __( 'URL', 'envira-gallery' ),
                'value' => 'link',
            ),
        );

        return apply_filters( 'envira_gallery_sorting_options', $options );

    }

    /**
     * Helper method for retrieving sorting directions
     *
     * @since 1.3.8
     *
     * @return array Array of sorting directions
     */
    public function get_sorting_directions() {

        $directions = array(
            array(
                'name'  => __( 'Ascending (A-Z)', 'envira-gallery' ),
                'value' => 'ASC',
            ),
            array(
                'name'  => __( 'Descending (Z-A)', 'envira-gallery' ),
                'value' => 'DESC',
            ),
        );

        return apply_filters( 'envira_gallery_sorting_directions', $directions );

    }

    /**
     * Helper method for retrieving lightbox themes.
     *
     * @since 1.0.0
     *
     * @return array Array of lightbox theme data.
     */
    public function get_lightbox_themes() {

        $themes = array(
//            array(
//                'value' => 'base_dark',
//                'name'  => __( 'Base (Dark)', 'envira-gallery' ),
//                'file'  => $this->base->file
//            ),
            array(
                'value' => 'base',
                'name'  => __( 'Legacy', 'envira-gallery' ),
                'file'  => $this->base->file
            )
        );

        return apply_filters( 'envira_gallery_lightbox_themes', $themes );

    }

    /**
     * Helper method for retrieving image sizes.
     *
     * @since 1.3.6
     *
     * @global array $_wp_additional_image_sizes Array of registered image sizes.
     *
     * @param   bool    $wordpress_only     WordPress Only (excludes the default and envira_gallery_random options)
     * @return  array                       Array of image size data.
     */
    public function get_image_sizes( $wordpress_only = false ) {

        if ( ! $wordpress_only ) {
            $sizes = array(
                array(
                    'value'  => 'default',
                    'name'   => __( 'Default', 'envira-gallery' ),
                )
            );
        }

        global $_wp_additional_image_sizes;
        $wp_sizes = get_intermediate_image_sizes();
        foreach ( (array) $wp_sizes as $size ) {
            if ( isset( $_wp_additional_image_sizes[$size] ) ) {
                $width  = absint( $_wp_additional_image_sizes[$size]['width'] );
                $height = absint( $_wp_additional_image_sizes[$size]['height'] );
            } else {
                $width  = absint( get_option( $size . '_size_w' ) );
                $height = absint( get_option( $size . '_size_h' ) );
            }

            if ( ! $width && ! $height ) {
                $sizes[] = array(
                    'value'  => $size,
                    'name'   => ucwords( str_replace( array( '-', '_' ), ' ', $size ) ),
                );
            } else {
                $sizes[] = array(
                    'value'  => $size,
                    'name'   => ucwords( str_replace( array( '-', '_' ), ' ', $size ) ) . ' (' . $width . ' &#215; ' . $height . ')',
                    'width'  => $width,
                    'height' => $height,
                );
            }
        }

        // Add Random option
        if ( ! $wordpress_only ) {
            $sizes[] = array(
                'value'  => 'envira_gallery_random',
                'name'   => __( 'Random', 'envira-gallery' ),
            );
        }

        return apply_filters( 'envira_gallery_image_sizes', $sizes );

    }

    /**
     * Helper method for retrieving title displays.
     *
     * @since 1.0.0
     *
     * @return array Array of title display data.
     */
    public function get_title_displays() {

        $displays = array(
            array(
                'value' => 'float',
                'name'  => __( 'Float', 'envira-gallery' )
            ),
            array(
                'value' => 'float_wrap',
                'name'  => __( 'Float (Wrapped)', 'envira-gallery' )
            ),
            array(
                'value' => 'inside',
                'name'  => __( 'Inside', 'envira-gallery' )
            ),
            array(
                'value' => 'outside',
                'name'  => __( 'Outside', 'envira-gallery' )
            ),
            array(
                'value' => 'over',
                'name'  => __( 'Over', 'envira-gallery' )
            )
        );

        return apply_filters( 'envira_gallery_title_displays', $displays );

    }

    /**
     * Helper method for retrieving arrow positions.
     *
     * @since 1.3.3.7
     *
     * @return array Array of arrow position display data.
     */
    public function get_arrows_positions() {

        $displays = array(
            array(
                'value' => 'inside',
                'name'  => __( 'Inside', 'envira-gallery' )
            ),
            array(
                'value' => 'outside',
                'name'  => __( 'Outside', 'envira-gallery' )
            ),
        );

        return apply_filters( 'envira_gallery_arrows_positions', $displays );

    }

    /**
     * Helper method for retrieving lightbox transition effects.
     *
     * @since 1.0.0
     *
     * @return array Array of transition effect data.
     */
    public function get_transition_effects() {

        $effects = array(
            array(
                'value' => 'none',
                'name'  => __( 'No Effect', 'envira-gallery' )
            ),
            array(
                'value' => 'fade',
                'name'  => __( 'Fade', 'envira-gallery' )
            ),
            array(
                'value' => 'elastic',
                'name'  => __( 'Elastic', 'envira-gallery' )
            ),
        );

        return apply_filters( 'envira_gallery_transition_effects', $effects );

    }

    /**
     * Helper method for retrieving an array of lightbox transition effect values
     *
     * @since 1.4.1.2
     *
     * @return array Transition values
     */
    public function get_transition_effects_values() {

        // Get effects
        $effects = $this->get_transition_effects();

        // Build array
        $effect_values = array();
        foreach ( $effects as $effect ) {
            $effect_values[] = $effect['value'];
        }

        // Return
        return apply_filters( 'envira_gallery_transition_effects_values', $effect_values, $effects );

    }

    /**
     * Helper method for retrieving lightbox easing transition effects.
     *
     * These are deliberately seperate from get_transition_effects() above, so that
     * we can determine whether an effect on a Gallery or Album is an easing one or not.
     *
     * In turn, that determines the setting keys used for Fancybox (e.g. openEffect vs openEasing)
     *
     * @since 1.4.1.2
     *
     * @return array Array of easing transition effects
     */
    public function get_easing_transition_effects() {

        $effects = array(
            array(
                'value' => 'Swing',
                'name'  => __( 'Swing', 'envira-gallery' )
            ),
            // array(
            //     'value' => 'Quad',
            //     'name'  => __( 'Quad', 'envira-gallery' )
            // ),
            // array(
            //     'value' => 'Cubic',
            //     'name'  => __( 'Cubic', 'envira-gallery' )
            // ),
            // array(
            //     'value' => 'Quart',
            //     'name'  => __( 'Quart', 'envira-gallery' )
            // ),
            // array(
            //     'value' => 'Quint',
            //     'name'  => __( 'Quint', 'envira-gallery' )
            // ),
            // array(
            //     'value' => 'Sine',
            //     'name'  => __( 'Sine', 'envira-gallery' )
            // ),
            // array(
            //     'value' => 'Expo',
            //     'name'  => __( 'Expo', 'envira-gallery' )
            // ),
            // array(
            //     'value' => 'Circ',
            //     'name'  => __( 'Circ', 'envira-gallery' )
            // ),
            // array(
            //     'value' => 'Back',
            //     'name'  => __( 'Back', 'envira-gallery' )
            // ),
            // array(
            //     'value' => 'Bounce',
            //     'name'  => __( 'Bounce', 'envira-gallery' )
            // ),
        );

        return apply_filters( 'envira_gallery_easing_transition_effects', $effects );

    }

    /**
     * Helper method for retrieving toolbar positions.
     *
     * @since 1.0.0
     *
     * @return array Array of toolbar position data.
     */
    public function get_toolbar_positions() {

        $positions = array(
            array(
                'value' => 'top',
                'name'  => __( 'Top', 'envira-gallery' )
            ),
            array(
                'value' => 'bottom',
                'name'  => __( 'Bottom', 'envira-gallery' )
            )
        );

        return apply_filters( 'envira_gallery_toolbar_positions', $positions );

    }

    /**
     * Helper method for retrieving thumbnail positions.
     *
     * @since 1.0.0
     *
     * @return array Array of thumbnail position data.
     */
    public function get_thumbnail_positions() {

        $positions = array(
            array(
                'value' => 'top',
                'name'  => __( 'Top', 'envira-gallery' )
            ),
            array(
                'value' => 'bottom',
                'name'  => __( 'Bottom', 'envira-gallery' )
            )
        );

        return apply_filters( 'envira_gallery_thumbnail_positions', $positions );

    }

    /**
     * Helper method for setting default config values.
     *
     * @since 1.0.0
     *
     * @global int $id      The current post ID.
     * @global object $post The current post object.
     * @param string $key   The default config key to retrieve.
     * @return string       Key value on success, false on failure.
     */
    public function get_config_default( $key ) {

        global $id, $post;

        // Get the current post ID. If ajax, grab it from the $_POST variable.
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['post_id'] ) ) {
            $post_id = absint( $_POST['post_id'] );
        } else {
            $post_id = isset( $post->ID ) ? $post->ID : (int) $id;
        }

		// Prepare default values.
        $defaults = $this->get_config_defaults( $post_id );

        // Return the key specified.
        return isset( $defaults[$key] ) ? $defaults[$key] : false;

    }

    /**
     * Retrieves the slider config defaults.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @return array       Array of slider config defaults.
     */
    public function get_config_defaults( $post_id ) {

        // Prepare default values.
        $defaults = array(
            // Images Tab
            'type'                => 'default',

            // Config Tab
            'columns'             => '0',
            'justified_row_height' => 150, // automatic/justified layout
			'justified_gallery_theme' => 'normal',
			'justified_margins'       => 1,
            'gallery_theme'       => 'base',
            'display_description' => 0,
            'description'		  => '',
            'gutter'              => 10,
            'margin'              => 10,
            'random'              => 0,
            'sorting_direction'   => 'ASC',
            'image_size'          => 'default', // Default = uses the below crop_width and crop_height
            'image_sizes_random'  => array(),
            'crop_width'          => 640,
            'crop_height'         => 480,
            'crop'                => 0,
            'dimensions'		  => 0,
            'isotope'			  => 1,
            'css_animations'	  => 1,
            'css_opacity'         => 100,
            'lazy_loading'        => 1, // lazy loading 'ON' for new galleries
            'lazy_loading_delay'  => 500,

            // Lightbox
            'lightbox_enabled'    => 1,
            'lightbox_theme'      => 'base',
            'lightbox_image_size' => 'default',
            'title_display'       => 'float',
            'arrows'              => 1,
            'arrows_position'     => 'inside',
            'keyboard'            => 1,
            'mousewheel'          => 1,
            'toolbar'             => 1,
            'toolbar_title'		  => 0,
            'toolbar_position'    => 'top',
            'aspect'              => 1,
            'loop'                => 1,
            'lightbox_open_close_effect' => 'fade',
            'effect'              => 'fade',
            'html5'               => 0,

            // Thumbnails
            'thumbnails'          => 1,
            'thumbnails_width'    => 75,
            'thumbnails_height'   => 50,
            'thumbnails_position' => 'bottom',

            // Mobile
      //     'mobile_columns'      => 1,
            'mobile'              => 1,
            'mobile_width'        => 320,
            'mobile_height'       => 240,
            'mobile_lightbox'     => 1,
            'mobile_touchwipe'    => 1,
            'mobile_touchwipe_close' => 0,
            'mobile_arrows'       => 1,
            'mobile_toolbar'      => 1,
            'mobile_thumbnails'   => 1,
            'mobile_thumbnails_width'    => 75,
            'mobile_thumbnails_height'   => 50,

            // Misc
            'title'               => '',
            'slug'                => '',
            'classes'             => array(),
            'rtl'                 => 0,
        );

        // For Lite, change some defaults
		$defaults['toolbar']            = 0;
		$defaults['thumbnails']         = 0;
		$defaults['mobile_touchwipe']   = 0;
		$defaults['mobile_toolbar']     = 0;
		$defaults['mobile_thumbnails']  = 0;

        // Allow devs to filter the defaults.
        $defaults = apply_filters( 'envira_gallery_defaults', $defaults, $post_id );

        return $defaults;

    }

    /**
     * Returns an array of supported file type groups and file types
     *
     * @since 1.3.3.2
     *
     * @return array Supported File Types
     */
    public function get_supported_filetypes() {

        $supported_file_types = array(
            array(
                'title'     => __( 'Image Files', 'envira-gallery' ),
                'extensions'=> 'jpg,jpeg,jpe,gif,png,bmp,tif,tiff,JPG,JPEG,JPE,GIF,PNG,BMP,TIF,TIFF',
            ),
        );

        // Allow Developers and Addons to filter the supported file types
        $supported_file_types = apply_filters( 'envira_gallery_supported_file_types', $supported_file_types );

        return $supported_file_types;
    }

    /**
     * Returns an array of support file types in full MIME format
     *
     * @since 1.4.0.2
     *
     * @return array Supported File Types
     */
    public function get_supported_filetypes_mimes() {

        $supported_file_types = array(
            'image/jpg',
            'image/jpeg',
            'image/jpe',
            'image/gif',
            'image/png',
            'image/bmp',
            'image/tif',
            'image/tiff',
        );

        // Allow Developers and Addons to filter the supported file types
        $supported_file_types = apply_filters( 'envira_gallery_supported_file_types_mimes', $supported_file_types );

        return $supported_file_types;

    }

    /**
     * Returns an array of positions for new images to be added to in an existing Gallery
     *
     * @since 1.3.3.6
     *
     * @return array
     */
    public function get_media_positions() {

        $positions = array(
            array(
                'value' => 'before',
                'name'  => __( 'Before Existing Images', 'envira-gallery' )
            ),
            array(
                'value' => 'after',
                'name'  => __( 'After Existing Images', 'envira-gallery' )
            ),
        );

        return apply_filters( 'envira_gallery_media_positions', $positions );

    }

    /**
     * Returns an array of media deletion options, when an Envira Gallery is deleted
     *
     * @since 1.3.5.1
     *
     * @return array
     */
    public function get_media_delete_options() {

        $options = array(
            array(
                'value' => '',
                'name'  => __( 'No', 'envira-gallery' )
            ),
            array(
                'value' => '1',
                'name'  => __( 'Yes', 'envira-gallery' )
            ),
        );

        return apply_filters( 'envira_gallery_media_delete_options', $options );

    }

    /**
     * API method for cropping images.
     *
     * @since 1.0.0
     *
     * @global object $wpdb The $wpdb database object.
     *
     * @param string $url      The URL of the image to resize.
     * @param int $width       The width for cropping the image.
     * @param int $height      The height for cropping the image.
     * @param bool $crop       Whether or not to crop the image (default yes).
     * @param string $align    The crop position alignment.
     * @param bool $retina     Whether or not to make a retina copy of image.
     * @param array $data      Array of gallery data (optional).
     * @param bool $force_overwrite      Forces an overwrite even if the thumbnail already exists (useful for applying watermarks)
     * @return WP_Error|string Return WP_Error on error, URL of resized image on success.
     */
    public function resize_image( $url, $width = null, $height = null, $crop = true, $align = 'c', $quality = 100, $retina = false, $data = array(), $force_overwrite = false ) {

        global $wpdb;

        // Get common vars.
        $args   = array( $url, $width, $height, $crop, $align, $quality, $retina, $data );

        // Filter args
        $args = apply_filters( 'envira_gallery_resize_image_args', $args );

        // Don't resize images that don't belong to this site's URL
        // Strip ?lang=fr from blog's URL - WPML adds this on
        // and means our next statement fails
        if ( is_multisite() ) {
            $site_url = preg_replace( '/\?.*/', '', network_site_url() );
        } else {
            $site_url = preg_replace( '/\?.*/', '', get_bloginfo( 'url' ) );
        }

        // WPML check - if there is a /fr or any domain in the url, then remove that from the $site_url
        if ( defined('ICL_LANGUAGE_CODE') ) {
            if ( strpos( $site_url, '/'.ICL_LANGUAGE_CODE ) !== false ) {
                $site_url = str_replace( '/'.ICL_LANGUAGE_CODE, '', $site_url );
            }
        }

        if ( strpos( $url, $site_url ) === false ) {
            return $url;
        }

        // Get image info
        $common = $this->get_image_info( $args );

        // Unpack variables if an array, otherwise return WP_Error.
        if ( is_wp_error( $common ) ) {
            return $common;
        } else {
            extract( $common );
        }

        // If the destination width/height values are the same as the original, don't do anything.
        if ( !$force_overwrite && $orig_width === $dest_width && $orig_height === $dest_height ) {
            return $url;
        }



        // If the file doesn't exist yet, we need to create it.
        if ( ! file_exists( $dest_file_name ) || ( file_exists( $dest_file_name ) && $force_overwrite ) ) {
            // We only want to resize Media Library images, so we can be sure they get deleted correctly when appropriate.
            $get_attachment = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid='%s'", $url ) );

            // Load the WordPress image editor.
            $editor = wp_get_image_editor( $file_path );

            // If an editor cannot be found, the user needs to have GD or Imagick installed.
            if ( is_wp_error( $editor ) ) {
                return new WP_Error( 'envira-gallery-error-no-editor', __( 'No image editor could be selected. Please verify with your webhost that you have either the GD or Imagick image library compiled with your PHP install on your server.', 'envira-gallery' ) );
            }

            // Set the image editor quality.
            $editor->set_quality( $quality );

            // If cropping, process cropping.
            if ( $crop ) {
                $src_x = $src_y = 0;
                $src_w = $orig_width;
                $src_h = $orig_height;

                $cmp_x = $orig_width / $dest_width;
                $cmp_y = $orig_height / $dest_height;

                // Calculate x or y coordinate and width or height of source
                if ( $cmp_x > $cmp_y ) {
                    $src_w = round( $orig_width / $cmp_x * $cmp_y );
                    $src_x = round( ($orig_width - ($orig_width / $cmp_x * $cmp_y)) / 2 );
                } else if ( $cmp_y > $cmp_x ) {
                    $src_h = round( $orig_height / $cmp_y * $cmp_x );
                    $src_y = round( ($orig_height - ($orig_height / $cmp_y * $cmp_x)) / 2 );
                }

                // Positional cropping.
                if ( $align && $align != 'c' ) {
                    if ( strpos( $align, 't' ) !== false || strpos( $align, 'tr' ) !== false || strpos( $align, 'tl' ) !== false ) {
                        $src_y = 0;
                    }

                    if ( strpos( $align, 'b' ) !== false || strpos( $align, 'br' ) !== false || strpos( $align, 'bl' ) !== false ) {
                        $src_y = $orig_height - $src_h;
                    }

                    if ( strpos( $align, 'l' ) !== false ) {
                        $src_x = 0;
                    }

                    if ( strpos ( $align, 'r' ) !== false ) {
                        $src_x = $orig_width - $src_w;
                    }
                }

                // Crop the image.
                $editor->crop( $src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height );
            } else {
                // Just resize the image.
                $editor->resize( $dest_width, $dest_height );
            }

            // Save the image.
            $saved = $editor->save( $dest_file_name );

            // Print possible out of memory errors.
            if ( is_wp_error( $saved ) ) {
                @unlink( $dest_file_name );
                return $saved;
            }

            // Add the resized dimensions and alignment to original image metadata, so the images
            // can be deleted when the original image is delete from the Media Library.
            if ( $get_attachment ) {
                $metadata = wp_get_attachment_metadata( $get_attachment[0]->ID );

                if ( isset( $metadata['image_meta'] ) ) {
                    $md = $saved['width'] . 'x' . $saved['height'];

                    if ( $crop ) {
                        $md .= $align ? "_${align}" : "_c";
                    }

                    $metadata['image_meta']['resized_images'][] = $md;
                    wp_update_attachment_metadata( $get_attachment[0]->ID, $metadata );
                }
            }

            // Set the resized image URL.
            $resized_url = str_replace( basename( $url ), basename( $saved['path'] ), $url );
        } else {
            // Set the resized image URL.
            $resized_url = str_replace( basename( $url ), basename( $dest_file_name ), $url );
        }

        // Return the resized image URL.
        return apply_filters( 'envira_gallery_resize_image_resized_url', $resized_url );

    }

    /**
     * Helper method to return common information about an image.
     *
     * @since 1.0.0
     *
     * @param array $args      List of resizing args to expand for gathering info.
     * @return WP_Error|string Return WP_Error on error, array of data on success.
     */
    public function get_image_info( $args ) {

        // Unpack arguments.
        list( $url, $width, $height, $crop, $align, $quality, $retina, $data ) = $args;

        // Return an error if no URL is present.
        if ( empty( $url ) ) {
            return new WP_Error( 'envira-gallery-error-no-url', __( 'No image URL specified for cropping.', 'envira-gallery' ) );
        }

        // Get the image file path.
        $urlinfo       = parse_url( $url );
        $wp_upload_dir = wp_upload_dir();

        // Interpret the file path of the image.
        if ( preg_match( '/\/[0-9]{4}\/[0-9]{2}\/.+$/', $urlinfo['path'], $matches ) ) {
            $file_path = $wp_upload_dir['basedir'] . $matches[0];
        } else {
            $pathinfo    = parse_url( $url );
            // $uploads_dir = is_multisite() ? '/files/' : '/wp-content/';
            $uploads_dir = is_multisite() ? '/files/' : '/' . str_replace( ABSPATH, '', WP_CONTENT_DIR ) . '/';
            $file_path   = ABSPATH . str_replace( dirname( $_SERVER['SCRIPT_NAME'] ) . '/', '', strstr( $pathinfo['path'], $uploads_dir ) );
            $file_path   = preg_replace( '/(\/\/)/', '/', $file_path );
        }

        // Attempt to stream and import the image if it does not exist based on URL provided.
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'envira-gallery-error-no-file', __( 'No file could be found for the image URL specified.', 'envira-gallery' ) );
        }

        // Attempt to stream and import the image if it does not exist based on URL provided.
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'envira-gallery-error-no-file', __( 'No file could be found for the image URL specified.', 'envira-gallery' ) );
        }

        // Get original image size.
        $size = @getimagesize( $file_path );

        // If no size data obtained, return an error.
        if ( ! $size ) {
            return new WP_Error( 'envira-gallery-error-no-size', __( 'The dimensions of the original image could not be retrieved for cropping.', 'envira-gallery' ) );
        }

        // Set original width and height.
        list( $orig_width, $orig_height, $orig_type ) = $size;

        // Generate width or height if not provided.
        if ( $width && ! $height ) {
            $height = floor( $orig_height * ($width / $orig_width) );
        } else if ( $height && ! $width ) {
            $width = floor( $orig_width * ($height / $orig_height) );
        } else if ( ! $width && ! $height ) {
            return new WP_Error( 'envira-gallery-error-no-size', __( 'The dimensions of the original image could not be retrieved for cropping.', 'envira-gallery' ) );
        }

        // Allow for different retina image sizes.
        $retina = $retina ? ( $retina === true ? 2 : $retina ) : 1;

        // Destination width and height variables
        $dest_width  = $width * $retina;
        $dest_height = $height * $retina;

        // Some additional info about the image.
        $info = pathinfo( $file_path );
        $dir  = $info['dirname'];
        $ext  = $info['extension'];
        $name = wp_basename( $file_path, ".$ext" );

        // Suffix applied to filename
        $suffix = "${dest_width}x${dest_height}";

        // Set alignment information on the file.
        if ( $crop ) {
            $suffix .= ( $align ) ? "_${align}" : "_c";
        }

        // Get the destination file name
        $dest_file_name = "${dir}/${name}-${suffix}.${ext}";

        // Return the info.
        $info = array(
            'dir'            => $dir,
            'name'           => $name,
            'ext'            => $ext,
            'suffix'         => $suffix,
            'orig_width'     => $orig_width,
            'orig_height'    => $orig_height,
            'orig_type'      => $orig_type,
            'dest_width'     => $dest_width,
            'dest_height'    => $dest_height,
            'file_path'      => $file_path,
            'dest_file_name' => $dest_file_name,
        );

        return $info;

    }

    /**
     * Helper method to flush gallery caches once a gallery is updated.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @param string $slug The unique gallery slug.
     */
    public function flush_gallery_caches( $post_id, $slug = '' ) {

        // Delete known gallery caches.
        delete_transient( '_eg_cache_' . $post_id );
        delete_transient( '_eg_cache_all' );

        // Possibly delete slug gallery cache if available.
        if ( ! empty( $slug ) ) {
            delete_transient( '_eg_cache_' . $slug );
        }

        // Run a hook for Addons to access.
        do_action( 'envira_gallery_flush_caches', $post_id, $slug );

    }

    /**
     * Helper method to return the max execution time for scripts.
     *
     * @since 1.0.0
     *
     * @param int $time The max execution time available for PHP scripts.
     */
    public function get_max_execution_time() {

        $time = ini_get( 'max_execution_time' );
        return ! $time || empty( $time ) ? (int) 0 : $time;

    }

    /**
     * Helper method to return the transient expiration time
     *
     * @since 1.3.6.4
     *
     * @return int Expiration Time (in seconds)
     */
    public function get_transient_expiration_time( $plugin = 'envira-gallery' ) {

        // Define the default
        $default = DAY_IN_SECONDS;

        // Allow devs to filter this depending on the plugin
        $default = apply_filters( 'envira_gallery_get_transient_expiration_time', $default, $plugin );

        // Return
        return $default;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Common ) ) {
            self::$instance = new Envira_Gallery_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_gallery_common = Envira_Gallery_Common::get_instance();