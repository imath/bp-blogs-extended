<?php
/**
 * BP Blogs Extended - Template
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

/**
 * Enqueue the css file
 */
function bpb_extended_enqueue_cssjs() {
	if ( ! bp_is_blogs_component() ) {
		return false;
	}

	// Use this filter to override my style!
	$style = apply_filters( 'bpb_extended_enqueue_cssjs', bpb_extended_get_template_url() . 'bp-blogs-extended.css' );

	wp_enqueue_style( 'bp-blogs-extended-style', $style, array(), bpb_extended_get_version() );
}
add_action( 'bp_enqueue_scripts', 'bpb_extended_enqueue_cssjs' );

/**
 * Display the admins in the blog's header
 */
function bpb_extended_blog_admins() {
	echo bpb_extended_list_blog_admins();
}
	/**
	 * Get the admins list
	 */
	function bpb_extended_list_blog_admins() {
		$blog = bpb_extended_current_blog();

		$output = '<span class="activity">' . esc_html__( 'No Admins', 'bp-blogs-extended' ) . '</span>';

		if ( ! empty( $blog->admins ) ) {
			$output = '<ul id="blog-admins">';

			foreach( (array) $blog->admins as $admin ) {
				$output .= '<li>';

				if ( ! buddypress()->avatar->show_avatars ) {
					$output .= sprintf(
						'<a href="%s" title="%s">%s</a>',
						bp_core_get_user_domain( $admin->ID, $admin->user_nicename, $admin->user_login ),
						sprintf( esc_html__( 'Profile of %s', 'bp-blogs-extended' ), $admin->display_name ),
						$admin->user_nicename
					);
				} else {
					$output .= sprintf(
						'<a href="%s">%s</a>',
						bp_core_get_user_domain( $admin->ID, $admin->user_nicename, $admin->user_login ),
						bp_core_fetch_avatar( array(
							'item_id' => $admin->ID,
							'email'   => $admin->user_email,
							'alt'     => sprintf( __( 'Profile picture of %s', 'bp-blogs-extended' ), $admin->display_name )
						) )
					);
				}

				$output .= '</li>';
			}

			$output .= '</ul>';
		}

		return apply_filters( 'bpb_extended_get_blog_admins', $output, $blog );
	}

/**
 * Checks if a blogs item has an avatar
 */
function bpb_extended_blog_has_avatar( $blog_id = 0) {
	if ( empty( $blog_id ) ) {
		$blog_id = bpb_extended_get_current_blog_id();
	}

	// Todo - this looks like an overgeneral check
	if ( ! empty( $_FILES ) ) {
		return false;
	}

	$blog_avatar = bp_core_fetch_avatar( array(
		'item_id' => $blog_id,
		'object'  => 'blog',
		'no_grav' => true,
		'html'    => false,
	) );

	if ( bp_core_avatar_default( 'local' ) === $blog_avatar ) {
		return false;
	}

	return true;
}

/**
 * Display the blog's avatar
 */
function bpb_extended_blog_avatar() {
	echo bpb_extended_get_blog_avatar();
}

	/**
	 * Get the blog's avatar
	 */
	function bpb_extended_get_blog_avatar( $args = array() ) {
		global $blogs_template;

		$r = bp_parse_args( $args, array(
			'item_id'    => $blogs_template->blog->blog_id,
			'title'      => $blogs_template->blog->name,
			'avatar_dir' => 'blog-avatars',
			'object'     => 'blog',
			'css_id'     => false,
			'class'      => 'avatar',
			'type'       => 'full',
			'width'      => false,
			'height'     => false,
			'id'         => false,
			'alt'        => __( 'Site Photo', 'bp-blogs-extended' ),
			'no_grav'    => true,
		), 'bpb_extended_get_blog_avatar' );

		return bp_core_fetch_avatar( $r );
	}

/**
 * Display the blog's delete avatar link
 */
function bpb_extended_blog_avatar_delete_link() {
	echo bpb_extended_blog_get_avatar_delete_link();
}

	/**
	 * Get the blog's delete avatar link
	 */
	function bpb_extended_blog_get_avatar_delete_link() {
		$link = trailingslashit( bpb_extended_get_blog_profile_link(
			bpb_extended_get_current_blog_id(),
			bpb_extended_get_current_blog_slug()
		) . 'manage/edit-photo/delete' );

		return apply_filters( 'bpb_extended_blog_get_avatar_delete_link', wp_nonce_url( $link, 'blog_avatar_delete' ) );
	}

