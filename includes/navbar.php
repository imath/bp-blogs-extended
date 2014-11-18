<?php
/**
 * BP Blogs Extended - NavBar
 *
 * Adapted version of the bp_nav & bp_options_nav to avoid slug collisions with
 * other components.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

/**
 * Add a new navigation item
 *
 * based on BuddyPress bp_core_new_nav_item()
 */
function bpb_extended_new_nav_item( $args = '', $component = '' ) {
	$bpb_extended = bpb_extended();
	$bp = buddypress();

	$defaults = array(
		'name'                    => false,
		'slug'                    => false,
		'item_css_id'             => false,
		'position'                => 99,
		'screen_function'         => false,
		'default_subnav_slug'     => false,
		'user_has_access'         => true,
	);

	$r = wp_parse_args( $args, $defaults );

	// If we don't have the required info we need, don't create this subnav item
	if ( empty( $r['name'] ) || empty( $r['slug'] ) ) {
		return false;
	}

	if ( empty( $r['user_has_access'] ) && ! bp_current_user_can( 'bp_moderate' ) && ! bp_is_item_admin() ) {
		return false;
	}

	if ( empty( $r['item_css_id'] ) ) {
		$r['item_css_id'] = $r['slug'];
	}

	if ( empty( $component ) ) {
		$component = bp_is_user() ? buddypress()->members->id : bp_current_component();
	}

	if ( ! is_callable( 'bp_get_' . $component . '_root_slug' ) ) {
		return false;
	}

	$link = trailingslashit( bp_get_root_domain() . '/' . call_user_func( 'bp_get_' . $component . '_root_slug' ) . '/' . bp_current_item() );

	$bpb_extended->{$component}->nav[ $r['slug'] ] = array(
		'name'                    => $r['name'],
		'slug'                    => $r['slug'],
		'link'                    => trailingslashit( $link . $r['slug'] ),
		'css_id'                  => $r['item_css_id'],
		'position'                => $r['position'],
		'screen_function'         => &$r['screen_function'],
		'default_subnav_slug'	  => $r['default_subnav_slug'],
	);

	/* Look for current item */
	if ( bp_is_current_action( $r['slug'] ) ) {

		// The requested URL has explicitly included the default subnav
		// (eg: http://example.com/members/membername/activity/just-me/)
		// The canonical version will not contain this subnav slug.
		if ( ! empty( $r['default_subnav_slug'] ) && bp_is_action_variable( $r['default_subnav_slug'], 0 ) && ! bp_action_variable( 1 ) ) {
			unset( $bp->canonical_stack['action_variables'][0] );
		} else if ( ! bp_action_variable( 0 ) ) {

			// Add our screen hook if screen function is callable
			if ( is_callable( $r['screen_function'] ) ) {
				add_action( 'bp_screens', $r['screen_function'], 3 );
			}

			if ( ! empty( $r['default_subnav_slug'] ) ) {
				$bp->current_action = apply_filters( 'bp_default_component_subnav', $r['default_subnav_slug'], $r );
			}
		}
	}

	do_action( 'bpb_extended_new_nav_item', $r, $args, $defaults );
}

/**
 * Sort the navigation items.
 *
 * based on BuddyPress bp_core_sort_nav_items()
 */
function bpb_extended_sort_nav_items() {
	$bpb_extended = bpb_extended();

	if ( bp_is_user() ) {
		return false;
	}

	if ( ! bp_current_component() ) {
		return false;
	}

	$component = bp_current_component();

	if ( empty( $bpb_extended->{$component}->nav ) || ! is_array( $bpb_extended->{$component}->nav ) ) {
		return false;
	}

	$temp = array();

	foreach ( (array) $bpb_extended->{$component}->nav as $slug => $nav_item ) {
		if ( empty( $temp[$nav_item['position']]) ) {
			$temp[$nav_item['position']] = $nav_item;
		} else {
			// increase numbers here to fit new items in.
			do {
				$nav_item['position']++;
			} while ( !empty( $temp[$nav_item['position']] ) );

			$temp[$nav_item['position']] = $nav_item;
		}
	}

	ksort( $temp );
	$bpb_extended->{$component}->nav = &$temp;
}
add_action( 'wp_head', 'bpb_extended_sort_nav_items' );

