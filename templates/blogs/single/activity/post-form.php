<?php
/**
 * BP Blogs Extended - Blog activity post form
 *
 * Can't use the BuddyPress post form as only groups and profile activities
 * can be posted from there.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

// Only usable in blog's single pages
if ( ! bpb_extended_is_single_item() || ! bpb_extended_current_blog() ) {
	return;
}
?>

<form action="<?php bp_activity_post_form_action(); ?>" method="post" id="whats-new-form" name="whats-new-form" role="complementary">

	<?php do_action( 'bp_before_blog_activity_post_form' ); ?>

	<div id="whats-new-avatar">
		<a href="<?php echo bp_loggedin_user_domain(); ?>">
			<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
		</a>
	</div>

	<p class="activity-greeting">
		<?php printf( __( "What's new in %s, %s?", 'bp-blogs-extended' ), bp_get_blog_name(), bp_get_user_firstname( bp_get_loggedin_user_fullname() ) );?>
	</p>

	<div id="whats-new-content">
		<div id="whats-new-textarea">
			<textarea class="bp-suggestions" name="whats-new" id="whats-new" cols="50" rows="10"><?php if ( isset( $_GET['r'] ) ) : ?>@<?php echo esc_textarea( $_GET['r'] ); ?> <?php endif; ?></textarea>
		</div>

		<div id="whats-new-options">
			<div id="whats-new-submit">
				<input type="submit" name="aw-whats-new-submit" id="aw-whats-new-submit" value="<?php esc_attr_e( 'Post Update', 'bp-blogs-extended' ); ?>" />
			</div>

			<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="blogs" />
			<input type="hidden" id="whats-new-post-in" name="whats-new-post-in" value="<?php bp_blog_id(); ?>" />

			<?php do_action( 'bp_blog_activity_post_form_options' ); ?>

		</div><!-- #whats-new-options -->
	</div><!-- #whats-new-content -->

	<?php wp_nonce_field( 'post_update', '_wpnonce_post_update' ); ?>
	<?php do_action( 'bp_after_blog_activity_post_form' ); ?>

</form><!-- #whats-new-form -->
