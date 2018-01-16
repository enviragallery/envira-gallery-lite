<?php
/**
 * Addons class.
 *
 * @since 1.5.0
 *
 * @package Envira_Gallery
 * @author  David Bisset
 */
class Envira_Gallery_Addons {

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
     * Holds the submenu pagehook.
     *
     * @since 1.5.0
     *
     * @var string
     */
    public $hook;
	public $key;
    /**
     * Primary class constructor.
     *
     * @since 1.5.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery_Lite::get_instance();

        // Add custom addons submenu.
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );

        // Add callbacks for addons tabs.
        add_action( 'envira_gallery_addons_section', array( $this, 'addons_content' ) );

        // Add the addons menu item to the Plugins table.
        add_filter( 'plugin_action_links_' . plugin_basename( $this->base->file ), array( $this, 'addons_link' ) );

    }

    /**
     * Register the Addons submenu item for Envira.
     *
     * @since 1.5.0
     */
    public function admin_menu() {

        // Register the submenu.
        $this->hook = add_submenu_page(
            'edit.php?post_type=envira',
            __( 'Envira Gallery Addons', 'envira-gallery' ),
            '<span style="color:#7cc048"> ' . __( 'Addons', 'envira-gallery' ) . '</span>',
            apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
            $this->base->plugin_slug . '-addons',
            array( $this, 'addons_page' )
        );

        // If successful, load admin assets only on that page and check for addons refresh.
        if ( $this->hook ) {
            add_action( 'load-' . $this->hook, array( $this, 'maybe_refresh_addons' ) );
            add_action( 'load-' . $this->hook, array( $this, 'addons_page_assets' ) );
        }

    }

    /**
     * Maybe refreshes the addons page.
     *
     * @since 1.5.0
     *
     * @return null Return early if not refreshing the addons.
     */
    public function maybe_refresh_addons() {

        if ( ! $this->is_refreshing_addons() ) {
            return;
        }

        if ( ! $this->refresh_addons_action() ) {
            return;
        }

        $this->get_addons_data( $this->base->get_license_key() );

    }

    /**
     * Loads assets for the addons page.
     *
     * @since 1.5.0
     */
    public function addons_page_assets() {

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

    }