/**
 * Add new sub navigation items
 *
 * based on BuddyPress bp_core_new_subnav_item()
 */
function bpb_extended_new_subnav_item( $args = '', $component = '' ) {
	$bpb_extended = bpb_extended();

	$r = wp_parse_args( $args, array(
		'name'              => false,
		'slug'              => false,
		'parent_slug'       => false,
		'parent_url'        => false,
		'item_css_id'       => false,
		'user_has_access'   => true,
		'position'          => 90,
		'screen_function'   => false,
		'link'              => '',
	) );

	// If we don't have the required info we need, don't create this subnav item
	if ( empty( $r['name'] ) || empty( $r['slug'] ) || empty( $r['parent_slug'] ) || empty( $r['parent_url'] ) || empty( $r['screen_function'] ) ) {
		return false;
	}

	if ( empty( $component ) ) {
		$component = bp_is_user() ? buddypress()->members->id : bp_current_component();
	}

	if ( empty( $bpb_extended->{$component}->nav ) || ! is_array( $bpb_extended->{$component}->nav ) ) {
		return false;
	}

	$link = $r['link'];

	// Link was not forced, so create one
	if ( empty( $link ) ) {
		$link = $r['parent_url'] . $r['slug'];

		// If this sub item is the default for its parent, skip the slug
		if ( ! empty( $bpb_extended->{$component}->nav[ $r['parent_slug'] ]['default_subnav_slug'] ) && $r['slug'] == $bpb_extended->{$component}->nav[ $r['parent_slug'] ]['default_subnav_slug'] ) {
			$link = $r['parent_url'];
		}
	}

	if ( empty( $r['item_css_id'] ) ) {
		$r['item_css_id'] = $r['slug'];
	}

	$subnav_item = array(
		'name'              => $r['name'],
		'link'              => trailingslashit( $link ),
		'slug'              => $r['slug'],
		'css_id'            => $r['item_css_id'],
		'position'          => $r['position'],
		'user_has_access'   => $r['user_has_access'],
		'screen_function'   => &$r['screen_function'],
	);

	$bpb_extended->{$component}->subnav[ $r['parent_slug'] ][ $r['slug'] ] = $subnav_item;

	if ( ! bp_is_current_component( $component ) && ! bp_is_current_action( $r['parent_slug'] ) ) {
		return;
	}

	if ( bp_action_variable( 0 ) && bp_is_action_variable( $r['slug'], 0 ) ) {

		$hooked = bpb_extended_maybe_hook_new_subnav_screen_function( $subnav_item, $component );

		// If redirect args have been returned, perform the redirect now
		if ( ! empty( $hooked['status'] ) && 'failure' === $hooked['status'] && isset( $hooked['redirect_args'] ) ) {
			bp_core_no_access( $hooked['redirect_args'] );
		}
	}
}

/**
 * For a given subnav item, either hook the screen function or generate redirect arguments, as necessary.
 *
 * based on BuddyPress bp_core_maybe_hook_new_subnav_screen_function()
 */
function bpb_extended_maybe_hook_new_subnav_screen_function( $subnav_item, $component ) {
	$retval = array(
		'status' => '',
	);

	// User has access, so let's try to hook the display callback
	if ( ! empty( $subnav_item['user_has_access'] ) ) {

		// Screen function is invalid
		if ( ! is_callable( $subnav_item['screen_function'] ) ) {
			$retval['status'] = 'failure';

		// Success - hook to bp_screens
		} else {
			add_action( 'bp_screens', $subnav_item['screen_function'], 3 );
			$retval['status'] = 'success';
		}

	// User doesn't have access. Determine redirect arguments based on
	// user status
	} else {
		$retval['status'] = 'failure';
		$message     = __( 'You do not have access to this page.', 'bp-blogs-extended' );
		$redirect_to = bp_get_root_domain();

		if ( is_callable( 'bp_get_' . $component . '_root_slug' ) ) {

			$redirect_to = trailingslashit( bp_get_root_domain() . '/' . call_user_func( 'bp_get_' . $component . '_root_slug' ) );

			if ( bp_is_current_component( $component ) && bp_current_item() ) {
				$redirect_to = trailingslashit( $redirect_to . bp_current_item() );
			}
		}

		$retval['redirect_args'] = array(
			'mode'     => 1,
			'message'  => $message,
			'root'     => $redirect_to,
			'redirect' => false,
		);
	}

	return $retval;
}

