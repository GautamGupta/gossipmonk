<?php

/* Remove ?ver from Javascript urls */

function gm_rtp_rssv_scripts() {
    global $wp_scripts;
    if (!is_a($wp_scripts, 'WP_Scripts'))
        return;
    foreach ($wp_scripts->registered as $handle => $script)
        $wp_scripts->registered[$handle]->ver = null;
}

function gm_rtp_rssv_styles() {
    global $wp_styles;
    if (!is_a($wp_styles, 'WP_Styles'))
        return;
    foreach ($wp_styles->registered as $handle => $style)
        $wp_styles->registered[$handle]->ver = null;
}

add_action('wp_print_scripts', 'gm_rtp_rssv_scripts', 999);
add_action('wp_print_footer_scripts', 'gm_rtp_rssv_scripts', 999);

add_action('admin_print_styles', 'gm_rtp_rssv_styles', 999);
add_action('wp_print_styles', 'gm_rtp_rssv_styles', 999);

/* Remove unnecessary ET stuff */
add_action( 'after_setup_theme', function() { remove_action( 'init', 'et_pb_register_posttypes', 0 ); } );

/* Post types - video etc */

function gm_is_et_pb_blog() {
	if (is_home() || did_action( 'et_pb_blog_start' ) )
		return true;
	return false;
}

function et_get_first_video() {
	$first_oembed  = '';
	$custom_fields = get_post_custom();

	foreach ( $custom_fields as $key => $custom_field ) {
		if ( 0 !== strpos( $key, '_oembed_' ) ) {
			continue;
		}

		$first_oembed = $custom_field[0];

		$video_width  = (int) apply_filters( 'et_blog_video_width', 1080 );
		// $video_height = (int) apply_filters( 'et_blog_video_height', 630 );

		$first_oembed = preg_replace( '/<embed /', '<embed wmode="transparent" ', $first_oembed );
		$first_oembed = preg_replace( '/<\/object>/','<param name="wmode" value="transparent" /></object>', $first_oembed );

		$first_oembed = preg_replace( "/width=\"[0-9]*\"/", "width={$video_width}", $first_oembed );
		// $first_oembed = preg_replace( "/height=\"[0-9]*\"/", "height={$video_height}", $first_oembed );

		// Autoplay
		$first_oembed = preg_replace('@embed/([^"&]*)@', 'embed/$1&showinfo=0&autoplay=1&autohide=1&iv_load_policy=3&modestbranding=1&theme=light', $first_oembed);

		break;
	}

	return ( '' !== $first_oembed ) ? $first_oembed : false;
}

add_filter( 'et_blog_video_width', function( $default ) { return gm_is_et_pb_blog() ? 320 : $default; } );
add_filter( 'et_blog_video_height', function( $default ) { return gm_is_et_pb_blog() ? 186 : $default; } );

/* Login Logo */

function gm_login_logo() { ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(http://gossipmonk.com/wp-content/uploads/2015/01/gossipmonk-logo-sq-80.png);
            padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'gm_login_logo' );

function gm_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'gm_login_logo_url' );

function gm_login_logo_url_title() {
    return get_bloginfo( 'name' );
}
add_filter( 'login_headertitle', 'gm_login_logo_url_title' );

/* Tracking authors */

function gm_analytics_track_author( $gaq_push ) {
	if ( is_single() || is_author() ) {
		$insert = array( "'set', 'contentGroup1', '" . get_the_author() . "'" );
		array_splice( $gaq_push, 2, 0, $insert );
	}

	return $gaq_push;
}

add_filter( 'yoast-ga-push-array-universal', 'gm_analytics_track_author' );

/* Microformat fix */

// Also add class="entry-title" to index.php and single.php

