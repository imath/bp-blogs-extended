<?php
/**
 * BP Blogs Extended - Activity
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

/**
 * Register activity actions
 *
 * @uses bp_activity_set_action()
 */
function bpb_extended_register_activity_actions() {
	bp_activity_set_action(
		buddypress()->blogs->id,
		'site_update',
		__( 'New site update', 'bp-blogs-extended' ),
		'bpb_extended_format_activity_action_new_site_update',
		__( 'Site Updates', 'bp-blogs-extended' ),
		array( 'activity', 'member', 'blog' )
	);

	bp_activity_set_action(
		buddypress()->blogs->id,
		'site_subscription',
		__( 'New site subscription', 'bp-blogs-extended' ),
		'bpb_extended_format_activity_action_new_site_subscription',
		__( 'Site Subscription', 'bp-blogs-extended' ),
		array( 'activity', 'member', 'blog' )
	);
}
add_action( 'bp_blogs_register_activity_actions', 'bpb_extended_register_activity_actions' );

/**
 * Get active post types
 *
 * @todo use the blog's post type settings
 * @see bpb_extended_post_types_management()
 */
function bpb_extended_get_active_types() {
	// Could use the BuddyPress blog meta table to get each blog post types :)
	$active_post_types = apply_filters( 'bpb_extended_tracked_post_types', array( 'post' ) );
	$actions = array();

	foreach ( $active_post_types as $post_type ) {
		$actions[] = 'new_blog_' . $post_type;

		if ( 'post' == $post_type ) {
			$actions[] = 'new_blog_comment';
		} else {
			$actions[] = 'new_blog_comment_' . $post_type;
		}
	}

	return (array) apply_filters( 'bpb_extended_get_active_types', $actions );
}

/**
 * Do some overrides if needed
 *
 * eg: - deactivate subscription type if blog is not accepting subscription
 *     - Make sure the new_blog_post and new_blog_comment are in the single blog's context
 *
 *
 * @param  array  $action    list of action's args
 * @param  string $component the BuddyPress components the action is relative to
 * @param  string $type      the type the action is relative to
 * @return array             list of action's args
 */
function bpb_extended_filter_activity_actions( $action = array(), $component = '', $type = '' ) {
	// If on a blog remove subscription type if not needed
	$current_blog = bpb_extended_current_blog();

	if ( ! empty( $current_blog ) && empty( $current_blog->blog_open ) && 'site_subscription' == $type ) {
		unset( $action['context'] );
	}

	// Do nothing if not needed
	if ( buddypress()->blogs->id != $component || ! in_array( $type, bpb_extended_get_active_types() ) ) {
		return $action;
	}

	if ( empty( $action['context'] ) ) {
		return $action;
	}

	// Add the actions into the blog single item context
	if ( ! in_array( 'blog', $action['context'] ) ) {
		$action['context'][] = 'blog';
	}

	return $action;
}
add_filter( 'bp_activity_set_action', 'bpb_extended_filter_activity_actions', 10, 3 );

/**
 * Set the default activity args for the activity loop
 * when on a single blogs item
 *
 * @param  array  $args activity loop args
 * @return array  $args activity loop args (edited if needed)
 */
function bpb_extended_filter_has_activities( $args = array() ) {
	// Viewing the blogs sinble item activity page
	if ( bpb_extended_is_single_item() ) {
		$current_blog_id = bpb_extended_get_current_blog_id();

		if ( empty( $current_blog_id ) ) {
			return $args;
		}

		$args = array_merge( $args, array(
			'object'      => buddypress()->blogs->id,
			'primary_id'  => $current_blog_id,
			'show_hidden' => true,
		) );

	// Viewing the blogs tab of the user's profile
	} else if ( bp_is_user() && buddypress()->blogs->id == $args['scope'] ) {
		$args = array_merge( $args, array(
			'object'      => buddypress()->blogs->id,
			'show_hidden' => bp_is_my_profile(),
		) );
	} else if ( bp_is_activity_directory() && is_user_logged_in() && buddypress()->blogs->id == $args['scope'] ) {
		$user_blogs   = bpb_extended_get_user_blog_ids( bp_loggedin_user_id() );
		$user_blogs   = wp_list_pluck( $user_blogs, 'userblog_id' );
		$args = array_merge( $args, array(
			'object'      => buddypress()->blogs->id,
			'primary_id'  => $user_blogs,
			'show_hidden' => true,
		) );
	}

	return $args;
}
add_filter( 'bp_after_has_activities_parse_args', 'bpb_extended_filter_has_activities', 10, 1 );

