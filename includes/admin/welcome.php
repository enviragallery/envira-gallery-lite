<?php
/**
 * Welcome class.
 *
 * @since 1.8.1
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team
 */

// namespace Envira\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Welcome Class
 *
 * @since 1.7.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */
class Envira_Welcome {

	/**
	 * Holds the submenu pagehook.
	 *
	 * @since 1.7.0
	 *
	 * @var string`
	 */
	public $hook;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.1
	 */
	public function __construct() {

		if ( ( defined( 'ENVIRA_WELCOME_SCREEN' ) && false === ENVIRA_WELCOME_SCREEN ) || apply_filters( 'envira_whitelabel', false ) === true ) {
			return;
		}

		// Add custom addons submenu.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 15 );

		// Add custom CSS class to body.
		add_filter( 'admin_body_class', array( $this, 'admin_welcome_css' ), 15 );

		// Add scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_head', array( $this, 'envira_menu_styles' ) );

		// Misc.
		add_action( 'admin_print_scripts', array( $this, 'disable_admin_notices' ) );

		//echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/docs/how-to-configure-your-gallery-settings", "whatsnewtab", "checkoutourchangelog", "envira-changelog" ); exit;

	}

	/**
	 * Add custom CSS to admin body tag.
	 *
	 * @since 1.8.1
	 * @param array $classes CSS Classes.
	 * @return array
	 */
	public function admin_welcome_css( $classes ) {

		if ( ! is_admin() ) {
			return;
		}

		$classes .= ' envira-welcome-enabled ';

		return $classes;

	}

	/**
	 * Register and enqueue addons page specific CSS.
	 *
	 * @since 1.8.1
	 */
	public function enqueue_admin_styles() {

		$welcome_pages = array( 'envira-gallery-lite-get-started', 'envira-gallery-lite-welcome', 'envira-gallery-lite-support', 'envira-gallery-lite-welcome-addons', 'envira-gallery-lite-changelog', 'envira-gallery-lite-upgrade', 'envira-gallery-lite-litevspro' );

		if ( isset( $_GET['post_type'] ) && isset( $_GET['page'] ) && 'envira' === wp_unslash( $_GET['post_type'] ) && in_array( wp_unslash( $_GET['page'] ), $welcome_pages ) ) { // @codingStandardsIgnoreLine

			wp_register_style( ENVIRA_SLUG . '-welcome-style', plugins_url( 'assets/css/welcome.css', ENVIRA_FILE ), array(), ENVIRA_VERSION );
			wp_enqueue_style( ENVIRA_SLUG . '-welcome-style' );

			// wp_register_style( ENVIRA_SLUG . '-addons-style', plugins_url( 'assets/css/addons.css', ENVIRA_FILE ), array(), ENVIRA_VERSION );
			// wp_enqueue_style( ENVIRA_SLUG . '-addons-style' );

		}

        // Run a hook to load in custom styles.
        do_action( 'envira_gallery_addons_styles' );

	}

