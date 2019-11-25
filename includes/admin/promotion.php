<?php
/**
 * Promotion class.
 *
 * @since 1.7.4
 *
 * @package envira
 * @author  Devin Vinson
 */
class Envira_Lite_Promotion {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.4.5
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.4.5
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the promotion slug.
	 *
	 * @since 1.1.4.5
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.1.4.5
	 *
	 * @var object
	 */
	public $base;

	/**
	 * API Username.
	 *
	 * @since 1.1.4.5
	 *
	 * @var bool|string
	 */
	public $user = false;


	/**
	 * Primary class constructor.
	 *
	 * @since 1.1.4.5
	 */
	public function __construct() {

		$this->base = Envira_Gallery_Lite::get_instance();

		add_action( 'admin_notices', array( $this, 'promotion' ) );
		add_action( 'wp_ajax_envira_dismiss_promotion', array( $this, 'dismiss_promotion' ) );
		add_filter( 'admin_footer_text',     array( $this, 'admin_footer'   ), 1, 2 );
	
	}

	/**
	 * When user is on a Envira related admin page, display footer text
	 * that graciously asks them to rate us.
	 *
	 * @since
	 * @param string $text
	 * @return string
	 */
	public function admin_footer( $text ) {
		global $current_screen;
		if ( !empty( $current_screen->id ) && strpos( $current_screen->id, 'envira' ) !== false ) {
			$url  = 'https://wordpress.org/support/plugin/envira-gallery-lite/promotions/?filter=5#new-post';
			$text = sprintf( __( 'Please rate <strong>Envira Gallery</strong> <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%s" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the Envira Gallery team!', 'wpforms' ), $url, $url );
		}
		return $text;
	}

	/**
	 * Add admin notices as needed for promotions.
	 *
	 * @since 1.1.6.1
	 */
	public function promotion() {

		// Verify that we can do a check for promotions.
		$promotion = get_option( 'envira_gallery_promotion' );
		$time     = time();
		$load     = false;

		if ( ! $promotion ) {
			$promotion = array(
				'time'         => $time,
				'dismissed'    => false
			);
			$load = true;
		} else {
			// Check if it has been dismissed or not.
			if ( (isset( $promotion['dismissed'] ) && ! $promotion['dismissed']) && (isset( $promotion['time'] ) && (($promotion['time'] + DAY_IN_SECONDS) <= $time)) ) {
				$load = true;
			}
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}

		// Update the promotion option now.
		update_option( 'envira_gallery_promotion', $promotion );

		// Run through optins on the site to see if any have been loaded for more than a week.
		$valid    = false;
		$galleries = $this->base->get_galleries();

		if ( ! $galleries ) {
			return;
		}

		foreach ( $galleries as $gallery ) {

			$data = get_post( $gallery['id']);

			// Check the creation date of the local optin. It must be at least one week after.
			$created = isset( $data->post_date ) ? strtotime( $data->post_date ) + (7 * DAY_IN_SECONDS) : false;
			if ( ! $created ) {
				continue;
			}

			if ( $created <= $time ) {
				$valid = true;
				break;
			}
		}

		// If we don't have a valid optin yet, return.
		if ( ! $valid ) {
			return;
		}

		// We have a candidate! Output a promotion message.
		?>
		<div class="notice notice-info is-dismissible envira-promotion-notice">
			<h2><?php _e( ' Black Friday is here! – Get 50% off Envira Gallery and unlock its most powerful features!', 'envira-gallery-lite' ); ?></h2>
			<p><?php _e( 'Easy, intuitive and fast, spend less time configuring your galleries and more time shooting and editing your photos.', 'envira-gallery-lite' );?>
			<p><?php _e( 'Envira Gallery makes it easy to create lightning-fast, mobile-responsive photo and video galleries. Create, edit and sync your favorite photo galleries from Adobe Lightroom directly to Wordpress. Protect your galleries with passwords, watermarks and no-right-click downloads. Want to sell your photos in WordPress? Easily turn your galleries and images into products with WooCommerce and much more!', 'envira-gallery-lite' );?></p>
			<p><?php _e( 'Use the coupon code', 'envira-gallery-lite' );?>
			<strong><?php _e( '“LITEUPGRADE50”', 'envira-gallery-lite' );?></strong>
			<?php _e( 'to get 50% off your order. Hurry – offer ends soon!', 'envira-gallery-lite' );?></p>

			<p><a href="https://enviragallery.com/lite/?tracking=lite-tab" target="_new" class="button is-primary envira-button envira-subscribe-field">Upgrade Now!</a></p>

		</div>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				$(document).on('click', '.envira-dismiss-promotion-notice, .envira-promotion-notice button', function( event ) {
					if ( ! $(this).hasClass('envira-promotion-out') ) {
						event.preventDefault();
					}

					$.post( ajaxurl, {
						action: 'envira_dismiss_promotion'
					});

					$('.envira-promotion-notice').remove();
				});
			});
		</script>
		<?php
	}

	/**
	 * Dismiss the promotion nag
	 *
	 * @since 1.1.6.1
	 */
	public function dismiss_promotion() {

		$promotion = get_option( 'envira_gallery_promotion' );
		if ( ! $promotion ) {
			$promotion = array();
		}

		$promotion['time']      = time();
		$promotion['dismissed'] = true;

		update_option( 'envira_gallery_promotion', $promotion );
		die;
	}


	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Lite_Promotion ) ) {
			self::$instance = new Envira_Lite_Promotion();
		}

		return self::$instance;

	}
}

$envira_lite_promotion = Envira_Lite_Promotion::get_instance();