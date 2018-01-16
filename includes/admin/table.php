<?php
/**
 * WP List Table Admin Class.
 *
 * @since 1.5.0
 *
 * @package Envira_Gallery
 * @author  Envira Team
 */
class Envira_Gallery_Table_Admin {

    /**
     * Holds the class object.
     *
     * @since 1.5.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.5.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.5.0
     *
     * @var object
     */
    public $base;

    /**
     * Holds the metabox class object.
     *
     * @since 1.5.0
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

        // Load the metabox class object.
        $this->metabox = Envira_Gallery_Metaboxes::get_instance();

        // Load CSS and JS.
        add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

        // Append data to various admin columns.
        add_filter( 'manage_edit-envira_columns', array( &$this, 'envira_columns' ) );
        add_action( 'manage_envira_posts_custom_column', array( &$this, 'envira_custom_columns'), 10, 2 );

    }
    
    /**
     * Loads styles for all Envira-based WP_List_Table Screens.
     *
     * @since 1.5.0
     *
     * @return null Return early if not on the proper screen.
     */
    public function styles() {

        // Get current screen.
        $screen = get_current_screen();
        
        // Bail if we're not on the Envira Post Type screen.
        if ( 'envira' !== $screen->post_type && 'envira_album' !== $screen->post_type ) {
            return;
        }

        // Bail if we're not on a WP_List_Table.
        if ( 'edit' !== $screen->base ) {
            return;
        }

        // Load necessary admin styles.
        wp_register_style( $this->base->plugin_slug . '-table-style', plugins_url( 'assets/css/table.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-table-style' );

        // Fire a hook to load in custom admin styles.
        do_action( 'envira_gallery_table_styles' );

    }

    /**
     * Loads scripts for all Envira-based Administration Screens.
     *
     * @since 1.5.0
     *
     * @return null Return early if not on the proper screen.
     */
    public function scripts() {

        // Get current screen.
        $screen = get_current_screen();
        
        // Bail if we're not on the Envira Post Type screen.
        if ( 'envira' !== $screen->post_type && 'envira_album' !== $screen->post_type ) {
            return;
        }

        // Bail if we're not on a WP_List_Table.
        if ( 'edit' !== $screen->base ) {
            return;
        }

        // Load necessary admin scripts
        wp_register_script( $this->base->plugin_slug . '-clipboard-script', plugins_url( 'assets/js/min/clipboard-min.js', $this->base->file ), array( 'jquery' ), $this->base->version );
        wp_enqueue_script( $this->base->plugin_slug . '-clipboard-script' );

        // Gallery / Album Selection
        // Just register and localize the script; if a third party Addon wants to use this, they can use both functions:
        // wp_enqueue_media();
        // wp_enqueue_script( 'envira-gallery-gallery-select-script' );
        wp_register_script( $this->base->plugin_slug . '-gallery-select-script', plugins_url( 'assets/js/gallery-select.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_localize_script( $this->base->plugin_slug . '-gallery-select-script', 'envira_gallery_select', array(
            'get_galleries_nonce'   => wp_create_nonce( 'envira-gallery-editor-get-galleries' ),
            'modal_title'           => __( 'Insert', 'envira-gallery' ),
            'insert_button_label'   => __( 'Insert', 'envira-gallery' ),
        ) );

        wp_register_script( $this->base->plugin_slug . '-table-script', plugins_url( 'assets/js/min/table-min.js', $this->base->file ), array( 'jquery' ), $this->base->version );
        wp_enqueue_script( $this->base->plugin_slug . '-table-script' );

        // Fire a hook to load in custom admin scripts.
        do_action( 'envira_gallery_admin_scripts' );

    }

    /**
     * Customize the post columns for the Envira post type.
     *
     * @since 1.0.0
     *
     * @param array $columns  The default columns.
     * @return array $columns Amended columns.
     */
    public function envira_columns( $columns ) {

        // Add additional columns we want to display.
        $envira_columns = array(
            'cb'            => '<input type="checkbox" />',
            'image'         => __( '', 'envira-gallery' ),
            'title'         => __( 'Title', 'envira-gallery' ),
            'shortcode'     => __( 'Shortcode', 'envira-gallery' ),
            'posts'         => __( 'Posts', 'envira-gallery' ),
            'modified'      => __( 'Last Modified', 'envira-gallery' ),
            'date'          => __( 'Date', 'envira-gallery' )
        );

        // Allow filtering of columns
        $envira_columns = apply_filters( 'envira_gallery_table_columns', $envira_columns, $columns );

        // Return merged column set.  This allows plugins to output their columns (e.g. Yoast SEO),
        // and column management plugins, such as Admin Columns, should play nicely.
        return array_merge( $envira_columns, $columns );

    }

    /**
     * Add data to the custom columns added to the Envira post type.
     *
     * @since 1.0.0
     *
     * @global object $post  The current post object
     * @param string $column The name of the custom column
     * @param int $post_id   The current post ID
     */
    public function envira_custom_columns( $column, $post_id ) {

        global $post;
        $post_id = absint( $post_id );

        switch ( $column ) {
            /**
            * Image
            */
            case 'image':
                // Get Gallery Images.
                $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
                if ( ! empty( $gallery_data['gallery'] ) && is_array( $gallery_data['gallery'] ) ) {
                    // Display the first image
                    $images = $gallery_data['gallery'];
                    reset( $images );
                    $key = key( $images );
                    if ( is_numeric( $key ) ) {
                        $thumb = wp_get_attachment_image_src( $key, 'thumbnail' );
                    } else {
                        $thumb = array( $image['src'] );
                    }

                    echo '<img src="' . $thumb[0] . '" width="75" /><br />';
                    printf( _n( '%d Image', '%d Images', count( $gallery_data['gallery'] ), 'envira-gallery' ), count( $gallery_data['gallery'] ) );
                }
                break;

            /**
            * Shortcode
            */
            case 'shortcode' :
                echo '
                <div class="envira-code">
                    <code id="envira_shortcode_' . $post_id . '">[envira-gallery id="' . $post_id . '"]</code>
                    <a href="#" title="' . __( 'Copy Shortcode to Clipboard', 'envira-gallery' ) . '" data-clipboard-target="#envira_shortcode_' . $post_id . '" class="dashicons dashicons-clipboard envira-clipboard">
                        <span>' . __( 'Copy to Clipboard', 'envira-gallery' ) . '</span>
                    </a>
                </div>';

                // Hidden fields are for Quick Edit
                // class is used by assets/js/admin.js to remove these fields when a search is about to be submitted, so we dont' get long URLs
                echo '<input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[columns]" value="' . $this->metabox->get_config( 'columns' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[gallery_theme]" value="' . $this->metabox->get_config( 'gallery_theme' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[gutter]" value="' . $this->metabox->get_config( 'gutter' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[margin]" value="' . $this->metabox->get_config( 'margin' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[crop_width]" value="' . $this->metabox->get_config( 'crop_width' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[crop_height]" value="' . $this->metabox->get_config( 'crop_height' ) . '" />';
                break;

            /**
            * Posts
            */
            case 'posts':
                $posts = get_post_meta( $post_id, '_eg_in_posts', true );
                if ( is_array( $posts ) ) {
                    foreach ( $posts as $in_post_id ) {
                        echo '<a href="' . get_permalink( $in_post_id ) . '" target="_blank">' . get_the_title( $in_post_id ).'</a><br />';
                    }
                }
                break; 

            /**
            * Last Modified
            */
            case 'modified' :
                the_modified_date();
                break;
        }

    }
    
    /**
	 * Adds Envira fields to the quick editing and bulk editing screens
	 *
	 * @since 1.3.1
	 *
	 * @param string $column_name Column Name
	 * @param string $post_type Post Type
	 * @return HTML
	 */
    public function quick_edit_custom_box( $column_name, $post_type ) {

		// Check post type is Envira
		if ( 'envira' !== $post_type ) {
			return;
		}

        // Only apply to shortcode column
        //if ( 'shortcode' !== $column_name ) {
        //    return;
        //}

		// Get metabox instance
		$this->metabox = Envira_Gallery_Metaboxes::get_instance();

        // Depending on the column we're on, output some additional fields.
        switch ( $column_name ) {
            case 'shortcode':
                ?>
                <fieldset class="inline-edit-col-left inline-edit-envira-gallery">
                    <div class="inline-edit-col inline-edit-<?php echo $column_name ?>">
                        <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Number of Columns', 'envira-gallery'); ?></span>
                            <select name="_envira_gallery[columns]">
                                <?php foreach ( (array) $this->metabox->get_columns() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Gallery Theme', 'envira-gallery'); ?></span>
                            <select name="_envira_gallery[gallery_theme]">
                                <?php foreach ( (array) $this->metabox->get_gallery_themes() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <?php /* <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Column Gutter Width', 'envira-gallery'); ?></span>
                            <input type="number" name="_envira_gallery[gutter]" value="" />
                        </label>

                        <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Margin Below Each Image', 'envira-gallery'); ?></span>
                            <input type="number" name="_envira_gallery[margin]" value="" />
                            px
                        </label>

                        <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Image Dimensions', 'envira-gallery'); ?></span>
                            <input type="number" name="_envira_gallery[crop_width]" value="" />
                            x
                            <input type="number" name="_envira_gallery[crop_height]" value="" />
                            px
                        </label> */ ?>
                    </div>
                </fieldset>
                <?php
                break;
        }
			
		wp_nonce_field( 'envira-gallery', 'envira-gallery' );
		
    }

    /**
     * Adds Envira fields to the  bulk editing screens
     *
     * @since 1.3.1
     *
     * @param string $column_name Column Name
     * @param string $post_type Post Type
     * @return HTML
     */
    public function bulk_edit_custom_box( $column_name, $post_type ) {

        // Check post type is Envira
        if ( 'envira' !== $post_type ) {
            return;
        }

        // Only apply to shortcode column
        if ( 'shortcode' !== $column_name ) {
            return;
        }
        
        // Get metabox instance
        $this->metabox = Envira_Gallery_Metaboxes::get_instance();

        switch ( $column_name ) {
            case 'shortcode':
                ?>
                <fieldset class="inline-edit-col-left inline-edit-envira-gallery">
                    <div class="inline-edit-col inline-edit-<?php echo $column_name ?>">
                        <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Number of Columns', 'envira-gallery'); ?></span>
                            <select name="_envira_gallery[columns]">
                                <option value="-1" selected><?php _e( '— No Change —', 'envira-gallery' ); ?></option>
                                
                                <?php foreach ( (array) $this->metabox->get_columns() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Gallery Theme', 'envira-gallery'); ?></span>
                            <select name="_envira_gallery[gallery_theme]">
                                <option value="-1" selected><?php _e( '— No Change —', 'envira-gallery' ); ?></option>
                                
                                <?php foreach ( (array) $this->metabox->get_gallery_themes() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <?php /* <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Column Gutter Width', 'envira-gallery'); ?></span>
                            <input type="number" name="_envira_gallery[gutter]" value="" placeholder="<?php _e( '— No Change —', 'envira-gallery' ); ?>" />
                        </label>

                        <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Margin Below Each Image', 'envira-gallery'); ?></span>
                            <input type="number" name="_envira_gallery[margin]" value="" placeholder="<?php _e( '— No Change —', 'envira-gallery' ); ?>" />
                        </label>

                        <label class="inline-edit-group">
                            <span class="title"><?php _e( 'Image Dimensions', 'envira-gallery'); ?></span>
                            <input type="number" name="_envira_gallery[crop_width]" value="" placeholder="<?php _e( '— No Change —', 'envira-gallery' ); ?>" />
                            x
                            <input type="number" name="_envira_gallery[crop_height]" value="" placeholder="<?php _e( '— No Change —', 'envira-gallery' ); ?>" />
                            px
                        </label> */ ?>
                    </div>
                </fieldset>
                <?php
                break;
        }
            
        wp_nonce_field( 'envira-gallery', 'envira-gallery' );
        
    }
    
    /**
	* Called every time a WordPress Post is updated
	*
	* Checks to see if the request came from submitting the Bulk Editor form,
	* and if so applies the updates.  This is because there is no direct action
	* or filter fired for bulk saving
	*
	* @since 1.3.1
	*
	* @param int $post_ID Post ID
	*/
    public function bulk_edit_save( $post_ID ) {
	    
	    // Check we are performing a Bulk Edit
	    if ( !isset( $_REQUEST['bulk_edit'] ) ) {
		    return;
	    }
	    
	    // Bail out if we fail a security check.
        if ( ! isset( $_REQUEST['envira-gallery'] ) || ! wp_verify_nonce( $_REQUEST['envira-gallery'], 'envira-gallery' ) || ! isset( $_REQUEST['_envira_gallery'] ) ) {
            return;
        }
        
        // Check Post IDs have been submitted
        $post_ids = ( ! empty( $_REQUEST[ 'post' ] ) ) ? $_REQUEST[ 'post' ] : array();
		if ( empty( $post_ids ) || !is_array( $post_ids ) ) {
			return;
		}
		
		// Get metabox instance
		$this->metabox = Envira_Gallery_Metaboxes::get_instance();
	
		// Iterate through post IDs, updating settings
		foreach ( $post_ids as $post_id ) {
			// Get settings
	        $settings = get_post_meta( $post_id, '_eg_gallery_data', true );
	        if ( empty( $settings ) ) {
		        continue;
	        }
	        
	        // Update Settings, if they have values
	        if ( ! empty( $_REQUEST['_envira_gallery']['columns'] ) && $_REQUEST['_envira_gallery']['columns'] != -1 ) {
		        $settings['config']['columns']             = preg_replace( '#[^a-z0-9-_]#', '', $_REQUEST['_envira_gallery']['columns'] );
	        }
            if ( ! empty( $_REQUEST['_envira_gallery']['gallery_theme'] ) && $_REQUEST['_envira_gallery']['gallery_theme'] != -1 ) {
                $settings['config']['gallery_theme']       = preg_replace( '#[^a-z0-9-_]#', '', $_REQUEST['_envira_gallery']['gallery_theme'] );
            }
            if ( ! empty( $_REQUEST['_envira_gallery']['gutter'] ) ) {
                $settings['config']['gutter']       = absint( $_REQUEST['_envira_gallery']['gutter'] );
            }
            if ( ! empty( $_REQUEST['_envira_gallery']['margin'] ) ) {
                $settings['config']['margin']       = absint( $_REQUEST['_envira_gallery']['margin'] );
            }
            if ( ! empty( $_REQUEST['_envira_gallery']['crop_width'] ) ) {
                $settings['config']['crop_width']       = absint( $_REQUEST['_envira_gallery']['crop_width'] );
            }
            if ( ! empty( $_REQUEST['_envira_gallery']['crop_height'] ) ) {
                $settings['config']['crop_height']       = absint( $_REQUEST['_envira_gallery']['crop_height'] );
            }
	        
	        // Provide a filter to override settings.
			$settings = apply_filters( 'envira_gallery_bulk_edit_save_settings', $settings, $post_id );
			
			// Update the post meta.
			update_post_meta( $post_id, '_eg_gallery_data', $settings );
			
			// Finally, flush all gallery caches to ensure everything is up to date.
			$this->metabox->flush_gallery_caches( $post_id, $settings['config']['slug'] );

		}
	    
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.5.0
     *
     * @return object The Envira_Gallery_Table_Admin object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Table_Admin ) ) {
            self::$instance = new Envira_Gallery_Table_Admin();
        }

        return self::$instance;

    }

}

// Load the table admin class.
$envira_gallery_table_admin = Envira_Gallery_Table_Admin::get_instance();