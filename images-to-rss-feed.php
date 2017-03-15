<?php
/*
 Plugin Name: BEA - Add images to rss feed
 Plugin URI: http://www.beapi.fr
 Description: Handle all wanted image's format sizes to be added as enclosure into site's feeds.
 Version: 1.0
 Author: BeAPI
 Author URI: https://www.beapi.fr
 --

 Copyright 2017 - BeAPI Team (technique@beapi.fr)
*/

/**
 * To be loaded as mu-plugin
 *
 * More infos :
 * @see : Inspired by https://plugins.svn.wordpress.org/wp-feed-post-thumbnail/tags/2.1.0/classes/plugin.php
 * @see : Official Media RSS spec : http://www.rssboard.org/media-rss
 */

/**
 * Add Media Element to Feed Item
 *
 * @author Maxime CULEA
 */
function bea_add_feed_item_media() {
	global $post;

	if ( ! has_post_thumbnail( $post->ID ) ) {
		return;
	}

	$thumbnail = get_post( get_post_thumbnail_id( $post->ID ) );
	if ( ! $thumbnail instanceof WP_Post ) {
		return;
	}

	$img_sizes = apply_filters( 'bea_rss_img_formats', array( 'full' ) );
	if ( empty( $img_sizes ) ) {
		return;
	}

	$user_nicename = get_user_meta( (int) $thumbnail->post_author, 'user_nicename', true );
	$title         = sanitize_text_field( $thumbnail->post_title );
	$excerpt       = wp_kses_post( $thumbnail->post_excerpt );

	foreach ( $img_sizes as $img_size ) :
		$img_attr = wp_get_attachment_image_src( $thumbnail->ID, $img_size );

		$img_url    = esc_url( $img_attr[0] );
		$img_width  = absint( $img_attr[1] );
		$img_height = absint( $img_attr[2] ); ?>

		<media:content url="<?php echo $img_url; ?>"
			type="<?php echo esc_attr( $thumbnail->post_mime_type ); ?>"
			medium="image"
			width="<?php echo $img_width; ?>"
			height="<?php echo $img_height; ?>">
			<media:title type="plain">
				<?php echo $title; ?>
			</media:title>
			<?php if ( ! empty( $excerpt ) ) : ?>
				<media:description type="plain">
					<?php echo $excerpt; ?>
				</media:description>
			<?php endif;
			if ( ! empty( $user_nicename ) ) : ?>
				<media:copyright>
					<?php echo esc_html( $user_nicename ); ?>
				</media:copyright>
			<?php endif; ?>
		</media:content>
	<?php endforeach;
}

add_action( 'rss2_item', 'bea_add_feed_item_media' );

/**
 * Add MRSS namespace to feed
 *
 * @author Maxime CULEA
 */
function bea_add_feed_namespace() {
	echo 'xmlns:media="http://search.yahoo.com/mrss/"';
}

add_action( 'rss2_ns', 'bea_add_feed_namespace' );
