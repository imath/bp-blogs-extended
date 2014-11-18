<?php
/**
 * BP Blogs Extended - Blog activities
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;
?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<li class="feed"><a href="<?php bpb_extended_activity_feed_link(); ?>" title="<?php esc_attr_e( 'RSS Feed', 'bp-blogs-extended' ); ?>"><?php _e( 'RSS', 'bp-blogs-extended' ); ?></a></li>

		<?php do_action( 'bp_blog_activity_syndication_options' ); ?>

		<li id="activity-filter-select" class="last">
			<label for="activity-filter-by"><?php _e( 'Show:', 'bp-blogs-extended' ); ?></label>
			<select id="activity-filter-by">
				<option value="-1"><?php _e( '&mdash; Everything &mdash;', 'bp-blogs-extended' ); ?></option>

				<?php bp_activity_show_filters( 'blog' ); ?>

				<?php do_action( 'bp_blog_activity_filter_options' ); ?>
			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<?php do_action( 'bp_before_blog_activity_post_form' ); ?>

<?php if ( bpb_extended_blog_can_post() ) : ?>

	<?php bp_get_template_part( 'blogs/single/activity/post-form' ); ?>

<?php endif; ?>

<?php do_action( 'bp_after_blog_activity_post_form' ); ?>
<?php do_action( 'bp_before_blog_activity_content' ); ?>

<div class="activity single-blog" role="main">

	<?php bp_get_template_part( 'activity/activity-loop' ); ?>

</div><!-- .activity.single-group -->

<?php do_action( 'bp_after_blog_activity_content' ); ?>
