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

		global $current_screen;

		if ( !empty( $current_screen->id ) && strpos( $current_screen->id, 'envira' ) !== false ) {

			// Verify that we can do a check for promotions.
			$promotion = get_option( 'envira_gallery_promotion_lifetime' );
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
				<p><strong><a href="<?php echo Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link( "https://enviragallery.com/lite/", "lifetimealert", "lifetimelink", "" ); ?>"><?php _e( 'Upgrade to a premium Envira Gallery subscription for 50% off now!', 'envira-gallery-lite' );?></a></strong><?php _e( ' — a limited-time offer! — and get access to addons, updates, and technical support!', 'envira-gallery-lite' );?></p>
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
	}

	/**
	 * Dismiss the promotion nag
	 *
	 * @since 1.1.6.1
	 */
	public function dismiss_promotion() {

		$promotion = get_option( 'envira_gallery_promotion_lifetime' );
		if ( ! $promotion ) {
			$promotion = array();
		}

		$promotion['time']      = time();
		$promotion['dismissed'] = true;

		update_option( 'envira_gallery_promotion_lifetime', $promotion );
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