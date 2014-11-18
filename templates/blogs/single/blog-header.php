<?php
/**
 * BP Blogs Extended - Blog Header
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

do_action( 'bp_before_blog_header' ); ?>

<div id="item-actions">

	<h3><?php _e( 'Site Admins', 'bp-blogs-extended' ); ?></h3>

	<?php bpb_extended_blog_admins();

	do_action( 'bp_after_blog_menu_admins' ); ?>

</div><!-- #item-actions -->

<div id="item-header-avatar">
	<a href="<?php bp_blog_permalink(); ?>" title="<?php bp_blog_name(); ?>">

		<?php bpb_extended_blog_avatar(); ?>

	</a>
</div><!-- #item-header-avatar -->

<div id="item-header-content">
	<span class="highlight"><?php bpb_extended_blog_type(); ?></span>
	<span class="activity"><?php bp_blog_last_active(); ?></span>

	<?php do_action( 'bp_before_blog_header_meta' ); ?>

	<div id="item-meta">

		<?php bp_blog_description(); ?>

		<div id="item-buttons">

			<?php do_action( 'bp_blog_header_actions' ); ?>

		</div><!-- #item-buttons -->

		<?php do_action( 'bp_blog_header_meta' ); ?>

	</div>
</div><!-- #item-header-content -->

<?php
do_action( 'bp_after_blog_header' );
do_action( 'template_notices' );
?>