function et_postinfo_meta( $postinfo, $date_format, $comment_zero, $comment_one, $comment_more ){
	global $themename;

	$postinfo_meta = '';

	if ( in_array( 'author', $postinfo ) )
		$postinfo_meta .= ' ' . esc_html__('by',$themename) . ' ' . et_get_the_author_posts_link();

	if ( in_array( 'date', $postinfo ) ) {
		if ( in_array( 'author', $postinfo ) ) $postinfo_meta .= ' | ';
		$postinfo_meta .= '<span class="updated">' . get_the_time( $date_format ) . '</span>';
	}

	if ( in_array( 'categories', $postinfo ) ){
		if ( in_array( 'author', $postinfo ) || in_array( 'date', $postinfo ) ) $postinfo_meta .= ' | ';
		$postinfo_meta .= get_the_category_list(', ');
	}

	if ( in_array( 'comments', $postinfo ) ){
		if ( in_array( 'author', $postinfo ) || in_array( 'date', $postinfo ) || in_array( 'categories', $postinfo ) ) $postinfo_meta .= ' | ';
		$postinfo_meta .= et_get_comments_popup_link( $comment_zero, $comment_one, $comment_more );
	}

	echo $postinfo_meta;
}

function et_get_the_author_posts_link(){
	global $authordata, $themename;

	$link = sprintf(
		'<a href="%1$s" title="%2$s" rel="author" class="author vcard"><span class="fn">%3$s</span></a>',
		esc_url( get_author_posts_url( $authordata->ID, $authordata->user_nicename ) ),
		esc_attr( sprintf( __( 'Posts by %s', $themename ), get_the_author() ) ),
		get_the_author()
	);

	return apply_filters( 'the_author_posts_link', $link );
}

/* SEO fix: Remove duplicate fb meta's -- yoast seo already does the job */
remove_action( 'wp_head', 'fbgraphinfo' );

/* SEO fix: Add author meta tag, apparently Yoast doesn't */
function gm_author_meta() {
	global $post;

	if ( is_single() && 'post' == get_post_type() ) {
		echo '<meta name="author" content="' . get_the_author() . '" />' . "\n";
	}
}
add_action( 'wpseo_head', 'gm_author_meta', 31 );

/* Infinite scroll & Javascript Fixes */

// Navigation on post page for infinite scroll, so that the post page is not loaded as next page & we can have canonical redirect

// Both single and index should display same number of posts per page
// Permalinks should be active

function gm_next_posts_link( $label = null, $max_page = 0 ) {
	global $paged, $wp_query;

	$paged = get_query_var( 'paged' );

	if ( !$max_page )
		$max_page = $wp_query->max_num_pages;

	if ( !$paged )
		$paged = 1;

	$nextpage = intval($paged) + 1;

	if ( null === $label )
		$label = __( 'Next Page &raquo;' );

	if ( $nextpage <= $max_page ) {
		$attr = apply_filters( 'next_posts_link_attributes', '' );

		echo '<a href="' . home_url() . '/page/' . $nextpage . "\" $attr>" . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) . '</a>';
	}
}

function gm_previous_posts_link( $label = null ) {
	global $paged;

	$paged = get_query_var( 'paged' );

	if ( null === $label )
		$label = __( '&laquo; Previous Page' );

	if ( $paged > 1 ) {
		$nextpage = intval($paged) - 1;
		if ( $nextpage < 1 )
			$nextpage = 1;
		$attr = apply_filters( 'previous_posts_link_attributes', '' );
		echo '<a href="' . home_url() . '/page/' . $nextpage . "\" $attr>". preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) .'</a>';
	}
}

// remove_filter('template_redirect', 'redirect_canonical');

function gossipmonk_scripts() {
	wp_enqueue_script( 'infinitescroll', get_stylesheet_directory_uri() . '/js/jquery.infinitescroll.min.js', array( 'jquery' ), '1.5.100504', true );
	wp_enqueue_script( 'gossipmonk', get_stylesheet_directory_uri() . '/js/custom.js', array( 'jquery', 'imagesloaded', 'jquery-masonry-3', 'infinitescroll' ), '0.1', true );
}
add_action( 'wp_enqueue_scripts', 'gossipmonk_scripts' );

/* Ad */

function gm_get_mobile_ads_code() {
	return '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script><ins class="adsbygoogle" style="display:inline-block;width:300px;height:250px" data-ad-client="ca-pub-9374071255921921" data-ad-slot="6817047312"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
}

function gm_get_responsive_ads_code() {
	return et_get_option( 'divi_468_adsense' );
}

function gm_get_ads_code() {
	return /* wp_is_mobile() ? gm_get_mobile_ads_code() : */ gm_get_responsive_ads_code();
}

