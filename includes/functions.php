<?php
/**
 * BP Blogs Extended - Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

/**
 * Get plugin version
 *
 * @uses   bpb_extended()
 * @return string the plugin version
 */
function bpb_extended_get_version() {
	return bpb_extended()->version;
}

/**
 * Get the template url
 *
 * Needed to enqueue the little css rules needed by the plugin
 *
 * @uses   bpb_extended()
 * @return string url to the template folder of the plugin
 */
function bpb_extended_get_template_url() {
	return bpb_extended()->tpl_url;
}

/**
 * Get current displayed single blog item
 *
 * @uses   bp_is_blogs_component()
 * @uses   bp_is_user()
 * @uses   bpb_extended()
 * @return object current blog's data
 */
function bpb_extended_current_blog() {
	if ( ! bp_is_blogs_component() || bp_is_user() ) {
		return false;
	}

	$bpb_extended = bpb_extended();

	if ( empty( $bpb_extended->current_blog->id ) ) {
		return false;
	}

	return $bpb_extended->current_blog;
}

/**
 * Get current displayed single blog item ID
 *
 * @uses   bpb_extended_current_blog()
 * @return int|bool current blog's id or false if not set
 */
function bpb_extended_get_current_blog_id() {
	$current_blog = bpb_extended_current_blog();

	if ( ! empty( $current_blog->id ) ) {
		return $current_blog->id;
	} else {
		return false;
	}
}

/**
 * Get current displayed single blog item slug
 *
 * @uses   bpb_extended_current_blog()
 * @return string|bool current blog's slug or false if not set
 */
function bpb_extended_get_current_blog_slug() {
	$current_blog = bpb_extended_current_blog();

	if ( ! empty( $current_blog->slug ) ) {
		return $current_blog->slug;
	} else {
		return false;
	}
}

/**
 * Are we on the blogs single item home page?
 *
 * @uses   bpb_extended_is_single_item()
 * @uses   bp_current_action()
 * @uses   bp_is_current_action()
 * @return bool true if on the single home page, false otherwise
 */
function bpb_extended_is_blog_home() {
	if ( bpb_extended_is_single_item() && ( ! bp_current_action() || bp_is_current_action( 'home' ) ) ) {
		return true;
	}

	return false;
}

/**
 * Are we on a blogs single item ?
 *
 * @uses  bpb_extended()
 * @return bool true if on a blogs single item, false otherwise
 */
function bpb_extended_is_single_item() {
	$bpb_extended = bpb_extended();
	return (bool) ! empty( $bpb_extended->is_single_item );
}

/**
 * Are we viewing the members of a blogs single item ?
 *
 * @uses   bpb_extended_is_single_item()
 * @uses   bp_is_current_action()
 * @return bool true if viewing the members of a blogs single item, false otherwise
 */
function bpb_extended_is_blog_members() {
	return (bool) ( bpb_extended_is_single_item() && bp_is_current_action( 'members' ) );
}

/**
 * Does the current blog have a custom front ?
 *
 * @uses   bpb_extended_current_blog()
 * @return bool true if current blog has a custom front, false otherwise
 */