/**
 * Make sure while on the blogs tabs of the user's profile activities
 * only blogs filters will be listed.
 *
 * @param  array  $filters    list of filters
 * @param  string $context    the BuddyPress context (member, group)
 * @return array              list of filters restricted to blogs actions if needed, unchanged otherwise
 */
function bpb_extended_filter_actions_contexts( $filters = array(), $context = '' ) {
	if ( 'member' == $context && bpb_extended_is_user_blogs_activity() ) {
		$blog_filters = array_fill_keys( array_keys( (array) buddypress()->activity->actions->blogs ), true );
		$filters = array_intersect_key( $filters, $blog_filters );
	}

	return $filters;
}
add_filter( 'bp_get_activity_show_filters_options', 'bpb_extended_filter_actions_contexts', 10, 2 );

/**
 * Activity action callback for new site updates
 *
 * @param  string  $action      the content of the action
 * @param  BP_Activity_Activity the activity object
 * @return string  $action      the content of the action
 */
function bpb_extended_format_activity_action_new_site_update( $action = '', $activity = null ) {
	if ( empty( $activity->item_id ) ) {
		return $action;
	}

	$user_link = bp_core_get_userlink( $activity->user_id );
	$blog_name = bp_blogs_get_blogmeta( $activity->item_id, 'name' );

	$action = sprintf(
		__( '%1$s posted an update on the site %2$s', 'bp-blogs-extended' ),
		$user_link,
		'<a href="' . esc_url( bpb_extended_get_blog_profile_link( $activity->item_id ) ) . '">' . esc_html( $blog_name ) . '</a>'
	);

	return apply_filters( 'bpb_extended_format_activity_action_new_blog_update', $action, $activity );
}

/**
 * Activity action callback for new site subscriptions
 *
 * @param  string  $action      the content of the action
 * @param  BP_Activity_Activity the activity object
 * @return string  $action      the content of the action
 */
function bpb_extended_format_activity_action_new_site_subscription( $action = '', $activity = null ) {
	if ( empty( $activity->item_id ) ) {
		return $action;
	}

	$user_link = bp_core_get_userlink( $activity->user_id );
	$blog_name = bp_blogs_get_blogmeta( $activity->item_id, 'name' );

	$action = sprintf(
		__( '%1$s subscribed to the site %2$s', 'bp-blogs-extended' ),
		$user_link,
		'<a href="' . esc_url( bpb_extended_get_blog_profile_link( $activity->item_id ) ) . '">' . esc_html( $blog_name ) . '</a>'
	);

	return apply_filters( 'bpb_extended_format_activity_action_new_site_subscription', $action, $activity );
}

/**
 * Publish an activity.
 *
 * Can't use bp_blogs_record_activity() it keeps on updating the activity once posted
 * as 'secondary_item_id' is not set.
 *
 * @param  array  $args      the content of the action
 * @param  BP_Activity_Activity the activity object
 * @uses   bp_activity_add() to post the activity
 * @return string  $action      the content of the action
 */
