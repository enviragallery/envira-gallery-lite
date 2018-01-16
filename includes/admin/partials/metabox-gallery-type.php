<?php
/**
 * Outputs the Gallery Type Tab Selector and Panels
 *
 * @since   1.5.0
 *
 * @package Envira_Gallery
 * @author 	Envira Team
 */

?>
<h2 id="envira-types-nav" class="nav-tab-wrapper envira-tabs-nav" data-container="#envira-types" data-update-hashbang="0">
	<label class="nav-tab nav-tab-native-envira-gallery<?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) == 'default' ) ? ' envira-active' : '' ); ?>" for="envira-gallery-type-default" data-tab="#envira-gallery-native">
		<input id="envira-gallery-type-default" type="radio" name="_envira_gallery[type]" value="default" <?php checked( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ), 'default' ); ?> /> 
		<span><?php _e( 'Native Envira Gallery', 'envira-gallery' ); ?></span>
	</label>
	
	<a href="#envira-gallery-external" title="<?php _e( 'External Gallery', 'envira-gallery' ); ?>" class="nav-tab nav-tab-external-gallery<?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) != 'default' ) ? ' envira-active' : '' ); ?>">
		<span><?php _e( 'External Gallery', 'envira-gallery' ); ?></span>
	</a>
</h2>

<!-- Types -->
<div id="envira-types" data-navigation="#envira-types-nav">
	<!-- Native Envira Gallery - Drag and Drop Uploader -->
	<div id="envira-gallery-native" class="envira-tab envira-clear<?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) == 'default' ) ? ' envira-active' : '' ); ?>">
		<!-- Errors -->
	    <div id="envira-gallery-upload-error"></div>

	    <!-- WP Media Upload Form -->
	    <?php 
	    media_upload_form();
	    ?>
	    <script type="text/javascript">
	        var post_id = <?php echo $data['post']->ID; ?>, shortform = 3;
	    </script>
	    <input type="hidden" name="post_id" id="post_id" value="<?php echo $data['post']->ID; ?>" />
	</div>

	<!-- External Gallery -->
	<div id="envira-gallery-external" class="envira-tab envira-clear<?php echo ( ( $data['instance']->get_config( 'type', $data['instance']->get_config_default( 'type' ) ) != 'default' ) ? ' envira-active' : '' ); ?>">
	
		<?php	$upgrade_link = Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link(); ?>
		<p class="envira-intro"><?php _e( 'Create Dynamic Galleries with Envira', 'envira-gallery' ); ?></p>
		<ul id="envira-gallery-types-nav">
			<li id="envira-gallery-type-instagram">
				<a href="<?php echo $upgrade_link; ?>" title="<?php _e( 'Build Galleries from Instagram images.', 'envira-gallery' ); ?>" target="_blank">
					<div class="icon"></div>
					<div class="title"><?php _e( 'Instagram', 'envira-gallery' ); ?></div>
				</a>
			</li>
		</ul>
		<p>
			<?php _e( 'Envira Pro allows you to build galleries from Instagram photos, images from your posts, and more.', 'envira-gallery' ); ?>
		</p>
		<p>
			<a href="<?php echo $upgrade_link; ?>" class="button button-primary button-x-large" title="<?php _e( 'Click Here to Upgrade', 'envira-gallery' ); ?>" target="_blank">
				<?php _e( 'Click Here to Upgrade', 'envira-gallery' ); ?>
			</a>
		</p>

	</div>
</div>