function gm_single_post_ad_mid( $content = '' ) {
	if ( !is_single() || 'post' != get_post_type() || 'draft' == get_post_status() )
		return $content;

	$mid_post = '<div class="et-single-post-ad et-single-post-ad-mid">' . gm_get_ads_code() . '</div> <!-- .et-single-post-ad .et-single-post-ad-mid -->';

	$closing_p = '</p>';
	$paragraphs = explode( $closing_p, $content );
	$index = 2;
	foreach ($paragraphs as $paragraph_id => $paragraph) {
		if ( trim( $paragraph ) )
			$paragraphs[$paragraph_id] .= $closing_p;

		if ( $paragraph_id == $index )
			$paragraphs[$paragraph_id] .= $mid_post;
	}

	$content = implode( '', $paragraphs );

	return $content;
}
add_filter( 'the_content', 'gm_single_post_ad_mid' );

function gm_single_post_ad_bottom( $content = '' ) {
	if ( !is_single() || 'post' != get_post_type() || 'draft' == get_post_status() )
		return $content;

	return $content . '<div class="et-single-post-ad et-single-post-ad-bottom">' . gm_get_ads_code() . '</div><!-- .et-single-post-ad -->';
}
add_filter( 'the_content', 'gm_single_post_ad_bottom', -5 );

/* Monarch -- Add text before share buttons and space after */
function gm_monarch_inline_bottom_heading( $content = '' ) {
	global $themename;

	if ( class_exists( 'ET_Monarch' ) && is_single() && 'post' == get_post_type() /* && ET_Monarch::check_applicability( ET_Monarch::monarch_options[ 'sharing_inline_post_types' ], 'inline' ) && in_array( ET_Monarch::monarch_options[ 'sharing_inline_icons_location' ], array( 'below', 'above_below' ) */ )
		$content .= '<h2>' . __( 'Share This Post With Your Friends', $themename ) . '</h2>';

	return $content;

}
// add_filter( 'the_content', 'gm_monarch_inline_bottom_heading', -1 );

/* Remove stickies from front page */
function gm_modify_front_main_query( $query ) {
	if ( $query->is_home() && $query->is_main_query() ) { // Run only on the homepage
		$query->set( 'ignore_sticky_posts', true );
	}
}
// Hook my above function to the pre_get_posts action
add_action( 'pre_get_posts', 'gm_modify_front_main_query' );

