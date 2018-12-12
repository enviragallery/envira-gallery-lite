<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Shortcode {

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
     * Holds the gallery data.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $data;

    /**
     * Holds gallery IDs for init firing checks.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $done = array();

    /**
     * Iterator for galleries on the page.
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $counter = 1;

    /**
     * Holds image URLs for indexing.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $index = array();

	public $gallery_data = array();
	public $common;
	public $is_mobile;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery_Lite::get_instance();
		$this->common = Envira_Gallery_Common::get_instance();
		$this->is_mobile = envira_mobile_detect()->isMobile();

        // Register main gallery style.
        wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/envira.css', $this->base->file ), array(), $this->base->version );
        wp_register_style( $this->base->plugin_slug . '-fancybox', plugins_url( 'assets/css/fancybox.css', $this->base->file ), array(), $this->base->version );
        wp_register_style( $this->base->plugin_slug . '-lazyload', plugins_url( 'assets/css/responsivelyLazy.css', $this->base->file ), array(), $this->base->version );

        // if ( $this->get_config( 'columns', $data ) == 0 ) :
        wp_register_style( $this->base->plugin_slug . '-jgallery', plugins_url( 'assets/css/justifiedGallery.css', $this->base->file ), array(), $this->base->version );

        // Register main gallery script.
        wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );

        // Load hooks and filters.
        add_shortcode( 'envira-gallery', array( $this, 'shortcode' ) );
        add_filter( 'widget_text', 'do_shortcode' );
        //add_filter( 'envira_gallery_output_image_attr', array( $this, 'add_image_srcset_attributes' ), 10, 5 );
        add_filter( 'style_loader_tag', array( $this, 'add_stylesheet_property_attribute' ) );

    }

    /**
     * Creates the shortcode for the plugin.
     *
     * @since 1.0.0
     *
     * @global object $post The current post object.
     *
     * @param array $atts Array of shortcode attributes.
     * @return string     The gallery output.
     */
    public function shortcode( $atts ) {

		if ( is_admin() ){
			return;
		}
        global $post;

        // If no attributes have been passed, the gallery should be pulled from the current post.
        $gallery_id = false;
        if ( empty( $atts ) ) {
            $gallery_id = $post->ID;
            $data       = is_preview() ? $this->base->_get_gallery( $gallery_id ) : $this->base->get_gallery( $gallery_id );
        } else if ( isset( $atts['id'] ) ) {
            $gallery_id = (int) $atts['id'];
            $data       = is_preview() ? $this->base->_get_gallery( $gallery_id ) : $this->base->get_gallery( $gallery_id );
        } else if ( isset( $atts['slug'] ) ) {
            $gallery_id = $atts['slug'];
            $data       = is_preview() ? $this->base->_get_gallery_by_slug( $gallery_id ) : $this->base->get_gallery_by_slug( $gallery_id );
        } else {
            // A custom attribute must have been passed. Allow it to be filtered to grab data from a custom source.
            $data = apply_filters( 'envira_gallery_custom_gallery_data', false, $atts, $post );
            $gallery_id = $data['config']['id']; // was $data['config']['id']
        }

        // Change the gallery order, if specified
        $data = $this->maybe_sort_gallery( $data, $gallery_id );

        // Limit the number of images returned, if specified
        // [envira-gallery id="123" limit="10"] would only display 10 images
        if ( isset( $atts['limit'] ) && is_numeric( $atts['limit'] ) ) {
            $images = $data['gallery'];
            $images = array_slice( $data['gallery'], 0, absint( $atts['limit'] ), true );
            $data['gallery'] = $images;
        }

        // Allow the data to be filtered before it is stored and used to create the gallery output.
        $data = apply_filters( 'envira_gallery_pre_data', $data, $gallery_id );

        // If there is no data to output or the gallery is inactive, do nothing.
        if ( ! $data || empty( $data['gallery'] ) || isset( $data['status'] ) && 'inactive' == $data['status'] && ! is_preview() ) {
            return;
        }

		$this->gallery_data = $data;

        // Get rid of any external plugins trying to jack up our stuff where a gallery is present.
        $this->plugin_humility();

        // Prepare variables.
        $this->data[ $data['id'] ]  = $this->gallery_data;
        $this->index[ $data['id'] ] = array();
        $gallery                  = '';
        $i                        = 1;

        // If this is a feed view, customize the output and return early.
        if ( is_feed() ) {
            return $this->do_feed_output( $data );
        }

        $lazy_loading_delay = isset( $this->gallery_data['config']['lazy_loading_delay'] ) ? intval($this->gallery_data['config']['lazy_loading_delay']) : 500;

        // Load scripts and styles.
        wp_enqueue_style( $this->base->plugin_slug . '-style' );
        wp_enqueue_style( $this->base->plugin_slug . '-lazyload' );
        wp_enqueue_style( $this->base->plugin_slug . '-fancybox' );
        wp_enqueue_style( $this->base->plugin_slug . '-jgallery' );
        wp_enqueue_script( $this->base->plugin_slug . '-script' );
        // If lazy load is active, load the lazy load script
        if ( $this->get_config( 'lazy_loading', $data ) == 1 ) {
            wp_localize_script( $this->base->plugin_slug . '-script', 'envira_lazy_load', 'true');
            wp_localize_script( $this->base->plugin_slug . '-script', 'envira_lazy_load_initial', 'false');
            wp_localize_script( $this->base->plugin_slug . '-script', 'envira_lazy_load_delay', (string) $lazy_loading_delay);
        }

        // Load custom gallery themes if necessary.
        if ( 'base' !== $this->get_config( 'gallery_theme', $this->gallery_data ) && $this->get_config( 'columns', $this->gallery_data ) > 0 ) {
            // if columns is zero, then it's automattic which means we do not load gallery themes because it will mess up the new javascript layout
            $this->load_gallery_theme( $this->get_config( 'gallery_theme', $this->gallery_data ) );
        }

        // Load custom lightbox themes if necessary.
        if ( 'base' !== $this->get_config( 'lightbox_theme', $this->gallery_data ) ) {
            $this->load_lightbox_theme( $this->get_config( 'lightbox_theme', $this->gallery_data ) );
        }

        // Load gallery init code in the footer.
        add_action( 'wp_footer', array( $this, 'gallery_init' ), 1000 );

        // Run a hook before the gallery output begins but after scripts and inits have been set.
        do_action( 'envira_gallery_before_output', $this->gallery_data );

        // Apply a filter before starting the gallery HTML.
        $gallery = apply_filters( 'envira_gallery_output_start', $gallery, $this->gallery_data );

        // Build out the gallery HTML.
        $gallery .= '<div id="envira-gallery-wrap-' . sanitize_html_class( $this->gallery_data['id'] ) . '" class="' . $this->get_gallery_classes( $this->gallery_data ) . '" itemscope itemtype="http://schema.org/ImageGallery">';
            $gallery  = apply_filters( 'envira_gallery_output_before_container', $gallery, $this->gallery_data );

            // Description
            if ( isset( $this->gallery_data['config']['description_position'] ) && $this->gallery_data['config']['description_position'] == 'above' ) {
                $gallery = $this->description( $gallery, $this->gallery_data );
            }


            $opacity_insert = false;
            if ( $this->get_config( 'columns', $this->gallery_data ) == 0 ) {
                $opacity_insert = ' style="opacity: 0.0" ';
            }

            // add justified CSS?
            $extra_css = 'envira-gallery-justified-public';
            $row_height = false;
            $justified_gallery_theme = false;
            if ( $this->get_config( 'columns', $this->gallery_data ) > 0 ) {
                $extra_css = false;
            } else {
                $row_height = $this->get_config( 'justified_row_height', $this->gallery_data );
                $justified_gallery_theme = $this->get_config( 'justified_gallery_theme', $this->gallery_data );
            }

            $gallery .= '<div' .  $opacity_insert . ' data-row-height="'.$row_height.'" data-gallery-theme="'.$justified_gallery_theme.'" id="envira-gallery-' . sanitize_html_class( $this->gallery_data['id'] ) . '" class="envira-gallery-public '.$extra_css.' envira-gallery-' . sanitize_html_class( $this->get_config( 'columns', $this->gallery_data ) ) . '-columns envira-clear' . ( $this->get_config( 'isotope', $this->gallery_data ) ? ' enviratope' : '' ) . ( $this->get_config( 'css_animations', $this->gallery_data ) ? ' envira-gallery-css-animations' : '' ) . '" data-envira-columns="' . $this->get_config( 'columns', $this->gallery_data ) . '">';

                // Start image loop
                foreach ( $data['gallery'] as $id => $item ) {

                    // Add the gallery item to the markup
                    $gallery = $this->generate_gallery_item_markup( $gallery, $this->gallery_data, $item, $id, $i );

                    // Increment the iterator.
                    $i++;

                }
                // End image loop

            $gallery .= '</div>';
            // Description
            if ( isset( $this->gallery_data['config']['description_position'] ) && $this->gallery_data['config']['description_position'] == 'below' ) {
                $gallery = $this->description( $gallery, $this->gallery_data );
            }

            $gallery  = apply_filters( 'envira_gallery_output_after_container', $gallery, $this->gallery_data );

        $gallery .= '</div>';
        $gallery  = apply_filters( 'envira_gallery_output_end', $gallery, $this->gallery_data );

        // Increment the counter.
        $this->counter++;

        // Remove any contextual filters so they don't affect other galleries on the page.
        if ( $this->get_config( 'mobile', $this->gallery_data ) ) {
            remove_filter( 'envira_gallery_output_image_attr', array( $this, 'mobile_image' ), 999, 4 );
        }

        // Add no JS fallback support.
        $no_js    = '<noscript>';
        $no_js   .= $this->get_indexable_images( $this->gallery_data['id'] );
        $no_js   .= '</noscript>';
        $gallery .= $no_js;

        // Return the gallery HTML.
        return apply_filters( 'envira_gallery_output', $gallery, $this->gallery_data );

    }

    /**
    * Outputs an individual gallery item in the grid
    *
    * @since 1.4.2.1
    *
    * @param    string  $gallery    Gallery HTML
    * @param    array   $data       Gallery Config
    * @param    array   $item       Gallery Item (Image)
    * @param    int     $id         Gallery Image ID
    * @param    int     $i          Index
    * @return   string              Gallery HTML
    */
    public function generate_gallery_item_markup( $gallery, $data, $item, $id, $i ) {

        // Get some config values that we'll reuse for each image
        $padding             = absint( round( $this->get_config( 'gutter', $data ) / 2 ) );
        $html5_attribute     = ( ( $this->get_config( 'html5', $data ) == '1' ) ? 'data-envirabox-group' : 'rel' );
        $thumbnail_start_url = get_bloginfo( 'url' ) . '/' . get_bloginfo( 'url' );

        // Skip over images that are pending (ignore if in Preview mode).
        if ( isset( $item['status'] ) && 'pending' == $item['status'] && ! is_preview() ) {
            return $gallery;
        }

        $item     = apply_filters( 'envira_gallery_output_item_data', $item, $id, $data, $i );

        // Get image and image retina URLs
        // These calls will generate thumbnails as necessary for us.
        $imagesrc         = $this->get_image_src( $id, $item, $data );
        $image_src_retina = $this->get_image_src( $id, $item, $data, false, true );
        $placeholder      = wp_get_attachment_image_src( $id, 'medium' ); // $placeholder is null because $id is 0 for instagram?

        // Filter output before starting this gallery item.
        $gallery  = apply_filters( 'envira_gallery_output_before_item', $gallery, $id, $item, $data, $i );

        // Maybe change the item's link if it is an image and we have an image size defined for the Lightbox
        $item = $this->maybe_change_link( $id, $item, $data );

        // Non-ASCII filenames fail when FILTER_VALIDATE_URL is applied to them when saving a gallery to generate thumbs
        // This resulted in the blog URL being prepended to the URL, therefore breaking the thumbnail URL
        // This reverts that change for the few edge cases where this happened
        if ( strpos( $item['thumb'], $thumbnail_start_url ) !== false ) {
            $item['thumb'] = str_replace( $thumbnail_start_url, get_bloginfo( 'url' ) . '/', $item['thumb'] );
        }

        $output   = '<div id="envira-gallery-item-' . sanitize_html_class( $id ) . '" class="' . $this->get_gallery_item_classes( $item, $i, $data ) . '" style="padding-left: ' . $padding . 'px; padding-bottom: ' . $this->get_config( 'margin', $data ) . 'px; padding-right: ' . $padding . 'px;" ' . apply_filters( 'envira_gallery_output_item_attr', '', $id, $item, $data, $i ) . ' itemscope itemtype="http://schema.org/ImageObject">';

        $output .= '<div class="envira-gallery-item-inner">';
        $output  = apply_filters( 'envira_gallery_output_before_link', $output, $id, $item, $data, $i );

        // Top Left box

        $css_class = false; // no css classes yet
        $css_class = apply_filters( 'envira_gallery_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'top-left' );

        $output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-top-left">';
        $output  = apply_filters( 'envira_gallery_output_dynamic_position', $output, $id, $item, $data, $i, 'top-left' );
        $output .= '</div>';

        // Top Right box

        $css_class = false; // no css classes yet
        $css_class = apply_filters( 'envira_gallery_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'top-right' );

        $output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-top-right">';
        $output  = apply_filters( 'envira_gallery_output_dynamic_position', $output, $id, $item, $data, $i, 'top-right' );
        $output .= '</div>';

        // Bottom Left box

        $css_class = false; // no css classes yet
        $css_class = apply_filters( 'envira_gallery_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'bottom-left' );

        $output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-bottom-left">';
        $output  = apply_filters( 'envira_gallery_output_dynamic_position', $output, $id, $item, $data, $i, 'bottom-left' );
        $output .= '</div>';

        // Bottom Right box
        $css_class = false; // no css classes yet
        $css_class = apply_filters( 'envira_gallery_output_dynamic_position_css', $css_class, $output, $id, $item, $data, $i, 'bottom-right' );

        $output .= '<div class="envira-gallery-position-overlay ' . $css_class . ' envira-gallery-bottom-right">';
        $output  = apply_filters( 'envira_gallery_output_dynamic_position', $output, $id, $item, $data, $i, 'bottom-right' );
        $output .= '</div>';

        // Title
        $title = str_replace('<', '&lt;', $item['title'] ); // not sure why this was not encoded
        $title = strip_tags( htmlspecialchars( $title ) );

        // Caption
        // Lite doesn't support captions, so we fallback to the title.
        $caption = $title;

        // Determine if we create a link.
        // If the user has disabled lightbox, there should not be a link
        // "Turn off the lightbox then the image shouldn't be clicked"
        if (!$this->get_config( 'lightbox_enabled', $data ) ) {
            $create_link = false;
        } else if ( ! empty( $item['link'] ) ) {
            $create_link = true;
        } else {
            $create_link = false;
        }

        // Filter the ability to create a link
        $create_link = apply_filters( 'envira_gallery_create_link', $create_link, $data, $id, $item, $i, $this->is_mobile );

        if ( $create_link ) {
            $output .= '<a href="' . esc_url( $item['link'] ) . '" class="' . ( ( isset($item['link_new_window']) && $item['link_new_window'] == 1 ) ? '' : 'envira-gallery-' . sanitize_html_class( $data['id'] ) . ' ' ) . 'envira-gallery-link" ' . $html5_attribute . '="enviragallery' . sanitize_html_class( $data['id'] ) . '" title="' . $title . '" data-envira-caption="' . $caption . '" data-envira-retina="' . ( isset( $item['lightbox_retina_image'] ) ? $item['lightbox_retina_image'] : '' ) . '" data-thumbnail="' . esc_url( $item['thumb'] ) . '"' . ( ( isset($item['link_new_window']) && $item['link_new_window'] == 1 ) ? ' target="_blank"' : '' ) . ' ' . apply_filters( 'envira_gallery_output_link_attr', '', $id, $item, $data, $i ) . ' itemprop="contentUrl">';
        }

        $output  = apply_filters( 'envira_gallery_output_before_image', $output, $id, $item, $data, $i );

        $gallery_theme = $this->get_config( 'columns', $data ) == 0 ? ' envira-' . $this->get_config( 'justified_gallery_theme', $data ) : '';

        // Build the image and allow filtering
        // How we build the html depends on the lazy load script

        // Check if user has lazy loading on - if so, we add the css class

        $envira_lazy_load = $this->get_config( 'lazy_loading', $data ) == 1 ? 'envira-lazy' : '';

        // Determine/confirm the width/height of the image
        // $placeholder should hold it but not for instagram

        if ( $this->is_mobile == 'mobile' && !$this->get_config( 'mobile', $data ) ) { // if the user is viewing on mobile AND user unchecked 'Create Mobile Gallery Images?' in mobile tab
            $output_src = $item['src'];
        } else if ( $this->get_config( 'crop', $data ) ) { // the user has selected the image to be cropped
            $output_src = $imagesrc;
        } else if ( $this->get_config( 'image_size', $data ) && $imagesrc ) { // use the image being provided thanks to the user selecting a unique image size
            $output_src = $imagesrc;
        } else if ( !empty( $item['width'] ) ) {
            $output_src = $item['src'];
        } else if ( !empty( $placeholder[0] ) ) {
            $output_src = $placeholder[0];
        } else {
            $output_src = false;
        }

        if ( $this->get_config( 'crop', $data ) && $this->get_config( 'crop_width', $data ) && $this->get_config( 'image_size', $data ) != 'full' ) {
            $output_width = $this->get_config( 'crop_width', $data );
        } else if ( $this->get_config( 'columns', $data ) != 0 && $this->get_config( 'image_size', $data ) && $this->get_config( 'image_size', $data ) != 'full' && $this->get_config( 'crop_width', $data ) && $this->get_config( 'crop_height', $data ) ) {
            $output_width = $this->get_config( 'crop_width', $data );
        } else if ( !empty( $item['width'] ) ) {
            $output_width = $item['width'];
        } else if ( !empty( $placeholder[1] ) ) {
            $output_width = $placeholder[1];
        } else {
            $output_width = false;
        }

        if ( $this->get_config( 'crop', $data ) && $this->get_config( 'crop_width', $data ) && $this->get_config( 'image_size', $data ) != 'full' ) {
            $output_height = $this->get_config( 'crop_height', $data );
        } else if ( $this->get_config( 'columns', $data ) != 0 && $this->get_config( 'image_size', $data ) && $this->get_config( 'image_size', $data ) != 'full' && $this->get_config( 'crop_width', $data ) && $this->get_config( 'crop_height', $data ) ) {
            $output_height = $this->get_config( 'crop_height', $data );
        } else if ( !empty( $item['height'] ) ) {
            $output_height = $item['height'];
        } else if ( !empty( $placeholder[2] ) ) {
            $output_height = $placeholder[2];
        } else {
            $output_height = false;
        }

        if ( $this->get_config( 'columns', $data ) == 0 ) {

            // Automatic

            $output_item = '<img id="envira-gallery-image-' . sanitize_html_class( $id ) . '" class="envira-gallery-image envira-gallery-image-' . $i . $gallery_theme . ' '.$envira_lazy_load.'" data-envira-index="' . $i . '" src="' . esc_url( $output_src ) . '"' . ( $this->get_config( 'dimensions', $data ) ? ' width="' . $this->get_config( 'crop_width', $data ) . '" height="' . $this->get_config( 'crop_height', $data ) . '"' : '' ) . ' data-envira-src="' . esc_url( $output_src ) . '" data-envira-gallery-id="' . $data['id'] . '" data-envira-item-id="' . $id . '" data-envira-caption="' . strip_tags( htmlspecialchars( $item['title'] ) ) . '" alt="' . esc_attr( $item['alt'] ) . '" title="' . strip_tags( htmlspecialchars( $item['title'] ) ) . '" ' . apply_filters( 'envira_gallery_output_image_attr', '', $id, $item, $data, $i ) . ' itemprop="thumbnailUrl" data-envira-srcset="' . esc_url( $output_src ) . ' 400w,' . esc_url( $output_src ) . ' 2x" data-envira-width="'.$output_width.'" data-envira-height="'.$output_height.'" srcset="' . ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $image_src_retina ) . ' 2x' ) . '" data-safe-src="'. ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $output_src ) ) . '" />';

        } else {

            // Legacy

            $output_item = false;

            if ( $envira_lazy_load ) {

                if ( $output_height > 0 && $output_width > 0 ) {
                    $padding_bottom = ( $output_height / $output_width ) * 100;
                } else {
                    // this shouldn't be happening, but this avoids a debug message
                    $padding_bottom = 100;
                }
                $output_item .= '<div class="envira-lazy" data-test-width="'.$output_width.'" data-test-height="'.$output_height.'" style="padding-bottom:'.$padding_bottom.'%;">';

            }

            $output_item .= '<img id="envira-gallery-image-' . sanitize_html_class( $id ) . '" class="envira-gallery-image envira-gallery-image-' . $i . $gallery_theme . '" data-envira-index="' . $i . '" src="' . esc_url( $output_src ) . '"' . ( $this->get_config( 'dimensions', $data ) ? ' width="' . $this->get_config( 'crop_width', $data ) . '" height="' . $this->get_config( 'crop_height', $data ) . '"' : '' ) . ' data-envira-src="' . esc_url( $output_src ) . '" data-envira-gallery-id="' . $data['id'] . '" data-envira-item-id="' . $id . '" data-envira-caption="' . $caption . '" alt="' . esc_attr( $item['alt'] ) . '" title="' . strip_tags( esc_attr( $item['title'] ) ) . '" ' . apply_filters( 'envira_gallery_output_image_attr', '', $id, $item, $data, $i ) . ' itemprop="thumbnailUrl" data-envira-srcset="' . esc_url( $output_src ) . ' 400w,' . esc_url( $output_src ) . ' 2x" srcset="' . ( ( $envira_lazy_load ) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' : esc_url( $image_src_retina ) . ' 2x' ) . '" />';

            if ( $envira_lazy_load ) {

                $output_item .= '</div>';

            }

        }


                $output_item = apply_filters( 'envira_gallery_output_image', $output_item, $id, $item, $data, $i );

                // Add image to output
                $output .= $output_item;
                $output  = apply_filters( 'envira_gallery_output_after_image', $output, $id, $item, $data, $i );

            if ( $create_link ) {
                $output .= '</a>';
            }

            $output  = apply_filters( 'envira_gallery_output_after_link', $output, $id, $item, $data, $i );
            $output .= '</div>';

        $output .= '</div>';
        $output  = apply_filters( 'envira_gallery_output_single_item', $output, $id, $item, $data, $i );

        // Append the image to the gallery output
        $gallery .= $output;

        // Filter the output before returning
        $gallery  = apply_filters( 'envira_gallery_output_after_item', $gallery, $id, $item, $data, $i );

        return $gallery;

    }

    /**
     * Adds a srcset attribute to an image if wp_get_attachment_image_srcset() exists in WordPress (4.4.+)
     *
     * This provides responsive compatibility and allows browsers to choose which image to download
     *
     * @since 1.4.0.3
     *
     * @param string    $atts   Image Attributes
     * @param int       $id     ID
     * @param array     $item   Image
     * @param array     $data   Gallery Config
     * @param int       $i      Image Index
     * @return string           Image Attributes
     */
    public function add_image_srcset_attributes( $atts, $id, $item, $data, $i ) {

        // Check if wp_get_attachment_image_srcset() exists
        if ( ! function_exists( 'wp_get_attachment_image_srcset' ) ) {
            return $atts;
        }

        // Check if item is a Media Library item
        if ( ! is_numeric( $id ) ) {
            return $atts;
        }

        // Check if the gallery has cropping enabled. If so, don't apply srcset attribute, as it'll output
        // images that don't conform to the user's requirements
        if ( $this->get_config( 'crop', $data ) ) {
            return $atts;
        }

        // Iterate through WordPress' registered sizes to find the largest available size
        // that doesn't exceed this Galleries image dimensions
        $sizes = get_intermediate_image_sizes();
        $max_width = 0;
        foreach ( $sizes as $size ) {
            // Get the width
            $width = get_option( $size . '_size_w' );

            // Check if the width is smaller or equal to the gallery image width option
            if ( $width <= $this->get_config( 'crop_width', $data ) && $width > $max_width ) {
                // This size can be used in the srcset
                $max_width = $width;
                $max_size = $size;
            }
        }

        // If no size meets our criteria, bail
        if ( ! isset( $max_size ) ) {
            return $atts;
        }

        // Add the srcset and sizes based on $max_size
        $atts .= ' srcset="' . wp_get_attachment_image_srcset( $id, $max_size ) . '"';
        $atts .= ' sizes="' . wp_calculate_image_sizes( array( $this->get_config( 'crop_width', $data ), $this->get_config( 'crop_height', $data ) ), $item['src'] ) . '"';

        // Return
        return $atts;

    }

    /**
    * Add the 'property' tag to stylesheets enqueued in the body
    *
    * @since 1.4.1.1
    */
    public function add_stylesheet_property_attribute( $tag ) {

        // If the <link> stylesheet is any Envira-based stylesheet, add the property attribute
        if ( strpos( $tag, "id='envira-" ) !== false ) {
            $tag = str_replace( '/>', 'property="stylesheet" />', $tag );
        }

        return $tag;

    }

    /**
     * Maybe sort the gallery images, if specified in the config
     *
     * Note: To ensure backward compat with the previous 'random' config
     * key, the sorting parameter is still stored in the 'random' config
     * key.
     *
     * @since 1.3.8
     *
     * @param   array   $data       Gallery Config
     * @param   int     $gallery_id Gallery ID
     * @return  array               Gallery Config
     */
    public function maybe_sort_gallery( $data, $gallery_id ) {

        // Get sorting method
        $sorting_method     = (string) $this->get_config( 'random', $data );
        $sorting_direction  = $this->get_config( 'sorting_direction', $data );

        // Sort images based on method
        switch ( $sorting_method ) {
            /**
            * Random
            * - Again, by design, to ensure backward compat when upgrading from 1.3.7.x or older
            * where we had a 'random' key = 0 or 1. Sorting was introduced in 1.3.8
            */
            case '1':
                // Shuffle keys
                $keys = array_keys( $data['gallery'] );
                shuffle( $keys );

                // Rebuild array in new order
                $new = array();
                foreach( $keys as $key ) {
                    $new[ $key ] = $data['gallery'][ $key ];
                }

                // Assign back to gallery
                $data['gallery'] = $new;
                break;

            /**
            * Image Meta
            */
            case 'src':
            case 'title':
            case 'caption':
            case 'alt':
            case 'link':
                // Get metadata
                $keys = array();
                foreach ( $data['gallery'] as $id => $item ) {
                    $keys[ $id ] = strip_tags( $item[ $sorting_method ] );
                }

                // Sort titles / captions
                if ( $sorting_direction == 'ASC' ) {
                    asort( $keys );
                } else {
                    arsort( $keys );
                }

                // Iterate through sorted items, rebuilding gallery
                $new = array();
                foreach( $keys as $key => $title ) {
                    $new[ $key ] = $data['gallery'][ $key ];
                }

                // Assign back to gallery
                $data['gallery'] = $new;
                break;

            /**
            * Published Date
            */
            case 'date':
                // Get published date for each
                $keys = array();
                foreach ( $data['gallery'] as $id => $item ) {
                    // If the attachment isn't in the Media Library, we can't get a post date - assume now
                    if ( ! is_numeric( $id ) || ( false === ( $attachment = get_post( $id ) ) ) ) {
                        $keys[ $id ] = date( 'Y-m-d H:i:s' );
                    } else {
                        $keys[ $id ] = $attachment->post_date;
                    }
                }

                // Sort titles / captions
                if ( $sorting_direction == 'ASC' ) {
                    asort( $keys );
                } else {
                    arsort( $keys );
                }

                // Iterate through sorted items, rebuilding gallery
                $new = array();
                foreach( $keys as $key => $title ) {
                    $new[ $key ] = $data['gallery'][ $key ];
                }

                // Assign back to gallery
                $data['gallery'] = $new;
                break;

            /**
            * None
            * - Do nothing
            */
            case '0':
            case '':
                break;

            /**
            * If developers have added their own sort options, let them run them here
            */
            default:
                $data = apply_filters( 'envira_gallery_sort_gallery', $data, $sorting_method, $gallery_id );
                break;

        }

        return $data;

    }

    /**
    * Builds HTML for the Gallery Description
    *
    * @since 1.3.0.2
    *
    * @param string $gallery Gallery HTML
    * @param array $data Data
    * @return HTML
    */
    public function description( $gallery, $data ) {

        $gallery .= '<div class="envira-gallery-description envira-gallery-description-above" style="padding-bottom: ' . $this->get_config( 'margin', $data ) . 'px;">';
            $gallery  = apply_filters( 'envira_gallery_output_before_description', $gallery, $data );

            // Get description.
            $description = $data['config']['description'];

            // If the WP_Embed class is available, use that to parse the content using registered oEmbed providers.
            if ( isset( $GLOBALS['wp_embed'] ) ) {
                $description = $GLOBALS['wp_embed']->autoembed( $description );
            }

            // Get the description and apply most of the filters that apply_filters( 'the_content' ) would use
            // We don't use apply_filters( 'the_content' ) as this would result in a nested loop and a failure.
            $description = wptexturize( $description );
            $description = convert_smilies( $description );
            $description = wpautop( $description );
            $description = prepend_attachment( $description );

            // Requires WordPress 4.4+
            if ( function_exists( 'wp_make_content_images_responsive' ) ) {
                $description = wp_make_content_images_responsive( $description );
            }

            // Append the description to the gallery output.
            $gallery .= $description;

            // Filter the gallery HTML.
            $gallery  = apply_filters( 'envira_gallery_output_after_description', $gallery, $data );
        $gallery .= '</div>';

        return $gallery;
    }

    /**
     * Outputs the gallery init script in the footer.
     *
     * @since 1.0.0
     */
    public function gallery_init() {

        // envira_galleries stores all Fancybox instances
        // envira_isotopes stores all Isotope instances
        // envira_isotopes_config stores Isotope configs for each Gallery
        ?>

        <script type="text/javascript">
            <?php ob_start(); ?>

            var envira_galleries = [],
                envira_gallery_images = [],
                envira_isotopes = [],
                envira_isotopes_config = [];

            jQuery(document).ready(function($){

                <?php
                do_action( 'envira_gallery_api_start_global' );
                foreach ( $this->data as $data ) {
                    // Prevent multiple init scripts for the same gallery ID.
                    if ( in_array( $data['id'], $this->done ) ) {
                        continue;
                    }
                    $this->done[] = $data['id'];

                    do_action( 'envira_gallery_api_start', $data );

                    // Define container
                    ?>
                    var envira_container_<?php echo $data['id']; ?> = '';

                function envira_album_lazy_load_image( $id ) {

                    <?php if ( $this->get_config( 'lazy_loading', $data ) ) { ?>

                    responsivelyLazy.run('#envira-gallery-'+ $id);

                    <?php } else { ?>
                        /* to do: enable ENVIRA DEBUG to display this and other errors */
                        /* console.log ('load_images was pinged, but lazy load turned off'); */
                    <?php } ?>

                }

                    <?php if ( $this->get_config( 'columns', $data ) == 0 ) : ?>

                    <?php

                        // if the user has selected a custom theme, only output the needed JS
                        $gallery_theme = $this->get_config( 'justified_gallery_theme', $data );

                        // in some cases, previous gallery using the old automattic layout aren't showing a row height, so just in case...
                        $justified_row_height = $this->get_config( 'justified_row_height', $data ) ? $this->get_config( 'justified_row_height', $data ) : 150;

                    ?>

                        $('#envira-gallery-<?php echo $data["id"]; ?>').enviraJustifiedGallery({
                            rowHeight : <?php echo $justified_row_height; ?>,
                            maxRowHeight: -1,
                            waitThumbnailsLoad: true,
                            selector: '> div > div',
                            lastRow: 'nojustify',
							border: 0,
							margins: <?php echo $this->get_config( 'justified_margins', $data ) ? $this->get_config( 'justified_margins', $data ) : 1; ?>,

                        });

                        $('#envira-gallery-<?php echo $data["id"]; ?>').justifiedGallery().on('jg.complete', function (e) {

                            envira_album_lazy_load_image(<?php echo $data['id']; ?>);
                            $(window).scroll(function(event){
                                envira_album_lazy_load_image(<?php echo $data['id']; ?>);
                            });

                        });

                        $( document ).on( "envira_pagination_ajax_load_completed", function() {
                            $('#envira-gallery-<?php echo $data["id"]; ?>').justifiedGallery().on('jg.complete', function (e) {

                                envira_album_lazy_load_image(<?php echo $data['id']; ?>);
                                $(window).scroll(function(event){
                                    envira_album_lazy_load_image(<?php echo $data['id']; ?>);
                                });

                            });
                        });

                        <?php if ( $gallery_theme == 'js-desaturate' || $gallery_theme == 'js-threshold' || $gallery_theme == 'js-blur' || $gallery_theme == 'js-vintage' ) : ?>

                        $('#envira-gallery-<?php echo $data["id"]; ?>').on('jg.complete', function (e) {
                            if( navigator.userAgent.match(/msie/i) || $.browser.msie || navigator.appVersion.indexOf('Trident/') > 0 ) {
                                $('#envira-gallery-<?php echo $data["id"]; ?> img').each(function() {
                                    var keep_id = $(this).attr('id');
                                    $(this).attr('id', keep_id + '-effects' );
                                    $(this).wrap('<div class="effect-wrapper" style="display:inline-block;width:' + this.width + 'px;height:' + this.height + 'px;">').clone().addClass('gotcolors').css({'position': 'absolute', 'opacity' : 0, 'z-index' : 1 }).attr('id', keep_id).insertBefore(this);
                                    <?php

                                        switch ($gallery_theme) {
                                            case 'js-desaturate':
                                                echo 'this.src = jg_effect_desaturate($(this).attr("src"));';
                                                break;
                                            case 'js-threshold':
                                                echo 'this.src = jg_effect_threshold(this.src);';
                                                break;
                                            case 'js-blur':
                                                echo 'this.src = jg_effect_blur(this.src);';
                                                break;
                                            case 'js-vintage':
                                                echo 'jg_effect_vintage( this );';
                                                break;
                                        }

                                    ?>
                                });
                                $('#envira-gallery-<?php echo $data["id"]; ?> img').hover(
                                    function() {
                                        $(this).stop().animate({opacity: 1}, 200);
                                    },
                                    function() {
                                        $(this).stop().animate({opacity: 0}, 200);
                                    }
                                );
                            }
                            else {
                                /*$('#envira-gallery-<?php echo $data["id"]; ?> img').each(function() {
                                    $(this).addClass('envira-<?php echo $gallery_theme; ?>');
                                });*/

                                $('#envira-gallery-<?php echo $data["id"]; ?> img').hover(
                                    function() {
                                        $(this).removeClass('envira-<?php echo $gallery_theme; ?>');
                                    },
                                    function() {
                                        $(this).addClass('envira-<?php echo $gallery_theme; ?>');
                                    }
                                );
                            }


                        });

                        <?php endif; ?>

                        $('#envira-gallery-<?php echo $data["id"]; ?>').css('opacity', '1');

                    <?php endif; ?>

                    <?php
                    // Isotope: Start
                    if ( $this->get_config( 'columns', $data ) > 0 && $this->get_config( 'isotope', $data ) ) {
                        // Define config for this Isotope Gallery
                        ?>
                        envira_isotopes_config['<?php echo $data['id']; ?>'] = {
                            <?php do_action( 'envira_gallery_api_enviratope_config', $data ); ?>
                            itemSelector: '.envira-gallery-item',
                            <?php
                            // If columns = 0, use fitRows
                            // if ( $this->get_config( 'columns', $data ) > 0 ) {
                                ?>
                                masonry: {
                                    columnWidth: '.envira-gallery-item'
                                }
                                <?php /*
                            } else {
                                ?>
                                layoutMode: 'fitRows'
                                <?php
                             } */
                            ?>
                        };
                        <?php
                        // Initialize Isotope
                        ?>
                        envira_isotopes['<?php echo $data['id']; ?>'] = envira_container_<?php echo $data['id']; ?>
                                                                    = $('#envira-gallery-<?php echo $data['id']; ?>').enviratope(envira_isotopes_config['<?php echo $data['id']; ?>']);

                        $('#envira-gallery-<?php echo $data["id"]; ?>').on( 'layoutComplete',
                          function( event, laidOutItems ) {
                            envira_album_lazy_load_image(<?php echo $data['id']; ?>);
                            $(window).scroll(function(event){
                                envira_album_lazy_load_image(<?php echo $data['id']; ?>);
                            });
                          }
                        );

                        $( document ).on( "envira_pagination_ajax_load_completed", function() {
                            $('#envira-gallery-<?php echo $data["id"]; ?>').on( 'layoutComplete',
                              function( event, laidOutItems ) {
                                envira_album_lazy_load_image(<?php echo $data['id']; ?>);
                                $(window).scroll(function(event){
                                    envira_album_lazy_load_image(<?php echo $data['id']; ?>);
                                });
                              }
                            );
                        });

                        <?php
                        // Re-layout Isotope when each image loads
                        ?>
                        envira_isotopes['<?php echo $data['id']; ?>'].enviraImagesLoaded()
                            .done(function() {
                                envira_isotopes['<?php echo $data['id']; ?>'].enviratope('layout');
                            })
                            .progress(function() {
                                envira_isotopes['<?php echo $data['id']; ?>'].enviratope('layout');
                            });
                        <?php
                        do_action( 'envira_gallery_api_enviratope', $data );
                    }
                    // Isotope: End

                    // CSS Animations: Start
                    if ( $this->get_config( 'css_animations', $data ) ) {
                        $opacity = $this->get_config( 'css_opacity', $data );

                        // Defaults Addon Gallery may not have been saved since opacity introduction, so force a value if one doesn't exist.
                        if ( empty( $opacity ) ) {
                            $opacity = 100;
                        }

                        // Reduce to factor of 1
                        $opacity = ( $opacity / 100 );
                        ?>
                        envira_container_<?php echo $data['id']; ?> = $('#envira-gallery-<?php echo $data['id']; ?>').enviraImagesLoaded( function() {
                            $('.envira-gallery-item img').fadeTo( 'slow', <?php echo $opacity; ?> );
                        });
                        <?php
                    }
                    // CSS Animations: End

                    // Fancybox: Start
                    if ( $this->get_config( 'lightbox_enabled', $data ) ) {
                        // By default, we'll use the images in the Gallery DOM to populate the lightbox.
                        // However, Addons (e.g. Pagination) may require us to give access to all images
                        // in a Gallery, not just the paginated subset on screen.
                        // Those Addons can populate this array now which will tell envirabox which images to use.
                        $lightbox_images = apply_filters( 'envira_gallery_lightbox_images', false, $data );
                        $title_fb1 = $this->get_config( 'title_display', $data );
                        if ( 'float_wrap' == $title_fb1 || 'float' == $title_fb1 ) {
	                        $title_fb1 = 'float';
                        }

                        ?>
                        envira_gallery_options = {
	                        // All of these options are for Envira Lite with Fancybox 1 to try and match Fancybox 2.
	                        padding: <?php echo in_array( $this->get_config( 'lightbox_theme', $data ), array( 'base_dark' ) ) ? '4' : '15'; ?>,
	                        cyclic: true,
	                        titlePosition: '<?php echo $title_fb1; ?>',
	                        // End options for Envira Lite with Fancybox 1.

                            <?php do_action( 'envira_gallery_api_config', $data ); // Depreciated ?>
                            <?php do_action( 'envira_gallery_api_envirabox_config', $data ); ?>
                            <?php if ( ! $this->get_config( 'keyboard', $data ) ) : ?>
                            keys: 0,
                            <?php endif; ?>
                            margin: <?php echo apply_filters( 'envirabox_margin', 60, $data ); ?>,
                            arrows: <?php echo in_array( $this->get_config( 'lightbox_theme', $data ), array( 'base_dark' ) ) ? 'true' : $this->get_config( 'arrows', $data ); ?>,
                            aspectRatio: <?php echo $this->get_config( 'aspect', $data ); ?>,
                            loop: <?php echo $this->get_config( 'loop', $data ); ?>,
                            mouseWheel: <?php echo $this->get_config( 'mousewheel', $data ); ?>,
                            preload: 1,

                            <?php
                            /* Get open and transition effects */
                            $lightbox_open_close_effect = $this->get_config( 'lightbox_open_close_effect', $data );
                            $lightbox_transition_effect = $this->get_config( 'effect', $data );

                            /* Get standard effects */
                            $lightbox_standard_effects = $this->common->get_transition_effects_values();

                            /* If open/close is standard, use openEffect, closeEffect */
                            if ( in_array( $lightbox_open_close_effect, $lightbox_standard_effects ) ) {
                                ?>
                                openEffect: '<?php echo $lightbox_open_close_effect; ?>',
                                closeEffect: '<?php echo $lightbox_open_close_effect; ?>',
                                <?php
                            } else {

                                // easing effects have been depreciated, and will default back to 'swing'

                                ?>
                                openEffect: 'elastic', // FancyBox default is fade, should be elastic for the openEffect/closeEffect functions
                                closeEffect: 'elastic',
                                openEasing: 'swing', // <?php echo ( $lightbox_open_close_effect == "Swing" ? "swing" : "easeIn" . $lightbox_open_close_effect ); ?>',
                                closeEasing: 'swing', // <?php echo ( $lightbox_open_close_effect == "Swing" ? "swing" : "easeOut" . $lightbox_open_close_effect ); ?>',
                                openSpeed: <?php echo apply_filters( 'envirabox_open_speed', 500, $data ); ?>,
                                closeSpeed: <?php echo apply_filters( 'envirabox_close_speed', 500, $data ); ?>,
                                <?php
                            }

                            /* If transition effect is standard, use nextEffect, prevEffect */
                            if ( in_array( $lightbox_transition_effect, $lightbox_standard_effects ) ) {
                                ?>
                                nextEffect: '<?php echo $lightbox_transition_effect; ?>',
                                prevEffect: '<?php echo $lightbox_transition_effect; ?>',
                                <?php
                            } else {

                                // easing effects have been depreciated, and will default back to 'swing'

                                ?>
                                nextEasing: 'swing', // '<?php echo ( $lightbox_transition_effect == "Swing" ? "swing" : "easeIn" . $lightbox_transition_effect ); ?>',
                                prevEasing: 'swing', // '<?php echo ( $lightbox_transition_effect == "Swing" ? "swing" : "easeOut" . $lightbox_transition_effect ); ?>',
                                nextSpeed: <?php echo apply_filters( 'envirabox_next_speed', 600, $data ); ?>,
                                prevSpeed: <?php echo apply_filters( 'envirabox_prev_speed', 600, $data ); ?>,
                                <?php
                            }
                            ?>
                            tpl: {
                                wrap     : '<?php echo $this->get_lightbox_template( $data ); ?>',
                                image    : '<img class="envirabox-image" src="{href}" alt="" data-envira-title="" data-envira-caption="" data-envira-index="" data-envira-data="" />',
                                iframe   : '<iframe id="envirabox-frame{rnd}" name="envirabox-frame{rnd}" class="envirabox-iframe" frameborder="0" vspace="0" hspace="0" allowtransparency="true" wekitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
                                error    : '<p class="envirabox-error"><?php echo __( 'The requested content cannot be loaded.<br/>Please try again later.</p>', 'envira-gallery' ); ?>',
                                closeBtn : '<a title="<?php echo __( 'Close', 'envira-gallery' ); ?>" class="envirabox-item envirabox-close" href="#"></a>',
                                next     : '<a title="<?php echo __( 'Next', 'envira-gallery' ); ?>" class="envirabox-nav envirabox-next envirabox-arrows-<?php echo $this->get_config( 'arrows_position', $data ); ?>" href="#"><span></span></a>',
                                prev     : '<a title="<?php echo __( 'Previous', 'envira-gallery' ); ?>" class="envirabox-nav envirabox-prev envirabox-arrows-<?php echo $this->get_config( 'arrows_position', $data ); ?>" href="#"><span></span></a>'
                                <?php do_action( 'envira_gallery_api_templates', $data ); ?>
                            },
                            helpers: {
                                <?php
                                do_action( 'envira_gallery_api_helper_config', $data );
                                // Grab title display
                                $title_display = $this->get_config( 'title_display', $data );
                                if ( $title_display == 'float_wrap' ) {
                                    $title_display = 'float';
                                }
                                ?>
                                title: {
                                    <?php do_action( 'envira_gallery_api_title_config', $data ); ?>
                                    type: '<?php echo $title_display; ?>'
                                },
                                <?php if ( $this->get_config( 'thumbnails', $data ) ) : ?>
                                thumbs: {
                                    width: <?php echo $this->get_config( 'thumbnails_width', $data ); ?>,
                                    height: <?php echo $this->get_config( 'thumbnails_height', $data ); ?>,
                                    source: function(current) {
                                        if ( typeof current.element == 'undefined' ) {
                                            return current.thumbnail;
                                        } else {
                                            return $(current.element).data('thumbnail');
                                        }
                                    },
                                    <?php if ( in_array( $this->get_config( 'lightbox_theme', $data ), array( 'base_dark' ) ) ) : ?>
                                    dynamicMargin: true,
                                    <?php endif; ?>
                                    position: '<?php echo in_array( $this->get_config( 'lightbox_theme', $data ), array( 'base_dark' ) )
                                                       ? 'bottom'
                                                       : $this->get_config( 'thumbnails_position', $data ); ?>'
                                },
                                <?php endif; ?>
                                <?php if ( $this->get_config( 'toolbar', $data ) && ! in_array( $this->get_config( 'lightbox_theme', $data ), array( 'base_dark' ) ) ) : ?>
                                buttons: {
                                    tpl: '<?php echo $this->get_toolbar_template( $data ); ?>',
                                    position: '<?php echo $this->get_config( 'toolbar_position', $data ); ?>',
                                    padding: '<?php echo ( ( $this->get_config( 'toolbar_position', $data ) == 'bottom' && $this->get_config( 'thumbnails', $data ) && $this->get_config( 'thumbnails_position', $data ) == 'bottom' ) ? true : false ); ?>'
                                },
                                <?php endif; ?>
                            },
                            <?php do_action( 'envira_gallery_api_config_callback', $data ); ?>
                            beforeLoad: function(){

                                <?php

                                if ( ! $lightbox_images ) {

                                ?>
                                this.title = $(this.element).attr('data-envira-caption');
                                <?php

                                } else {

                                ?>
                                this.title = this.group[ this.index ].caption;
                                <?php

                                }

                                ?>

                                <?php do_action( 'envira_gallery_api_before_load', $data ); ?>
                            },
                            afterLoad: function(){
                                $('envirabox-overlay-fixed').on({
                                    'touchmove' : function(e){
                                        e.preventDefault();
                                    }
                                });

                                <?php do_action( 'envira_gallery_api_after_load', $data ); ?>
                            },
                            beforeShow: function(){
                                $(window).on({
                                    'resize.envirabox' : function(){
                                        $.envirabox.update();
                                    }
                                });

                                <?php
                                // Set data attributes on the lightbox image, based on either
                                // the image in the DOM or (if $lightbox_images defined) the image
                                // from $lightbox_images
                                ?>
                                if ( typeof this.element === 'undefined' ) {
                                    <?php
                                    // Using $lightbox_images
                                    ?>
                                    var gallery_id = this.group[ this.index ].gallery_id;
                                    var gallery_item_id = this.group[ this.index ].id;
                                    var alt = this.group[ this.index ].alt;
                                    var title = this.group[ this.index ].title;
                                    var caption = this.group[ this.index ].caption;
                                    var index = this.index;
                                } else {
                                    <?php
                                    // Using image from DOM
                                    // Get a bunch of data attributes from clicked image link
                                    ?>
                                    var gallery_id = this.element.find('img').data('envira-gallery-id');
                                    var gallery_item_id = this.element.find('img').data('envira-item-id');
                                    var alt = this.element.find('img').attr('alt');
                                    var title = this.element.find('img').parent().attr('title');
                                    var caption = this.element.find('img').parent().data('envira-caption');
                                    var retina_image = this.element.find('img').parent().data('envira-retina');
                                    var index = this.element.find('img').data('envira-index');
                                }

                                <?php
                                // Set alt, data-envira-title, data-envira-caption and data-envira-index attributes on Lightbox image
                                ?>
                                this.inner.find('img').attr('alt', alt)
                                                      .attr('data-envira-gallery-id', gallery_id)
                                                      .attr('data-envira-item-id', gallery_item_id)
                                                      .attr('data-envira-title', title)
                                                      .attr('data-envira-caption', caption)
                                                      .attr('data-envira-index', index);

                                <?php
                                // Set retina image srcset if specified
                                ?>
                                if ( typeof retina_image !== 'undefined' && retina_image !== '' ) {
                                    this.inner.find('img').attr('srcset', retina_image + ' 2x');
                                }

                                <?php do_action( 'envira_gallery_api_before_show', $data ); ?>
                            },
                            onStart: function(){
                                <?php ?>
                                $('#envirabox-wrap, #envirabox-wrap #envirabox-left, #envirabox-wrap #envirabox-right').swipe( {
                                    excludedElements:"label, button, input, select, textarea, .noSwipe",
                                    swipe: function(event, direction, distance, duration, fingerCount, fingerData) {
                                        if (direction === 'left') {
                                            $.envirabox.next(direction);
                                        } else if (direction === 'right') {
                                            $.envirabox.prev(direction);
                                        } else if (direction === 'up') {
                                            $.envirabox.close();
                                        }
                                    }
                                } );
                                <?php

                                // If title helper = float_wrap, add a CSS class so we can disable word-wrap
                                if ( $this->get_config( 'title_display', $data ) == 'float_wrap' ) {
                                    ?>
                                    if ( typeof this.helpers.title !== 'undefined' ) {
                                        if ( ! $( 'div.envirabox-title' ).hasClass( 'envirabox-title-text-wrap' ) ) {
                                            $( 'div.envirabox-title' ).addClass( 'envirabox-title-text-wrap' );
                                        }
                                    }
                                    <?php
                                }

                                do_action( 'envira_gallery_api_after_show', $data ); ?>
                            },
                            beforeClose: function(){
                                <?php do_action( 'envira_gallery_api_before_close', $data ); ?>
                            },
                            afterClose: function(){
                                $(window).off('resize.envirabox');
                                <?php do_action( 'envira_gallery_api_after_close', $data ); ?>
                            },
                            onUpdate: function(){
                                <?php
                                if ( $this->get_config( 'toolbar', $data ) ) : ?>
                                var envira_buttons_<?php echo $data['id']; ?> = $('#envirabox-buttons li').map(function(){
                                    return $(this).width();
                                }).get(),
                                    envira_buttons_total_<?php echo $data['id']; ?> = 0;
                                $.each(envira_buttons_<?php echo $data['id']; ?>, function(i, val){
                                    envira_buttons_total_<?php echo $data['id']; ?> += parseInt(val, 10);
                                });

                                envira_buttons_total_<?php echo $data['id']; ?> += 1;

                                $('#envirabox-buttons ul').width(envira_buttons_total_<?php echo $data['id']; ?>);
                                $('#envirabox-buttons').width(envira_buttons_total_<?php echo $data['id']; ?>).css('left', ($(window).width() - envira_buttons_total_<?php echo $data['id']; ?>)/2);
                                <?php endif; ?>

                                <?php do_action( 'envira_gallery_api_on_update', $data ); ?>
                            },
                            onCancel: function(){
                                <?php do_action( 'envira_gallery_api_on_cancel', $data ); ?>
                            },
                            onPlayStart: function(){
                                <?php do_action( 'envira_gallery_api_on_play_start', $data ); ?>
                            },
                            onPlayEnd: function(){
                                <?php do_action( 'envira_gallery_api_on_play_end', $data ); ?>
                            }
                        };
                        <?php

                        if ( ! $lightbox_images ) {
                            // No lightbox images specified, so use images from DOM
                            ?>
                            envira_galleries['<?php echo $data['id']; ?>'] = $('.envira-gallery-<?php echo $data['id']; ?>').envirabox( envira_gallery_options );
                            <?php

                        } else {
                            // Use images from $lightbox_images
                            add_filter( 'envira_minify_strip_double_forward_slashes', '__return_false' );
                            ?>
                            envira_gallery_images[<?php echo $data['id']; ?>] = [];
                            <?php
                            // Build a JS array of all images
                            $count = 0;
                            foreach ( $lightbox_images as $image_id => $image ) {
                                // If no image ID exists, skip
                                if ( empty( $image_id ) ) {
                                    continue;
                                }
                                ?>
                                envira_gallery_images[<?php echo $data['id']; ?>].push({
                                    href: '<?php echo $image['src']; ?>',
                                    gallery_id: <?php echo $data['id']; ?>,
                                    id: <?php echo $image_id; ?>,
                                    alt: '<?php echo addslashes( str_replace( "\n", '<br />', $image['alt'] ) ); ?>',
                                    caption: '<?php echo addslashes( str_replace( "\n", '<br />', $image['caption'] ) ); ?>',
                                    title: '<?php echo addslashes( str_replace( "\n", '<br />', $image['title'] ) ); ?>',
                                    index: <?php echo $count; ?>,
                                    thumbnail: '<?php echo ( isset( $image['thumb'] ) ? $image['thumb'] : '' ); ?>'
                                    <?php do_action( 'envira_gallery_api_lightbox_image_attributes', $image, $image_id, $lightbox_images, $data ); ?>
                                });
                                <?php
                                $count++;
                            }

                            // Open envirabox when an image is clicked, telling envirabox which images are available to it.
                            ?>
                            envira_galleries['<?php echo $data['id']; ?>'] = $('.envira-gallery-<?php echo $data['id']; ?>').envirabox( envira_gallery_options, envira_gallery_images[<?php echo $data['id']; ?>] );
                            $('#envira-gallery-wrap-<?php echo $data['id']; ?>').on('click', 'a.envira-gallery-link', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                $.envirabox.close();
                                if ( $('#envira-gallery-wrap-<?php echo $data['id']; ?> div.envira-pagination').length > 0 ) { /* this exists, perhaps pagination doesn't display nav because it's only one page? */
                                    var envirabox_page = ( $('#envira-gallery-wrap-<?php echo $data['id']; ?> div.envira-pagination').data('page') - 1 );
                                } else {
                                    var envirabox_page = 0;
                                }

                                if ( $('#envira-gallery-wrap-<?php echo $data['id']; ?> div.envira-pagination').length > 0 ) { /* this exists, perhaps pagination doesn't display nav because it's only one page? */
                                    var envirabox_per_page = $('#envira-gallery-wrap-<?php echo $data['id']; ?> div.envira-pagination').data('per-page');
                                } else {
                                    var envirabox_per_page = $('.envira-gallery-image').length;
                                }

                                var envirabox_index = ( Number($('img', $(this)).data('envira-index')) - 1 );
                                envira_gallery_options.index = ((envirabox_page * envirabox_per_page) + envirabox_index);
                                envira_galleries['<?php echo $data['id']; ?>'] = $.envirabox( envira_gallery_images[<?php echo $data['id']; ?>], envira_gallery_options );
                            });
                            <?php
                        }

                        do_action( 'envira_gallery_api_lightbox', $data );
                        // Fancybox: End
                    }

                    do_action( 'envira_gallery_api_end', $data );
                } // foreach

                do_action( 'envira_gallery_api_end_global', $this->data );

                ?>
            });

            <?php

	        if ( defined('SCRIPT_DEBUG') && ! SCRIPT_DEBUG ){

               // Minify before outputting to improve page load time.
				echo $this->minify( ob_get_clean() );

            }else{

	            echo ob_get_clean();

            }

            ?>
        </script>
        <?php

    }

    /**
     * Loads a custom gallery display theme.
     *
     * @since 1.0.0
     *
     * @param string $theme The custom theme slug to load.
     */
    public function load_gallery_theme( $theme ) {

        // Loop through the available themes and enqueue the one called.
        foreach ( $this->common->get_gallery_themes() as $array => $data ) {
            if ( $theme !== $data['value'] ) {
                continue;
            }
            if ( file_exists( plugin_dir_path( $data['file'] ) . 'themes/' . $theme . '/style.css' ) ) {
                wp_enqueue_style( $this->base->plugin_slug . $theme . '-theme', plugins_url( 'themes/' . $theme . '/style.css', $data['file'] ), array( $this->base->plugin_slug . '-style' ) );
            }
            else {
                wp_enqueue_style( $this->base->plugin_slug . $theme . '-theme', plugins_url( 'themes/' . $theme . '/css/style.css', $data['file'] ), array( $this->base->plugin_slug . '-style' ) );
            }
            break;
        }

    }

    /**
     * Loads a custom gallery lightbox theme.
     *
     * @since 1.0.0
     *
     * @param string $theme The custom theme slug to load.
     */
    public function load_lightbox_theme( $theme ) {

        // Loop through the available themes and enqueue the one called.
        foreach ( $this->common->get_lightbox_themes() as $array => $data ) {
            if ( $theme !== $data['value'] ) {
                continue;
            }

            if ( file_exists( plugin_dir_path( $data['file'] ) . 'themes/' . $theme . '/style.css' ) ) {
                wp_enqueue_style( $this->base->plugin_slug . $theme . '-theme', plugins_url( 'themes/' . $theme . '/style.css', $data['file'] ), array( $this->base->plugin_slug . '-style' ) );
            }
            else {
                wp_enqueue_style( $this->base->plugin_slug . $theme . '-theme', plugins_url( 'themes/' . $theme . '/css/style.css', $data['file'] ), array( $this->base->plugin_slug . '-style' ) );
            }
            break;
        }

    }

    /**
     * Helper method for adding custom gallery classes.
     *
     * @since 1.0.0
     *
     * @param array $data The gallery data to use for retrieval.
     * @return string     String of space separated gallery classes.
     */
    public function get_gallery_classes( $data ) {

        // Set default class.
        $classes   = array();
        $classes[] = 'envira-gallery-wrap';

        // Add custom class based on data provided.
        $classes[] = 'envira-gallery-theme-' . $this->get_config( 'gallery_theme', $data );
        $classes[] = 'envira-lightbox-theme-' . $this->get_config( 'lightbox_theme', $data );

        // If we have custom classes defined for this gallery, output them now.
        foreach ( (array) $this->get_config( 'classes', $data ) as $class ) {
            $classes[] = $class;
        }

        // If the gallery has RTL support, add a class for it.
        if ( $this->get_config( 'rtl', $data ) ) {
            $classes[] = 'envira-gallery-rtl';
        }

        // Allow filtering of classes and then return what's left.
        $classes = apply_filters( 'envira_gallery_output_classes', $classes, $data );
        return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

    }

    /**
     * Helper method for adding custom gallery classes.
     *
     * @since 1.0.4
     *
     * @param array $item Array of item data.
     * @param int $i      The current position in the gallery.
     * @param array $data The gallery data to use for retrieval.
     * @return string     String of space separated gallery item classes.
     */
    public function get_gallery_item_classes( $item, $i, $data ) {

        // Set default class.
        $classes   = array();
        $classes[] = 'envira-gallery-item';
        $classes[] = 'enviratope-item';
        $classes[] = 'envira-gallery-item-' . $i;

        // If lazy load exists, add that
        if ( isset( $data['config']['lazy_loading'] ) && $data['config']['lazy_loading'] ) {
            $classes[] = 'envira-lazy-load';
        }

        // Allow filtering of classes and then return what's left.
        $classes = apply_filters( 'envira_gallery_output_item_classes', $classes, $item, $i, $data );
        return trim( implode( ' ', array_map( 'trim', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) ) );

    }

    /**
     * Changes the link attribute of an image, if the Lightbox config
     * requires a different sized image to be displayed.
     *
     * @since 1.3.6
     *
     * @param int $id      The image attachment ID to use.
     * @param array $item  Gallery item data.
     * @param array $data  The gallery data to use for retrieval.
     * @return array       Image array
     */
    public function maybe_change_link( $id, $item, $data ) {

        // Check gallery config
        $image_size = $this->get_config( 'lightbox_image_size', $data );

        //check if the url is a valid image if not return it
        if (! $this->is_image( $item['link'] ) ) {
            return $item;
        }

        // Return if we are serving a full size image
        if ( $image_size == 'default' || $image_size == 'full_width' ) {
            return $item;
        }

        // Get media library attachment at requested size
        $image = wp_get_attachment_image_src( $id, $image_size );

        if ( ! is_array( $image ) ) {
            return $item;
        }

        // Inject new image size into $item
        $item['link'] = $image[0];

        // Return
        return $item;

    }

	/**
	 * Helper method to retrieve the proper image src attribute based on gallery settings.
	 *
	 * @since 1.0.0
	 *
	 * @param int       $id            The image attachment ID to use.
	 * @param array          $item          Gallery item data.
	 * @param array          $data          The gallery data to use for retrieval.
	 * @param bool      $this->is_mobile         Whether or not to retrieve the mobile image.
	 * @param bool      $retina        Whether to return a retina sized image.
	 * @return string                  The proper image src attribute for the image.
	 */
	public function get_image_src( $id, $item, $data, $mobile = false, $retina = false ) {

		// Define variable
		$src = false;

		// If this image is an instagram, we grab the src and don't use the $id
		// otherwise using the $id refers to a postID in the database and has been known
		// at times to pull up the wrong thumbnail instead of the instagram one

		$instagram = false;

		if ( !empty($item['src']) && strpos( $item['src'], 'cdninstagram' ) !== false ) :
			// using 'cdninstagram' because it seems all urls contain it - but be watchful in the future
			$instagram     = true;
			$src      = $item['src'];
			$image         = $item['src'];
		endif;

		$image_size = $this->get_config( 'image_size', $data );

		if ( !$src ) :

			// Check if this Gallery uses a WordPress defined image size
			if ( $image_size != 'default' && ! $retina ) {
				// If image size is envira_gallery_random, get a random image size.
				if ( $image_size == 'envira_gallery_random' ) {

					// Get random image sizes that have been chosen for this Gallery.
					$image_sizes_random = (array) $this->get_config( 'image_sizes_random', $data );

					if ( count( $image_sizes_random ) == 0 ) {
						// The user didn't choose any image sizes - use them all.
						$wordpress_image_sizes = $this->common->get_image_sizes( true );
						$wordpress_image_size_random_key = array_rand( $wordpress_image_sizes, 1 );
						$image_size = $wordpress_image_sizes[ $wordpress_image_size_random_key ]['value'];
					} else {
						$wordpress_image_size_random_key = array_rand( $image_sizes_random, 1 );
						$image_size = $image_sizes_random[ $wordpress_image_size_random_key ];
					}

					// Get the random WordPress defined image size
					$src = wp_get_attachment_image_src( $id, $image_size );
				} else {
					// Get the requested WordPress defined image size
					$src = wp_get_attachment_image_src( $id, $image_size );
				}
			} else {

				if ( ! $retina ){

					$isize = $this->find_clostest_size( $data ) != '' ? $this->find_clostest_size( $data ) : 'full';
					$src = apply_filters( 'envira_gallery_retina_image_src', wp_get_attachment_image_src( $id, $isize ), $id, $item, $data, $this->is_mobile );

				}else{
					$src = apply_filters( 'envira_gallery_retina_image_src', wp_get_attachment_image_src( $id, 'full' ), $id, $item, $data, $this->is_mobile );
				}


			}

		endif;


		// Check if this returned an image
		if ( ! $src ) {
			// Fallback to the $item's image source
			$image = $item['src'];
		} else if ( ! $instagram ) {
			$image = $src[0];
		}

		// If we still don't have an image at this point, something went wrong
		if ( ! isset( $image ) ) {
			return apply_filters( 'envira_gallery_no_image_src', $item['link'], $id, $item, $data );
		}

		// Prep our indexable images.
		if ( $image && ! $this->is_mobile ) {
			$this->index[ $data['id'] ][ $id ] = array(
				'src' => $image,
				'alt' => ! empty( $item['alt'] ) ? $item['alt'] : ''
			);
		}

		// If the current layout is justified/automatic
		// if the image size is a WordPress size and we're not requesting a retina image we don't need to resize or crop anything.
		if ( $image_size != 'default' && ! $retina ) {
		// if ( ( $image_size != 'default' && ! $retina ) ) {
			// Return the image
			return apply_filters( 'envira_gallery_image_src', $image, $id, $item, $data );
		}

		// If the image size is default (i.e. the user has input their own custom dimensions in the Gallery),
		// we may need to resize the image now
		// This is safe to call every time, as resize_image() will check if the image already exists, preventing thumbnails
		// from being generated every single time.
		$type = $this->is_mobile ? 'mobile' : 'crop'; // 'crop' is misleading here - it's the key that stores the thumbnail width + height
		$args = array(
			'position' => 'c',
			'width'      => $this->get_config( $type . '_width', $data ),
			'height'   => $this->get_config( $type . '_height', $data ),
			'quality'  => 100,
			'retina'   => $retina,
		);

		// If we're requesting a retina image, and the gallery uses a registered WordPress image size,
		// we need use the width and height of that registered WordPress image size - not the gallery's
		// image width and height, which are hidden settings.
		if ( $image_size != 'default' && $retina ) {
			// Find WordPress registered image size
			$wordpress_image_sizes = $this->common->get_image_sizes( true ); // true = WordPress only image sizes (excludes random)

			foreach ( $wordpress_image_sizes as $size ) {
				if ( $size['value'] !== $image_size ) {
					continue;
				}

                // Define the $args so at least they exist
                $args['width'] = $args['height'] = false;

                // We found the image size. Use its dimensions
                if ( !empty( $size['width'] ) ) {
                    $args['width'] = $size['width'];
                }
                if ( !empty( $size['height'] ) ) {
                    $args['height'] = $size['height'];
                }
				break;

			}
		}

		// Filter
		$args     = apply_filters( 'envira_gallery_crop_image_args', $args);
		$resized_image = $this->common->resize_image( $image, $args['width'], $args['height'], $this->get_config( 'crop', $data ), $args['position'], $args['quality'], $args['retina'], $data );

		// If there is an error, possibly output error message and return the default image src.
		if ( is_wp_error( $resized_image ) ) {
			// If WP_DEBUG is enabled, and we're logged in, output an error to the user
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_user_logged_in() ) {
				echo '<pre>Envira: Error occured resizing image (these messages are only displayed to logged in WordPress users):<br />';
				echo 'Error: ' . $resized_image->get_error_message() . '<br />';
				echo 'Image: ' . $image . '<br />';
				echo 'Args: ' . var_export( $args, true ) . '</pre>';
			}

			// Return the non-cropped image as a fallback.
			return apply_filters( 'envira_gallery_image_src', $image, $id, $item, $data );
		} else {
			return apply_filters( 'envira_gallery_image_src', $resized_image, $id, $item, $data );
		}

	}

	public function find_clostest_size( $data ){

		$image_sizes = $this->get_image_sizes();
		$dimensions =  $this->get_config( 'dimensions', $data );
		$width =  $this->get_config( 'crop_width', $data );
		$height =  $this->get_config( 'crop_height', $data );
		$match   = false;

		usort( $image_sizes, array( $this, 'usort_callback' ) );

		foreach( $image_sizes as $num ) {

			$num['width']  = (int) $num['width'];
			$num['height'] = (int) $num['height'];

			//skip over sizes that are smaller
			if ( $num['height'] < $height || $num['width'] < $width ){
				continue;
			}
			if ( $num['width'] > $width && $num['height'] > $height ) {

				if ( $match === false ) {

					$match = true;
					$size = $num['name'];

					return $size;
				}
			}
		}
		return '';

	}

	/**
	 * Helper function for usort and php 5.3 >.
	 *
	 * @access public
	 * @param mixed $a
	 * @param mixed $b
	 * @return void
	 */
	function usort_callback($a, $b) {

		return intval($a['width']) - intval($b['width'] );

	}

	/**
	 * get_image_sizes function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_image_sizes(){

		global $_wp_additional_image_sizes;

		$sizes = array();
		foreach ( get_intermediate_image_sizes() as $_size ) {

			if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {

				if ( (bool) get_option( "{$_size}_crop" ) === true ){

					continue;

				}
				$sizes[ $_size ]['name']   = $_size;
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );

		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

			if( $_wp_additional_image_sizes[ $_size ]['crop'] === true ){

				continue;

			}

			$sizes[ $_size ] = array(
				'name'  => $_size,
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}
		return $sizes;
	}

    /**
     * Helper method to retrieve the proper gallery toolbar template.
     *
     * @since 1.0.0
     *
     * @param array $data Array of gallery data.
     * @return string     String template for the gallery toolbar.
     */
    public function get_toolbar_template( $data ) {

        // Build out the custom template based on options chosen.
        $template  = '<div id="envirabox-buttons">';
            $template .= '<ul>';
                $template  = apply_filters( 'envira_gallery_toolbar_start', $template, $data );

                // Prev
                $template .= '<li><a class="btnPrev" title="' . __( 'Previous', 'envira-gallery' ) . '" href="javascript:;"></a></li>';
                $template  = apply_filters( 'envira_gallery_toolbar_after_prev', $template, $data );

                // Next
                $template .= '<li><a class="btnNext" title="' . __( 'Next', 'envira-gallery' ) . '" href="javascript:;"></a></li>';
                $template  = apply_filters( 'envira_gallery_toolbar_after_next', $template, $data );

                // Title
                if ( $this->get_config( 'toolbar_title', $data ) ) {
                    $template .= '<li id="envirabox-buttons-title"><span>' . $this->get_config( 'title', $data ) . '</span></li>';
                    $template  = apply_filters( 'envira_gallery_toolbar_after_title', $template, $data );
                }

                // Close
                $template .= '<li><a class="btnClose" title="' . __( 'Close', 'envira-gallery' ) . '" href="javascript:;"></a></li>';
                $template  = apply_filters( 'envira_gallery_toolbar_after_close', $template, $data );

                $template  = apply_filters( 'envira_gallery_toolbar_end', $template, $data );
            $template .= '</ul>';
        $template .= '</div>';

        // Return the template, filters applied and all.
        return apply_filters( 'envira_gallery_toolbar', $template, $data );

    }

    /**
    * Helper method to retrieve the gallery lightbox template
    *
    * @since 1.3.1.4
    *
    * @param array $data Array of gallery data
    * @return string String template for the gallery lightbox
    */
    public function get_lightbox_template( $data ) {

        // Build out the lightbox template
        $envirabox_wrap_css_classes = apply_filters( 'envirabox_wrap_css_classes', 'envirabox-wrap', $data );

        $envirabox_theme = apply_filters( 'envirabox_theme', 'envirabox-theme-' . $this->get_config( 'lightbox_theme', $data ), $data );

        $template = '<div class="' . $envirabox_wrap_css_classes . '" tabIndex="-1"><div class="envirabox-skin ' . $envirabox_theme . '"><div class="envirabox-outer"><div class="envirabox-inner">';

        // Top Left box
        $template .= '<div class="envirabox-position-overlay envira-gallery-top-left">';
        $template  = apply_filters( 'envirabox_output_dynamic_position', $template, $data, 'top-left' );
        $template .= '</div>';

        // Top Right box
        $template .= '<div class="envirabox-position-overlay envira-gallery-top-right">';
        $template  = apply_filters( 'envirabox_output_dynamic_position', $template, $data, 'top-right' );
        $template .= '</div>';

        // Bottom Left box
        $template .= '<div class="envirabox-position-overlay envira-gallery-bottom-left">';
        $template  = apply_filters( 'envirabox_output_dynamic_position', $template, $data, 'bottom-left' );
        $template .= '</div>';

        // Bottom Right box
        $template .= '<div class="envirabox-position-overlay envira-gallery-bottom-right">';
        $template  = apply_filters( 'envirabox_output_dynamic_position', $template, $data, 'bottom-right' );
        $template .= '</div>';

        $template .= '</div></div></div></div>';

        // Return the template, filters applied
        return apply_filters( 'envira_gallery_lightbox_template', str_replace( "\n", '', $template ), $data );

    }

    /**
     * Helper method for retrieving config values.
     *
     * @since 1.0.0
     *
     * @param string $key The config key to retrieve.
     * @param array $data The gallery data to use for retrieval.
     * @return string     Key value on success, default if not set.
     */
    public function get_config( $key, $data ) {

        return isset( $data['config'][$key] ) ? $data['config'][$key] : $this->common->get_config_default( $key );

    }

    /**
     * Helper method to minify a string of data.
     *
     * @since 1.0.4
     *
     * @param string $string  String of data to minify.
     * @return string $string Minified string of data.
     */
    public function minify( $string, $stripDoubleForwardslashes = true ) {

        // Added a switch for stripping double forwardslashes
        // This can be disabled when using URLs in JS, to ensure http:// doesn't get removed
        // All other comment removal and minification will take place
        $stripDoubleForwardslashes = apply_filters( 'envira_minify_strip_double_forward_slashes', $stripDoubleForwardslashes );

        if ( $stripDoubleForwardslashes ) {
            $clean = preg_replace( '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $string );
        } else {
            // Use less aggressive method
            $clean = preg_replace( '!/\*.*?\*/!s', '', $string );
            $clean = preg_replace( '/\n\s*\n/', "\n", $clean );
        }

        $clean = str_replace( array( "\r\n", "\r", "\t", "\n", '  ', '    ', '     ' ), '', $clean );

        return apply_filters( 'envira_gallery_minified_string', $clean, $string );

    }

    /**
     * I'm sure some plugins mean well, but they go a bit too far trying to reduce
     * conflicts without thinking of the consequences.
     *
     * 1. Prevents Foobox from completely borking envirabox as if Foobox rules the world.
     *
     * @since 1.0.0
     */
    public function plugin_humility() {

        if ( class_exists( 'fooboxV2' ) ) {
            remove_action( 'wp_footer', array( $GLOBALS['foobox'], 'disable_other_lightboxes' ), 200 );
        }

    }

    /**
     * Outputs only the first image of the gallery inside a regular <div> tag
     * to avoid styling issues with feeds.
     *
     * @since 1.0.5
     *
     * @param array $data      Array of gallery data.
     * @return string $gallery Custom gallery output for feeds.
     */
    public function do_feed_output( $data ) {

        $gallery = '<div class="envira-gallery-feed-output">';
            foreach ( $data['gallery'] as $id => $item ) {
                // Skip over images that are pending (ignore if in Preview mode).
                if ( isset( $item['status'] ) && 'pending' == $item['status'] && ! is_preview() ) {
                    continue;
                }

                $imagesrc = $this->get_image_src( $id, $item, $data );
                $gallery .= '<img class="envira-gallery-feed-image" src="' . esc_url( $imagesrc ) . '" title="' . trim( esc_html( $item['title'] ) ) . '" alt="' .trim( esc_html( $item['alt'] ) ) . '" />';
                break;
             }
        $gallery .= '</div>';

        return apply_filters( 'envira_gallery_feed_output', $gallery, $data );

    }

    /**
     * Returns a set of indexable image links to allow SEO indexing for preloaded images.
     *
     * @since 1.0.0
     *
     * @param mixed $id       The slider ID to target.
     * @return string $images String of indexable image HTML.
     */
    public function get_indexable_images( $id ) {

        // If there are no images, don't do anything.
        $images = '';
        $i      = 1;
        if ( empty( $this->index[$id] ) ) {
            return $images;
        }

        foreach ( (array) $this->index[$id] as $attach_id => $data ) {
            $images .= '<img src="' . esc_url( $data['src'] ) . '" alt="' . esc_attr( $data['alt'] ) . '" />';
            $i++;
        }

        return apply_filters( 'envira_gallery_indexable_images', $images, $this->index, $id );

    }

    /**
     * isImage function.
     *
     * @access public
     * @param mixed $url
     * @return void
     */
    function is_image( $url ) {

        $p = strrpos( $url, ".");

        if ( $p === false ){

            return false;

        }

        $extension = strtolower( trim( substr( $url, $p ) ) );

        $img_extensions = array('.gif', '.jpg', '.jpeg', '.png', '.tiff', '.tif');

        if ( in_array( $extension, $img_extensions ) ){

            return true;

        }

        return false;
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Shortcode ) ) {
            self::$instance = new Envira_Gallery_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_gallery_shortcode = Envira_Gallery_Shortcode::get_instance();