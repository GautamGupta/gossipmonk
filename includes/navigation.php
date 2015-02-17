<div class="pagination clearfix">
	<div class="alignleft"><?php if ( did_action( 'gm_single_et_pb_blog_start' ) ) gm_next_posts_link(esc_html__('&laquo; Previous Page','Divi')); else next_posts_link(esc_html__('&laquo; Previous Page','Divi')); ?></div>
	<div class="alignright"><?php if ( did_action( 'gm_single_et_pb_blog_start' ) ) gm_previous_posts_link(esc_html__('Next Page &raquo;', 'Divi')); else previous_posts_link(esc_html__('Next Page &raquo;', 'Divi')); ?></div>
</div>