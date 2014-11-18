<?php
/**
 * BP Blogs Extended - "Blavatar"
 *
 * @see see https://buddypress.trac.wordpress.org/ticket/192
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die; ?>

<h4><?php _e( 'Change Site&#39;s Photo', 'bp-blogs-extended' ); ?></h4>

<?php do_action( 'bp_before_blog_avatar_upload_content' ); ?>

<form action="" method="post" id="avatar-upload-form" class="standard-form" enctype="multipart/form-data">

	<?php if ( 'upload-image' == bp_get_avatar_admin_step() ) : ?>

		<p><?php esc_html_e( "Upload an image to use as a profile photo for this site. The image will be shown on the site&#39;s details page, and in search results.", 'bp-blogs-extended' ); ?></p>

		<p>
			<input type="file" name="file" id="file" />
			<input type="submit" name="upload" id="upload" value="<?php esc_attr_e( 'Upload Image', 'bp-blogs-extended' ); ?>" />
			<input type="hidden" name="action" id="action" value="bp_avatar_upload" />
		</p>

		<?php if ( bpb_extended_blog_has_avatar() ) : ?>

			<p><?php _e( "If you'd like to remove the existing site&#39;s profile photo but not upload a new one, please use the delete site&#39;s profile photo button.", 'bp-blogs-extended' ); ?></p>

			<?php bp_button( array(
				'id'         => 'delete_blog_avatar',
				'component'  => 'blogs',
				'wrapper_id' => 'delete-blog-avatar-button',
				'link_class' => 'edit',
				'link_href'  => bpb_extended_blog_get_avatar_delete_link(),
				'link_title' => __( 'Delete Site Profile Photo', 'bp-blogs-extended' ),
				'link_text'  => __( 'Delete Site Profile Photo', 'bp-blogs-extended' )
			) ); ?>

		<?php endif; ?>

		<?php wp_nonce_field( 'bp_avatar_upload' ); ?>

	<?php endif; ?>

	<?php if ( 'crop-image' == bp_get_avatar_admin_step() ) : ?>

		<h4><?php _e( 'Crop Profile Photo', 'bp-blogs-extended' ); ?></h4>

		<img src="<?php bp_avatar_to_crop(); ?>" id="avatar-to-crop" class="avatar" alt="<?php esc_attr_e( 'Profile photo to crop', 'bp-blogs-extended' ); ?>" />

		<div id="avatar-crop-pane">
			<img src="<?php bp_avatar_to_crop(); ?>" id="avatar-crop-preview" class="avatar" alt="<?php esc_attr_e( 'Profile photo preview', 'bp-blogs-extended' ); ?>" />
		</div>

		<input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php esc_attr_e( 'Crop Image', 'bp-blogs-extended' ); ?>" />

		<input type="hidden" name="image_src" id="image_src" value="<?php bp_avatar_to_crop_src(); ?>" />
		<input type="hidden" id="x" name="x_crop" />
		<input type="hidden" id="y" name="y_crop" />
		<input type="hidden" id="w" name="w_crop" />
		<input type="hidden" id="h" name="h_crop" />

		<?php wp_nonce_field( 'bp_avatar_cropstore' ); ?>

	<?php endif; ?>

</form>

<?php do_action( 'bp_after_blog_avatar_upload_content' ); ?>
