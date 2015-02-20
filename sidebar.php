<?php
if ( ( is_single() || is_page() ) && 'et_full_width_page' === get_post_meta( get_the_ID(), '_et_pb_page_layout', true ) )
	return;

if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<div id="sidebar">
		<div id="sidebar-ad" class="et_pb_widget widget_text">
			<div class="textwidget"><?php echo gm_get_ads_code(); ?></div>
		</div>
		<div id="sidebar-inside">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div>
	</div> <!-- end #sidebar -->
<?php endif; ?>