<?php
/**
 * BP Blogs Extended - Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

/**
 * Home screen for the blogs single item
 */
function bpb_extended_site_home() {
	if ( ! bpb_extended_is_single_item() ) {
		return false;
	}

	do_action( 'blogs_screen_blog_home' );

	bp_core_load_template( apply_filters( 'blog_template_blog_home', 'blogs/single/home' ) );
}

/**
 * Activity screen for the blogs single item
 */
function bpb_extended_site_activity() {
	if ( ! bpb_extended_is_single_item() ) {
		return false;
	}

	do_action( 'blogs_screen_blog_activity' );

	bp_core_load_template( apply_filters( 'blog_template_blog_activity', 'blogs/single/home' ) );
}

/**
 * Memebers screen for the blogs single item
 */
function bpb_extended_site_members() {
	if ( ! bpb_extended_is_single_item() ) {
		return false;
	}

	do_action( 'blogs_screen_blog_members' );

	bp_core_load_template( apply_filters( 'blog_template_blog_members', 'blogs/single/home' ) );
}

/**
 * Settings screen for the blogs single item
 */
function bpb_extended_site_settings() {
	if ( ! bpb_extended_is_blog_manage() ) {
		return false;
	}

	do_action( 'blogs_screen_blog_settings' );

	bp_core_load_template( apply_filters( 'blog_template_blog_settings', 'blogs/single/home' ) );
}

/**
 * Edit Avatar screen for the blogs single item
 *
 * @see https://buddypress.trac.wordpress.org/ticket/192
 */
function bpb_extended_site_avatar() {
	if ( ! bpb_extended_is_blog_manage() && ! bp_is_action_variable( 'edit-photo', 0 ) ) {
		return false;
	}

	// If the logged-in user doesn't have permission or if avatar uploads are disabled, then stop here
	if ( ! bp_is_item_admin() || (int) bp_get_option( 'bp-disable-avatar-uploads' ) || ! buddypress()->avatar->show_avatars ) {
		return false;
	}

	$bp = buddypress();

	$referer = trailingslashit( bpb_extended_get_blog_profile_link(
		bpb_extended_get_current_blog_id(),
		bpb_extended_get_current_blog_slug()
	) . 'manage/edit-photo' ) ;

	// If the blog admin has deleted the admin avatar
	if ( bp_is_action_variable( 'delete', 1 ) ) {

		// Check the nonce
		check_admin_referer( 'blog_avatar_delete' );

		if ( bp_core_delete_existing_avatar( array( 'item_id' => bpb_extended_get_current_blog_id(), 'object' => 'blog' ) ) ) {
			bp_core_add_message( __( 'The Site profile photo was deleted successfully!', 'bp-blogs-extended' ) );
		} else {
			bp_core_add_message( __( 'There was a problem deleting the Site profile photo; please try again.', 'bp-blogs-extended' ), 'error' );
		}

		bp_core_redirect( $referer );
	}

	if ( ! isset( $bp->avatar_admin ) ) {
		$bp->avatar_admin = new stdClass();
	}

	$bp->avatar_admin->step = 'upload-image';

	if ( ! empty( $_FILES ) ) {

		// Check the nonce
		check_admin_referer( 'bp_avatar_upload' );

		// Pass the file to the avatar upload handler
		if ( bp_core_avatar_handle_upload( $_FILES, 'bpb_extended_avatar_upload_dir' ) ) {
			$bp->avatar_admin->step = 'crop-image';

			// Make sure we include the jQuery jCrop file for image cropping
			add_action( 'wp_print_scripts', 'bp_core_add_jquery_cropper' );
		}

	}

	// If the image cropping is done, crop the image and save a full/thumb version
	if ( isset( $_POST['avatar-crop-submit'] ) ) {

		// Check the nonce
		check_admin_referer( 'bp_avatar_cropstore' );

		/**
		 * In the template blogs/single/settings/photo, to avoid the bug
		 * reported here https://buddypress.trac.wordpress.org/ticket/5999
		 * I choose to change the name of the posted values and added a
		 * '_crop' suffix to each.
		 */
		$args = array(
			'object'        => 'blog',
			'avatar_dir'    => 'blog-avatars',
			'item_id'       => bpb_extended_get_current_blog_id(),
			'original_file' => $_POST['image_src'],
			'crop_x'        => $_POST['x_crop'],
			'crop_y'        => $_POST['y_crop'],
			'crop_w'        => $_POST['w_crop'],
			'crop_h'        => $_POST['h_crop']
		);

		if ( ! bp_core_avatar_handle_crop( $args ) ) {
			bp_core_add_message( __( 'There was a problem cropping the Site profile photo.', 'bp-blogs-extended' ), 'error' );
		} else {
			bp_core_add_message( __( 'The new Site profile photo was uploaded successfully.', 'bp-blogs-extended' ) );
		}

		bp_core_redirect( $referer );
	}

	do_action( 'blogs_screen_blog_avatar' );

	bp_core_load_template( apply_filters( 'blog_template_blog_avatar', 'blogs/single/home' ) );
}

/**
 * User's profile blogs activity screen
 */
function bpb_extended_activity_member_screen() {
	bp_update_is_item_admin( bp_current_user_can( 'bp_moderate' ), 'activity' );

	do_action( 'blog_activity_member_screen' );

	bp_core_load_template( apply_filters( 'bp_activity_template_groups_activity', 'members/single/home' ) );
}

/**
 * Theme Compat Class
 *
 * No support is provided for BP Default or Specific BP Themes
 * This themes should be able to use the templates in the
 * plugin's templates directory to build their own templates
 * and include their header/sidebar and footer.
 */
class BP_Blogs_Extended_Screens {

	/**
	 * The constructor
	 */
	public function __construct() {
		$this->setup_actions();
	}

	public static function manage_screens() {
		$bpb_extended = bpb_extended();

		if ( empty( $bpb_extended->screens ) ) {
			$bpb_extended->screens = new self;
		}

		return $bpb_extended->screens;
	}

	private function setup_actions() {
		add_action( 'bp_setup_theme_compat', array( $this, 'use_theme_compat' ) );
	}

	public function use_theme_compat() {
		if ( bp_is_blogs_component() && ! bp_is_user() && bpb_extended_is_single_item() ) {

			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'blog_item_dummy_post' ) );
			add_filter( 'bp_replace_the_content',                    array( $this, 'blog_item_content'    ) );

		}
	}

	/**
	 * Update the global $post with single blog item data
	 */
	public function blog_item_dummy_post() {

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => bpb_extended()->current_blog->name,
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'is_page'        => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Filter the_content with the single blog item main template part
	 */
	public function blog_item_content() {
		bp_buffer_template_part( apply_filters( 'blos_template_blog_home', 'blogs/single/home' ) );
	}

}
add_action( 'bp_init', array( 'BP_Blogs_Extended_Screens', 'manage_screens' ) );
