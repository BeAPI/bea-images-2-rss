<?php
/*
 Plugin Name: BEA - Add images to rss feed
 Plugin URI: http://www.beapi.fr
 Description: Handle all wanted image's format sizes to be added as enclosure into site's feeds.
 Version: 1.1
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
 * @use SimplePie_Item $item->get_enclosures()
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

	// Add the media:group to complain with SimplePie parsing, even if media spec don't need it if inside an item
	echo "<media:group>";
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
			<media:description>
				<?php if ( is_array ( $img_size ) ) {
                                        $img_size = ( ! empty ( $img_size['width'] ) && ! empty( $img_size['height'] ) ) ? 
sprintf( '%s-%s', $img_size['width'], $img_size['height'] ) : implode( '-', $img_size );
                                }
                                echo $img_size; ?>
			</media:description>
			<?php if ( ! empty( $user_nicename ) ) : ?>
				<media:copyright>
					<?php echo esc_html( $user_nicename ); ?>
				</media:copyright>
			<?php endif; ?>
		</media:content>
	<?php endforeach;
	echo "</media:group>";
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
