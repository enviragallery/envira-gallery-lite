<?php
/**
 * Outputs the Gallery Settings Tabs and Config options.
 *
 * @since   1.5.0
 *
 * @package Envira_Gallery
 * @author 	Envira Team
 */
?>
<!-- Tabs -->
<ul id="envira-tabs-nav" class="envira-tabs-nav" data-container="#envira-tabs" data-update-hashbang="1">
	<?php
	// Iterate through the available tabs, outputting them in a list.
    $i = 0;
	foreach ( $data['tabs'] as $id => $title ) {
		$class = ( 0 === $i ? ' envira-active' : '' );
		?>
		<li class="envira-<?php echo $id; ?>">
			<a href="#envira-tab-<?php echo $id; ?>" title="<?php echo $title; ?>"<?php echo ( ! empty( $class ) ? ' class="' . $class . '"' : '' ); ?>>
				<?php echo $title; ?>
			</a>
		</li>
		<?php

		$i++;
	}
	?>
</ul>

<!-- Settings -->
<div id="envira-tabs" data-navigation="#envira-tabs-nav">
    <?php
    // Iterate through the registered tabs, outputting a panel and calling a tab-specific action,
    // which renders the settings view for that tab.
    $i = 0;
    foreach ( $data['tabs'] as $id => $title ) {
        $class = ( 0 === $i ? 'envira-active' : '' );
        ?>
        <div id="envira-tab-<?php echo $id; ?>" class="envira-tab envira-clear <?php echo $class; ?>">
        	<?php do_action( 'envira_gallery_tab_' . $id, $data['post'] ); ?>
        	
        </div>
        <?php
        $i++;
    }
    ?>
</div>

<div class="envira-clear"></div>