/**
 * Display the blog's profile link
 */
function bpb_extended_blog_profile_link() {
	echo bpb_extended_get_blog_profile_link();
}

	/**
	 * Get the blog's profile link
	 */
	function bpb_extended_get_blog_profile_link( $blog_id = 0, $slug = false ) {

		if ( empty( $blog_id ) || ! is_numeric( $blog_id ) ) {
			$blog_id = bp_get_blog_id();
		}

		if ( ! empty( $slug ) ) {
			$blog_slug = $slug;
		} else {
			$blog_slug = bpb_extended_get_blog_slug_by_id( $blog_id );
		}

		$link = trailingslashit( bp_get_root_domain() . '/' . bp_get_blogs_root_slug() . '/'. $blog_slug );

		return apply_filters( 'bpb_extended_get_blog_profile_link', $link, $blog_slug, $blog_id );
	}

function bpb_extended_filter_bp_get_blog_permalink( $link = '' ) {
	$bpb_extended = bpb_extended();
	$blog_id = bp_get_blog_id();

	if ( empty( $bpb_extended->blog_permalink[ $blog_id ] ) ) {
		$bpb_extended->blog_permalink[ $blog_id ] = $link;
	}

	if ( true == apply_filters( 'bpb_extended_donot_filter_bp_get_blog_permalink', false ) || $blog_id == bp_get_root_blog_id() ) {
		return $link;
	}

	return bpb_extended_get_blog_profile_link( $blog_id );
}
add_filter( 'bp_get_blog_permalink', 'bpb_extended_filter_bp_get_blog_permalink', 10, 1 );

function bpb_extended_visit_blog_button( $button = array() ) {
	$bpb_extended = bpb_extended();
	$blog_id      = bp_get_blog_id();

	if ( true == apply_filters( 'bpb_extended_donot_filter_bp_get_blog_permalink', false ) || $blog_id == bp_get_root_blog_id()  ) {
		return $button;
	}

	if ( ! empty( $bpb_extended->blog_permalink[ $blog_id ] ) ) {
		$button['link_href'] = $bpb_extended->blog_permalink[ $blog_id ];
	} else {
		add_filter( 'bpb_extended_donot_filter_bp_get_blog_permalink', '__return_true' );
		$button['link_href'] = bp_get_blog_permalink();
	}

	return $button;
}
add_filter( 'bp_get_blogs_visit_blog_button', 'bpb_extended_visit_blog_button', 10, 1 );

/**
 * Display an action button in the blogs listing
 * to display the blogs profile
 */
function bpb_extended_view_blog_profile_button( $args = '' ) {
	echo bpb_extended_get_blog_profile_button( $args );
}
add_action( 'bp_directory_blogs_actions',  'bpb_extended_view_blog_profile_button', 9 );

	/**
	 * Get an action button in the blogs listing
	 * to display the blogs profile
	 */
	function bpb_extended_get_blog_profile_button( $args = '' ) {
		// Slug is empty, i'll need to use an alias (main?)
		// meaning add this alias to the forbidden names..
		if ( bp_get_blog_id() == bp_get_root_blog_id() ) {
			return false;
		}

		$defaults = array(
			'id'                => 'view_blog_profile',
			'component'         => 'blogs',
			'must_be_logged_in' => false,
			'block_self'        => false,
			'wrapper_class'     => 'blog-button profile',
			'link_href'         => bpb_extended_get_blog_profile_link(),
			'link_class'        => 'blog-button profile',
			'link_text'         => __( 'View Details', 'bp-blogs-extended' ),
			'link_title'        => __( 'View Details', 'bp-blogs-extended' ),
		);

		$button = wp_parse_args( $args, $defaults );

		// Filter and return the HTML button
		return bp_get_button( apply_filters( 'bpb_extended_get_blog_profile_button', $button ) );
	}

/**
 * Is the blog open to subscriptions ?
 */