/* Modified et_pb_blog for our needs, used everywhere */
function gm_et_pb_blog( $atts ) {
	extract( shortcode_atts( array(
			'custom_query' => false,
			'module_id' => '',
			'module_class' => '',
			'fullwidth' => 'on',
			'posts_number' => 10,
			'include_categories' => '',
			'meta_date' => 'M j, Y',
			'show_thumbnail' => 'on',
			'show_content' => 'off',
			'show_author' => 'on',
			'show_date' => 'on',
			'show_categories' => 'on',
			'show_pagination' => 'on',
			'background_layout' => 'light',
			'show_more' => 'off',
			'ignore_sticky_posts' => false,
		), $atts
	) );

	global $paged;

	$container_is_closed = false;

	if ( 'on' !== $fullwidth ){
		wp_enqueue_script( 'jquery-masonry-3' );
	}

	$args = array( 'posts_per_page' => (int) $posts_number, 'ignore_sticky_posts' => (bool) $ignore_sticky_posts );

	$et_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );

	if ( is_front_page() ) {
		$paged = $et_paged;
	}

	if ( '' !== $include_categories )
		$args['cat'] = $include_categories;

	if ( ! is_search() ) {
		$args['paged'] = $et_paged;
	}

	ob_start();

	if ( !empty( $custom_query ) )
		query_posts( $args );

	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();

			$post_format = get_post_format();

			$thumb = '';

			$width = 'on' === $fullwidth ? 1080 : 400;
			$width = (int) apply_filters( 'et_pb_blog_image_width', $width );

			$height = 'on' === $fullwidth ? 675 : 250;
			$height = (int) apply_filters( 'et_pb_blog_image_height', $height );
			$classtext = 'on' === $fullwidth ? 'et_pb_post_main_image' : '';
			$titletext = get_the_title();
			$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Blogimage' );
			$thumb = $thumbnail["thumb"];

			$no_thumb_class = '' === $thumb || 'off' === $show_thumbnail ? ' et_pb_no_thumb' : '';

			if ( in_array( $post_format, array( /* 'video', */ 'gallery' ) ) ) {
				$no_thumb_class = '';
			} ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'et_pb_post' . $no_thumb_class ); ?>>

		<?php
			et_divi_post_format_content();

			if ( ! in_array( $post_format, array( 'link', 'audio', 'quote' ) ) ) {
				/* if ( 'video' === $post_format && false !== ( $first_video = et_get_first_video() ) ) :
					printf(
						'<div class="et_main_video_container">
							%1$s
						</div>',
						$first_video
					);
				else*/if ( 'gallery' === $post_format ) :
					et_gallery_images();
				elseif ( '' !== $thumb	 && 'on' === $show_thumbnail ) :
					if ( 'on' !== $fullwidth ) echo '<div class="et_pb_image_container">'; ?>
						<a href="<?php the_permalink(); ?>">
							<?php if ( 'video' === $post_format ) : ?>
								<span class="playbutton"></span>
							<?php endif; ?>
							<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height ); ?>
						</a>
				<?php
					if ( 'on' !== $fullwidth ) echo '</div> <!-- .et_pb_image_container -->';
				endif;
			} ?>

		<?php if ( 'off' === $fullwidth || ! in_array( $post_format, array( 'link', 'audio', 'quote', 'gallery' ) ) ) { ?>
			<?php if ( ! in_array( $post_format, array( 'link', 'audio' ) ) ) { ?>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php } ?>

			<?php
				if ( 'on' === $show_author || 'on' === $show_date || 'on' === $show_categories ) {
					printf( '<p class="post-meta">%1$s %2$s %3$s %4$s %5$s</p>',
						(
							'on' === $show_author
								? sprintf( __( 'by %s', 'Divi' ), et_get_the_author_posts_link() )
								: ''
						),
						(
							( 'on' === $show_author && 'on' === $show_date )
								? ' | '
								: ''
						),
						(
							'on' === $show_date
								? sprintf( __( '%s', 'Divi' ), get_the_date( $meta_date ) )
								: ''
						),
						(
							(( 'on' === $show_author || 'on' === $show_date ) && 'on' === $show_categories)
								? ' | '
								: ''
						),
						(
							'on' === $show_categories
								? get_the_category_list(', ')
								: ''
						)
					);
				}

				if ( 'on' === $show_content ) {
					global $more;
					$more = null;

					the_content( __( 'read more...', 'Divi' ) );
				} else {
					if ( has_excerpt() ) {
						the_excerpt();
					} else {
						truncate_post( 270 );
					}
					$more = 'on' == $show_more ? sprintf( ' <a href="%1$s" class="more-link" >%2$s</a>' , esc_url( get_permalink() ), __( 'read more', 'Divi' ) )  : '';
					echo $more;
				} ?>
		<?php } // 'off' === $fullwidth || ! in_array( $post_format, array( 'link', 'audio', 'quote', 'gallery' ?>

		</article> <!-- .et_pb_post -->
<?php
		} // endwhile

		if ( 'on' === $show_pagination && ! is_search() ) {
			echo '</div> <!-- .et_pb_posts -->';

			$container_is_closed = true;

			if ( function_exists( 'wp_pagenavi' ) )
				wp_pagenavi();
			else
				get_template_part( 'includes/navigation', 'index' );
		}

		wp_reset_query();
	} else {
		get_template_part( 'includes/no-results', 'index' );
	}

	$posts = ob_get_contents();

	ob_end_clean();

	$class = " et_pb_bg_layout_{$background_layout}";

	$output = sprintf(
		'<div%5$s class="%1$s%3$s%6$s">
			%2$s
		%4$s',
		( 'on' === $fullwidth ? 'et_pb_posts' : 'et_pb_blog_grid clearfix' ),
		$posts,
		esc_attr( $class ),
		( ! $container_is_closed ? '</div> <!-- .et_pb_posts -->' : '' ),
		( '' !== $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '' ),
		( '' !== $module_class ? sprintf( ' %1$s', esc_attr( $module_class ) ) : '' )
	);

	if ( 'on' !== $fullwidth )
		$output = sprintf( '<div class="et_pb_blog_grid_wrapper">%1$s</div>', $output );

	return $output;
}
remove_shortcode( 'et_pb_blog' );
add_shortcode( 'et_pb_blog', 'gm_et_pb_blog' );
