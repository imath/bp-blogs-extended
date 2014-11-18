<?php
/**
 * BP Blogs Extended - Blog home
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;
?>

<div id="buddypress">

	<?php if ( bp_has_blogs( array( 'include_blog_ids' => bpb_extended()->current_blog->id ) ) ) : while ( bp_blogs() ) : bp_the_blog(); ?>

	<?php do_action( 'bp_before_blog_home_content' ); ?>

	<div id="item-header" role="complementary">

		<?php bp_get_template_part( 'blogs/single/blog-header' ); ?>

	</div><!-- #item-header -->

	<div id="item-nav">
		<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
			<ul>

				<?php bpb_extended_main_nav(); ?>

				<?php do_action( 'bp_blog_main_nav' ); ?>

			</ul>
		</div>
	</div><!-- #item-nav -->

	<div id="item-body">

		<?php do_action( 'bp_before_blog_body' );

		/**
		 * Does this next bit look familiar? If not, go check out WordPress's
		 */

			// Looking at home location
			if ( bpb_extended_is_blog_home() ) :

				// Use custom front if one exists
				if     ( bpb_extended_custom_front() ) : bp_get_template_part( 'blogs/single/front'    );

				// Default to activity
				elseif ( bp_is_active( 'activity' )  ) : bp_get_template_part( 'blogs/single/activity' );

				// Otherwise show members
				elseif ( bp_is_active( 'members'  )  ) : bp_get_template_part( 'blogs/single/members'  );

				endif;

			// Not looking at home
			else :

				// Blog manage
				if     ( bpb_extended_is_blog_manage()   ) : bp_get_template_part( 'blogs/single/manage'   );

				// Group Activity
				elseif ( bpb_extended_is_blog_activity() ) : bp_get_template_part( 'blogs/single/activity' );

				// Group Members
				elseif ( bpb_extended_is_blog_members()  ) : bp_get_template_part( 'blogs/single/members'  );

				// Anything else (plugins mostly)
				else                                       : bp_get_template_part( 'blogs/single/plugins'  );

				endif;

			endif;

		do_action( 'bp_after_blog_body' ); ?>

	</div><!-- #item-body -->

	<?php do_action( 'bp_after_blog_home_content' ); ?>

	<?php endwhile; endif; ?>

</div><!-- #buddypress -->
