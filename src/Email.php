<?php

namespace DeliciousBrains\WPPromoter;

class Email {

	public function init() {
		add_action( 'add_meta_boxes_post', array( $this, 'add_meta_box' ) );
		add_action( 'add_meta_boxes_doc', array( $this, 'add_meta_box' ) );

		add_action( 'admin_print_scripts-post-new.php', array( $this, 'enqueue_scripts' ), 11 );
		add_action( 'admin_print_scripts-post.php', array( $this, 'enqueue_scripts' ), 11 );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_action( 'draft_to_publish', array( $this, 'convert_email_message' ), 10, 2 );
		add_action( 'draft_to_future', array( $this, 'convert_email_message' ), 10, 2 );
	}

	public function convert_email_message( $post ) {
		if ( ! in_array( $post->post_type, apply_filters( 'dbi_post_promoter_post_types', array( 'post', 'doc' ) ) ) ) {
			return;
		}

		if ( isset( $_POST['article_promo_email_message'] ) ) {
			$message = $_POST['article_promo_email_message'];
		}
		else {
			$message = get_post_meta( $post->ID, 'article_promo_email_message', true );
		}

		if ( isset( $_POST['article_promo_email_subject'] ) ) {
			$subject = $_POST['article_promo_email_subject'];
		}
		else {
			$subject = get_post_meta( $post->ID, 'article_promo_email_subject', true );
		}

		$subject = trim( $subject );
		if ( ! $subject ) {
			$subject = $post->post_title;
		}

		if ( isset( $_POST['article_promo_email_subject'] ) ) {
			$_POST['article_promo_email_subject'] = $subject;
		}
		else {
			update_post_meta( $post->ID, 'article_promo_email_subject', $subject );
		}

		if ( ! $message ) {
			return;
		}

		$clone_post = $post;
		$clone_post->post_status = 'publish'; // Ensure the pretty permalink is used for scheduled posts

		$url = get_permalink( $clone_post );

		$utm = array(
			'utm_source' => urlencode( 'Email marketing software' ),
			'utm_medium' => 'email',
			'utm_campaign' => 'weekly-article',
			'utm_content' => urlencode( $post->post_name ),
		);

		$tagged_url = add_query_arg( $utm, $url );
		$link = sprintf( '<a href="%s">%s</a>', $tagged_url, $url );

		// Standardize newline characters to "\n".
		$message = str_replace( array( "\r\n", "\r" ), "\n", $message );

		// Normalize <br>
		$message = str_replace( array( '<br>', '<br/>' ), '<br />', $message );

		// Replace any new line characters on their own line with a <br />
		$message = preg_replace( '|^\n|m', "<br />\n", $message );

		// Replace any new line characters that aren't preceded by a <br /> with a <br />.
		$message = preg_replace( '|(?<!<br />)\s*\n|', "<br />\n", $message );

		$message = str_replace( '[link]', $link, $message );
		$message = str_replace( '[signature]', $this->get_author_html( $post ), $message );

		if ( isset( $_POST['article_promo_email_message'] ) ) {
			$_POST['article_promo_email_message'] = $message;
		}
		else {
			update_post_meta( $post->ID, 'article_promo_email_message', $message );
		}
	}

	public function save_post( $post_id, $post ) {
		if ( ! isset( $_POST['article_promo_email_message'] ) ) {
			return;
		}

		if ( ! isset( $_POST['article_promo_email_nonce'] ) || ! wp_verify_nonce( $_POST['article_promo_email_nonce'], 'article-promo-email' ) ) {
			return;
		}

		$post_type = get_post_type_object( $post->post_type );
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return;
		}

		if ( ! empty( trim( $_POST['article_promo_email_subject'] ) ) ) {
			update_post_meta( $post_id, 'article_promo_email_subject', $_POST['article_promo_email_subject'] );
		}

		update_post_meta( $post_id, 'article_promo_email_message', $_POST['article_promo_email_message'] );
	}

	public function get_author_html( $post ) {
		$user = new \WP_User( $post->post_author );
		ob_start();
		?>
		<table style="border-spacing: 0;">
			<tr>
				<td valign="top" style="font-size: 14px; line-height: 16px; font-family: Helvetica, Arial, sans-serif; padding-bottom: 14px;">
					<?php echo $user->first_name, ' ', $user->last_name; ?><br>
					<a style="font-size: 12px; color: #000000;" href="https://deliciousbrains.com?utm_source=Email%20marketing%20software&utm_medium=email&utm_campaign=email-signature">Delicious Brains Inc.</a>
				</td>
				<td>&nbsp;&nbsp;</td>
				<td>
					<img src="<?php echo get_avatar_url( $user->ID ); ?>" alt="" width="40" height="40" style="margin: -4px 0 0 0; font-family: Arial, sans-serif; border-radius: 50%;">
				</td>
			</tr>
		</table>
		<?php
		$html = ob_get_clean();
		$html = preg_replace( '@^\t\t@m', '', $html );
		return $html;
	}

	public function add_meta_box( $post ) {
		add_meta_box( 'article-promo-email', 'Email Draft', function( $post, $metabox ) {
			$message = get_post_meta( $post->ID, 'article_promo_email_message', true );
			$subject = get_post_meta( $post->ID, 'article_promo_email_subject', true );

			if ( ! $message ) {
				$message = "{% if subscriber.first_name %}Hey {{ subscriber.first_name }}{% else %}Hey{% endif %},\n\n\n\n[link]\n\nCheers,\n\n[signature]";
			}

			wp_nonce_field( 'article-promo-email', 'article_promo_email_nonce' );
			?>

			<input type="text" name="article_promo_email_subject" placeholder="Email Subject" value="<?php echo esc_attr( $subject ); ?>">

			<textarea name="article_promo_email_message" rows="12" placeholder="Email message goes here"><?php echo esc_html( $message ); ?></textarea>

			<p>
				<?php if ( ! in_array( get_post_status(), array( 'publish', 'future' ) ) ) : ?>
					When this post is published, the shortcode [link] will be replaced
					with a link to the post with the proper utm tags. The shortcode [signature]
					will be replaced with the signature of the author of the post and email.
					Newline characters will be replaced with &lt;br&gt; tags. The subject will
					be filled with the title of the post if it's empty.
				<?php else : ?>
					<a href="https://www.getdrip.com/6392218/broadcasts">Create a new broadcast in Drip</a>
				<?php endif; ?>
			</p>

			<?php
		} );
	}

	public function enqueue_scripts() {
		Display::enqueue( 'article-promo-email.css', 'article-promo-email' );
	}
}
