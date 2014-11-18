<?php
/**
 * BP Blogs Extended - Blog activities
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;

// Do not hook this action but make sure to add it in
// your template pack
do_action( 'bp_before_blog_front_page' ); ?>

<div class="front single-blog">

	<?php do_action( 'bp_before_blog_front_content' ); ?>

	<div class="front-widgets" role="main">

	<?php if ( !function_exists( 'dynamic_sidebar' ) || ! dynamic_sidebar( 'bpb-extended-sidebar' ) ) ?>

	</div><!-- .front-widgets -->

	<?php do_action( 'bp_before_blog_front_content' ); ?>

</div><!-- .front.single-blog -->

<?php
// Do not hook this action but make sure to add it in
// your template pack
do_action( 'bp_after_blog_front_page' ); ?>
