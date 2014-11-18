<?php
/**
 * BP Blogs Extended - Blog manage
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;
?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<?php if ( bp_is_item_admin() ) : ?>

			<?php bpb_extended_sub_nav();?>

		<?php endif; ?>
	</ul>
</div>

<?php
$action = bp_action_variable( 0 ) ? bp_action_variable( 0 ) : bp_current_action();

switch ( $action ) :
	case 'manage'  :
		bp_get_template_part( 'blogs/single/settings/general' );
		break;
	case 'edit-photo'   :
		bp_get_template_part( 'blogs/single/settings/photo'   );
		break;
	default:
		bp_get_template_part( 'blogs/single/plugins'          );
		break;
endswitch;