function bpb_extended_activity_publish( $args = array() ) {
	$r = bp_parse_args( $args, array(
		'user_id'   => bp_loggedin_user_id(),
		'action'    => '',
		'content'   => '',
		'component' => buddypress()->blogs->id,
		'type'      => 'site_update',
		'item_id'   => false,
	), 'bpb_extended_activity_publish' );

	if ( empty( $r['item_id'] ) ) {
		$r['item_id']       = bpb_extended_get_current_blog_id();
	}

	$r['hide_sitewide'] = (bool) ! get_blog_option( $r['item_id'], 'blog_public' );

	if ( empty( $r['action'] ) ) {
		$blog       = new stdClass();
		$blog->id   = $r['item_id'];
		$blog->name = bp_blogs_get_blogmeta( $r['item_id'], 'name', true );

		$user_link = bp_core_get_userlink( $r['user_id'] );

		$r['action'] = sprintf(
			__( '%1$s posted an update on the site %2$s', 'bp-blogs-extended' ),
			$user_link,
			'<a href="' . esc_url( bpb_extended_get_blog_profile_link( $blog->id ) ) . '">' . esc_html( $blog->name ) . '</a>'
		);
	}

	$activity_id =  bp_activity_add( $r );

	if ( ! empty( $activity_id ) ) {
		do_action( 'bpb_extended_activity_posted', $r['item_id'], $r );
	}

	return $activity_id;
}

/**
 * Post the Site update
 *
 * NB: error in bp-legacy/buddypress/buddypress-functions the first parameter should be false in 'bp_activity_custom_update'
 * filter :
 * $activity_id = apply_filters( 'bp_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content'] );
 *
 * Should be
 * $activity_id = apply_filters( 'bp_activity_custom_update', false, $_POST['object'], $_POST['item_id'], $_POST['content'] );
 * @see https://buddypress.trac.wordpress.org/ticket/6021
 *
 * @param  string  $object
 * @param  integer $item_id
 * @param  string  $content
 * @uses   bpb_extended_activity_publish() to post the activity
 * @return int     the activity id  just created
 */
function bpb_extended_blog_update( $object = '', $item_id = 0, $content = '' ) {
	// Only do something if on single blog
	if ( bpb_extended_is_single_item() ) {
		if ( empty( $object ) ) {
			$object  = $_POST['object'];
			$item_id = $_POST['item_id'];
			$content = $_POST['content'];
		}

		if ( $object != buddypress()->blogs->id ) {
			return false;
		}

		return bpb_extended_activity_publish( array(
			'item_id' => $item_id,
			'content' => $content,
		) );
	}
}
add_filter( 'bp_activity_custom_update', 'bpb_extended_blog_update', 10, 3 );

/**
 * Non public blog will now have tracking activities privately published
 *
 * @param  integer $is_public wether the blog is public ot not
 * @return integer            unchanged if public, 1 if private
 */
function bpb_extended_private_activities( $is_public ) {
	if ( empty( $is_public ) ) {
		$is_public = 1;

		// Now we need to filter the activity
		add_action( 'bp_activity_before_save', 'bpb_extended_private_activities_save', 10, 1 );
	}

	return $is_public;
}
add_filter( 'bp_is_blog_public', 'bpb_extended_private_activities', 10, 1 );

/**
 * Set the hide sitewide to true
 * @param  BP_Activity_Activity $activity
 */
function bpb_extended_private_activities_save( $activity = null ) {
	if ( buddypress()->blogs->id == $activity->component ) {
		$activity->hide_sitewide = (bool) ! get_option( 'blog_public' );
	}
}

/**
 * Update the last_activity meta value for a given blog.
 *
 * @param int $blog_id Optional.
 * @return bool|null False on failure.
 */
function bpb_extended_update_blog_last_activity( $blog_id = 0 ) {

	if ( empty( $blog_id ) ) {
		$blog_id = bpb_extended_get_current_blog_id();
	}

	if ( empty( $blog_id ) ) {
		return false;
	}

	bp_blogs_update_blogmeta( $blog_id, 'last_activity', bp_core_current_time() );
}
add_action( 'bpb_extended_activity_posted', 'bpb_extended_update_blog_last_activity' );
