<?php
/**
 * Review class.
 *
 * @since 1.1.4.5
 *
 * @package envira
 * @author  Devin Vinson
 */
class Envira_Lite_Review {

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
	 * Holds the review slug.
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

		add_action( 'admin_notices', array( $this, 'review' ) );
		add_action( 'wp_ajax_envira_dismiss_review', array( $this, 'dismiss_review' ) );
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
			$url  = 'https://wordpress.org/support/plugin/envira-gallery-lite/reviews/?filter=5#new-post';
			$text = sprintf( __( 'Please rate <strong>Envira Gallery</strong> <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%s" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the Envira Gallery team!', 'wpforms' ), $url, $url );
		}
		return $text;
	}

	/**
	 * Add admin notices as needed for reviews.
	 *
	 * @since 1.1.6.1
	 */
	public function review() {

		// Verify that we can do a check for reviews.
		$review = get_option( 'envira_gallery_review' );
		$time     = time();
		$load     = false;

		if ( ! $review ) {
			$review = array(
				'time'         => $time,
				'dismissed' => false
			);
			$load = true;
		} else {
			// Check if it has been dismissed or not.
			if ( (isset( $review['dismissed'] ) && ! $review['dismissed']) && (isset( $review['time'] ) && (($review['time'] + DAY_IN_SECONDS) <= $time)) ) {
				$load = true;
			}
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}

		// Update the review option now.
		update_option( 'envira_gallery_review', $review );

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

		// We have a candidate! Output a review message.
		?>
		<div class="notice notice-info is-dismissible envira-review-notice">
			<p><?php _e( 'Hey, I noticed you created a photo gallery with Envira - that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'envira-gallery-lite' ); ?></p>
			<p><strong><?php _e( '~ Nathan Signh<br>CEO of Envira Gallery', 'envira-gallery-lite' ); ?></strong></p>
			<p>
				<a href="https://wordpress.org/support/plugin/envira-gallery-lite/reviews/?filter=5#new-post" class="envira-dismiss-review-notice envira-review-out" target="_blank" rel="noopener"><?php _e( 'Ok, you deserve it', 'envira-gallery-lite' ); ?></a><br>
				<a href="#" class="envira-dismiss-review-notice" target="_blank" rel="noopener"><?php _e( 'Nope, maybe later', 'envira-gallery-lite' ); ?></a><br>
				<a href="#" class="envira-dismiss-review-notice" target="_blank" rel="noopener"><?php _e( 'I already did', 'envira-gallery-lite' ); ?></a><br>
			</p>
		</div>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				$(document).on('click', '.envira-dismiss-review-notice, .envira-review-notice button', function( event ) {
					if ( ! $(this).hasClass('envira-review-out') ) {
						event.preventDefault();
					}

					$.post( ajaxurl, {
						action: 'envira_dismiss_review'
					});

					$('.envira-review-notice').remove();
				});
			});
		</script>
		<?php
	}

	/**
	 * Dismiss the review nag
	 *
	 * @since 1.1.6.1
	 */
	public function dismiss_review() {

		$review = get_option( 'envira_gallery_review' );
		if ( ! $review ) {
			$review = array();
		}

		$review['time']      = time();
		$review['dismissed'] = true;

		update_option( 'envira_gallery_review', $review );
		die;
	}


	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Lite_Review ) ) {
			self::$instance = new Envira_Lite_Review();
		}

		return self::$instance;

	}
}

$envira_lite_review = Envira_Lite_Review::get_instance();