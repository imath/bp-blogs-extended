<?php
/**
 * BP Blogs Extended - Filters
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

/**
 * Count the number of blogs the user is member of
 *
 * @param  integer $count number of blogs the user is admin of
 * @return integer        number of blogs the user is member of
 */
function bpb_extended_get_total_blog_count_for_user( $count = 0 ) {
	if ( ! bpb_extended()->is_debug ) {
		return $count;
	}

	return count( bpb_extended_get_user_blog_ids() );
}
add_filter( 'bp_get_total_blog_count_for_user', 'bpb_extended_get_total_blog_count_for_user', 10, 1 );

/**
 * Make sure a loggedin user will and access to all he's member of
 *
 * @param  array  $args the blogs loop args
 * @return array  $args the blogs loop args (edited if needed)
 */
function bpb_extended_filter_user_blogs( $args = array() ) {
	if ( ! bp_is_blogs_component() || ! bpb_extended()->is_debug ) {
		return $args;
	}

	if ( ! empty( $args['user_id'] ) || bpb_extended_is_single_item() ) {

		$filter = false;

		$current_blog = bpb_extended_current_blog();
		$user_blogs   = bpb_extended_get_user_blog_ids( $args['user_id'] );
		$user_blogs   = wp_list_pluck( $user_blogs, 'blog_id' );

		if ( ! empty( $current_blog ) && empty( $current_blog->is_public ) ) {
			// user is not a member
			if ( ! $current_blog->is_member ) {
				return $args;
			} else {
				$filter = true;
			}
		}

		if ( ! empty( $args['user_id'] ) && bp_loggedin_user_id() == $args['user_id'] && ! bp_current_user_can( 'bp_moderate' ) ) {
			$filter = true;
		}

		if ( empty( $current_blog ) ) {
			$args['include_blog_ids'] = $user_blogs;
			$args['user_id'] = 0;
		}

		if ( ! empty( $filter ) ) {
			// Ouch! Danger zone, but it's the only way i've found
			// Don't worry the other filter will make this very temporary
			add_filter( 'bp_current_user_can', '__return_true' );

			// Remove the above filter asap!
			add_filter( 'bp_has_blogs', 'bpb_extended_filter_user_blogs_remove_filter' );
		}
	}

	return $args;
}
add_filter( 'bp_after_has_blogs_parse_args', 'bpb_extended_filter_user_blogs', 10, 1 );

/**
 * Eventually remove the filter on user can
 *
 * @param  bool $has_blogs
 * @return bool $has_blogs (unchanged)
 */
function bpb_extended_filter_user_blogs_remove_filter( $has_blogs = '' ) {
	remove_filter( 'bp_current_user_can', '__return_true' );
	return $has_blogs;
}

/**
 * Filter <title>
 *
 * @param  string  $title
 * @param  string  $old_title
 * @param  string  $sep
 * @return string  the title for the blog's single item if need
 */
function bpb_extended_filter_page_title( $title = '', $old_title = '', $sep = '' ) {
	if ( ! bpb_extended_is_single_item() ) {
		return $title;
	}

	$current_blog = bpb_extended_current_blog();

	if ( empty( $current_blog ) ) {
		return $title;
	}

	$bpb_extended = bpb_extended();
	$component    = bp_current_component();
	$action       = bp_current_action();

	if ( ! empty( $bpb_extended->{$component}->nav[ $action ] ) ) {
		$action = $bpb_extended->{$component}->nav[ $action ]['name'];
		$action = preg_replace( '/([.0-9]+)/', '', $action );
		$action = trim( strip_tags( $action ) );
	}

	return apply_filters( 'bpb_extended_filter_page_title',
		sprintf( __( '%1$s %3$s %2$s', 'bp-blogs-extended' ), strip_tags( $current_blog->name ), $action, $sep ),
		$title,
		$old_title,
		$sep
	);
}
add_filter( 'bp_modify_page_title', 'bpb_extended_filter_page_title', 10, 3 );

/**
 * Filter the querystring if on blog's members page
 *
 * @global $wpdb
 * @param  string  $querystring
 * @param  string  $object (Activity/Members..)
 * @param  string  $content
 * @return array|string     the modified querysting if needed, unchanged otherwise
 */
function bpb_extended_filter_querystring( $querystring = '', $object = '' ) {
	global $wpdb;

	if ( ! bpb_extended_is_single_item() ) {
		return $querystring;
	}

	$current_blog_id = bpb_extended_get_current_blog_id();

	if ( empty( $current_blog_id ) ) {
		return $querystring;
	}

	// To return the original in the filter
	$old_querystring = $querystring;

	if ( 'members' == $object ) {
		$querystring = bp_parse_args( $querystring, array(), 'bpb_extended_blog_members' );

		if( ! empty( $querystring['user_id'] ) ) {
			unset( $querystring['user_id'] );
		}

		// Set scope to all
		$querystring['scope'] = 'all';

		// Set meta to current blog id
		$querystring['meta_key'] = $wpdb->get_blog_prefix( $current_blog_id ) . 'capabilities';
	}

	// Finally return the new querystring
	return apply_filters( 'bpb_extended_filter_querystring', $querystring, $old_querystring, $object ) ;
}
add_filter( 'bp_ajax_querystring', 'bpb_extended_filter_querystring', 20, 2 );

/**
 * Replace blog's admin avatar by blog's avatar
 *
 * IMHO the filter bp_get_blog_avatar() should be placed before trying
 * to get the blog's admin avatar to avoid to fetch an avatar for nothing.
 *
 * @uses bpb_extended_get_blog_avatar()
 */
function bpb_extended_filter_avatar( $avatar = '', $blog_id = 0, $r = array() ) {
	$r['item_id'] = $blog_id;
	return bpb_extended_get_blog_avatar( $r );
}
add_filter( 'bp_get_blog_avatar', 'bpb_extended_filter_avatar', 10, 3 );

/** BuddyPress Mentions *******************************************************/

/**
 * Make sure Mentions JS are loaded when on a blog's single item
 *
 * @param  bool whether to load or not JS
 * @uses   bpb_extended_is_single_item()
 * @return bool        true if on a single blog item
 */
function bpb_extended_maybe_load_mentions_scripts( $load = false ) {
	if ( empty( $load ) ) {
		$load = bpb_extended_is_single_item();
	}

	return $load;
}
add_filter( 'bp_activity_maybe_load_mentions_scripts', 'bpb_extended_maybe_load_mentions_scripts', 10, 1 );

/**
 * Make sure Mentions JS are loaded when on a blog's single item
 *
 * @global $wpdb
 * @param  array $user_query the user query arguments of the mentions class
 * @uses   bpb_extended_is_single_item()
 * @uses   bpb_extended_get_current_blog_id()
 * @return array            the user query arguments with custom ones if needed
 */
function bpb_extended_filter_suggestions_query_args( $user_query = array() ) {
	global $wpdb;

	if ( ! bpb_extended_is_single_item() ) {
		return $user_query;
	}

	if( ! empty( $user_query['user_id'] ) ) {
		unset( $user_query['user_id'] );
	}

	// Set meta to current blog id
	$user_query['meta_key'] = $wpdb->get_blog_prefix( bpb_extended_get_current_blog_id() ) . 'capabilities';

	return $user_query;
}
add_filter( 'bp_members_suggestions_query_args', 'bpb_extended_filter_suggestions_query_args', 10, 1 );
