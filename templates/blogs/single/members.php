<?php
/**
 * BP Blogs Extended - Blog members
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;
?>

<form action="" method="post" id="members-blog-form" class="dir-form">

	<div class="item-list-tabs" id="subnav" role="navigation">
		<ul>
			<?php bpb_extended_sub_nav();?>

			<?php do_action( 'bp_members_blog_member_sub_types' ); ?>

			<li id="members-order-select" class="last filter">
				<label for="members-order-by"><?php _e( 'Order By:', 'bp-blogs-extended' ); ?></label>
				<select id="members-order-by">
					<option value="active"><?php _e( 'Last Active', 'bp-blogs-extended' ); ?></option>

					<?php if ( bp_is_active( 'xprofile' ) ) : ?>
						<option value="alphabetical"><?php _e( 'Alphabetical', 'bp-blogs-extended' ); ?></option>
					<?php endif; ?>

					<?php do_action( 'bp_members_blog_order_options' ); ?>
				</select>
			</li>
		</ul>
	</div>

	<div id="blog-list" class="members item-list">
		<?php bp_get_template_part( 'members/members-loop' ); ?>
	</div><!-- #members-dir-list -->

	<?php do_action( 'bp_blog_members_content' ); ?>

	<?php wp_nonce_field( 'blog_members', '_wpnonce-member-filter' ); ?>

	<?php do_action( 'bp_after_blog_members_content' ); ?>

</form><!-- #members-directory-form -->

<?php do_action( 'bp_after_blog_members' ); ?>
