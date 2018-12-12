<?php
/**
 * Gutenberg class.
 *
 * @since 1.8.5
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg class.
 *
 * @since 1.8.5
 */
class Envira_Gutenberg {

	public static $instance = null;

	public $base = null;
	public $common = null;
	/**
	 * Flag to determine if media modal is loaded.
	 *
	 * @since 1.8.5
	 *
	 * @var object
	 */
	public $loaded = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.5
	 */
	public function __construct() {

		$this->base = Envira_Gallery_Lite::get_instance();
		$this->common = Envira_Gallery_Common::get_instance();
		add_action( 'enqueue_block_assets', array( $this, 'block_assets' ), 10 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ), 10 );

	}

	/**
	 * Enqueue Gutenberg block assets for both frontend + backend.
	 *
	 * `wp-blocks`: includes block type registration and related functions.
	 *
	 * @since 1.0.0
	 */
	public function block_assets() {

		wp_enqueue_style(
			'envira_gutenberg-style-css', // Handle.
			plugins_url( 'assets/css/blocks.style.build.css', $this->base->file ), // Block style CSS.
			array( 'wp-blocks' ), // Dependency to include the CSS after it.
			$this->base->version
		);

	}


	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * `wp-blocks`: includes block type registration and related functions.
	 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
	 * `wp-i18n`: To internationalize the block's text.
	 *
	 * @since 1.0.0
	 */
	public function editor_assets() {

		wp_enqueue_script(
			'envira_gutenberg-block-js',
			plugins_url( 'assets/js/envira-gutenberg.js', $this->base->file ), // Block.build.js: we register the block here and built with Webpack.
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ), // dependencies, defined above.
			$this->base->version,
			true // Enqueue the script in the footer.
		);

		$columns = $this->common->get_columns();

		$new_columns = array();
		foreach ( $columns as $options ) {
			$new_columns[] = array(
				'label' => $options['name'],
				'value' => $options['value'],
			);
		}
		$lightbox_options = $this->common->get_lightbox_themes();

		$new_lightbox = array();
		foreach ( $lightbox_options as $options ) {
			$new_lightbox[] = array(
				'label' => $options['name'],
				'value' => $options['value'],
			);
		}

		$image_option = $this->common->get_image_sizes();

		$new_sizes = array();
		foreach ( $image_option as $options ) {
			$new_sizes[] = array(
				'label' => $options['name'],
				'value' => $options['value'],
			);
		}
		$options = array(
			'columns'         => $new_columns,
			'lightbox_themes' => $new_lightbox,
			'image_sizes'     => $new_sizes,
			'sorting_options' => $this->common->get_sorting_options(),
		);
		$args_array = array( 'options' => $options );
		wp_localize_script(
			'envira_gutenberg-block-js',
			'envira_args',
			$args_array
		);

		// Styles.
		wp_enqueue_style(
			'envira_gutenberg-block-editor-css', // Handle.
			plugins_url( 'assets/css/blocks.editor.build.css', $this->base->file ), // Block editor CSS.
			array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
			$this->base->version
		);
	}

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_License object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gutenberg ) ) {
            self::$instance = new self();
        }

        return self::$instance;

    }

}

$envira_gutenberg = Envira_Gutenberg::get_instance();