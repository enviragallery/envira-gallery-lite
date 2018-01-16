<?php
/**
 * Settings class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Settings {

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
     * Holds the submenu pagehook.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $hook;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = ( class_exists( 'Envira_Gallery' ) ? Envira_Gallery::get_instance() : Envira_Gallery_Lite::get_instance() );

        // Only load the Settings submenu item and admin page if we're running Envira Gallery, and not Envira Gallery Lite.
        if ( class_exists( 'Envira_Gallery' ) ) {
            // Add custom settings submenu.
            add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );

            // Add callbacks for settings tabs.
            add_action( 'envira_gallery_tab_settings_general', array( $this, 'settings_general_tab' ) );

            // Add the settings menu item to the Plugins table.
            add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'envira-gallery.php' ), array( $this, 'settings_link' ) );
        }

    }

    /**
     * Register the Settings submenu item for Envira.
     *
     * @since 1.0.0
     */
    public function admin_menu() {

        // Register the submenu.
        $this->hook = add_submenu_page(
            'edit.php?post_type=envira',
            __( 'Envira Gallery Settings', 'envira-gallery' ),
            __( 'Settings', 'envira-gallery' ),
            apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
            $this->base->plugin_slug . '-settings',
            array( $this, 'settings_page' )
        );

        // If successful, load admin assets only on that page and check for addons refresh.
        if ( $this->hook ) {
            add_action( 'load-' . $this->hook, array( $this, 'maybe_fix_migration' ) );
            add_action( 'load-' . $this->hook, array( $this, 'update_image_settings' ) );
            add_action( 'load-' . $this->hook, array( $this, 'settings_page_assets' ) );
        }

    }

    /**
     * Maybe fixes the broken migration.
     *
     * @since 1.3.1.6
     *
     * @return null Return early if not fixing the broken migration
     */
    public function maybe_fix_migration() {
        
        // Check if user pressed 'Fix' button and nonce is valid
        if ( ! isset( $_POST['envira-serialization-submit'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['envira-serialization-nonce'], 'envira-serialization-nonce' ) ) {
            return;
        }
        
        // If here, fix potentially broken migration
        // Get WPDB and serialization class
        global $wpdb, $fixedGalleries;
        require plugin_dir_path( __FILE__ ) . 'serialization.php';
        $instance = Envira_Serialization_Admin::get_instance();
         
        // Keep count of the number of galleries that get fixed
        $fixedGalleries = 0;
        
        // Query to get all Envira CPTs
        $galleries = new WP_Query( array (
            'post_type'     => 'envira',
            'post_status'   => 'any',
            'posts_per_page'=> -1, 
        ) );
        
        // Iterate through galleries
        if ( $galleries->posts ) {
            foreach ( $galleries->posts as $gallery ) {

                $fixed = false;
                
                // Attempt to get gallery data
                $gallery_data = get_post_meta( $gallery->ID, '_eg_gallery_data', true );

                // Skip empty galleries
                if ( empty( $gallery_data ) ) {
                    continue;
                }

                // If gallery data isn't an array, something broke
                if ( ! is_array( $gallery_data ) ) { 
                    // Need to fix the broken serialized string for this gallery
                    // Get raw string from DB
                    $query = $wpdb->prepare( "  SELECT meta_value
                                                FROM ".$wpdb->prefix."postmeta
                                                WHERE post_id = %d
                                                AND meta_key = %s
                                                LIMIT 1",
                                                $gallery->ID,
                                                '_eg_gallery_data' );
                    $raw_gallery_data = $wpdb->get_row( $query );

                    // Do the fix, which returns an unserialized array
                    $gallery_data = $instance->fix_serialized_string( $raw_gallery_data->meta_value );

                    // Check we now have an array of unserialized data
                    if ( ! is_array ( $gallery_data ) ) {
                        continue;
                    }

                    // Mark as fixed
                    $fixed = true;
                }

                // Next, check each gallery image has a valid URL
                // Some migrations seem to strip the leading HTTP URL, which causes us problems with thumbnail generation.
                if ( isset( $gallery_data['gallery'] ) ) {
                    foreach ( $gallery_data['gallery'] as $id => $item ) {
                        // Source
                        if ( isset( $item['src'] ) ) {
                            if ( ! empty( $item['src'] ) && ! filter_var( $item['src'], FILTER_VALIDATE_URL ) ) {
                                // Image isn't a valid URL - fix
                                $gallery_data['gallery'][ $id ]['src'] = get_bloginfo( 'url' ) . '/' . $item['src'];
                                $fixed = true;
                            }
                        }

                        // Link
                        if ( isset( $item['link'] ) ) {
                            if ( ! empty( $item['link'] ) && ! filter_var( $item['link'], FILTER_VALIDATE_URL ) ) {
                                // Image isn't a valid URL - fix
                                $gallery_data['gallery'][ $id ]['link'] = get_bloginfo( 'url' ) . '/' . $item['link'];
                                $fixed = true;
                            }
                        }

                        // Thumbnail
                        if ( isset( $item['thumb'] ) ) {
                            if ( ! empty( $item['thumb'] ) && ! filter_var( $item['thumb'], FILTER_VALIDATE_URL ) ) {
                                // Thumbnail isn't a valid URL - fix
                                $gallery_data['gallery'][ $id ]['thumb'] = get_bloginfo( 'url' ) . '/' . $item['thumb'];
                                $fixed = true;
                            }
                        }
                    }
                }
                
                // Finally, store the post meta if a fix was applied
                if ( $fixed ) {
                    update_post_meta( $gallery->ID, '_eg_gallery_data', $gallery_data );
                    $fixedGalleries++;
                }

            }
        }
        
        // Output an admin notice so the user knows what happened
        add_action( 'envira_gallery_settings_general_tab_notice', array( $this, 'fixed_migration' ) );
        
    }

    /**
     * Outputs a WordPress style notification to tell the user how many galleries were
     * fixed after running the migration fixer
     *
     * @since 1.3.1.6
     */
    public function fixed_migration() {
        global $fixedGalleries;
        
        ?>
        <div class="notice updated below-h2">
            <p><strong><?php echo $fixedGalleries . __( ' galleries(s) fixed successfully.', 'envira-gallery' ); ?></strong></p>
        </div>
        <?php
            
    }

    /**
     * Saves images Settings:
     * - Add New Images
     * - Delete Images on Gallery Deletion
     *
     * @since 1.3.3.6
     *
     * @return null Return early if not fixing the broken migration
     */
    public function update_image_settings() {

        // Check if user pressed the 'Update' button and nonce is valid
        if ( ! isset( $_POST['envira-gallery-settings-submit'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['envira-gallery-settings-nonce'], 'envira-gallery-settings-nonce' ) ) {
            return;
        }

        // Update settings
        $this->update_setting( 'media_position', $_POST['envira_media_position'] );
        $this->update_setting( 'image_delete', $_POST['envira_image_delete'] );
        $this->update_setting( 'media_delete', $_POST['envira_media_delete'] );

        // Output an admin notice so the user knows what happened
        add_action( 'envira_gallery_settings_general_tab_notice', array( $this, 'updated_settings' ) );

    }

    /**
     * Helper method for updating a setting's value.
     *
     * @since 1.3.3.6
     *
     * @param string $key   The setting key
     * @param string $value The value to set for the key
     * @return null
     */
    public function update_setting( $key, $value ) {

        // Prefix the key
        $key = 'envira_gallery_' . $key;

        // Allow devs to filter
        $value = apply_filters( 'envira_gallery_get_setting', $value, $key );

        // Update option
        update_option( $key, $value );

    }

    /**
     * Helper method for getting a setting's value. Falls back to the default
     * setting value if none exists in the options table.
     *
     * @since 1.3.3.6
     *
     * @param string $key   The setting key to retrieve.
     * @return string       Key value on success, false on failure.
     */
    public function get_setting( $key ) {

        // Prefix the key
        $prefixed_key = 'envira_gallery_' . $key;

        // Get the option value
        $value = get_option( $prefixed_key );

        // If no value exists, fallback to the default
        if ( ! $value ) {
            $value = $this->get_setting_default( $key );
        }

        // Allow devs to filter
        $value = apply_filters( 'envira_gallery_get_setting', $value, $key, $prefixed_key );

        return $value;

    }

    /**
     * Helper method for getting a setting's default value
     *
     * @since 1.3.3.6
     *
     * @param string $key   The default setting key to retrieve.
     * @return string       Key value on success, false on failure.
     */
    public function get_setting_default( $key ) {

        // Prepare default values.
        $defaults = $this->get_setting_defaults();

        // Return the key specified.
        return isset( $defaults[ $key ] ) ? $defaults[ $key ] : false;

    }

    /**
     * Retrieves the default settings
     *
     * @since 1.3.3.6
     *
     * @return array       Array of default settings.
     */
    public function get_setting_defaults() {

        // Prepare default values.
        $defaults = array(
            'media_position' => 'after',
        );

        // Allow devs to filter the defaults.
        $defaults = apply_filters( 'envira_gallery_settings_defaults', $defaults );
        
        return $defaults;

    }

    /**
     * Outputs a WordPress style notification to tell the user settings were saved
     *
     * @since 1.3.3.6
     */
    public function updated_settings() {
        
        ?>
        <div class="notice updated below-h2">
            <p><strong><?php _e( 'Settings saved successfully.', 'envira-gallery' ); ?></strong></p>
        </div>
        <?php
            
    }

    /**
     * Loads assets for the settings page.
     *
     * @since 1.0.0
     */
    public function settings_page_assets() {

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

    }

    /**
     * Register and enqueue settings page specific CSS.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_styles() {

        wp_register_style( $this->base->plugin_slug . '-settings-style', plugins_url( 'assets/css/settings.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-settings-style' );

        // Run a hook to load in custom styles.
        do_action( 'envira_gallery_settings_styles' );

    }

    /**
     * Register and enqueue settings page specific JS.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_scripts() {

        // Tabs
        wp_register_script( $this->base->plugin_slug . '-tabs-script', plugins_url( 'assets/js/tabs.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-tabs-script' );

        // Settings
        wp_register_script( $this->base->plugin_slug . '-settings-script', plugins_url( 'assets/js/settings.js', $this->base->file ), array( 'jquery', 'jquery-ui-tabs' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-settings-script' );
        wp_localize_script(
            $this->base->plugin_slug . '-settings-script',
            'envira_gallery_settings',
            array(
                'active'           => __( 'Status: Active', 'envira-gallery' ),
                'activate'         => __( 'Activate', 'envira-gallery' ),
                'activate_nonce'   => wp_create_nonce( 'envira-gallery-activate' ),
                'activating'       => __( 'Activating...', 'envira-gallery' ),
                'ajax'             => admin_url( 'admin-ajax.php' ),
                'deactivate'       => __( 'Deactivate', 'envira-gallery' ),
                'deactivate_nonce' => wp_create_nonce( 'envira-gallery-deactivate' ),
                'deactivating'     => __( 'Deactivating...', 'envira-gallery' ),
                'inactive'         => __( 'Status: Inactive', 'envira-gallery' ),
                'install'          => __( 'Install', 'envira-gallery' ),
                'install_nonce'    => wp_create_nonce( 'envira-gallery-install' ),
                'installing'       => __( 'Installing...', 'envira-gallery' ),
                'proceed'          => __( 'Proceed', 'envira-gallery' )
            )
        );

        // Run a hook to load in custom scripts.
        do_action( 'envira_gallery_settings_scripts' );

    }

    /**
     * Callback to output the Envira settings page.
     *
     * @since 1.0.0
     */
    public function settings_page() {

        do_action('envira_head');

        ?>

        <!-- Tabs -->
        <h1 id="envira-tabs-nav" class="envira-tabs-nav" data-container="#envira-gallery-settings" data-update-hashbang="1">
            <?php 
            $i = 0; 
            foreach ( (array) $this->get_envira_settings_tab_nav() as $id => $title ) {
                $class = ( 0 === $i ? 'envira-active' : '' ); 
                ?>
                <a class="nav-tab <?php echo $class; ?>" href="#envira-tab-<?php echo $id; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
                <?php 
                $i++; 
            }
            ?>
        </h1>

        <!-- Tab Panels -->
        <div id="envira-gallery-settings" class="wrap">
            <h1 class="envira-hideme"></h1>
            <div class="envira-gallery envira-clear">
                <div id="envira-tabs" class="envira-clear" data-navigation="#envira-tabs-nav">
                    <?php 
                    $i = 0; 
                    foreach ( (array) $this->get_envira_settings_tab_nav() as $id => $title ) {
                        $class = ( 0 === $i ? 'envira-active' : '' ); 
                        ?>
                        <div id="envira-tab-<?php echo $id; ?>" class="envira-tab envira-clear <?php echo $class; ?>">
                            <?php do_action( 'envira_gallery_tab_settings_' . $id ); ?>
                        </div>
                        <?php
                        $i++;
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php

    }

    /**
     * Callback for getting all of the settings tabs for Envira.
     *
     * @since 1.0.0
     *
     * @return array Array of tab information.
     */
    public function get_envira_settings_tab_nav() {

        $tabs = array(
            'general' => __( 'General', 'envira-gallery' ), // This tab is required. DO NOT REMOVE VIA FILTERING.
        );
        $tabs = apply_filters( 'envira_gallery_settings_tab_nav', $tabs );

        return $tabs;

    }

    /**
     * Callback for displaying the UI for general settings tab.
     *
     * @since 1.0.0
     */
    public function settings_general_tab() {

        // Get settings
        $media_position = $this->get_setting( 'media_position' );
        $image_delete = $this->get_setting( 'image_delete' );
        $media_delete = $this->get_setting( 'media_delete' );
        ?>
        <div id="envira-settings-general">
            <?php 
            // Output any notices now
            do_action( 'envira_gallery_settings_general_tab_notice' );
            ?>

            <table class="form-table">
                <tbody>
                    <tr id="envira-settings-key-box">
                        <th scope="row">
                            <label for="envira-settings-key"><?php _e( 'Envira License Key', 'envira-gallery' ); ?></label>
                        </th>
                        <td>
                            <form id="envira-settings-verify-key" method="post">
                                <input type="password" name="envira-license-key" id="envira-settings-key" value="<?php echo ( $this->base->get_license_key() ? $this->base->get_license_key() : '' ); ?>" />
                                <?php wp_nonce_field( 'envira-gallery-key-nonce', 'envira-gallery-key-nonce' ); ?>
                                <?php submit_button( __( 'Verify Key', 'envira-gallery' ), 'primary', 'envira-gallery-verify-submit', false ); ?>
                                <?php submit_button( __( 'Deactivate Key', 'envira-gallery' ), 'secondary', 'envira-gallery-deactivate-submit', false ); ?>
                                <p class="description"><?php _e( 'License key to enable automatic updates for Envira.', 'envira-gallery' ); ?></p>
                            </form>
                        </td>
                    </tr>
                    <?php $type = $this->base->get_license_key_type(); if ( ! empty( $type ) ) : ?>
                    <tr id="envira-settings-key-type-box">
                        <th scope="row">
                            <label for="envira-settings-key-type"><?php _e( 'Envira License Key Type', 'envira-gallery' ); ?></label>
                        </th>
                        <td>
                            <form id="envira-settings-key-type" method="post">
                                <span class="envira-license-type"><?php printf( __( 'Your license key type for this site is <strong>%s.</strong>', 'envira-gallery' ), $this->base->get_license_key_type() ); ?>
                                <input type="hidden" name="envira-license-key" value="<?php echo $this->base->get_license_key(); ?>" />
                                <?php wp_nonce_field( 'envira-gallery-key-nonce', 'envira-gallery-key-nonce' ); ?>
                                <?php submit_button( __( 'Refresh Key', 'envira-gallery' ), 'primary', 'envira-gallery-refresh-submit', false ); ?>
                                <p class="description"><?php _e( 'Your license key type (handles updates and Addons). Click refresh if your license has been upgraded or the type is incorrect.', 'envira-gallery' ); ?></p>
                            </form>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!-- Fix Broken Migration -->
                    <tr id="envira-serialization-box">
                        <th scope="row">
                            <label for="envira-serialization"><?php _e( 'Fix Broken Migration', 'envira-gallery' ); ?></label>
                        </th>
                        <td>
                            <form id="envira-serialization" method="post">
                                <?php wp_nonce_field( 'envira-serialization-nonce', 'envira-serialization-nonce' ); ?>
                                <?php submit_button( __( 'Fix', 'envira-gallery' ), 'primary', 'envira-serialization-submit', false ); ?>
                                <p class="description"><?php _e( 'If you have changed the URL of your WordPress web site, and manually executed a search/replace query on URLs in your WordPress database, your galleries will probably no longer show any images.  If this is the case, click the button above to fix this. We recommend using a migration plugin or script next time :)', 'envira-gallery' ); ?></p>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- <hr /> -->

            <!-- Settings Form -->
            <form id="envira-media-delete" method="post">
                <table class="form-table">
                    <tbody>
                        <!-- Media Position -->
                        <tr id="envira-media-position-box">
                            <th scope="row">
                                <label for="envira-media-position"><?php _e( 'Add New Images', 'envira-gallery' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-media-position" name="envira_media_position">
                                    <?php foreach ( (array) Envira_Gallery_Common::get_instance()->get_media_positions() as $i => $data ) : ?>
                                        <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $media_position ); ?>><?php echo $data['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e( 'When adding media to a Gallery, choose whether to add this media before or after any existing images.', 'envira-gallery' ); ?></p>
                            </td>
                        </tr>

                        <!-- Delete Media -->
                        <tr id="envira-image-delete-box">
                            <th scope="row">
                                <label for="envira-image-delete"><?php _e( 'Delete Image on Gallery Image Deletion', 'envira-gallery' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-image-delete" name="envira_image_delete">
                                    <?php foreach ( (array) Envira_Gallery_Common::get_instance()->get_media_delete_options() as $i => $data ) : ?>
                                        <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $image_delete ); ?>><?php echo $data['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e( 'When deleting an Image from a Gallery, choose whether to delete all media associated with that image. Note: If image(s) in the Media Library are attached to other Posts, they will not be deleted.', 'envira-gallery' ); ?></p>
                            </td>
                        </tr>
                        
                        <tr id="envira-media-delete-box">
                            <th scope="row">
                                <label for="envira-media-delete"><?php _e( 'Delete Images on Gallery Deletion', 'envira-gallery' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-media-delete" name="envira_media_delete">
                                    <?php foreach ( (array) Envira_Gallery_Common::get_instance()->get_media_delete_options() as $i => $data ) : ?>
                                        <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $media_delete ); ?>><?php echo $data['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e( 'When deleting a Gallery, choose whether to delete all media associated with the gallery. Note: If image(s) in the Media Library are attached to other Posts, they will not be deleted.', 'envira-gallery' ); ?></p>
                            </td>
                        </tr>

                        <?php do_action( 'envira_gallery_settings_general_box' ); ?>
                    </tbody>
                </table>

                <?php wp_nonce_field( 'envira-gallery-settings-nonce', 'envira-gallery-settings-nonce' ); ?>
                <?php submit_button( __( 'Save Settings', 'envira-gallery' ), 'primary', 'envira-gallery-settings-submit', false ); ?>
            </form>
        </div>
        <?php

    }

   /**
     * Retrieve the plugin basename from the plugin slug.
     *
     * @since 1.0.0
     *
     * @param string $slug The plugin slug.
     * @return string      The plugin basename if found, else the plugin slug.
     */
    public function get_plugin_basename_from_slug( $slug ) {

        $keys = array_keys( get_plugins() );

        foreach ( $keys as $key ) {
            if ( preg_match( '|^' . $slug . '|', $key ) ) {
                return $key;
            }
        }

        return $slug;

    }

    /**
     * Add Settings page to plugin action links in the Plugins table.
     *
     * @since 1.0.0
     *
     * @param array $links  Default plugin action links.
     * @return array $links Amended plugin action links.
     */
    public function settings_link( $links ) {

        $settings_link = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( array( 'post_type' => 'envira', 'page' => 'envira-gallery-settings' ), admin_url( 'edit.php' ) ) ), __( 'Settings', 'envira-gallery' ) );
        array_unshift( $links, $settings_link );

        return $links;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Settings object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Settings ) ) {
            self::$instance = new Envira_Gallery_Settings();
        }

        return self::$instance;

    }

}

// Load the settings class.
$envira_gallery_settings = Envira_Gallery_Settings::get_instance();