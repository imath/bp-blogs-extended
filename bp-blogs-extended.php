<?php
/**
 * BP Blogs Extended is a plugin to add single items to BuddyPress blogs component.
 * https://buddypress.trac.wordpress.org/ticket/192
 *
 * @package   BP Blogs Extended
 * @author    imath
 * @license   GPL-2.0+
 * @link      http://imathi.eu
 *
 * @buddypress-plugin
 * Plugin Name:       BP Blogs Extended
 * Plugin URI:        http://imathi.eu/tag/bp-blogs-extended
 * Description:       BP Blogs Extended is a plugin to add single items to BuddyPress blogs component.
 * Version:           1.0.0-alpha
 * Author:            imath
 * Author URI:        http://imathi.eu/
 * Text Domain:       bp-blogs-extended
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/imath/bp-blogs-extended
 * Network:           true
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or die;


if ( ! class_exists( 'BP_Blogs_Extended_Loader' ) ) :
/**
 * BP Blogs Extended Loader Class
 *
 * @since BP Blogs Extended (1.0.0)
 */
class BP_Blogs_Extended_Loader {
	/**
	 * Instance of this class.
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 *
	 * @var     object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets some globals for the plugin
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 */
	private function setup_globals() {
		/** BP Blogs Extended globals *************************************************/
		$this->version        = '1.0.0-alpha';
		$this->domain         = 'bp-blogs-extended';
		$this->file           = __FILE__;
		$this->basename       = plugin_basename( $this->file );
		$this->plugin_dir     = plugin_dir_path( $this->file );
		$this->plugin_url     = plugin_dir_url ( $this->file );
		$this->lang_dir       = trailingslashit( $this->plugin_dir   . 'languages' );
		$this->includes_dir   = trailingslashit( $this->plugin_dir   . 'includes'  );
		$this->tpl_dir        = trailingslashit( $this->plugin_dir ) . 'templates'  ;
		$this->tpl_url        = trailingslashit( $this->plugin_url   . 'templates' );
		$this->is_debug       = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$this->user_blogs     = array();
		$this->blog_permalink = array();

		// Blogs single item specific nav (different from bp_nav to avoid collisions)
		$this->blogs         = new stdClass();
		$this->blogs->nav    = array();
		$this->blogs->subnav = array();

		/** BuddyPress specific globals ***********************************************/
		$this->bp_version           = '2.1';
		$this->is_root_site         = bp_is_root_blog();
		$this->bp_network_activated = bp_is_network_activated();
		$this->is_blogs_active      = bp_is_active( 'blogs' );
	}

	/**
	 * Checks BuddyPress version
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 */
	public function version_check() {
		// taking no risk
		if ( ! defined( 'BP_VERSION' ) ) {
			return false;
		}

		return version_compare( BP_VERSION, $this->bp_version, '>=' );
	}

	/**
	 * Includes the needed file
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 */
	public function includes() {
		if ( ! $this->version_check() || ! $this->bp_network_activated || ! $this->is_blogs_active ) {
			return;
		}

		// Include needed files
		require( $this->includes_dir . 'navbar.php'    );
		require( $this->includes_dir . 'functions.php' );
		require( $this->includes_dir . 'screens.php'   );
		require( $this->includes_dir . 'template.php'  );
		require( $this->includes_dir . 'activity.php'  );
		require( $this->includes_dir . 'actions.php'   );
		require( $this->includes_dir . 'filters.php'   );
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 */
	private function setup_hooks() {
		// Do things only if required BuddyPress version match and blogs component is active
		if ( $this->version_check() && $this->bp_network_activated && $this->is_blogs_active ) {

			if ( $this->is_root_site ) {
				add_action( 'bp_register_theme_directory', array( $this, 'register_template_dir' ) );
				add_action( 'bp_loaded', array( $this, 'cache_group' ), 5 );
			}

			// Register a custom sidebar on each sites
			add_action( 'bp_widgets_init', array( $this, 'register_sidebar' ), 1 );

		} else {
			add_action( 'admin_notices', array( $this, 'admin_warning' ) );
		}

		// loads the languages..
		add_action( 'bp_loaded', array( $this, 'load_textdomain' ) );

	}

	/**
	 * Add plugin's template dir to BuddyPress template stack
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 */
	public function register_template_dir() {
		// Insert it after everybody to only being checked if template
		// wasn't previously found in the stack
		bp_register_template_stack( array( $this, 'template_dir' ),  20 );
	}

	/**
	 * Returns the plugin's templates dir or let BuddyPress deals with it
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 *
	 * @return mixed false|string the template dir of the plugin or false if not needed
	 */
	public function template_dir() {
		return apply_filters( 'bp_blogs_extended_template_dir', $this->tpl_dir );
	}

	/**
	 * Register a cache global group
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 *
	 * @uses wp_cache_add_global_groups()
	 */
	public function cache_group() {
		wp_cache_add_global_groups( array( 'bpb_extended' ) );
	}

	/**
	 * Register a new dynamic sidebar in subsites only
	 *
	 * If a subsite drops at least 1 widget in this sidebar it will generate
	 * a custom front page for the subsite's profile homepage
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 *
	 * @uses register_sidebar()
	 */
	public function register_sidebar() {
		// Leave a way to customize the sidebar template args for the root site
		$sidebar_args = apply_filters( 'bpb_extended_subsite_sidebar', array(
			'before_widget' => '<div id="%1$s" class="%2$s bpb-extended-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );

		// Don't register the sidebar when in root's admin for now
		if ( ! ( $this->is_root_site && is_admin() ) ) {
			$sidebar_args = array_merge( array(
				'name'          => __( 'BuddyPress Site&#39;s profile page', 'bp-blogs-extended' ),
				'description'   => __( 'Want a custom front page for your Site&#39;s profile page? Simply drop some widgets here.', 'bp-blogs-extended' ),
				'id'            => 'bpb-extended-sidebar',
			), $sidebar_args );

			// Finally register the sidebar
			register_sidebar( $sidebar_args );
		}
	}

	/**
	 * Output a warning in case required BuddyPress version does not match.
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 *
	 * @return string HTML Output
	 */
	public function admin_warning() {
		if ( $this->version_check() ) {
			return;
		}
		?>
		<div id="message" class="error fade">
			<p>
				<?php printf( esc_html__( 'Ouch!! Please upgrade to BuddyPress %s to use this widget!', 'bp-blogs-extended' ), $this->bp_version ) ;?>
			</p>
		</div>
		<?php
	}

	/**
	 * Loads the translation files
	 *
	 * @package BP Blogs Extended
	 * @since   1.0
	 *
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_plugin_textdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		$test = load_plugin_textdomain( $this->domain, false, dirname( $this->basename ) . '/languages' );
	}
}
endif;

// Let's start !
function bpb_extended() {
	return BP_Blogs_Extended_Loader::start();
}
add_action( 'bp_include', 'bpb_extended', 10 );