    /**
     * Register and enqueue addons page specific CSS.
     *
     * @since 1.5.0
     */
    public function enqueue_admin_styles() {

        wp_register_style( $this->base->plugin_slug . '-addons-style', plugins_url( 'assets/css/addons.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-addons-style' );

        // Run a hook to load in custom styles.
        do_action( 'envira_gallery_addons_styles' );

    }

    /**
     * Register and enqueue addons page specific JS.
     *
     * @since 1.5.0
     */
    public function enqueue_admin_scripts() {

        // List.js
        wp_register_script( $this->base->plugin_slug . '-list-script', plugins_url( 'assets/js/min/list-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-list-script' );

        // Addons
        wp_register_script( $this->base->plugin_slug . '-addons-script', plugins_url( 'assets/js/addons.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-addons-script' );
        wp_localize_script(
            $this->base->plugin_slug . '-addons-script',
            'envira_gallery_addons',
            array(
                'activate_nonce'      => wp_create_nonce( 'envira-gallery-activate' ),
                'active'           => __( 'Status: Active', 'envira-gallery' ),
                'activate'         => __( 'Activate', 'envira-gallery' ),
                'get_addons_nonce'   => wp_create_nonce( 'envira-gallery-get-addons' ),
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
        do_action( 'envira_gallery_addons_scripts' );

    }

    /**
     * Callback to output the Envira addons page.
     *
     * @since 1.5.0
     */
    public function addons_page() {

        do_action('envira_head');
        ?>

        <div id="addon-heading" class="subheading clearfix">
            <h1><?php _e( 'Envira Gallery Addons', 'envira-gallery' ); ?></h1>
            <form id="add-on-search">
                <span class="spinner"></span>
                <input id="add-on-searchbox" name="envira-addon-search" value="" placeholder="<?php _e( 'Search Envira Addons', 'envira-gallery' ); ?>" />
                <select id="envira-filter-select">
                    <option value="asc"><?php _e( 'Sort Ascending (A-Z)', 'envira-gallery' ); ?></option>
                    <option value="desc"><?php _e( 'Sort Descending (Z-A)', 'envira-gallery' ); ?></option>
                </select>
            </form>
        </div>

        <div id="envira-gallery-addons" class="wrap">
            <div class="envira-gallery envira-clear">
                <?php do_action( 'envira_gallery_addons_section' ); ?>
            </div>
        </div>
        <?php

    }

    /**
     * Callback for displaying the UI for Addons.
     *
     * @since 1.5.0
     */
    public function addons_content() {

        // If error(s) occured during license key verification, display them and exit now.
        if ( false !== $this->base->get_license_key_errors() ) {
            ?>
            <div class="error below-h2">
                <p>
                    <?php _e( 'In order to get access to Addons, you need to resolve your license key errors.', 'envira-gallery' ); ?>
                </p>
            </div>
            <?php
            return;
        }

        // Get Addons
        $addons = $this->get_addons();

        // If no Addon(s) were returned, our API call returned an error.
        // Show an error message with a button to reload the page, which will trigger another API call.
        if ( ! $addons ) {
            ?>
            <form id="envira-addons-refresh-addons-form" method="post">
                <p>
                    <?php _e( 'There was an issue retrieving the addons for this site. Please click on the button below the refresh the addons data.', 'envira-gallery' ); ?>
                </p>
                <p>
                    <a href="<?php echo $_SERVER['REQUEST_URI']; ?>" class="button button-primary"><?php _e( 'Refresh Addons', 'envira-gallery' ); ?></a>
                </p>
            </form>
            <?php
            return;
        }

        // If here, we have Addons to display, so let's output them now.
        // Get installed plugins and upgrade URL
        $installed_plugins = get_plugins();
        $upgrade_url = Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link();
        ?>
        <div id="envira-addons">
            <?php
            // Output Addons the User is licensed to use.
            if ( count( $addons['licensed'] )> 0 ) {
                ?>
                <div class="envira-addons-area licensed" class="envira-clear">
                    <h3><?php _e( 'Available Addons', 'envira-gallery' ); ?></h3>

                    <div id="envira-addons-licensed" class="envira-addons">
                        <!-- list container class required for list.js -->
                        <div class="list">
                            <?php
                            foreach ( (array) $addons['licensed'] as $i => $addon ) {
                                $this->get_addon_card( $addon, $i, true, $installed_plugins );
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            } // Close licensed addons

            // Output Addons the User isn't licensed to use.
            if ( count( $addons['unlicensed'] )> 0 ) {
                ?>
                <div class="envira-addons-area unlicensed" class="envira-clear">
                    <h3><?php _e( 'Unlock More Addons', 'envira-gallery' ); ?></h3>
                    <p><?php echo sprintf( __( '<strong>Want even more addons?</strong> <a href="%s">Upgrade your Envira Gallery account</a> and unlock the following addons.', 'envira-gallery' ), $upgrade_url ); ?></p>

                    <div id="envira-addons-unlicensed" class="envira-addons">
                        <!-- list container class required for list.js -->
                        <div class="list">
                            <?php
                            foreach ( (array) $addons['unlicensed'] as $i => $addon ) {
                                $this->get_addon_card( $addon, $i, false, $installed_plugins );
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            } // Close unlicensed addons
            ?>
        </div>
        <?php

    }

    /**
     * Retrieves addons from the stored transient or remote server.
     *
     * @since 1.5.0
     *
     * @return bool | array    false | Array of licensed and unlicensed Addons.
     */
    public function get_addons() {

        // Get license key and type.
        $key = $this->base->get_license_key();
        $type = $this->base->get_license_key_type();

        // Get addons data from transient or perform API query if no transient.
        //if ( false === ( $addons = get_transient( '_eg_addons' ) ) ) {
            $addons = $this->get_addons_data( $key );
        //}

        // If no Addons exist, return false
        if ( ! $addons ) {
            return false;
        }

        // Iterate through Addons, to build two arrays:
        // - Addons the user is licensed to use,
        // - Addons the user isn't licensed to use.
        $results = array(
            'licensed'  => array(),
            'unlicensed'=> array(),
        );
        foreach ( (array) $addons as $i => $addon ) {

            // Determine whether the user is licensed to use this Addon or not.
            if (
                empty( $type ) ||
                ( in_array( 'advanced', $addon->categories ) && $type != 'gold' && $type != 'platinum' ) ||
                ( in_array( 'basic', $addon->categories ) && ( $type != 'silver' && $type != 'gold' && $type != 'platinum' ) )
            ) {
                // Unlicensed
                $results['unlicensed'][] = $addon;
                continue;
            }

            // Licensed
            $results['licensed'][] = $addon;

        }

        // Return Addons, split by licensed and unlicensed.
        return $results;

    }

    /**
     * Pings the remote server for addons data.
     *
     * @since 1.5.0
     *
     * @param   string      $key    The user license key.
     * @return  array               Array of addon data otherwise.
     */
    public function get_addons_data( $key ) {
		
		$this->key = $key;
		
        // Get Addons
        // If the key is valid, we'll get personalised upgrade URLs for each Addon (if necessary) and plugin update information.
        $addons = $this->perform_remote_request( 'get-addons-data-v15', array( 'tgm-updater-key' => $key ) );

        // If there was an API error, set transient for only 10 minutes.
        if ( ! $addons ) {
            set_transient( '_eg_addons', false, 10 * MINUTE_IN_SECONDS );
            return false;
        }

        // If there was an error retrieving the addons, set the error.
        if ( isset( $addons->error ) ) {
            set_transient( '_eg_addons', false, 10 * MINUTE_IN_SECONDS );
            return false;
        }

        // Otherwise, our request worked. Save the data and return it.
        set_transient( '_eg_addons', $addons, DAY_IN_SECONDS );
        return $addons;

    }

    /**
     * Flag to determine if addons are being refreshed.
     *
     * @since 1.5.0
     *
     * @return bool True if being refreshed, false otherwise.
     */
    public function is_refreshing_addons() {

        return isset( $_POST['envira-gallery-refresh-addons-submit'] );

    }

    /**
     * Verifies nonces that allow addon refreshing.
     *
     * @since 1.5.0
     *
     * @return bool True if nonces check out, false otherwise.
     */
    public function refresh_addons_action() {

        return isset( $_POST['envira-gallery-refresh-addons-submit'] ) && wp_verify_nonce( $_POST['envira-gallery-refresh-addons'], 'envira-gallery-refresh-addons' );

    }

    /**
     * Retrieve the plugin basename from the plugin slug.
     *
     * @since 1.5.0
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
     * Add Addons page to plugin action links in the Plugins table.
     *
     * @since 1.5.0
     *
     * @param   array   $links    Default plugin action links.
     * @return  array   $links    Amended plugin action links.
     */
    public function addons_link( $links ) {

        $addons_link = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( array( 'post_type' => 'envira', 'page' => 'envira-gallery-lite-addons' ), admin_url( 'edit.php' ) ) ), __( 'Addons', 'envira-gallery' ) );
        array_unshift( $links, $addons_link );

        return $links;

    }

    /**
     * Outputs the addon "box" on the addons page.
     *
     * @since 1.5.0
     *
     * @param   object  $addon              Addon data from the API / transient call
     * @param   int     $counter            Index of this Addon in the collection
     * @param   bool    $is_licensed        Whether the Addon is licensed for use
     * @param   array   $installed_plugins  Installed WordPress Plugins
     */
    public function get_addon_card( $addon, $counter = 0, $is_licensed = false, $installed_plugins = false ) {

        // Setup some vars
        $plugin_basename   = $this->get_plugin_basename_from_slug( $addon->slug );
        $categories = implode( ',', $addon->categories );
        if ( ! $installed_plugins ) {
            $installed_plugins = get_plugins();
        }

        // If the Addon doesn't supply an upgrade_url key, it's because the user hasn't provided a license
        // get_upgrade_link() will return the Lite or Pro link as necessary for us.
        if ( ! isset( $addon->upgrade_url ) ) {
            $addon->upgrade_url = Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link();
        }

        // Output the card
        ?>
        <div class="envira-addon">
            <h3 class="envira-addon-title"><?php echo esc_html( $addon->title ); ?></h3>
            <?php
            if ( ! empty( $addon->image ) ) {
                ?>
                <img class="envira-addon-thumb" src="<?php echo esc_url( $addon->image ); ?>" alt="<?php echo esc_attr( $addon->title ); ?>" />
                <?php
            }
            ?>

            <p class="envira-addon-excerpt"><?php echo esc_html( $addon->excerpt ); ?></p>

            <?php
            // If the Addon is unlicensed, show the upgrade button
            if ( ! $is_licensed ) {
                ?>
                <div class="envira-addon-active envira-addon-message">
                    <div class="interior">
                        <div class="envira-addon-upgrade">
                            <a href="<?php echo esc_url( $addon->upgrade_url ); ?>" target="_blank" class="button button-primary envira-addon-upgrade-button"  rel="<?php echo esc_attr( $plugin_basename ); ?>">
                                <?php _e( 'Upgrade Now', 'envira-gallery' ); ?>
                            </a>
                            <span class="spinner envira-gallery-spinner"></span>
                        </div>
                    </div>
                </div>
                <?php
            } else {
                // Addon is licensed

                // If the plugin is not installed, display an install message and button.
                if ( ! isset( $installed_plugins[ $plugin_basename ] ) ) {
                    ?>
                    <div class="envira-addon-not-installed envira-addon-message">
                        <div class="interior">
                            <span class="addon-status"><?php _e( 'Status: <span>Not Installed</span>', 'envira-gallery' ); ?></span>
                            <div class="envira-addon-action">
                                <a class="button button-primary envira-addon-action-button envira-install-addon" href="#" rel="<?php echo esc_url( $addon->url ); ?>">
                                    <i class="envira-cloud-download"></i>
                                    <?php _e( 'Install', 'envira-gallery' ); ?>
                                </a>
                                <span class="spinner envira-gallery-spinner"></span>
                            </div>
                        </div>
                    </div>
                    <?php
                } else {
                    // Plugin is installed.
                    if ( is_plugin_active( $plugin_basename ) ) {
                        // Plugin is active. Display the active message and deactivate button.
                        ?>
                        <div class="envira-addon-active envira-addon-message">
                            <div class="interior">
                                <span class="addon-status"><?php _e( 'Status: <span>Active</span>', 'envira-gallery' ); ?></span>
                                <div class="envira-addon-action">
                                    <a class="button button-primary envira-addon-action-button envira-deactivate-addon" href="#" rel="<?php echo esc_attr( $plugin_basename ); ?>">
                                        <i class="envira-toggle-on"></i>
                                        <?php _e( 'Deactivate', 'envira-gallery' ); ?>
                                    </a>
                                    <span class="spinner envira-gallery-spinner"></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        // Plugin is inactivate. Display the inactivate mesage and activate button.
                        ?>
                        <div class="envira-addon-inactive envira-addon-message">
                            <div class="interior">
                                <span class="addon-status"><?php _e( 'Status: <span>Inactive</span>', 'envira-gallery' ); ?></span>
                                <div class="envira-addon-action">
                                    <a class="button button-primary envira-addon-action-button envira-activate-addon" href="#" rel="<?php echo esc_attr( $plugin_basename ); ?>">
                                        <i class="envira-toggle-on"></i>
                                        <?php _e( 'Activate', 'envira-gallery' ); ?>
                                    </a>
                                    <span class="spinner envira-gallery-spinner"></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>
        <?php

    }
    /**
     * Queries the remote URL via wp_remote_post and returns a json decoded response.
     *
     * @since 1.0.0
     *
     * @param string $action        The name of the $_POST action var.
     * @param array $body           The content to retrieve from the remote URL.
     * @param array $headers        The headers to send to the remote URL.
     * @param string $return_format The format for returning content from the remote URL.
     * @return string|bool          Json decoded response on success, false on failure.
     */
    public function perform_remote_request( $action, $body = array(), $headers = array(), $return_format = 'json' ) {

        // Build the body of the request.
        $body = wp_parse_args(
            $body,
            array(
                'tgm-updater-action'     => $action,
                'tgm-updater-key'        => $this->key,
                'tgm-updater-wp-version' => get_bloginfo( 'version' ),
                'tgm-updater-referer'    => site_url()
            )
        );
        $body = http_build_query( $body, '', '&' );

        // Build the headers of the request.
        $headers = wp_parse_args(
            $headers,
            array(
                'Content-Type'   => 'application/x-www-form-urlencoded',
                'Content-Length' => strlen( $body )
            )
        );

        // Setup variable for wp_remote_post.
        $post = array(
            'headers' => $headers,
            'body'    => $body
        );

        // Perform the query and retrieve the response.
        $response      = wp_remote_post( 'https://enviragallery.com', $post );
        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        // Bail out early if there are any errors.
        if ( 200 != $response_code || is_wp_error( $response_body ) ) {
            return false;
        }

        // Return the json decoded content.
        return json_decode( $response_body );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.5.0
     *
     * @return object The Envira_Gallery_Addons object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Addons ) ) {
            self::$instance = new Envira_Gallery_Addons();
        }

        return self::$instance;

    }

}

// Load the addons class.
$envira_gallery_addons = Envira_Gallery_Addons::get_instance();