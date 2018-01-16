<?php
/**
 * Outputs the Gallery Code Metabox Content.
 *
 * @since   1.5.0
 *
 * @package Envira_Gallery
 * @author 	Envira Team
 */
?>
<p><?php _e( 'You can place this gallery anywhere into your posts, pages, custom post types or widgets by using <strong>one</strong> the shortcode(s) below:', 'envira-gallery' ); ?></p>
<div class="envira-code">
	<code id="envira_shortcode_id_<?php echo $data['post']->ID; ?>"><?php echo '[envira-gallery id="' . $data['post']->ID . '"]'; ?></code>
	<a href="#" title="<?php _e( 'Copy Shortcode to Clipboard', 'envira-gallery' ); ?>" data-clipboard-target="#envira_shortcode_id_<?php echo $data['post']->ID; ?>" class="dashicons dashicons-clipboard envira-clipboard">
		<span><?php _e( 'Copy to Clipboard', 'envira-gallery' ); ?></span>
	</a>
</div>

<?php
if ( ! empty( $data['gallery_data']['config']['slug'] ) ) {
	?>
	<div class="envira-code">
		<code id="envira_shortcode_slug_<?php echo $data['post']->ID; ?>"><?php echo '[envira-gallery slug="' . $data['gallery_data']['config']['slug'] . '"]'; ?></code>
		<a href="#" title="<?php _e( 'Copy Shortcode to Clipboard', 'envira-gallery' ); ?>" data-clipboard-target="#envira_shortcode_slug_<?php echo $data['post']->ID; ?>" class="dashicons dashicons-clipboard envira-clipboard">
			<span><?php _e( 'Copy to Clipboard', 'envira-gallery' ); ?></span>
		</a>
	</div>
	<?php
}
?>

<p><?php _e( 'You can also place this gallery into your template files by using <strong>one</strong> the template tag(s) below:', 'envira-gallery' ); ?></p>
<div class="envira-code">
	<code id="envira_template_tag_id_<?php echo $data['post']->ID; ?>"><?php echo 'if ( function_exists( \'envira_gallery\' ) ) { envira_gallery( \'' . $data['post']->ID . '\' ); }'; ?></code>
	<a href="#" title="<?php _e( 'Copy Template Tag to Clipboard', 'envira-gallery' ); ?>" data-clipboard-target="#envira_template_tag_id_<?php echo $data['post']->ID; ?>" class="dashicons dashicons-clipboard envira-clipboard">
		<span><?php _e( 'Copy to Clipboard', 'envira-gallery' ); ?></span>
	</a>
</div>

<?php 
if ( ! empty( $data['gallery_data']['config']['slug'] ) ) {
	?>
	<div class="envira-code">
	    <code id="envira_template_tag_slug_<?php echo $data['post']->ID; ?>"><?php echo 'if ( function_exists( \'envira_gallery\' ) ) { envira_gallery( \'' . $data['gallery_data']['config']['slug'] . '\', \'slug\' ); }'; ?></code>
	    <a href="#" title="<?php _e( 'Copy Template Tag to Clipboard', 'envira-gallery' ); ?>" data-clipboard-target="#envira_template_tag_slug_<?php echo $data['post']->ID; ?>" class="dashicons dashicons-clipboard envira-clipboard">
			<span><?php _e( 'Copy to Clipboard', 'envira-gallery' ); ?></span>
		</a>
	</div>
    <?php
}