	/**
	 * Add custom CSS to block out certain menu items ONLY when welcome screen is activated.
	 *
	 * @since 1.8.1
	 */
	public function envira_menu_styles() { 

		if ( is_admin() ) {

		?>

			<style>

			/* ==========================================================================
			Menu
			========================================================================== */
			li#menu-posts-envira ul li:last-child,
			li#menu-posts-envira ul li:nth-last-child(2),
			li#menu-posts-envira ul li:nth-last-child(3),
			li#menu-posts-envira ul li:nth-last-child(4) {
				display: none;
			}

			</style>

		<?php

		}

	}



	/**
	 * Making page as clean as possible
	 *
	 * @since 1.8.1
	 */
	public function disable_admin_notices() {

		global $wp_filter;

		$welcome_pages = array( 'envira-gallery-lite-get-started', 'envira-gallery-lite-welcome', 'envira-gallery-lite-support', 'envira-gallery-lite-changelog', 'envira-gallery-lite-upgrade' );

		if ( isset( $_GET['post_type'] ) && isset( $_GET['page'] ) && 'envira' === wp_unslash( $_GET['post_type'] ) && in_array( wp_unslash( $_GET['page'] ), $welcome_pages ) ) { // @codingStandardsIgnoreLine

			if ( isset( $wp_filter['user_admin_notices'] ) ) {
				unset( $wp_filter['user_admin_notices'] );
			}
			if ( isset( $wp_filter['admin_notices'] ) ) {
				unset( $wp_filter['admin_notices'] );
			}
			if ( isset( $wp_filter['all_admin_notices'] ) ) {
				unset( $wp_filter['all_admin_notices'] );
			}
		}

	}

	/**
	 * Register the Welcome submenu item for Envira.
	 *
	 * @since 1.8.1
	 */
	public function admin_menu() {
		$whitelabel = apply_filters( 'envira_whitelabel', false ) ? '' : __( 'Envira Gallery ', 'envira-gallery-lite' );
		// Register the submenus.
		add_submenu_page(
			'edit.php?post_type=envira',
			$whitelabel . __( 'Get Started', 'envira-gallery-lite' ),
			'<span style="color:#FFA500"> ' . __( 'Get Started', 'envira-gallery-lite' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-get-started',
			array( $this, 'help_page' )
		);

		add_submenu_page(
			'edit.php?post_type=envira',
			$whitelabel . __( 'Upgrade Envira Gallery', 'envira-gallery-lite' ),
			'<span style="color:#FFA500"> ' . __( 'Upgrade Envira Gallery', 'envira-gallery-lite' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-upgrade',
			array( $this, 'upgrade_page' )
		);

		add_submenu_page(
			'edit.php?post_type=envira',
			$whitelabel . __( 'Lite vs Pro', 'envira-gallery-lite' ),
			'<span style="color:#FFA500"> ' . __( 'Lite vs Pro', 'envira-gallery-lite' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-litevspro',
			array( $this, 'lite_vs_pro_page' )
		);
	
		add_submenu_page(
			'edit.php?post_type=envira',
			$whitelabel . __( 'Welcome', 'envira-gallery-lite' ),
			'<span style="color:#FFA500"> ' . __( 'Welcome', 'envira-gallery-lite' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-welcome',
			array( $this, 'welcome_page' )
		);

		add_submenu_page(
			'edit.php?post_type=envira',
			$whitelabel . __( 'Support', 'envira-gallery-lite' ),
			'<span style="color:#FFA500"> ' . __( 'Support', 'envira-gallery-lite' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support',
			array( $this, 'support_page' )
		); 

	}

	/**
	 * Output welcome text and badge for What's New and Credits pages.
	 *
	 * @since 1.8.1
	 */
	public static function welcome_text() {

		// Switch welcome text based on whether this is a new installation or not.
		$welcome_text = ( self::is_new_install() )
			? esc_html( 'Thank you for installing Envira Lite! Envira provides great gallery features for your WordPress site!', 'envira-gallery-lite' )
			: esc_html( 'Thank you for updating! Envira Lite %s has many recent improvements that you will enjoy.', 'envira-gallery-lite' );

		?>
		<?php /* translators: %s: version */ ?>
		<h1 class="welcome-header"><?php printf( esc_html__( 'Welcome to %1$s Envira Gallery Lite %2$s', 'envira-gallery-lite' ), '<span class="envira-leaf"></span>&nbsp;', esc_html( self::display_version() ) ); ?></h1>

		<div class="about-text">
			<?php
			if ( self::is_new_install() ) {
				echo esc_html( $welcome_text );
			} else {
				printf( $welcome_text, self::display_version() ); // @codingStandardsIgnoreLine
			}
			?>
		</div>

		<?php
	}

	/**
	 * Output tab navigation
	 *
	 * @since 2.2.0
	 *
	 * @param string $tab Tab to highlight as active.
	 */
	public static function tab_navigation( $tab = 'whats_new' ) {
		?>

		<h3 class="nav-tab-wrapper">
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-lite-welcome' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : // @codingStandardsIgnoreLine
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							array(
								'post_type' => 'envira',
								'page'      => 'envira-gallery-lite-welcome',
							),
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'What&#8217;s New', 'envira-gallery-lite' ); ?>
			</a>
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-lite-get-started' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : // @codingStandardsIgnoreLine
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							array(
								'post_type' => 'envira',
								'page'      => 'envira-gallery-lite-get-started',
							),
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'Get Started', 'envira-gallery-lite' ); ?>
			</a>
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-lite-litevspro' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : // @codingStandardsIgnoreLine
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							array(
								'post_type' => 'envira',
								'page'      => 'envira-gallery-lite-litevspro',
							),
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'Lite vs Pro', 'envira-gallery-lite' ); ?>
			</a>
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-lite-upgrade' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : // @codingStandardsIgnoreLine
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							array(
								'post_type' => 'envira',
								'page'      => 'envira-gallery-lite-upgrade',
							),
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'Upgrade Envira Gallery', 'envira-gallery-lite' ); ?>
			</a>

		</h3>

		<?php
	}

	/**
	 * Output the sidebar.
	 *
	 * @since 1.8.5
	 */
	public function sidebar() {

		global $wp_version;

		if ( isset( $_GET['page'] ) && $_GET['page'] === 'envira-gallery-lite-litevspro' ) {
			return;
		}

		?>

			<div class="envira-welcome-sidebar">

				<?php

				if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {

					?>

					<div class="sidebox warning php-warning">

					<h4><?php esc_html_e( 'Please Upgrade Your PHP Version!', 'envira-gallery-lite' ); ?></h4>
					<p><?php echo wp_kses( 'Your hosting provider is using PHP <strong>' . PHP_VERSION . '</strong>, an outdated and unsupported version. Soon Envira Gallery will need a minimum of PHP <strong>5.6</strong>.', wp_kses_allowed_html( 'post' ) ); ?></p>
					<a target="_blank" href="https://enviragallery.com/docs/update-php" class="button button-primary">Learn More</a>

					</div>

				<?php } ?>

				<?php

				if ( ! empty( $wp_version ) && version_compare( $wp_version, '4.8', '<' ) ) {

					?>

				<div class="sidebox warning php-warning">

					<h4><?php esc_html_e( 'Please Upgrade Your WordPress Version!', 'envira-gallery-lite' ); ?></h4>
					<p><?php echo wp_kses( 'You are currently using WordPress <strong>' . $wp_version . '</strong>, an outdated version. Soon Envira Gallery will need a minimum of WordPress <strong>4.8</strong>.', wp_kses_allowed_html( 'post' ) ); ?></p>
					<a target="_blank" href="https://enviragallery.com/docs/update-wordpress" class="button button-primary">Learn More</a>

				</div>

				<?php } ?>

				<?php

				if ( class_exists( 'Envira_Gallery' ) && envira_get_license_key() === false ) {

					?>

				<div class="sidebox">
					<form id="envira-settings-verify-key" method="post" action="<?php echo esc_url( admin_url( 'edit.php?post_type=envira&page=envira-gallery-settings' ) ); ?>">
						<h4><?php esc_html_e( 'Activate License Key', 'envira-gallery-lite' ); ?></h4>
						<p><?php esc_html_e( 'License key to enable automatic updates for Envira. License key to enable automatic updates for Envira. ', 'send-system-info' ); ?></p>
						<input type="password" name="envira-license-key" id="envira-settings-key" value="" />
						<?php wp_nonce_field( 'envira-gallery-key-nonce', 'envira-gallery-key-nonce' ); ?>
						<?php submit_button( __( 'Verify Key', 'envira-gallery-lite' ), 'primary', 'envira-gallery-verify-submit', false ); ?>
					</form>
				</div>

					<?php

				}
				?>
				<?php

				$url = 'https://wordpress.org/support/plugin/envira-gallery-lite/reviews/';

				?>
					<div class="sidebox">

							<h4><?php esc_html_e( 'We Need Your Help', 'envira-gallery-lite' ); ?></h4>
							<?php /* translators: %1$s: url, %2$s url */ ?>
							<p><?php echo sprintf( __( 'Please rate <strong>Envira Gallery</strong> <a href="%1$s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%2$s" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the Envira Gallery team!', 'envira-gallery-lite' ), esc_url( $url ), esc_url( $url ) ); // @codingStandardsIgnoreLine ?></p>
							<a target="_blank" href="<?php echo esc_url( $url ); ?>" class="button button-primary">Rate It</a>

					</div>
				<div class="sidebox">
					<form action="https://enviragallery.us3.list-manage.com/subscribe/post?u=beaa9426dbd898ac91af5daca&amp;id=2ee2b5572e" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
						<h4><?php esc_html_e( 'Join 58,709 web developers, photographers and artists who already have a head start.', 'send-system-info' ); ?></h4>
						<p><?php esc_html_e( 'Get free tips and resources on how to get the most out of Envira Gallery and WordPress delivered directly to your inbox.', 'send-system-info' ); ?></p>
						<div class="form-row"><input type="text" value="" name="FNAME" placeholder="Name" id="mce-FNAME"></div>
						<div class="form-row"><input type="email" placeholder="Email" name="EMAIL" required /></div>

						<input type="submit" class="button button-primary" value="Sign Up" />
					</form>
				</div>
			</div>


		<?php
	}

	/**
	 * Output the about screen.
	 *
	 * @since 1.8.5
	 */
	public function welcome_page() {
		?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="envira-title">

					<?php self::welcome_text(); ?>

				</div>

				<div class="envira-welcome-main">

					<?php self::tab_navigation( __METHOD__ ); ?>

					<div class="envira-welcome-panel">

						<div class="wraps about-wsrap">

							<div class="envira-recent-section">

								<h3 class="headline-title"><?php esc_html_e( 'Envira Gallery is the most beginner-friendly drag &amp; drop WordPress gallery plugin.', 'envira-gallery-lite' ); ?></h3>

								<h3 class="title"><?php esc_html_e( 'Recent Updates To Envira Lite:', 'envira-gallery-lite' ); ?></h3>

								<div class="envira-recent envirathree-column">
									<div class="enviracolumn">
											<h4 class="title"><?php esc_html_e( 'Bug Fixes', 'envira-gallery-lite' ); ?> <span class="badge updated">UPDATED</span></h4>
											<?php /* translators: %1$s: link */ ?>
											<p><?php printf( esc_html__( 'Bugs involving automatic and column galleries on the same page, certain character displaying in the admin, and Gutenberg Block tweaks.' ) ); ?></p>
									</div>
									<div class="enviracolumn">
											<h4 class="title"><?php esc_html_e( 'Gutenberg Block', 'envira-gallery-lite' ); ?></h4>
											<?php /* translators: %1$s: link */ ?>
											<p><?php printf( esc_html__( 'Improved support and additional features for the Envira Lite Gutenberg block. Bug fixes involving the gallery preview and items that were appearing out of order.' ) ); ?></p>
									</div>

									<div class="enviracolumn">
											<h4 class="title"><?php esc_html_e( 'Enhancements', 'envira-gallery-lite' ); ?></h4>
											<p><?php printf( esc_html__( 'Ability to set margins for Automatic Layouts. Also better workings with various popular WordPress plugins and themes.', 'envira-gallery-lite' ) ); ?></p>
									</div>
								</div>

							</div>


							<div class="envira-recent-section last-section">

								<h3>Recent Updates To Envira Pro:</h3>

								<div class="envira-feature">
									<img class="icon" src="https://enviragallery.com/wp-content/uploads/2015/08/drag-drop-icon.png" />
									<h4 class="feature-title"><?php esc_html_e( 'Getting Better And Better!', 'envira-gallery-lite' ); ?></h4>
									<?php /* translators: %1$s: url, %2$s url */ ?>
									<p><?php printf( esc_html__( 'This latest update contains enhancements and improvements - some of which are based on your user feedback! Check out %1$s.', 'envira-gallery-lite' ), '<a target="_blank" href="' . Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/docs/how-to-configure-your-gallery-settings", "whatsnewtab", "checkoutourchangelog", "#envira-changelog" ) . '">our changelog</a>' ); ?></p>
								</div>

								<div class="envira-feature opposite">
									<img class="icon" src="https://enviragallery.com/wp-content/uploads/2015/10/proofing-icon.png" />
									<h4 class="feature-title">
										<?php esc_html_e( 'Proofing Addon', 'envira-gallery-lite' ); ?>
									</h4>
									<p>
										<?php /* translators: %1$s: url, %2$s url */ ?>
										<?php printf( esc_html__( 'New and improved features and functions make client image proofing even easier for your photography business.', 'envira-gallery-lite' ) ); ?>
										</p>
								</div>

								<div class="envira-feature">
								<img class="icon" src="<?php echo esc_url( plugins_url( 'assets/images/icons/automatic-layout.png', ENVIRA_FILE ) ); ?>" />
								<h4 class="feature-title"><?php esc_html_e( 'Gallery Layouts', 'envira-gallery-lite' ); ?> <span class="badge updated">NEW</span> </h4>
								<?php /* translators: %1$s: button */ ?>
								<p><?php printf( esc_html__( 'New and improved features and functions make client image proofing even easier for your photography business.' ) ); ?></p>
								</div>

								<div class="envira-feature opposite">
								<img class="icon" src="https://enviragallery.com/wp-content/uploads/2020/09/audio_icon.png" style="border: 1px solid #000;" />
								<h4 class="feature-title"><?php esc_html_e( 'Audio Addon', 'envira-gallery-lite' ); ?> <span class="badge updated">NEW</span> </h4>
								<?php /* translators: %1$s: button */ ?>
								<p><?php printf( esc_html__( 'This addon allows you to easily add an audio track (such as background music or a narration) to the lightboxes in your Envira galleries. %s', 'envira-gallery-lite' ), '<a target="_blank" href="' . Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/audio-addon/", "whatsnewtab", "audioaddonreadmore", "" ) . '">Read More</a>' ); ?></p>
								</div>

							</div>

													



							<?php $this->envira_assets(); ?>

						</div>

					</div>

				</div>

				<?php $this->sidebar(); ?>

		</div> <!-- wrap -->

		<?php
	}

	/**
	 * Output the support screen.
	 *
	 * @since 1.8.1
	 */
	public function support_page() {
		?>

		<div class="envira-welcome-wrap envira-support">

			<div class="envira-title">

				<?php self::welcome_text(); ?>

			</div>

			<?php $this->sidebar(); ?>

			<div class="envira-support-main">

				<?php self::tab_navigation( __METHOD__ ); ?>

				<div class="envira-support-panel">

					<div class="wraps about-wsrap">

						<h3 class="headline-title"><?php esc_html_e( 'Got A Question? We Can Help!', 'envira-gallery-lite' ); ?></h3>

						<div class="envira-recent-section">

							<h3 class="title"><?php esc_html_e( 'Functionality:', 'envira-gallery-lite' ); ?></h3>

							<article class="docs">

								<ul>
									<li>
									<a href="https://enviragallery.com/docs/how-to-add-animated-gifs-to-your-gallery/" title="How to Add Animated GIFs to Your Gallery">
									How to Add Animated GIFs to Your Gallery							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/add-facebook-application-id/" title="How to Add Your Facebook Application ID to the Social Addon">
									How to Add Your Facebook Application ID to the Social Addon							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/how-to-bulk-edit-gallery-images/" title="How to Bulk Edit Gallery Images">
									How to Bulk Edit Gallery Images							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/justified-image-grid-gallery/" title="How to Create a Justified Image Grid Gallery">
									How to Create a Justified Image Grid Gallery							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/import-export-galleries/" title="How to Import and Export Galleries">
									How to Import and Export Galleries							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/supersize-addon/" title="How to Supersize Lightbox Images">
									How to Supersize Lightbox Images							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/how-to-use-the-bulk-apply-settings/" title="How to Use the Bulk Apply Settings">
									How to Use the Bulk Apply Settings							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/add-envira-gallery-widget/" title="How to Use the Envira Gallery Widget">
									How to Use the Envira Gallery Widget							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/standalone-addon/" title="How to Use the Standalone Feature in Envira Gallery">
									How to Use the Standalone Feature in Envira Gallery							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/display-tag-based-dynamic-gallery/" title="Display a Tag Based Dynamic Gallery">
									Display a Tag Based Dynamic Gallery							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/display-image-thumbnails-random-order/" title="Display Image Thumbnails in a Random Order">
									Display Image Thumbnails in a Random Order							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/lightbox-arrows-inside-outside/" title="Display Lightbox Nav Arrows Inside/Outside of Image">
									Display Lightbox Nav Arrows Inside/Outside of Image							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/how-to-turn-off-the-lightbox-for-envira/" title="How to Turn Off the Lightbox for Envira">
									How to Turn Off the Lightbox for Envira							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/using-a-wordpress-user-role/" title="Using A WordPress User Role">
									Using A WordPress User Role							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/envira-gallery-lightbox-options/" title="Envira Gallery Lightbox Options">
									Envira Gallery Lightbox Options							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/using-envira-galleries-and-page-builder-tabbed-content/" title="Using Envira Galleries and Page Builder Tabbed Content">
									Using Envira Galleries and Page Builder Tabbed Content							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/how-to-enable-rtl-support/" title="How to Enable RTL Support">
									How to Enable RTL Support							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/how-to-preview-envira-galleries/" title="How to Preview Envira Galleries">
									How to Preview Envira Galleries							</a>
									</li>
									<li>
									<a href="https://enviragallery.com/docs/enable-shortcodes-in-gallery-descriptions/" title="Enable Shortcodes in Gallery Descriptions">
									Enable Shortcodes in Gallery Descriptions							</a>
									</li>
								</ul>
								</article>

								<div style="margin: 20px auto 0 auto;">
									<a  target="_blank" href="https://enviragallery.com/categories/docs/functionality/" class="button button-primary">See More Guides On Functionality</a>
								</div>

								<h3 class="title" style="margin-top: 30px;"><?php esc_html_e( 'Addons:', 'envira-gallery-lite' ); ?></h3>

								<article class="docs">
									<ul>
										<li>
										<a href="https://enviragallery.com/docs/how-to-add-animated-gifs-to-your-gallery/" title="How to Add Animated GIFs to Your Gallery">
										How to Add Animated GIFs to Your Gallery							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/add-facebook-application-id/" title="How to Add Your Facebook Application ID to the Social Addon">
										How to Add Your Facebook Application ID to the Social Addon							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-bulk-edit-gallery-images/" title="How to Bulk Edit Gallery Images">
										How to Bulk Edit Gallery Images							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/justified-image-grid-gallery/" title="How to Create a Justified Image Grid Gallery">
										How to Create a Justified Image Grid Gallery							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/import-export-galleries/" title="How to Import and Export Galleries">
										How to Import and Export Galleries							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/supersize-addon/" title="How to Supersize Lightbox Images">
										How to Supersize Lightbox Images							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-use-the-bulk-apply-settings/" title="How to Use the Bulk Apply Settings">
										How to Use the Bulk Apply Settings							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/add-envira-gallery-widget/" title="How to Use the Envira Gallery Widget">
										How to Use the Envira Gallery Widget							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/standalone-addon/" title="How to Use the Standalone Feature in Envira Gallery">
										How to Use the Standalone Feature in Envira Gallery							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/display-tag-based-dynamic-gallery/" title="Display a Tag Based Dynamic Gallery">
										Display a Tag Based Dynamic Gallery							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/display-image-thumbnails-random-order/" title="Display Image Thumbnails in a Random Order">
										Display Image Thumbnails in a Random Order							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/lightbox-arrows-inside-outside/" title="Display Lightbox Nav Arrows Inside/Outside of Image">
										Display Lightbox Nav Arrows Inside/Outside of Image							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-turn-off-the-lightbox-for-envira/" title="How to Turn Off the Lightbox for Envira">
										How to Turn Off the Lightbox for Envira							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/using-a-wordpress-user-role/" title="Using A WordPress User Role">
										Using A WordPress User Role							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/envira-gallery-lightbox-options/" title="Envira Gallery Lightbox Options">
										Envira Gallery Lightbox Options							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/using-envira-galleries-and-page-builder-tabbed-content/" title="Using Envira Galleries and Page Builder Tabbed Content">
										Using Envira Galleries and Page Builder Tabbed Content							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-enable-rtl-support/" title="How to Enable RTL Support">
										How to Enable RTL Support							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-preview-envira-galleries/" title="How to Preview Envira Galleries">
										How to Preview Envira Galleries							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/enable-shortcodes-in-gallery-descriptions/" title="Enable Shortcodes in Gallery Descriptions">
										Enable Shortcodes in Gallery Descriptions							</a>
										</li>
									</ul>
								</article>

								<div style="margin: 20px auto 0 auto;">
									<a  target="_blank" href="https://enviragallery.com/categories/docs/addons/" class="button button-primary">See More Guides On Addons</a>
								</div>

								<h3 class="title" style="margin-top: 30px;"><?php esc_html_e( 'Styling:', 'envira-gallery-lite' ); ?></h3>

								<article class="docs">
									<ul>
										<li>
										<a href="https://enviragallery.com/docs/how-to-add-animated-gifs-to-your-gallery/" title="How to Add Animated GIFs to Your Gallery">
										How to Add Animated GIFs to Your Gallery							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/add-facebook-application-id/" title="How to Add Your Facebook Application ID to the Social Addon">
										How to Add Your Facebook Application ID to the Social Addon							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-bulk-edit-gallery-images/" title="How to Bulk Edit Gallery Images">
										How to Bulk Edit Gallery Images							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/justified-image-grid-gallery/" title="How to Create a Justified Image Grid Gallery">
										How to Create a Justified Image Grid Gallery							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/import-export-galleries/" title="How to Import and Export Galleries">
										How to Import and Export Galleries							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/supersize-addon/" title="How to Supersize Lightbox Images">
										How to Supersize Lightbox Images							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-use-the-bulk-apply-settings/" title="How to Use the Bulk Apply Settings">
										How to Use the Bulk Apply Settings							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/add-envira-gallery-widget/" title="How to Use the Envira Gallery Widget">
										How to Use the Envira Gallery Widget							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/standalone-addon/" title="How to Use the Standalone Feature in Envira Gallery">
										How to Use the Standalone Feature in Envira Gallery							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/display-tag-based-dynamic-gallery/" title="Display a Tag Based Dynamic Gallery">
										Display a Tag Based Dynamic Gallery							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/display-image-thumbnails-random-order/" title="Display Image Thumbnails in a Random Order">
										Display Image Thumbnails in a Random Order							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/lightbox-arrows-inside-outside/" title="Display Lightbox Nav Arrows Inside/Outside of Image">
										Display Lightbox Nav Arrows Inside/Outside of Image							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-turn-off-the-lightbox-for-envira/" title="How to Turn Off the Lightbox for Envira">
										How to Turn Off the Lightbox for Envira							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/using-a-wordpress-user-role/" title="Using A WordPress User Role">
										Using A WordPress User Role							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/envira-gallery-lightbox-options/" title="Envira Gallery Lightbox Options">
										Envira Gallery Lightbox Options							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/using-envira-galleries-and-page-builder-tabbed-content/" title="Using Envira Galleries and Page Builder Tabbed Content">
										Using Envira Galleries and Page Builder Tabbed Content							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-enable-rtl-support/" title="How to Enable RTL Support">
										How to Enable RTL Support							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/how-to-preview-envira-galleries/" title="How to Preview Envira Galleries">
										How to Preview Envira Galleries							</a>
										</li>
										<li>
										<a href="https://enviragallery.com/docs/enable-shortcodes-in-gallery-descriptions/" title="Enable Shortcodes in Gallery Descriptions">
										Enable Shortcodes in Gallery Descriptions							</a>
										</li>
									</ul>
								</article>

								<div style="margin: 20px auto 0 auto;">
									<a target="_blank" href="https://enviragallery.com/categories/docs/styling/" class="button button-primary">See More Guides On Styling</a>
								</div>

								</div>

								<hr/>

				</div>

			</div>

		</div> <!-- wrap -->

		<?php
	}

	/**
	 * Output the about screen.
	 *
	 * @since 1.8.1
	 */
	public function help_page() {
		?>

		<div class="envira-welcome-wrap envira-help">

			<div class="envira-title">

				<?php self::welcome_text(); ?>

			</div>

			<?php $this->sidebar(); ?>

			<div class="envira-get-started-main">

				<?php self::tab_navigation( __METHOD__ ); ?>

				<div class="envira-get-started-section">

						<div class="envira-admin-get-started-panel">

							<div class="section-text text-left">

								<h2>Creating your first gallery</h2>

								<p>Want to get started creating your first gallery? By following the step by step instructions in this walkthrough, you can easily publish your first gallery on your site.</p>

								<p>To begin, you’ll need to be logged into the WordPress admin area. Once there, click on Envira Gallery in the admin sidebar to go the Add New page.</p>

								<p>This will launch the Envira Gallery Builder.</p>

								<ul class="list-of-links">
									<li><a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-use-envira-gallery/", "gettingstartedtab", "howtouseenviragallery", "" ); ?>">How to get started with Envira Gallery</a></li>
									<li><a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-optimize-image-galleries-for-mobile-using-envira-gallery", "gettingstartedtab", "howtooptimizeimagegalleriesformobile", "" ); ?>">How to optimize image galleries for mobile using Envira Gallery</a></li>
									<li><a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-protect-your-website-from-image-theft/", "gettingstartedtab", "howtoprotectyourgalleriesfromimagetheft", "" ); ?>">How to protect your galleries and images from online theft</a></li>
									<li><a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-sell-your-photos-in-wordpress", "gettingstartedtab", "howtosellyourphotosinwordpress", "" ); ?>">How to sell your photos in WordPress</a></li>
									<li><a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-enhance-gallery-layouts-with-customizable-gallery-themes/", "gettingstartedtab", "howtoenhancegallerylayouts", "" ); ?>">How to enhance gallery layouts with customizable gallery themes</a></li>
									</li>
								</ul>

							</div>

							<div class="feature-photo-column">
									<img class="feature-photo" src="<?php echo esc_url( plugins_url( 'assets/images/get-started/creating.png', ENVIRA_FILE ) ); ?>" />
							</div>

						</div> <!-- panel -->

						<div class="envira-admin-get-started-panel">

							<div class="section-text-column text-left">

								<h2>Upgrade to a complete Envira Gallery experience</h2>

								<p>Get the most out of Envira Gallery by <a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( false, 'gettingstartedtab', 'upgradetounlockallitspowerfulfeatures' ); ?>">upgrading to unlock all of its powerful features</a>.</p>

								<p>With Envira Gallery Pro, you can unlock amazing features like:</p>

								<ul>
									<li>Get your gallery set up in minutes with pre-built customizable templates </li>
									<li>Have more people find you on Google by making your galleries SEO friendly </li>
									<li>Display your photos in all their glory on mobile with a true full-screen experience. No bars, buttons or small arrows</li>
									<li>Tag your images for better organization and gallery display</li>
									<li>Improve load times and visitor experience by splitting your galleries into multiple pages </li>
									<li>Streamline your workflow by sharing your gallery images directly on your favorite social media networks </li>
									</li>
								</ul>

							</div>

							<div class="feature-photo-column">
									<img class="feature-photo" src="<?php echo esc_url( plugins_url( 'assets/images/get-started/upgrade.png', ENVIRA_FILE ) ); ?>" />
							</div>

						</div> <!-- panel -->

						<div class="envira-admin-get-started-banner middle">

							<div class="banner-text">
								<h3>Upgrade To Unleash the Power of Envira</h3>
								<p>Pricing starts at just $29... What are you waiting for?</p>
							</div>
							<div class="banner-button">
								<a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( false, 'getstartedtab', 'upgradenowbutton' ); ?>" class="button button-primary">Upgrade Now</a>
							</div>

						</div> <!-- banner -->

						<div class="envira-admin-get-started-panel mini-panel">

							<div class="feature-photo-column photo-left">
								<a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-create-a-masonry-image-gallery-in-wordpress/", "gettingstartedtab", "createamasonrygallerybutton", "" ); ?>"><img class="feature-photo" src="<?php echo esc_url( plugins_url( 'assets/images/get-started/how-to-create-a-masonry-image-gallery-in-wordpress.jpg', ENVIRA_FILE ) ); ?>" /></a>
							</div>

							<div class="section-text-column text-left">

								<h2>How to Create a Masonry Image Gallery in WordPress</h2>

								<p>Do you want to create a masonry style gallery in WordPress? Sometimes you need to display full-view thumbnails without cropping the height or width. In this tutorial, we will share with you how to create a masonry image gallery in WordPress.</p>

								<div class="banner-button">
									<a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-create-a-masonry-image-gallery-in-wordpress/", "gettingstartedtab", "createamasonrygallerybutton", "" ); ?>" class="button button-primary">Read Documentation</a>
								</div>

							</div>

						</div> <!-- panel -->

						<div class="envira-admin-get-started-panel mini-panel">

							<div class="feature-photo-column photo-left">
								<a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-create-an-image-slider-for-your-wordpress-galleries/", "gettingstartedtab", "createimageslidersforyourgalleries", "" ); ?>"><img class="feature-photo" src="<?php echo esc_url( plugins_url( 'assets/images/get-started/how-to-create-image-slider-for-your-wordpress-galleries.jpg', ENVIRA_FILE ) ); ?>" /></a>
							</div>

							<div class="section-text-column text-left">

								<h2>How to Create an Image Slider for Your WordPress Galleries</h2>

								<p>Do you want to create an image slider in WordPress? Want to display your photo galleries in a slideshow? In this article, we will show you how to create an image slider for your WordPress galleries.</p>

								<div class="banner-button">
									<a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/how-to-create-an-image-slider-for-your-wordpress-galleries/", "gettingstartedtab", "createimageslidersforyourgalleries", "" ); ?>" class="button button-primary">Read Documentation</a>
								</div>

							</div>

						</div> <!-- panel -->

						<div class="envira-admin-get-started-panel mini-panel">

							<div class="feature-photo-column photo-left">
								<a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/add-gallery-tags-wordpress/", "gettingstartedtab", "addgallerytagsinwordpress", "" ); ?>"><img class="feature-photo" src="<?php echo esc_url( plugins_url( 'assets/images/get-started/add-gallery-tags-in-wordpress.jpg', ENVIRA_FILE ) ); ?>" /></a>
							</div>

							<div class="section-text-column text-left">

								<h2>How to Make Your Images Easier for Visitors To Find</h2>

								<p>Do you want to add tags to your images in WordPress galleries? With image tagging, you can give your visitors a way to sort through them easily. In this tutorial, we will share how to add gallery tags in WordPress by using Envira Gallery.</p>

								<div class="banner-button">
									<a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/add-gallery-tags-wordpress/", "gettingstartedtab", "addgallerytagsinwordpress", "" ); ?>" class="button button-primary">Read Documentation</a>
								</div>

							</div>

						</div> <!-- panel -->

						<div class="envira-admin-get-started-banner bottom">

							<div class="banner-text">
								<h3>Start Creating Responsive Photo Galleries</h3>
								<p>Customize and Publish in Minutes... What are you waiting for?</p>
							</div>
							<div class="banner-button">
								<a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( false, 'getstartedtab', 'getenviragallerynowbutton' ); ?>" class="button button-primary">Get Envira Gallery Now</a>
							</div>

						</div> <!-- banner -->


					<?php //$this->envira_posts(); ?>

					<?php $this->envira_assets(); ?>

			</div>

		</div> <!-- wrap -->


		<?php
	}

	/**
	 * Output the upgrade screen.
	 *
	 * @since 1.8.1
	 */
	public function upgrade_page() {
		?>

		<div class="envira-welcome-wrap envira-help">

			<div class="envira-title">

				<?php self::welcome_text(); ?>

			</div>

			<?php $this->sidebar(); ?>

			<div class="envira-get-started-main">

				<?php self::tab_navigation( __METHOD__ ); ?>

				<div class="envira-get-started-panel">

					<div class="wraps upgrade-wrap">

						<h3 class="headline-title"><?php esc_html_e( 'Make Your Galleries Amazing!', 'envira-gallery-lite' ); ?></h3>

						<h4 class="headline-subtitle"><?php esc_html_e( 'Upgrade To Envira Pro and can get access to our full suite of features.', 'envira-gallery-lite' ); ?></h4>

						<a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( false, 'upgradeenviragallerytab', 'upgradetoenviraprobutton' ); ?>" class="button button-primary">Upgrade To Envira Pro</a>

					</div>

					<div class="upgrade-list">

						<ul>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/albums-addon/", "upgradeenviragallerytab", "albumsaddon", "" ); ?>">Albums Addon</a></h5>
									<p>Organize your galleries in Albums, choose cover photos and more.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/demo/masonry-gallery-demo/", "upgradeenviragallerytab", "masonrygallery", "" ); ?>">Masonry Gallery</a></h5>
									<p>Display your photo galleries in a masonry layout.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/demo/envira-gallery-theme-demo/", "upgradeenviragallerytab", "gallerythemesandlayouts", "" ); ?>">Gallery Themes/Layouts</a></h5>
									<p>Build responsive WordPress galleries that work on mobile, tablet and desktop devices.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/videos-addon/", "upgradeenviragallerytab", "videogalleries", "" ); ?>">Video Galleries</a></h5>
									<p>Not just for photos! Embed YouTube, Vimeo, Wistia, DailyMotion, Facebook, Instagram, Twitch, VideoPress, and self-hosted videos in your gallery.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/social-addon/", "upgradeenviragallerytab", "socialaddon", "" ); ?>">Social Addon</a></h5>
									<p>Allows users to share photos via email, Facebook, Twitter, Pinterest, LinkedIn and WhatsApp.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/proofing-addon/", "upgradeenviragallerytab", "imageproofing", "" ); ?>">Image Proofing</a></h5>
									<p>Client image proofing made easy for your photography business.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/woocommerce-addon/", "upgradeenviragallerytab", "ecommerce", "" ); ?>">Ecommerce</a></h5>
									<p>Instantly display and sell your photos with our native WooCommerce integration.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/deeplinking-addon/", "upgradeenviragallerytab", "deeplinking", "" ); ?>">Deeplinking</a></h5>
									<p>Make your gallery SEO friendly and easily link to images with deeplinking.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/slideshow-addon/", "upgradeenviragallerytab", "slideshows", "" ); ?>">Slideshows</a></h5>
									<p>Enable slideshows for your galleries, controls autoplay settings and more.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/lightroom-addon/", "upgradeenviragallerytab", "lightroomintegration", "" ); ?>">Lightroom Integration</a></h5>
									<p>Automatically create & sync photo galleries from your Adobe Lightroom collections.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/addons/protection-addon/", "upgradeenviragallerytab", "downloadprotection", "" ); ?>">Download Protection</a></h5>
									<p>Prevent visitors from downloading your images without permission.</p>
								</div>
							</li>
							<li>
								<div class="interior">
									<h5><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( false, 'upgradeenviragallerytab', 'dedicatedcustomersupport' ); ?>">Dedicated Customer Support... and much more!</a></h5>
									<p>Top notch customer support and dozens of pro features.</p>
								</div>
							</li>
						</ul>

					</div>

					<div class="upgrade-video">
						<iframe width="100%" src="https://www.youtube.com/embed/CLxxh_-7uFQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>

					<?php $this->envira_assets(); ?>

				</div>

			</div>

		</div> <!-- wrap -->


		<?php
	}

	/**
	 * Output the upgrade screen.
	 *
	 * @since 1.8.1
	 */
	public function lite_vs_pro_page() {
		?>

		<div class="envira-welcome-wrap envira-help">

			<div class="envira-title">

				<?php self::welcome_text(); ?>

			</div>

			<?php $this->sidebar(); ?>

			<div class="envira-get-started-main">

				<?php self::tab_navigation( __METHOD__ ); ?>

				<div class="envira-get-started-panel">

				<div id="envira-admin-litevspro" class="wrap envira-admin-wrap">

				<div class="envira-admin-litevspro-section no-bottom litevspro-table-header">
					<h3 class="centered">
						<strong>Lite</strong> vs <strong>Pro</strong>
					</h3>

					<p class="centered">Get the most out of Envira by upgrading to Pro and unlocking all of the powerful features.</p>
				</div>

				<div class="envira-admin-litevspro-section no-bottom envira-admin-litevspro-section-table">

						<table cellspacing="0" cellpadding="0" border="0">
							<thead>
								<th>Feature</th>
								<th>Lite</th>
								<th>Pro</th>
							</thead>
							<tbody>
								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Gallery Themes And Layouts</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-partial">
											<strong>Basic Gallery Theme</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong>All Gallery Themes &amp; Layouts</strong> 
											More themes to make your Galleries unique and professional.
										</p>
									</td>
								</tr>

								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Lightbox Features</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-partial">
											<strong>Basic Lightbox</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong>All Advanced Lightbox Features</strong>
											Multiple themes for your Gallery Lightbox display, Titles, Transitions, Fullscreen, Counter, Thumbnails  
										</p>
									</td>
								</tr>
   
								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Mobile Features</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-partial">
											<strong>Basic Mobile Gallery	</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong>All Advanced Mobile Settings</strong>Customize all aspects of your user's mobile gallery display experience to be different than the default desktop</p>
									</td>
								</tr>
								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Import/Export Options	</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-none">
											<strong>Limited Import/Export	</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong>All Import/Export </strong> Instagram, Dropbox, NextGen, Flickr, Zip and more</p>
									</td>
								</tr>
								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Video Galleries	</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-none">
											<strong> No Videos	</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong>All Videos Gallery </strong> Import your own videos or from any major video sharing platform</p>
									</td>
								</tr>
								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Social Sharing	</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-none">
											<strong>No Social Sharing	</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong>All Social Sharing Features</strong>Share your photos on any major social sharing platform</p>
									</td>
								</tr>
								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Advanced Gallery Features	</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-none">
											<strong>  No Advanced Features	</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong>All Advanced Features</strong>Albums, Ecommerce, Pagination, Deeplinking, and Expanded Gallery Configurations</p>
									</td>
								</tr>
								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Envira Gallery Addons 	</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-none">
											<strong>  No Addons Included 	</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong> All Addons Included</strong>WooCommerce, Tags and Filters, Proofing, Schedule, Password Protection, Lightroom, Slideshows, Watermarking and more (28 total)            </p>
									</td>
								</tr>
								<tr class="envira-admin-columns">
									<td class="envira-admin-litevspro-first-column">
										<p>Customer Support	</p>
									</td>
									<td class="envira-admin-litevspro-lite-column">
										<p class="features-none">
											<strong>Limited Customer Support</strong>
										</p>
									</td>
									<td class="envira-admin-litevspro-pro-column">
										<p class="features-full">
											<strong> Priority Customer Support</strong>Dedicated prompt service via email from our top tier support team. Your request is assigned the highest priority</p>
									</td>
								</tr>
								
							</tbody>
						</table>

				</div>

				<div class="envira-admin-litevspro-section envira-admin-litevspro-section-hero">
					<div class="envira-admin-about-section-hero-main no-border">
						<h3 class="call-to-action">
						<a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( false, 'litevsprotab', 'getenviragalleryprotoday' ); ?>" target="_blank" rel="noopener noreferrer">Get Envira Pro Today and Unlock all the Powerful Features					</a>
					</h3>

						<p>
							<strong>Bonus:</strong> Envira Lite users get <span class="envira-deal 20-percent-off">20% off regular price</span>, using the code in the link above.
						</p>
					</div>
				</div>

				</div>

					<?php $this->envira_assets(); ?>

				</div>

			</div>

		</div> <!-- wrap -->


		<?php
	}

	/**
	 * Output the changelog screen.
	 *
	 * @since 1.8.1
	 */
	public function changelog_page() {

		?>

		<div class="envira-welcome-wrap envira-changelog">

			<div class="envira-title">

				<?php self::welcome_text(); ?>

			</div>

			<?php $this->sidebar(); ?>

			<div class="envira-welcome-main changelog-main">

				<?php self::tab_navigation( __METHOD__ ); ?>

				<div class="envira-welcome-panel">

					<div class="wraps about-wsrap">

						<?php $this->return_changelog(); ?>

					</div>

				</div>

			</div>

		</div> <!-- wrap -->


		<?php
	}

	/**
	 * Changelog display.
	 *
	 * @since 1.8.1
	 */
	public function return_changelog() {
		?>

			<div id="changelog-envira-gallery">

			<h3>1.8.4.5 (10-31-2018)</h3>
				<ul>
				<li>Fix: Issue w/ standalone function and older versions of the Album addon.</li>
				</ul>
				<h3>1.8.4.4 (10-31-2018)</h3>
				<ul>
				<li>Added: When Lightbox is not in infinite loop, first previous and last next arrows no longer appear.</li>
				<li>Fix: Gallery title shows in gallery toolbar instead of page title.</li>
				<li>Fix: Minor bug fixes.</li>
				</ul>
				<h3>1.8.4.3 (10-18-2018)</h3>
				<ul>
				<li>Fix: Resolved issues for installs using older PHP versions.</li>
				</ul>
				
			</div>

		<?php
	}

	/**
	 * Output the addon screen.
	 *
	 * @since 1.8.1
	 */
	public function addon_page() {
		?>

		<div class="envira-welcome-wrap envira-help">

			<div class="envira-title">

				<?php self::welcome_text(); ?>

			</div>

			<?php $this->sidebar(); ?>

			<div class="envira-get-started-main">

				<?php self::tab_navigation( __METHOD__ ); ?>

				<h3>Unlock More Addons</h3>

				<?php do_action('envira_gallery_addons_section'); ?> 

			</div>

		</div>

		</div> <!-- wrap -->


		<?php
	}



	/**
	 * Returns a common row for posts from enviragallery.com.
	 *
	 * @since 1.8.5
	 */
	public function envira_posts() {
		?>

			<div class="envira-posts">

				<h3 class="title"><?php esc_html_e( 'Helpful Articles For Beginners:', 'envira-gallery-lite' ); ?></h3>
				<div class="envira-recent envirathree-column">


					<div class="enviracolumn">
						<img class="post-image" src="https://enviragallery.com/wp-content/uploads/2016/11/How-to-Fix-Flipped-or-Upside-Down-Images-in-WordPress-1.png" />
						<h4 class="title"><?php esc_html_e( 'How to Fix Flipped or Upside Down Images in WordPress', 'envira-gallery-lite' ); ?></h4>
						<?php /* Translators: %s */ ?>
						<p><?php printf( esc_html__( 'Do the images you upload to WordPress appear flipped? In this tutorial, we will show you how to fix flipped or upside down images in WordPress. %s', 'envira-gallery-lite' ), '<a href="https://enviragallery.com/how-to-fix-flipped-or-upside-down-images-in-wordpress/" target="_blank">Read More</a>' ); ?></p>
					</div>

					<div class="enviracolumn">
						<img class="post-image" src="https://enviragallery.com/wp-content/uploads/2017/12/best-photo-editing-software-for-photographers.jpg" />
						<h4 class="title"><?php esc_html_e( '17 Best Photo Editing Software for Photographers', 'envira-gallery-lite' ); ?></h4>
						<?php /* Translators: %s */ ?>
						<p><?php printf( esc_html__( 'Are you looking for professional photo editing software for your photos on Mac or Windows? In this guide, we will share the best photo editing software for photographers. %s', 'envira-gallery-lite' ), '<a href="https://enviragallery.com/best-photo-editing-software-for-photographers/" target="_blank">Read More</a>' ); ?></p>
					</div>

					<div class="enviracolumn">
						<img class="post-image" src="https://enviragallery.com/wp-content/uploads/2018/09/vidoe-gallery.jpg" />
						<h4 class="title"><?php esc_html_e( 'Announcing New Video Integrations', 'envira-gallery-lite' ); ?></h4>
						<?php /* Translators: %s */ ?>
						<p><?php printf( esc_html__( 'We’re pleased to introduce our expanded video gallery support options for Envira Gallery 1.8.1. More video platform integrations allow you to add more video sources for your galleries. %s', 'envira-gallery-lite' ), '<a href="https://enviragallery.com/announcing-new-video-integrations/" target="_blank">Read More</a>' ); ?></p>
					</div>


				</div>

			</div>

		<?php
	}


	/**
	 * Returns a common footer
	 *
	 * @since 1.8.5
	 */
	public function envira_assets() {
		?>

		<div class="envira-assets">
			<p>
				<?php esc_html_e( 'Learn more:', 'envira-gallery-lite' ); ?>&nbsp;<a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/blog/", "learnmore", "blog", "" ); ?>"><?php esc_html_e( 'Blog', 'envira-gallery-lite' ); ?></a>
				&bullet; <a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/docs/", "learnmore", "documentation", "" ); ?>"><?php esc_html_e( 'Documentation', 'envira-gallery-lite' ); ?></a>
			<?php /* &bullet; <a href="https://enviragallery.com/dev/"><?php _ex( 'Development Blog', 'About screen, link to development blog', 'envira-gallery-lite' ); ?></a> */ ?>
			</p>

			<div class="icons-container">
				<div class="label"><?php esc_html_e( 'Social:', 'envira-gallery-lite' ); ?></div>

				<ul class="social-icons">
					<li class="facebook">
						<a href="http://facebook.com/enviragallery" title="Facebook" target="_blank" class="facebook">
							Facebook</a>
					</li>
					<li class="twitter">
						<a href="http://twitter.com/enviragallery" title="Twitter" target="_blank" class="twitter">
							Twitter</a>
					</li>
					<li class="youtube">
						<a href="http://youtube.com/enviragallery" title="YouTube" target="_blank" class="youtube">
							YouTube</a>
					</li>
					<li class="pinterest">
						<a href="https://www.pinterest.com/enviragallery/" title="Pinterest" target="_blank" class="pinterest">
							Pinterest</a>
					</li>
					<li class="instagram">
						<a href="http://instagram.com/enviragallery" title="Instagram" target="_blank" class="instagram">
							Instagram</a>
					</li>
				</ul>

			</div>

			<p>

				<?php esc_html_e( 'Also by us: ', 'envira-gallery-lite' ); ?>

				<a target="_blank" href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "http://soliloquywp.com", "alsobyus", "soliloquyslider", "" ); ?>"><?php esc_html_e( 'Soliloquy Slider', 'envira-gallery-lite' ); ?></a>

			</p>

		</div>

		<?php
	}

	/**
	 * Return true/false based on whether a query argument is set.
	 *
	 * @return bool
	 */
	public static function is_new_install() {

		if ( get_transient( '_envira_is_new_install' ) ) {
			delete_transient( '_envira_is_new_install' );
			return true;
		}

		if ( isset( $_GET['is_new_install'] ) && 'true' === strtolower( sanitize_text_field( wp_unslash( $_GET['is_new_install'] ) ) ) ) { // @codingStandardsIgnoreLine
			return true;
		} elseif ( isset( $_GET['is_new_install'] ) ) { // @codingStandardsIgnoreLine
			return false;
		}

	}

	/**
	 * Return a user-friendly version-number string, for use in translations.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public static function display_version() {

		return ENVIRA_VERSION;

	}


}

$envira_welcome = new Envira_Welcome;