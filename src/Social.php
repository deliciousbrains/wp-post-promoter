<?php

namespace DeliciousBrains\WPPromoter;

class Social {

	protected function get_post_types() {
		return apply_filters( 'dbi_post_promoter_post_types', array( 'post', 'doc' ) );
	}

	public function init() {
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'enqueue_scripts' ), 11 );
		add_action( 'admin_print_scripts-post.php', array( $this, 'enqueue_scripts' ), 11 );

		foreach ( $this->get_post_types() as $post_type ) {
			add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_meta_box' ) );
		}
	}

	public function enqueue_scripts() {
		Display::enqueue( 'social.min.js', 'social-promoter', array( 'jquery' ), null, true );
		Display::enqueue( 'social-styles.css', 'social-promoter' );
	}

	public function add_meta_box() {

		add_meta_box( 'don-king', 'Social Promoter', function ( $post, $metabox ) {

			$utms = array(
				array(
					'source'    => 'Email marketing software',
					'medium'    => 'email',
					'campaign'  => 'weekly-article',
					'icon'      => 'email',
					'share_url' => '',
				),
				array(
					'source'    => 'twitter.com',
					'medium'    => 'social',
					'campaign'  => 'weekly-article',
					'icon'      => 'twitter',
					'share_url' => 'https://twitter.com/intent/tweet?text={post_title}&url={url}',
				),
				array(
					'source'    => 'plus.google.com',
					'medium'    => 'social',
					'campaign'  => 'weekly-article',
					'icon'      => 'googleplus',
					'share_url' => 'https://plus.google.com/b/115144056720143073533/dashboard/overview',
				),
				array(
					'source'    => 'facebook.com',
					'medium'    => 'social',
					'campaign'  => 'weekly-article',
					'icon'      => 'facebook',
					'share_url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
				),
			);
			?>

			<div class="change-campaign">
				<label>
					Campaign
					<input type="text" name="dk_campaign" value="weekly-article">
				</label>
				<button type="button" class="button">Update</button>
			</div>

			<input type="hidden" name="dk_post_title" value="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>">

			<table class="don-king-share">
				<tbody>

				<?php
				$i = 0;

				$clone_post              = $post;
				$clone_post->post_status = 'publish'; // Ensure the pretty permalink is used for scheduled posts
				$clone_post->filter      = 'sample'; // Make sure get_permalink doesn't use get_post
				$post_url                = get_permalink( $clone_post );

				foreach ( $utms as $utm ) {
					$icon      = $utm['icon'];
					$share_url = $utm['share_url'];
					unset( $utm['icon'], $utm['share_url'] );

					$utm['content'] = urlencode( $post->post_name );

					foreach ( $utm as $key => $value ) {
						$new_key         = 'utm_' . $key;
						$utm[ $new_key ] = urlencode( $value );
						unset( $utm[ $key ] );
					}

					$url = add_query_arg( $utm, $post_url );

					printf( '
				<tr>
					<td><a href="%s" target="_blank" class="dashicons dashicons-%s"></a></td>
					<td class="share-url"><input type="text" name="dk_url[%d]" value="%s"></td>
				</tr>', $share_url, $icon, $i, $url );

					$i ++;
				}
				?>

				</tbody>

			</table>

			<?php

		} );

	}
}