/**
 * Sort all subnavigation arrays.
 *
 * based on BuddyPress bp_core_sort_subnav_items()
 */
function bpb_extended_sort_subnav_items() {
	$bpb_extended = bpb_extended();

	if ( bp_is_user() ) {
		return false;
	}

	if ( ! bp_current_component() ) {
		return false;
	}

	$component = bp_current_component();

	if ( empty( $bpb_extended->{$component}->subnav ) || ! is_array( $bpb_extended->{$component}->subnav ) ) {
		return false;
	}

	foreach ( (array) $bpb_extended->{$component}->subnav as $parent_slug => $subnav_items ) {
		if ( ! is_array( $subnav_items ) ) {
			continue;
		}

		foreach ( (array) $subnav_items as $subnav_item ) {
			if ( empty( $temp[$subnav_item['position']]) ) {
				$temp[$subnav_item['position']] = $subnav_item;
			} else {
				// increase numbers here to fit new items in.
				do {
					$subnav_item['position']++;
				} while ( !empty( $temp[$subnav_item['position']] ) );

				$temp[$subnav_item['position']] = $subnav_item;
			}
		}

		ksort( $temp );
		$bpb_extended->{$component}->subnav[ $parent_slug ] = &$temp;
		unset( $temp );
	}
}
add_action( 'wp_head', 'bpb_extended_sort_subnav_items' );

/**
 * Output the main nav
 *
 * based on bp_get_displayed_user_nav()
 */
function bpb_extended_main_nav( $component = 'blogs' ) {
	$bpb_extended = bpb_extended();

	if ( empty( $component ) ) {
		$component = bp_is_user() ? buddypress()->members->id : bp_current_component();
	}

	foreach ( (array) $bpb_extended->{$component}->nav as $main_nav_item ) {

		$selected = '';

		if ( bp_is_current_action( $main_nav_item['slug'] ) ) {
			$selected = ' class="current selected"';
		}

		echo apply_filters_ref_array( 'bpb_extended_main_nav_' . $main_nav_item['css_id'], array( '<li id="' . $main_nav_item['slug'] . '-' . $main_nav_item['css_id'] . '-li" ' . $selected . '><a id="' . $component . '-' . $main_nav_item['slug'] . '" href="' . $main_nav_item['link'] . '">' . $main_nav_item['name'] . '</a></li>', &$main_nav_item['slug'] ) );
	}
}

/**
 * Output the sub nav
 *
 * based on bp_get_options_nav()
 */
function bpb_extended_sub_nav( $component = 'blogs' ) {
	$bpb_extended = bpb_extended();

	if ( empty( $component ) ) {
		$component = bp_is_user() ? buddypress()->members->id : bp_current_component();
	}

	$the_index       = bp_current_action();

	// Default subnav
	$selected_item   = bp_current_action();

	if ( bp_action_variable( 0 ) ) {
		$selected_item = bp_action_variable( 0 );
	}

	if ( empty( $bpb_extended->{$component}->subnav[ $the_index ] ) ) {
		return;
	}

	// Loop through each navigation item
	foreach ( (array) $bpb_extended->{$component}->subnav[ $the_index ] as $subnav_item ) {
		if ( empty( $subnav_item['user_has_access'] ) ) {
			continue;
		}

		// If the current action or an action variable matches the nav item id, then add a highlight CSS class.
		if ( $subnav_item['slug'] == $selected_item ) {
			$selected = ' class="current selected"';
		} else {
			$selected = '';
		}

		// echo out the final list item
		echo apply_filters( 'bpb_extended_main_nav_' . $subnav_item['css_id'], '<li id="' . $subnav_item['css_id'] . '-' . $component . '-li" ' . $selected . '><a id="' . $subnav_item['css_id'] . '" href="' . $subnav_item['link'] . '">' . $subnav_item['name'] . '</a></li>', $subnav_item, $selected_item );
	}
}
