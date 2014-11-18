<?php
/**
 * BP Blogs Extended - Blog manage - general
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;
?>

<?php do_action( 'bp_before_blog_settings_template' ); ?>

<form action="" method="post" class="standard-form" id="settings-form">

	<h4><?php esc_html_e( 'General settings', 'bp-blogs-extended' ); ?></h4>

	<label for="blog_name"><?php esc_html_e( 'Site Name', 'bp-blogs-extended' ); ?></label>
	<input type="text" name="bpb_extended[blogname]" id="blog_name" value="<?php echo esc_html( bp_get_blog_name() ); ?>" class="settings-input" />

	<label for="blog_description"><?php esc_html_e( 'Site Description', 'bp-blogs-extended' ); ?></label>
	<input type="text" name="bpb_extended[blogdescription]" id="blog_description" value="<?php echo esc_html( bp_get_blog_description() ); ?>" class="settings-input" />

	<?php do_action( 'bp_blog_settings_after_general' ); ?>

	<h4><?php esc_html_e( 'Members settings', 'bp-blogs-extended' ); ?></h4>

	<label for="blog_open">
		<input type="checkbox" name="bpb_extended[members][blog_open]" id="blog_open" value="1" <?php checked( 1, bpb_extended_blog_open() ); ?> />
		<?php esc_html_e( 'Allow community members to subscibe to your site', 'bp-blogs-extended' ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'Subscribers will become members of your site.', 'bp-blogs-extended' ); ?></p>

	<label for="blog_updates">
		<input type="checkbox" name="bpb_extended[members][blog_updates]" id="blog_updates" value="1" <?php checked( 1, bpb_extended_blog_updates() ); ?> />
		<?php esc_html_e( 'Allow your site members to publish activity updates', 'bp-blogs-extended' ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'By default, only site administrators can publish updates.', 'bp-blogs-extended' ); ?></p>

	<?php do_action( 'bp_blog_settings_after_members' ); ?>

	<?php if ( bp_is_active( 'activity' ) ) : ?>
		<h4><?php esc_html_e( 'Post types tracking settings', 'bp-blogs-extended' ); ?></h4>

		<label for="blog_public">
			<input type="checkbox" name="bpb_extended[blog_public]" id="blog_public" value="0" <?php checked( 0, bpb_extended_blog_public() ); ?> />
			<?php esc_html_e( 'Discourage search engines from indexing this site', 'bp-blogs-extended' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'If this setting is on, no post types will be tracked in the activity stream and the site will not be visible in the sites directory.', 'bp-blogs-extended' ); ?></p>

		<?php if ( bpb_extended_blog_public() ) bpb_extended_post_types_management() ; ?>

		<?php do_action( 'bp_blog_settings_after_activity' ); ?>

	<?php endif ; ?>

	<?php do_action( 'bp_blog_general_settings_before_submit' ); ?>

	<div class="submit">
		<input type="submit" name="bpb_extended[submit]" value="<?php esc_attr_e( 'Save Changes', 'bp-blogs-extended' ); ?>" id="submit" class="auto" />
	</div>

	<?php do_action( 'bp_blog_settings_after_submit' ); ?>

	<?php wp_nonce_field( 'blog_settings_general' ); ?>

</form>

<?php do_action( 'bp_after_blog_settings_template' ); ?>
