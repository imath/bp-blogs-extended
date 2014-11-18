<?php
/**
 * BP Blogs Extended - Blog plugins
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die; ?>

<?php do_action( 'bp_before_blog_plugin_template' ); ?>

<div class="item-list-tabs no-ajax" id="subnav">
	<ul>
		<?php bpb_extended_sub_nav();?>

		<?php do_action( 'bp_blog_plugin_sub_nav' ); ?>
	</ul>
</div><!-- .item-list-tabs -->

<h3><?php do_action( 'bp_template_title' ); ?></h3>

<?php do_action( 'bp_template_content' ); ?>

<?php do_action( 'bp_after_blog_plugin_template' ); ?>