function bpb_extended_blog_open() {
	$blog = bpb_extended_current_blog();

	if ( empty( $blog->blog_open ) ) {
		return 0;
	} else {
		return 1;
	}
}

/**
 * Display the blog's type
 */
function bpb_extended_blog_type() {
	echo bpb_extended_get_blog_type();
}

	/**
	 * Get the blog's type
	 */
	function bpb_extended_get_blog_type() {
		$type = __( 'Locked', 'bp-blogs-extended' );

		if ( ! bpb_extended_blog_public() ) {
			$type = __( 'Hidden', 'bp-blogs-extended' );
		} else if ( bpb_extended_blog_open() ) {
			$type = __( 'Open', 'bp-blogs-extended' );
		}

		return esc_html( $type );
	}

/**
 * Add a subcribe button in blog's header
 */
function bpb_extended_subscribe_button( $blog = false ) {
	echo bpb_extended_get_subscribe_button( $blog );
}
add_action( 'bp_blog_header_actions', 'bpb_extended_subscribe_button', 5 );
add_action( 'bp_blog_header_actions',  'bp_blogs_visit_blog_button', 10 );

	/**
	 * Get the blog's subcribe button
	 */
	function bpb_extended_get_subscribe_button( $blog = false ) {

		if ( empty( $blog ) ) {
			$blog = bpb_extended_current_blog();
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Blog is locked
		if ( empty( $blog->blog_open ) ) {
			return false;
		}

		// Already a member
		if ( ! empty( $blog->is_member ) ) {

			if ( bp_is_item_admin() ) {
				return false;
			}

			$button = array(
				'id'                => 'leave_blog',
				'component'         => 'blogs',
				'must_be_logged_in' => true,
				'block_self'        => false,
				'wrapper_class'     => 'blog-button leave',
				'wrapper_id'        => 'blogbutton-' . $blog->id,
				'link_href'         => wp_nonce_url( bpb_extended_get_blog_profile_link( $blog->id, $blog->slug ) . '?action=leave', 'blogs_leave_blog' ),
				'link_text'         => __( 'Unsubscribe', 'bp-blogs-extended' ),
				'link_title'        => __( 'Unsubscribe', 'bp-blogs-extended' ),
				'link_class'        => 'blog-button leave-blog confirm',
			);

		// Not a member
		} else {

			$button = array(
				'id'                => 'join_blog',
				'component'         => 'blogs',
				'must_be_logged_in' => true,
				'block_self'        => false,
				'wrapper_class'     => 'blog-button join',
				'wrapper_id'        => 'blogbutton-' . $blog->id,
				'link_href'         => wp_nonce_url( bpb_extended_get_blog_profile_link( $blog->id, $blog->slug ) . '?action=join', 'blogs_join_blog' ),
				'link_text'         => __( 'Subscribe', 'bp-blogs-extended' ),
				'link_title'        => __( 'Subscribe', 'bp-blogs-extended' ),
				'link_class'        => 'blog-button join-blog',
			);
		}

		// Filter and return the HTML button
		return bp_get_button( apply_filters( 'bpb_extended_get_subscribe_button', $button ) );
	}

/**
 * Can members publish updates ?
 */
function bpb_extended_blog_updates() {
	$blog = bpb_extended_current_blog();

	if ( empty( $blog->blog_updates ) ) {
		return 0;
	} else {
		return 1;
	}
}

/**
 * Can current user publish updates ?
 */
function bpb_extended_blog_can_post() {
	$blog = bpb_extended_current_blog();

	$can = bp_is_item_admin();

	if ( empty( $can ) && $blog->is_member ) {
		$can = (bool) bpb_extended_blog_updates();
	}

	return apply_filters( 'bpb_extended_blog_can_post', $can, $blog );
}

/**
 * @todo
 */
function bpb_extended_activity_feed_link() {
	echo "#";
}

function bpb_extended_activity_tab_blogs() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$count = bp_get_total_blog_count_for_user( bp_loggedin_user_id() );

	if ( empty( $count ) ) {
		return;
	}
	?>
	<li id="activity-blogs">
		<a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_blogs_slug() . '/'; ?>" title="<?php esc_attr_e( 'The activity of the sites I&#39;m part of.', 'bp-blogs-extended' ); ?>">
			<?php printf( __( 'My Sites <span>%s</span>', 'bp-blogs-extended' ), $count ); ?>
		</a>
	</li>
	<?php
}
add_action( 'bp_before_activity_type_tab_favorites', 'bpb_extended_activity_tab_blogs' );

