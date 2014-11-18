<?php
/**
 * BP Blogs Extended - Actions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

/**
 * Subscribe / Unsubscribe a member to the blog item
 */
function bpb_extended_manage_subsriptions() {
	if ( ! empty( $_GET['action'] ) && in_array( $_GET['action'], array( 'join', 'leave' ) ) ) {

		check_admin_referer( 'blogs_' . $_GET['action'] . '_blog' );

		$referer = wp_get_referer();
		$result = array(
			'message' => __( 'We were not able to perform this action, please try again later', 'bp-blogs-extended' ),
			'type'    => 'error',
		);

		if ( is_callable( 'bpb_extended_' . $_GET['action'] . '_blog' ) ) {
			$result = call_user_func( 'bpb_extended_' . $_GET['action'] . '_blog' );
		}

		bp_core_add_message( $result['message'], $result['type'] );
		bp_core_redirect( $referer );
	}
}
add_action( 'blogs_screen_blog_home', 'bpb_extended_manage_subsriptions' );

/**
 * Save the blog item settings
 */
function bpb_extended_save_blog_settings() {

	if ( ! empty( $_POST['bpb_extended']['submit'] ) ) {

		check_admin_referer( 'blog_settings_general' );

		unset( $_POST['bpb_extended']['submit'] );
		$options = $_POST['bpb_extended'];

		if ( ! isset( $options['blog_public'] ) ) {
			$options['blog_public'] = 1;
		}

		$old_post_types = $new_post_types = array();
		if ( isset( $options['post_types']['active'] ) ) {
			$old_post_types = explode( ',', $options['post_types']['active'] );
			unset( $options['post_types']['active'] );
		}

		if ( ! empty( $options['post_types'] ) && is_array( $options['post_types'] ) ) {
			$new_post_types = $options['post_types'];
			unset( $options['post_types'] );
		}

		$members_options = array(
			'blog_open'   => 0,
			'blog_updates' => 0,
		);

		if ( ! empty( $options['members'] ) ) {
			$members_options = wp_parse_args( $options['members'], $members_options );
			unset( $options['members'] );
		}

		$blog_id = bpb_extended_get_current_blog_id();
		$referer = wp_unslash( $_POST['_wp_http_referer'] );
		$message = __( 'We were not able to perform this action, please try again later', 'bp-blogs-extended' );

		if ( empty( $blog_id ) ) {
			bp_core_add_message( $message, 'error' );
			bp_core_redirect( $referer );
		}

		$success = 'error';

		// Update general settings
		switch_to_blog( $blog_id );

		if ( current_user_can( 'manage_options' ) ) {

			foreach ( $options as $key => $option ) {
				$sanitized_option = sanitize_option( $key, $option );

				update_option( $key, $sanitized_option );
			}
			$message = __( 'Settings saved.', 'bp-blogs-extended' );
			$success = '';

		} else {
			$message[] = __( 'You cannot update this site&#39;s options.', 'bp-blogs-extended' );
		}

		restore_current_blog();

		// Update post type settings
		if ( ! empty( $old_post_types ) || ! empty( $new_post_types ) ) {
			bp_blogs_update_blogmeta( $blog_id, 'post_types_tracked', $new_post_types );
			$message .= __( 'The post type section is a demo, it will not affect BuddyPress features for now.', 'bp-blogs-extended' );
		}

		// Update site members settings
		foreach ( $members_options as $key_member_option => $member_option ) {
			bp_blogs_update_blogmeta( $blog_id, $key_member_option, $member_option );
		}

		// Redirect the user once done
		bp_core_add_message( $message, $success );
		bp_core_redirect( $referer );
	}
}
add_action( 'blogs_screen_blog_settings', 'bpb_extended_save_blog_settings' );
