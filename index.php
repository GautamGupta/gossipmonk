<?php get_header(); ?>

<div id="main-content">
	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">
				<div class="et_pb_text et_pb_bg_layout_light et_pb_text_align_center">
					<?php echo gm_get_ads_code(); ?>
				</div>
				<?php echo gm_et_pb_blog( array( 'module_class' => 'et_pb_blog_grid clearfix et_pb_bg_layout_light', 'posts_number' => 20, 'fullwidth' => 'off', 'ignore_sticky_posts' => true /* , 'show_categories' => 'off', 'show_author' => 'off', 'show_date' => 'off' */ ) ); ?>		
			</div> <!-- #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->
</div> <!-- #main-content -->

<?php get_footer(); ?>