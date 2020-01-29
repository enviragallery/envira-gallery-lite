<?php
/**
 * Review class.
 *
 * @since 1.7.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Review Class
 *
 * @since 1.7.0
 */
class Envira_Subscribe {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {

		add_action( 'admin_notices', array( $this, 'subscribe' ) );
		add_action( 'wp_ajax_envira_dismiss_subscribe', array( $this, 'dismiss' ) );
	}

	/**
	 * Add admin notices as needed for reviews.
	 *
	 * @since 1.1.6.1
	 */
	public function subscribe() {

		if ( ! function_exists('get_current_screen') ){
			return;
		}

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the Envira Post Type screen.
		if ( 'envira' !== $screen->post_type || 'envira_page_envira-gallery-lite-litevspro' === $screen->id ) {
			return;
		}

		// Verify that we can do a check for reviews.
		$subscribe = get_option( 'envira_gallery_subscribe' );

		if ( false !== $subscribe && '' !== $subscribe ){
			return;
		}
		$gallery_count = wp_count_posts( 'envira' );

		// We have a candidate! Output a review message.
		?>
		<style>
			#group_256{
				visibility:hidden;
				display:none;
			}
			.is-primary.envira-button{
				background: #7cc048;
				border-color: #7cc048;
				-webkit-box-shadow: none;
				box-shadow: none;
				color: #fff;
			}
			.notice-info.envira-subscribe-notice{
				border-left-color: #7cc048;
			}
			.envira-subscribe-notice a{
				color: #7cc048;
			}
			.envira-subscribe-field{
				margin-right: 10px;
			}
			#envira-subscribe-success{
				display: none;
			}
			#envira-subscribe-error{
				display: none;
				color:red;
			}
		</style>
		<div class="notice notice-info is-dismissible envira-subscribe-notice">
			<div id="envira-subscribe-block">
				<div id="envira-subscribe-error"><p><strong><?php esc_html_e( 'Opps, Looks like something went wrong.', 'envira-gallery-lite' ); ?></strong></p></div>
				<h2><?php esc_html_e( 'Thank you for using Envira Gallery.', 'envira-gallery-lite' ); ?></h2>
				<p><strong><?php esc_html_e( 'Get Envira updates, photography tips, tutorials and resources straight to your mailbox.', 'envira-gallery-lite' ); ?></strong></p>
				<p>
				<form action="https://enviragallery.us3.list-manage.com/subscribe/post-json?u=beaa9426dbd898ac91af5daca&amp;id=2ee2b5572e&amp;c=?" method="get" id="envira-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
					<input class="envira-subscribe-field" type="name" name="FNAME" placeholder="First Name" required />
					<input class="envira-subscribe-field" type="email" name="EMAIL" placeholder="Email Address" required />
					<input class="subscribe_hidden" name="GALLERYCOU" type="hidden" value="<?php echo absint( $gallery_count->publish ); ?>" />
					<input type="checkbox" id="group_256" name="group[14349][256]" value="1" checked class="av-checkbox">
					<input class="button is-primary envira-button envira-subscribe-field" type="submit" value="Yes Please!"  />
				</form>
			</p>
			</div>
			<div id="envira-subscribe-success">
				<p><strong><?php esc_html_e('Thank you for subscribing to Envira Gallery', 'envira-gallery-lite' ); ?></strong></p>
			</div>
		</div>
		<script type="text/javascript">

			jQuery(document).ready( function($) {
				$('#envira-subscribe-form').on('submit', function(e){

					e.preventDefault();
					var post_url = $(this).attr("action"),
						request_method = $(this).attr("method"),
						form_data = $(this).serialize();

					$.ajax({
						url : post_url,
						type: request_method,
						data : form_data,
						dataType    : 'json',
						contentType: "application/json; charset=utf-8",
						error       : function(err) { alert("Could not connect to the registration server."); },
						success     : function(data) {
		                	if (data.result != "success") {
								$('#envira-subscribe-error').show();
		                    } else {
								$.post( ajaxurl, {
									action: 'envira_dismiss_subscribe'
								});
								$('#envira-subscribe-block').remove();
								$('#envira-subscribe-success').show();
								$('.envira-subscribe-notice').delay(2000).fadeOut("slow");

							}
						}
					})
				});
				$(document).on('click', '.envira-dismiss-subscribe-notice, .envira-subscribe-notice button', function( event ) {
					event.preventDefault();

					$.post( ajaxurl, {
						action: 'envira_dismiss_subscribe'
					});

					$('.envira-subscribe-notice').remove();
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
	public function dismiss() {

		update_option( 'envira_gallery_subscribe', true );
		die;
	}

}

$envira_subscirbe = new Envira_Subscribe;