/**
 * Is the blog public
 */
function bpb_extended_blog_public() {
	$blog = bpb_extended_current_blog();

	if ( ! empty( $blog ) ) {
		return (int) $blog->is_public;
	}

	return (int) get_blog_option( bp_get_blog_id(), 'blog_public', 1 );
}

/**
 * Get the post types having a buddypress-support feature for the
 * current blog
 */
function bpb_extended_get_buddypress_supported_post_types() {
	$blog = bpb_extended_current_blog();

	$supported = array();

	if ( ! empty( $blog ) ) {
		$supported = $blog->post_types;
	}

	return $supported;
}

/**
 * Manage the post types / demo purpose
 *
 * I think this is a nice way to have a UI to help
 * blog admins to choose which post types can be tracked
 * in the activity stream :
 * @see https://buddypress.trac.wordpress.org/ticket/3460#comment:57
 */
function bpb_extended_post_types_management() {
	$blog = bpb_extended_current_blog();

	// For demo purpose..
	if ( empty( $blog ) || ! bpb_extended()->is_debug ) {
		return;
	}

	$supported = bpb_extended_get_buddypress_supported_post_types();
	$tracked   = isset( $blog->post_types_tracked ) ? $blog->post_types_tracked : array( 'post' );

	if ( empty( $supported ) ) {
		bp_blogs_update_blogmeta( $blog->id, 'post_types_tracked', array() );
		return;
	}

	$active = array();
	?>

	<ul>
		<?php foreach ( $supported as $post_type => $name ) :

			if ( in_array( $post_type, $tracked ) ) {
				$active[ $post_type ] = $post_type;
			}
		?>
		<li>
			<label for="post-type-<?php echo esc_attr( $post_type );?>">
				<input type="checkbox" name="bpb_extended[post_types][]" id="post-type-<?php echo esc_attr( $post_type );?>" value="<?php echo esc_attr( $post_type );?>" <?php checked( isset( $active[ $post_type ] ) ); ?> />
				<?php echo esc_html( $name ); ?>
			</label>
		</li>
		<?php endforeach ;?>
	</ul>
	<input type="hidden" name="bpb_extended[post_types][active]" value="<?php echo join( ',', $active );?>"/>
	<p class="description"><?php esc_html_e( 'Activate the checkbox(es) to track the post type(s).', 'bp-blogs-extended' ); ?></p>
	<?php
}

/** Custom front **************************************************************/

function bpb_extended_custom_front_widgets( $sidebars_widgets = array() ) {
	return get_option( 'sidebars_widgets' );
}

/**
 * Custom front is loading blog's widgets
 *
 * Widgets needs to be added in the plugin's sidebar widget
 */
function bpb_extended_custom_front_switch() {
	global $wp_registered_widgets;
	$bpb_extended = bpb_extended();

	$bpb_extended->registered_widgets = $wp_registered_widgets;

	$blog_id = bpb_extended_get_current_blog_id();

	if ( ! empty( $blog_id ) ) {
		// Reset the registered widgets
		$wp_registered_widgets = array();

		switch_to_blog( $blog_id );

		// Make sure the widgets will be registered
		do_action( 'widgets_init' );

		// Make sure to Load the subsite sidebars widgets
		add_filter( 'sidebars_widgets', 'bpb_extended_custom_front_widgets', 10, 1 );
	}
}
add_action( 'bp_before_blog_front_page', 'bpb_extended_custom_front_switch', 0 );

/**
 * Custom front has been displayed, reset everything!
 */
function bpb_extended_custom_front_restore() {
	restore_current_blog();

	// Stop filtering
	remove_filter( 'sidebars_widgets', 'bpb_extended_custom_front_widgets', 10, 1 );

	// Restore the current site registered widgets.
	global $wp_registered_widgets;
	$bpb_extended = bpb_extended();

	$wp_registered_widgets  = $bpb_extended->registered_widgets;
}
add_action( 'bp_after_blog_front_page', 'bpb_extended_custom_front_restore', 0 );