function bpb_extended_custom_front() {
	$current_blog = bpb_extended_current_blog();
	$retval       = false;

	if ( ! empty( $current_blog->custom_front ) ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Are we viewing the manage part of a blogs single item ?
 *
 * @uses   bpb_extended_is_single_item()
 * @uses   bp_is_current_action()
 * @return bool true if viewing the manage part of a blogs single item, false otherwise
 */
function bpb_extended_is_blog_manage() {
	return (bool) ( bpb_extended_is_single_item() && bp_is_current_action( 'manage' ) );
}

/**
 * Are we viewing the activity part of a blogs single item ?
 *
 * @uses   bpb_extended_is_single_item()
 * @uses   bp_is_current_action()
 * @return bool true if viewing the activity part of a blogs single item, false otherwise
 */
function bpb_extended_is_blog_activity() {
	return (bool) ( bpb_extended_is_single_item() && bp_is_current_action( 'activity' ) );
}

/**
 * Are a user's blogs activities ?
 *
 * @uses   bp_is_user_activity()
 * @uses   bp_is_current_action()
 * @uses   bp_get_blogs_slug()
 * @return bool true if viewing the activity part of a blogs single item, false otherwise
 */
function bpb_extended_is_user_blogs_activity() {
	return (bool) ( bp_is_user_activity() && bp_is_current_action( bp_get_blogs_slug() ) );
}

/**
 * Get a blog id by its slug
 *
 * @uses   get_id_from_blogname()
 * @return int the blog id
 */
function bpb_extended_get_blog_id_by_slug( $slug = '' ) {
	return get_id_from_blogname( $slug );
}

/**
 * Get a blog slug by its ID
 *
 * @global $wpdb
 * @param  int $blog_id
 * @uses   wp_cache_get()
 * @uses   wp_cache_set()
 * @return string the blog slug
 */
function bpb_extended_get_blog_slug_by_id( $blog_id = 0 ) {
	global $wpdb;

	if ( empty( $blog_id ) ) {
		return false;
	}

	// Try to get the slug in Cache
	$slug = wp_cache_get( "get_blog_slug_by_id_{$blog_id}", 'bpb_extended' );

	if ( ! empty( $slug ) ) {
		return $slug;
	}

	$current_site = get_current_site();
	$slug         = $wpdb->get_var( $wpdb->prepare("SELECT path FROM {$wpdb->blogs} WHERE blog_id = %d", $blog_id ) );

	if ( ! empty( $current_site->path ) ) {
		$slug = str_replace( $current_site->path, '', $slug );
	}

	$slug = trim( $slug, '/' );

	// Cache the slug
	wp_cache_set( "get_blog_slug_by_id_{$blog_id}", $slug, 'bpb_extended' );

	return $slug;
}

/**
 * Clean blog slug cache if its detailed have been updated
 *
 * @param  int $blog_id
 * @uses   wp_cache_delete()
 */
function bpb_extended_clean_blog_slug_cache( $blog_id = 0 ) {
	if ( empty( $blog_id ) ) {
		return;
	}

	wp_cache_delete( "get_blog_slug_by_id_{$blog_id}", 'bpb_extended' );
}
add_action( 'refresh_blog_details', 'bpb_extended_clean_blog_slug_cache', 10, 1 );

/**
 * Check if at least one widget has been added to the subsite Blogs Extended sidebar
 *
 * if using this function out of bpb_extended_get_extra_data(), don't forget to eventually
 * switch blog.
 *
 * @uses get_option();
 */
function bpb_extended_is_active_sidebar() {
	$retval   = false;
	$sidebars = get_option( 'sidebars_widgets', array() );

	if ( ! empty( $sidebars['bpb-extended-sidebar'] ) && count( $sidebars['bpb-extended-sidebar'] ) >= 1 ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Fetch blog's extra data
 *
 * Admins, users count, whether the current user is a member of the blog
 * Post types registered on the blog having the buddypress-activity support
 * Work in progress see https://buddypress.trac.wordpress.org/ticket/5669
 *
 * @param  integer $blog_id the blog id
 * @uses   switch_to_blog
 * @uses   get_users()
 * @uses   count_users()
 * @uses   bpb_extended_is_active_sidebar()
 * @uses   get_post_types();
 * @uses   restore_current_blog()
 * @return array         extra data
 */
function bpb_extended_get_extra_data( $blog_id = 0 ) {
	if ( empty( $blog_id ) ) {
		return array();
	}

	// Init datas
	$extra_data = array(
		'admins'      => array(),
		'count_users' => 0,
		'is_member'   => false,
	);

	switch_to_blog( $blog_id );

	// Who are the admins ?
	$extra_data['admins'] = get_users( array(
		'role'   => 'administrator',
		'fields' => array( 'ID', 'display_name', 'user_login', 'user_nicename', 'user_email' ),
	) );

	// How many users ?
	$users = count_users();
	$extra_data['count_users'] = $users['total_users'];

	// Is current user a member of the blog ?
	$extra_data['is_member'] = current_user_can( 'read' );

	// Blog visibility
	$extra_data['is_public'] = get_option( 'blog_public' );

	// Use custom front ?
	$extra_data['custom_front'] = bpb_extended_is_active_sidebar();

	$post_types = get_post_types( false, 'object' );

	if ( bpb_extended()->is_debug ) {
		// For demo purpose..
		foreach ( $post_types as $key_post_type => $post_type ) {
			if ( post_type_supports( $key_post_type, 'buddypress-activity' ) ) {
				$extra_data['post_types'][ $key_post_type ] = $post_type->labels->name;
			}
		}
	}

	restore_current_blog();

	return $extra_data;
}

/**
 * Fetch the blogs the user is a member of
 *
 * @param  integer $user_id the user ID
 * @return array            the blogs the user is a member of
 */
function bpb_extended_get_user_blog_ids( $user_id = 0 ) {
	global $wpdb;
	$bpb_extended = bpb_extended();

	$blogs = array();

	if ( empty( $user_id ) ) {

		if ( bp_displayed_user_id() ) {
			$user_id = bp_displayed_user_id();
		} else {
			$user_id = bp_loggedin_user_id();
		}

	}

	if ( empty( $user_id ) ) {
		return $blogs;
	}

	// Get it once per load as used in various places
	if ( ! empty( $bpb_extended->user_blogs[ $user_id ] ) ) {
		return $bpb_extended->user_blogs[ $user_id ];
	}

	/**
	 * Get user blogs (get_blogs_of_user() is not returning the blogs public parameter :( )
	 * we need it to have a correct count if current user != displayed user
	 */
	$user_metas = get_user_meta( $user_id );
	if ( empty( $user_metas ) ) {
		return $blogs;
	}

	// Get the user's blogs id
	foreach ( array_keys( $user_metas ) as $meta_key ) {
		if ( preg_match( '/' . $wpdb->base_prefix. '(\d*)_capabilities/', $meta_key, $matches ) ) {
			if ( ! empty( $matches[1] ) ) {
				$blogs[] = intval( $matches[1] );
			}
		} else if ( $wpdb->base_prefix. 'capabilities' === $meta_key ) {
			$blogs[] = 1;
		}
	}

	if ( empty( $blogs ) ) {
		return $blogs;
	}

	// Only super admins and loggedin user can see all their blogs (including the not public ones)
	$public = (int) ( ! is_super_admin() && $user_id != bp_loggedin_user_id() );

	$sql = array(
		'select' => "SELECT * FROM $wpdb->blogs",
		'where'  => array(
			'in'       => 'blog_id IN (' . join( ',', wp_parse_id_list( $blogs ) ) .')',
			'deleted'  => $wpdb->prepare( 'deleted = %d', 0 ),
			'spam'     => $wpdb->prepare( 'spam = %d', 0 ),
			'mature'   => $wpdb->prepare( 'mature = %d', 0 ),
			'archived' => $wpdb->prepare( 'mature = %d', 0 ),
		),
	);

	if ( ! empty( $public ) ) {
		$sql['where']['public'] = $wpdb->prepare( 'public = %d', $public );
	}

	$sql['where'] = 'WHERE ' . join( ' AND ', $sql['where'] );

	// Get blogs that can be used (not deleted, not spam...)
	$blogs = $wpdb->get_results( join( ' ', $sql ) );

	// Put them in a global to avoid requesting for them more than once per load
	$bpb_extended->user_blogs[ $user_id ] = $blogs;

	// Return the list of blogs
	return $blogs;
}

/**
 * Setup the current blog item
 *
 * @uses bpb_extended() to store it
 * @uses buddypress()
 * @uses bp_current_action()
 * @uses bpb_extended_get_blog_id_by_slug()
 * @uses BP_Blogs_Blog::is_recorded()
 * @uses bp_blogs_get_blogmeta
 * @uses bpb_extended_get_extra_data
 * @uses bp_current_user_can
 * @uses bp_loggedin_user_id()
 * @uses wp_list_pluck()
 * @uses bp_update_is_item_admin()
 */
function bpb_extended_setup() {
	$bpb_extended = bpb_extended();
	$bp           = buddypress();

	if ( $bpb_extended->is_debug ) {
		// For demo purpose..
		add_post_type_support( 'post', 'buddypress-activity' );
		add_post_type_support( 'page', 'buddypress-activity' );
	}

	if ( ! bp_is_blogs_component() || bp_is_user() ) {
		return;
	}

	$blog_slug = bp_current_action();
	$blog_id   = bpb_extended_get_blog_id_by_slug( $blog_slug );

	$is_recorded = BP_Blogs_Blog::is_recorded( $blog_id );

	if ( empty( $is_recorded ) ) {
		$bpb_extended->current_blog = 0;
		return;
	} else {

		// Build the current blog object
		$current_blog               = new stdClass();
		$current_blog->id           = $blog_id;
		$current_blog->slug         = $blog_slug;

		// Populating BuddyPress blog metas for the current blog
		foreach ( bp_blogs_get_blogmeta( $blog_id ) as $key => $meta ) {
			if ( count( $meta ) == 1 ) {
				$current_blog->{$key} = maybe_unserialize( $meta[0] );
			} else {
				$current_blog->{$key} = array_map( 'maybe_unserialize', $meta );
			}
		}

		/**
		 * Fetch Extra data
		 * Get admins, users count and check if current user is a member of the blog
		 * and if we should use the custom front template as the home of blog's profile page.
		 */
		foreach ( (array) bpb_extended_get_extra_data( $blog_id ) as $key_data => $value_data ) {
			$current_blog->{$key_data} = $value_data;
		}

		// If blog is not public, non members can't access
		if ( empty( $current_blog->is_public ) && ! $current_blog->is_member ) {
			$bpb_extended->current_blog = 0;
			return;
		}

		$bpb_extended->current_blog     = $current_blog;
		$bp->current_item               = $blog_slug;
		$bpb_extended->is_single_item   = true;
		$bp->current_action             = bp_action_variable( 0 );
		array_shift( $bp->action_variables );

		if ( bp_current_user_can( 'bp_moderate' ) || in_array( bp_loggedin_user_id(), wp_list_pluck( $current_blog->admins, 'ID' ) ) ) {
			bp_update_is_item_admin( true, 'blogs' );
		}
	}
}
add_action( 'bp_blogs_setup_globals', 'bpb_extended_setup' );

/**
 * Set up canonical stack for the blogs single item area.
 *
 * @uses bpb_extended_is_single_item()
 * @uses buddypress()
 * @uses bpb_extended_current_blog()
 * @uses bp_is_active()
 * @uses bpb_extended_custom_front()
 * @uses bpb_extended_get_blog_profile_link()
 * @uses bp_current_action()
 * @uses bp_action_variables()
 */
function bpb_extended_setup_canonical_stack() {
	if ( ! bpb_extended_is_single_item() ) {
		return;
	}

	$bp = buddypress();
	$current_blog = bpb_extended_current_blog();

	if ( ! bp_current_action() ) {
		$bp->current_action = 'home';

		if ( ! bp_is_active( 'activity' ) && ! bpb_extended_custom_front() ) {
			$bp->current_action = 'members';
		}
	}

	// Prepare for a redirect to the canonical URL
	$bp->canonical_stack['base_url'] = bpb_extended_get_blog_profile_link( $current_blog->id, $current_blog->slug );

	if ( bp_current_action() ) {
		$bp->canonical_stack['action'] = bp_current_action();
	}

	if ( ! empty( $bp->action_variables ) ) {
		$bp->canonical_stack['action_variables'] = bp_action_variables();
	}

	// When viewing the default extension, the canonical URL should not have
	// that extension's slug, unless more has been tacked onto the URL via
	// action variables
	if ( bp_is_current_action( 'home' ) && empty( $bp->action_variables ) )  {
		unset( $bp->canonical_stack['action'] );
	}
}
add_action( 'bp_setup_canonical_stack', 'bpb_extended_setup_canonical_stack', 20 );

/**
 * Set up navigation for the blogs single item area.
 *
 * @uses bpb_extended()
 * @uses bpb_extended_is_single_item()
 * @uses bp_is_active()
 * @uses bpb_extended_custom_front()
 * @uses bpb_extended_new_nav_item()
 * @uses bpb_extended_get_blog_profile_link()
 * @uses buddypress()
 * @uses bpb_extended_new_subnav_item()
 */
function bpb_extended_setup_nav() {
	$bpb_extended = bpb_extended();

	if ( ! bpb_extended_is_single_item() ) {
		return;
	}

	$current_blog = $bpb_extended->current_blog;

	// Members can be on home if activity is not active and no custom front
	$members_nav = array(
		'name'                => sprintf( _x( 'Members <span>%s</span>', 'Single Blog members screen nav', 'bp-blogs-extended' ), $current_blog->count_users ),
		'slug'                => 'members',
		'position'            => 20,
		'screen_function'     => 'bpb_extended_site_members',
		'default_subnav_slug' => false,
		'item_css_id'         => 'all',
	);

	$main_nav_items = array(
		array(
			'name'                => __( 'Home', 'bp-blogs-extended' ),
			'slug'                => 'home',
			'position'            => 0,
			'screen_function'     => 'bpb_extended_site_home',
			'default_subnav_slug' => false,
			'item_css_id'         => 'all',
		),
	);

	if ( bp_is_active( 'activity' ) ) {
		// If activity is active it's the home
		$main_nav_items[] = $members_nav;

		// If custom front, create a new nav for activity
		if ( bpb_extended_custom_front() ) {
			$main_nav_items[] = array(
				'name'                => _x( 'Activity', 'Single Blog activity screen nav', 'bp-blogs-extended' ),
				'slug'                => 'activity',
				'position'            => 10,
				'screen_function'     => 'bpb_extended_site_activity',
				'default_subnav_slug' => false,
				'item_css_id'         => 'all',
			);
		}

	// Activity is not active but there's a custom front
	} else if ( bpb_extended_custom_front() ) {
		$main_nav_items[] = $members_nav;
	} else {
		$main_nav_items = array( $members_nav );
	}

	// Manage Main nav
	$main_nav_items[] = array(
		'name'                => _x( 'Manage', 'Single Blog manage screen nav', 'bp-blogs-extended' ),
		'slug'                => 'manage',
		'position'            => 1000,
		'screen_function'     => 'bpb_extended_site_settings',
		'default_subnav_slug' => 'manage',
		'user_has_access'     => bp_is_item_admin(),
	);

	// Build the main nav
	foreach( $main_nav_items as $main_nav_item ) {
		bpb_extended_new_nav_item( $main_nav_item, 'blogs' );
	}

	$blog_link = bpb_extended_get_blog_profile_link( $current_blog->id, $current_blog->slug );

	// Only manage needs a subnav for now
	$subnav = array(
		array(
			'name'            => _x( 'Edit Settings', 'Single Blog setting screen', 'bp-blogs-extended' ),
			'slug'            => 'manage',
			'parent_url'      => trailingslashit( $blog_link . 'manage' ),
			'parent_slug'     => 'manage',
			'screen_function' => 'bpb_extended_site_settings',
			'position'        => 0,
			'user_has_access' => bp_is_item_admin(),
		),
	);

	/**
	 * "Blavatar"
	 *
	 * @see see https://buddypress.trac.wordpress.org/ticket/192
	 */
	if ( ! (int) buddypress()->site_options['bp-disable-avatar-uploads'] && buddypress()->avatar->show_avatars ) {
		$subnav[] = array(
			'name'            => _x( 'Edit Photo', 'Single Blog photo screen', 'bp-blogs-extended' ) ,
			'slug'            => 'edit-photo',
			'parent_url'      => trailingslashit( $blog_link . 'manage' ),
			'parent_slug'     => 'manage',
			'screen_function' => 'bpb_extended_site_avatar',
			'position'        => 10,
			'user_has_access' => bp_is_item_admin(),
		);
	}

	// Build the subnav
	foreach ( $subnav as $nav ) {
		bpb_extended_new_subnav_item( $nav, 'blogs' );
	}
}
// Can't use bp_blogs_setup_nav as the component's nav returns false if ! is_user_logged_in()
add_action( 'bp_setup_nav', 'bpb_extended_setup_nav', 20 );

/**
 * Adds a tab to user's profile activity to display his "blogs activities"
 *
 * @uses bp_core_new_subnav_item()
 */
function bpb_extended_activity_setup_nav() {
	// Determine user to use
	if ( bp_displayed_user_domain() ) {
		$user_domain = bp_displayed_user_domain();
	} elseif ( bp_loggedin_user_domain() ) {
		$user_domain = bp_loggedin_user_domain();
	} else {
		return;
	}

	$activity_slug = bp_get_activity_slug();
	$blogs_slug    = bp_get_blogs_slug();

	// User link
	$activity_link = trailingslashit( $user_domain . $activity_slug );

	bp_core_new_subnav_item( array(
		'name'            => _x( 'Sites', 'Profile activity screen sub nav', 'bp-blogs-extended' ),
		'slug'            => $blogs_slug,
		'parent_url'      => $activity_link,
		'parent_slug'     => $activity_slug,
		'screen_function' => 'bpb_extended_activity_member_screen',
		'position'        => 60,
		'item_css_id'     => 'activity-blogs'
	) );
}
add_action( 'bp_activity_setup_nav', 'bpb_extended_activity_setup_nav' );

/**
 * Add a user to a blog
 *
 * @uses   bpb_extended_current_blog()
 * @uses   bp_is_item_admin()
 * @uses   add_user_to_blog()
 * @uses   is_wp_error()
 * @uses   bp_core_get_userlink()
 * @uses   bpb_extended_get_blog_profile_link()
 * @uses   bpb_extended_activity_publish()
 * @return array the result in a feedback array
 */
function bpb_extended_join_blog() {
	$blog = bpb_extended_current_blog();

	if ( empty( $blog->blog_open ) || bp_is_item_admin() ) {
		return array(
			'message' => __( 'We were not able to perform this action, please try again later', 'bp-blogs-extended' ),
			'type'    => 'error',
		);
	}

	$subscribed = add_user_to_blog( $blog->id, bp_loggedin_user_id(), 'subscriber' );

	if ( is_wp_error( $subscribed ) ) {
		$result = array(
			'message' => $subscribed->get_error_message(),
			'type'    => 'error',
		);
	} else {
		$result = array(
			'message' => __( 'You successfully subscribed to the site.', 'bp-blogs-extended' ),
			'type'    => '',
		);

		$user_link = bp_core_get_userlink( bp_loggedin_user_id() );

		$action = sprintf(
			__( '%1$s subscribed to the site %2$s', 'bp-blogs-extended' ),
			$user_link,
			'<a href="' . esc_url( bpb_extended_get_blog_profile_link( $blog->id, $blog->slug ) ) . '">' . esc_html( $blog->name ) . '</a>'
		);

		bpb_extended_activity_publish( array(
			'action' => $action,
			'type'   => 'site_subscription',
		) );
	}

	return $result;
}

/**
 * Remove a user from a blog
 *
 * @uses   bpb_extended_current_blog()
 * @uses   bp_is_item_admin()
 * @uses   remove_user_from_blog()
 * @uses   is_wp_error()
 * @return array the result in a feedback array
 */
function bpb_extended_leave_blog() {
	$blog = bpb_extended_current_blog();

	if ( empty( $blog->blog_open ) || bp_is_item_admin() ) {
		return array(
			'message' => __( 'We were not able to perform this action, please try again later', 'bp-blogs-extended' ),
			'type'    => 'error',
		);
	}

	$unsubscribed = remove_user_from_blog( bp_loggedin_user_id(), $blog->id );

	if ( is_wp_error( $unsubscribed ) ) {
		$result = array(
			'message' => $unsubscribed->get_error_message(),
			'type'    => 'error',
		);
	} else {
		$result = array(
			'message' => __( 'You successfully unsubscribed from the site.', 'bp-blogs-extended' ),
			'type'    => '',
		);
	}

	return $result;
}

/**
 * Get Blogs component avatar dir
 *
 * @see https://buddypress.trac.wordpress.org/ticket/192
 *
 * @uses   bpb_extended_get_current_blog_id()
 * @uses   bp_core_avatar_upload_path()
 * @uses   wp_mkdir_p()
 * @uses   bp_core_avatar_url()
 * @return array the wp_uploads_dir data
 */
function bpb_extended_avatar_upload_dir( $blog_id = 0 ) {
	$bp = buddypress();

	if ( empty( $blog_id ) ) {
		$blog_id = bpb_extended_get_current_blog_id();
	}

	$path    = bp_core_avatar_upload_path() . '/blog-avatars/' . $blog_id;
	$newbdir = $path;

	if ( ! file_exists( $path ) ) {
		@wp_mkdir_p( $path );
	}

	$newurl    = bp_core_avatar_url() . '/blog-avatars/' . $blog_id;
	$newburl   = $newurl;
	$newsubdir = '/blog-avatars/' . $blog_id;

	return apply_filters( 'bpb_extended_avatar_upload_dir', array(
		'path'    => $path,
		'url'     => $newurl,
		'subdir'  => $newsubdir,
		'basedir' => $newbdir,
		'baseurl' => $newburl,
		'error'   => false
	) );
}
