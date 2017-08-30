<?php
/**
 * Merlin
 * Better WordPress Theme Onboarding
 *
 * The following code is a derivative work from the
 * Envato WordPress Theme Setup Wizard by David Baker.
 *
 * @package   Merlin WP
 * @version   @@pkg.version
 * @link      https://merlinwp.com/
 * @author    Richard Tabor, from ThemeBeans.com
 * @copyright Copyright (c) 2017, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for open source use, or Merlin WP Commercial License for commercial use
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merlin.
 */
class Merlin {
	/**
	 * Current theme.
	 *
	 * @var object WP_Theme
	 */
	protected $theme;

	/**
	 * Current step.
	 *
	 * @var string
	 */
	protected $step = '';

	/**
	 * Steps.
	 *
	 * @var    array
	 */
	protected $steps = array();

	/**
	 * TGMPA instance.
	 *
	 * @var    object
	 */
	protected $tgmpa;

	/**
	 * Importer.
	 *
	 * @var    array
	 */
	protected $importer;

	/**
	 * Helper.
	 *
	 * @var    array
	 */
	protected $helper;

	/**
	 * Updater.
	 *
	 * @var    array
	 */
	protected $updater;

	/**
	 * The text string array.
	 *
	 * @var array $strings
	 */
	protected $strings = null;

	/**
	 * The location where Merlin is located within the theme.
	 *
	 * @var string $directory
	 */
	protected $directory = null;

	/**
	 * The location where the demo content is located within the theme.
	 *
	 * @var string $demo_directory
	 */
	protected $demo_directory = null;

	/**
	 * Top level admin page.
	 *
	 * @var string $merlin_url
	 */
	protected $merlin_url = null;

	/**
	 * The URL for the "Learn more about child themes" link.
	 *
	 * @var string $child_action_btn_url
	 */
	protected $child_action_btn_url = null;

	/**
	 * Turn on help mode to get some help.
	 *
	 * @var string $child_action_btn_url
	 */
	protected $help_mode = false;

	/**
	 * Turn on dev mode if you're developing.
	 *
	 * @var string $child_action_btn_url
	 */
	protected $dev_mode = false;

	/**
	 * The URL for the "Learn more about child themes" link.
	 *
	 * @var string $child_action_btn_url
	 */
	protected $branding = false;

	/**
	 * Setup plugin version.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function version() {

		if ( ! defined( 'MERLIN_VERSION' ) ) {
			define( 'MERLIN_VERSION', '0.1.2' );
		}
	}

	/**
	 * Class Constructor.
	 *
	 * @param array $config Package-specific configuration args.
	 * @param array $strings Text for the different elements.
	 */
	function __construct( $config = array(), $strings = array() ) {

		$this->version();

		$config = wp_parse_args( $config, array(
			'directory' => '',
			'demo_directory' => '',
			'merlin_url' => 'merlin',
			'child_action_btn_url' => '',
			'help_mode' => '',
			'dev_mode' => '',
			'branding' => '',
		) );

		// Set config arguments.
		$this->directory 			= $config['directory'];
		$this->demo_directory 			= $config['demo_directory'];
		$this->merlin_url			= $config['merlin_url'];
		$this->child_action_btn_url = $config['child_action_btn_url'];
		$this->help_mode 			= $config['help_mode'];
		$this->dev_mode 			= $config['dev_mode'];
		$this->branding 			= $config['branding'];

		// Strings passed in from the config file.
		$this->strings 				= $strings;

		// Retrieve a WP_Theme object.
		$this->theme 				= wp_get_theme();
		$this->slug  				= strtolower( preg_replace( '#[^a-zA-Z]#', '', $this->theme->get( 'Name' ) ) );

		// Is Dev Mode turned on?
		if ( true != $this->dev_mode ) {

			// Has this theme been setup yet?
			$already_setup 			= get_option( 'merlin_' . $this->slug . '_completed' );

			// Return if Merlin has already completed it's setup.
			if ( $already_setup ) {
				return;
			}
		}

		// Get TGMPA.
		if ( class_exists( 'TGM_Plugin_Activation' ) ) {
			$this->tgmpa = isset( $GLOBALS['tgmpa'] ) ? $GLOBALS['tgmpa'] : TGM_Plugin_Activation::get_instance();
		}

		add_action( 'admin_init', array( $this, 'required_classes' ) );
		add_action( 'admin_init', array( $this, 'redirect' ), 30 );
		add_action( 'after_switch_theme', array( $this, 'switch_theme' ) );
		add_action( 'admin_init', array( $this, 'steps' ), 30, 0 );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_page' ), 30, 0 );
		add_action( 'admin_footer', array( $this, 'svg_sprite' ) );
		add_filter( 'tgmpa_load', array( $this, 'load_tgmpa' ), 10, 1 );
		add_action( 'wp_ajax_merlin_content', array( $this, '_ajax_content' ), 10, 0 );
		add_action( 'wp_ajax_merlin_plugins', array( $this, '_ajax_plugins' ), 10, 0 );
		add_action( 'wp_ajax_merlin_child_theme', array( $this, 'generate_child' ), 10, 0 );
		add_action( 'wp_ajax_merlin_activate_license', array( $this, 'activate_license' ), 10, 0 );
		add_action( 'upgrader_post_install', array( $this, 'post_install_check' ), 10, 2 );
		//add_filter( 'sidebars_widgets', array( $this, 'unset_sidebar_widgets' ) );
	}

	/**
	 * Require necessary classes.
	 */
	function required_classes() {

		if ( ! class_exists( 'Merlin_WXR_Parser' ) ) {
			require get_parent_theme_file_path( $this->directory . '/merlin/includes/class-merlin-xml-parser.php' );
		}

		if ( ! class_exists( 'Merlin_Importer' ) ) {
			require get_parent_theme_file_path( $this->directory . '/merlin/includes/class-merlin-importer.php' );
			$this->importer = new Merlin_Importer();
		}

		if ( class_exists( 'EDD_Theme_Updater_Admin' ) ) {
			$this->updater = new EDD_Theme_Updater_Admin;
		}

		if ( ! class_exists( 'Merlin_Helper' ) and  true == $this->help_mode ) {
			require get_parent_theme_file_path( $this->directory . '/merlin/includes/class-merlin-helper.php' );
			$this->helper = new Merlin_Helper();
		}
	}

	/**
	 * Set redirection transient.
	 */
	public function switch_theme() {
		if ( ! is_child_theme() ) {
			set_transient( $this->theme->template . '_merlin_redirect', 1 );
		}
	}

	/**
	 * Redirection transient.
	 */
	public function redirect() {

		if ( ! get_transient( $this->theme->template . '_merlin_redirect' ) ) {
			return;
		}

		delete_transient( $this->theme->template . '_merlin_redirect' );

		wp_safe_redirect( admin_url( 'themes.php?page='.$this->merlin_url ) );

		exit;
	}

	/**
	 * Remove default sidebar widgets.
	 *
	 * @param array $sidebars_widgets An associative array of sidebars and their widgets.
	 * @todo Only run this when Merlin has be initiated, not when a theme is activated.
	 */
	function unset_sidebar_widgets( $sidebars_widgets ) {

		foreach ( $sidebars_widgets as $widget_area => $widget_list ) {

			foreach ( $widget_list as $pos => $widget_id ) {
				if ( 'search-2' == $widget_id || 'recent-posts-2' == $widget_id || 'recent-comments-2' == $widget_id || 'archives-2' == $widget_id || 'categories-2' == $widget_id || 'meta-2' == $widget_id ) {
					unset( $sidebars_widgets[ $widget_area ][ $pos ] );
				}
			}
		}

		return $sidebars_widgets;
	}

	/**
	 * Conditionally load TGMPA
	 *
	 * @param string $status User's manage capabilities.
	 */
	function load_tgmpa( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Determine if the user already has theme content installed.
	 * This can happen if swapping from a previous theme or updated the current theme.
	 * We change the UI a bit when updating / swapping to a new theme.
	 *
	 * @access public
	 */
	protected function is_possible_upgrade() {
		return false;
	}

	/**
	 * After a theme update, we clear the slug_merlin_completed option.
	 * This prompts the user to visit the update page again.
	 *
	 * @param 		string $return To end or not.
	 * @param 		string $theme  The current theme.
	 */
	function post_install_check( $return, $theme ) {

		if ( is_wp_error( $return ) ) {
			return $return;
		}

		if ( $theme !== $this->theme->stylesheet ) {
			return $return;
		}

		update_option( 'merlin_' . $this->slug . '_completed', false );

		return $return;
	}

	/**
	 * Add the admin menu item, under Appearance.
	 */
	function add_admin_menu() {

		// Strings passed in from the config file.
		$strings = $this->strings;

		$this->hook_suffix = add_theme_page(
			esc_html( $strings['admin-menu'] ), esc_html( $strings['admin-menu'] ), 'manage_options', $this->merlin_url, array( $this, 'admin_page' )
		);
	}

	/**
	 * Add the admin page.
	 */
	function admin_page() {

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Do not proceed, if we're not on the right page.
		if ( empty( $_GET['page'] ) || $this->merlin_url !== $_GET['page'] ) {
			return;
		}

		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		// Use minified libraries if dev mode is turned on.
		$suffix = ( ( true == $this->dev_mode ) ) ? '' : '.min';

		// Enqueue styles.
		wp_enqueue_style( 'merlin', get_parent_theme_file_uri( $this->directory . '/merlin/assets/css/merlin' . $suffix . '.css' ), array( 'wp-admin' ), MERLIN_VERSION );

		// Enqueue javascript.
		wp_enqueue_script( 'merlin', get_parent_theme_file_uri( $this->directory . '/merlin/assets/js/merlin' . $suffix . '.js' ), array( 'jquery-core' ), MERLIN_VERSION );

		// Localize the javascript.
		if ( class_exists( 'TGM_Plugin_Activation' ) ) {
			// Check first if TMGPA is included.
			wp_localize_script( 'merlin', 'merlin_params', array(
				'tgm_plugin_nonce' 	=> array(
					'update'  	=> wp_create_nonce( 'tgmpa-update' ),
					'install' 	=> wp_create_nonce( 'tgmpa-install' ),
				),
				'tgm_bulk_url' 		=> $this->tgmpa->get_tgmpa_url(),
				'ajaxurl'      		=> admin_url( 'admin-ajax.php' ),
				'wpnonce'      		=> wp_create_nonce( 'merlin_nonce' ),
			) );
		} else {
			// If TMGPA is not included.
			wp_localize_script( 'merlin', 'merlin_params', array(
				'ajaxurl'      		=> admin_url( 'admin-ajax.php' ),
				'wpnonce'      		=> wp_create_nonce( 'merlin_nonce' ),
			) );
		}

		ob_start();

		/**
		 * Start the actual page content.
		 */
		$this->header(); ?>

		<div class="merlin__wrapper">

			<div class="merlin__content merlin__content--<?php echo esc_attr( strtolower( $this->steps[ $this->step ]['name'] ) ); ?>">

				<?php
				// Content Handlers.
				$show_content = true;

				if ( ! empty( $_REQUEST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
					$show_content = call_user_func( $this->steps[ $this->step ]['handler'] );
				}

				if ( $show_content ) {
					$this->body();
				} ?>

			<?php $this->step_output(); ?>

			</div>

			<?php echo sprintf( '<a class="return-to-dashboard" href="%s">%s</a>', esc_url( admin_url( '/' ) ), esc_html( $strings['return-to-dashboard'] ) ); ?>

		</div>

		<?php $this->footer(); ?>
		
		<?php
		exit;
	}

	/**
	 * Output the header.
	 */
	protected function header() {

		// Strings passed in from the config file.
		$strings = $this->strings; 

		// Get the current step.
		$current_step = strtolower( $this->steps[ $this->step ]['name'] ); ?>

		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<?php printf( esc_html( $strings['title%s%s%s%s'] ), '<ti', 'tle>', esc_html( $this->theme->name ), '</title>' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="merlin__body merlin__body--<?php echo esc_attr( $current_step ); ?>">
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	protected function body() {
		isset( $this->steps[ $this->step ] ) ? call_user_func( $this->steps[ $this->step ]['view'] ) : false;
	}

	/**
	 * Output the footer.
	 */
	protected function footer() {

		// Is help_mode set in the merlin-config.php file?
		if ( true == $this->help_mode ) :
			$current_step = strtolower( $this->steps[ $this->step ]['name'] );
			$this->helper->helper_wizard( $current_step );
		endif;

		if ( true == $this->help_mode or true == $this->branding ) : ?>
			<a class="merlin--icon" target="_blank" href="https://merlinwp.com">
				<?php echo wp_kses( $this->svg( array( 'icon' => 'merlin' ) ), $this->svg_allowed_html() ); ?>
			</a>
		<?php endif; ?>

		</body>
		<?php do_action( 'admin_footer' ); ?>
		<?php do_action( 'admin_print_footer_scripts' ); ?>
		</html>
		<?php
	}

	/**
	 * SVG
	 */
	function svg_sprite() {

		// Define SVG sprite file.
		$svg = get_parent_theme_file_path( $this->directory . '/merlin/assets/images/sprite.svg' );

		// If it exists, include it.
		if ( file_exists( $svg ) ) {
			require_once apply_filters( 'merlin_svg_sprite', $svg );
		}
	}

	/**
	 * Return SVG markup.
	 *
	 * @param array $args {
	 *     Parameters needed to display an SVG.
	 *
	 *     @type string $icon  Required SVG icon filename.
	 *     @type string $title Optional SVG title.
	 *     @type string $desc  Optional SVG description.
	 * }
	 * @return string SVG markup.
	 */
	function svg( $args = array() ) {

		// Make sure $args are an array.
		if ( empty( $args ) ) {
			return __( 'Please define default parameters in the form of an array.', '@@textdomain' );
		}

		// Define an icon.
		if ( false === array_key_exists( 'icon', $args ) ) {
			return __( 'Please define an SVG icon filename.', '@@textdomain' );
		}

		// Set defaults.
		$defaults = array(
			'icon'        => '',
			'title'       => '',
			'desc'        => '',
			'aria_hidden' => true, // Hide from screen readers.
			'fallback'    => false,
		);

		// Parse args.
		$args = wp_parse_args( $args, $defaults );

		// Set aria hidden.
		$aria_hidden = '';

		if ( true === $args['aria_hidden'] ) {
			$aria_hidden = ' aria-hidden="true"';
		}

		// Set ARIA.
		$aria_labelledby = '';

		if ( $args['title'] && $args['desc'] ) {
			$aria_labelledby = ' aria-labelledby="title desc"';
		}

		// Begin SVG markup.
		$svg = '<svg class="icon icon--' . esc_attr( $args['icon'] ) . '"' . $aria_hidden . $aria_labelledby . ' role="img">';

		// If there is a title, display it.
		if ( $args['title'] ) {
			$svg .= '<title>' . esc_html( $args['title'] ) . '</title>';
		}

		// If there is a description, display it.
		if ( $args['desc'] ) {
			$svg .= '<desc>' . esc_html( $args['desc'] ) . '</desc>';
		}

		$svg .= '<use xlink:href="#icon-' . esc_html( $args['icon'] ) . '"></use>';

		// Add some markup to use as a fallback for browsers that do not support SVGs.
		if ( $args['fallback'] ) {
			$svg .= '<span class="svg-fallback icon--' . esc_attr( $args['icon'] ) . '"></span>';
		}

		$svg .= '</svg>';

		return $svg;
	}

	/**
	 * Adds data attributes to the body, based on Customizer entries.
	 */
	function svg_allowed_html() {

		$array = array(
			'svg' => array(
				'class' => array(),
				'aria-hidden' => array(),
				'role' => array(),
			),
			'use' => array(
				'xlink:href' => array(),
			),
		);

		return apply_filters( 'merlin_svg_allowed_html', $array );

	}

	/**
	 * Loading merlin-spinner.
	 */
	function loading_spinner() {

		// Define the spinner file.
		$spinner = $this->directory . '/merlin/assets/images/spinner';

		// Retrieve the spinner.
		get_template_part( apply_filters( 'merlin_loading_spinner', $spinner ) );

	}

	/**
	 * Setup steps.
	 */
	function steps() {

		$this->steps = array(
			'welcome' => array(
				'name'    => esc_html__( 'Welcome', '@@textdomain' ),
				'view'    => array( $this, 'welcome' ),
				'handler' => array( $this, 'welcome_handler' ),
			),
		);

		$this->steps['child'] = array(
			'name'    => esc_html__( 'Child', '@@textdomain' ),
			'view'    => array( $this, 'child' ),
		);

		// Show the plugin importer, only if TGMPA is included.
		if ( class_exists( 'TGM_Plugin_Activation' ) ) {
			$this->steps['plugins'] = array(
				'name'    => esc_html__( 'Plugins', '@@textdomain' ),
				'view'    => array( $this, 'plugins' ),
			);
		}

		// Show the content importer, only if there's demo content added.
		if ( $this->get_base_content() ) {
			$this->steps['content'] = array(
				'name'    => esc_html__( 'Content', '@@textdomain' ),
				'view'    => array( $this, 'content' ),
			);
		}

		$this->steps['ready'] = array(
			'name'    => esc_html__( 'Ready', '@@textdomain' ),
			'view'    => array( $this, 'ready' ),
		);

		$this->steps = apply_filters( $this->theme->template . '_merlin_steps', $this->steps );
	}

	/**
	 * Output the steps
	 */
	protected function step_output() {
		$ouput_steps 	= $this->steps;
		$array_keys 	= array_keys( $this->steps );
		$current_step 	= array_search( $this->step, $array_keys );

		array_shift( $ouput_steps ); ?>

		<ol class="dots">

			<?php foreach ( $ouput_steps as $step_key => $step ) :

				$class_attr = '';
				$show_link = false;

				if ( $step_key === $this->step ) {
					$class_attr = 'active';
				} elseif ( $current_step > array_search( $step_key, $array_keys ) ) {
					$class_attr = 'done';
					$show_link = true;
				} ?>

				<li class="<?php echo esc_attr( $class_attr ); ?>">
					<a href="<?php echo esc_url( $this->step_link( $step_key ) ); ?>" title="<?php echo esc_attr( $step['name'] ); ?>"></a>
				</li>

			<?php endforeach; ?>

		</ol>

		<?php
	}

	/**
	 * Get the step URL.
	 *
	 * @param 	string $step Name of the step, appended to the URL.
	 */
	protected function step_link( $step ) {
		return add_query_arg( 'step', $step );
	}

	/**
	 * Get the next step link.
	 */
	protected function step_next_link() {
		$keys = array_keys( $this->steps );
		$step = array_search( $this->step, $keys ) + 1;

		return add_query_arg( 'step', $keys[ $step ] );
	}

	/**
	 * Introduction step
	 */
	protected function welcome() {

		// Has this theme been setup yet? Compare this to the option set when you get to the last panel.
		$already_setup 			= get_option( 'merlin_' . $this->slug . '_completed' );

		// Theme Name.
		$theme 					= ucfirst( $this->theme );

		// Remove "Child" from the current theme name, if it's installed.
		$theme  = str_replace( ' Child','', $theme );

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header 				= ! $already_setup ? $strings['welcome-header%s'] : $strings['welcome-header-success%s'];
		$paragraph 				= ! $already_setup ? $strings['welcome%s'] : $strings['welcome-success%s'];
		$start 					= $strings['btn-start'];
		$no 					= $strings['btn-no'];
		?>

		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'welcome' ) ), $this->svg_allowed_html() ); ?>
			
			<h1><?php echo esc_html( sprintf( $header, $theme ) ); ?></h1>

			<p><?php echo esc_html( sprintf( $paragraph, $theme ) ); ?></p>
	
		</div>

		<footer class="merlin__content__footer">
			<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '/' ) ); ?>" class="merlin__button merlin__button--skip"><?php echo esc_html( $no ); ?></a>
			<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html( $start ); ?></a>
			<?php wp_nonce_field( 'merlin' ); ?>
		</footer>

	<?php
	}

	/**
	 * Handles save button from welcome page.
	 * This is to perform tasks when the setup wizard has already been run.
	 */
	protected function welcome_handler() {

		check_admin_referer( 'merlin' );

		return false;
	}

	/**
	 * Child theme generator.
	 */
	protected function child() {

		// Variables.
		$is_child_theme 			= is_child_theme();
		$child_theme_option 			= get_option( 'merlin_' . $this->slug . '_child' );
		$theme 					= $child_theme_option ? wp_get_theme( $child_theme_option )->name : $this->theme . ' Child';
		$action_url 				= $this->child_action_btn_url;

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header 				= ! $is_child_theme ? $strings['child-header'] : $strings['child-header-success'];
		$action 				= $strings['child-action-link'];
		$skip 					= $strings['btn-skip'];
		$next 					= $strings['btn-next'];
		$paragraph 				= ! $is_child_theme ? $strings['child'] : $strings['child-success%s'];
		$install 				= $strings['btn-child-install'];
		?>
		
		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'child' ) ), $this->svg_allowed_html() ); ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php echo esc_html( $header ); ?></h1>

			<p id="child-theme-text"><?php echo esc_html( sprintf( $paragraph, $theme ) ); ?></p>

			<a class="merlin__button merlin__button--knockout merlin__button--no-chevron" href="<?php echo esc_url( $action_url ); ?>" target="_blank"><?php echo esc_html( $action ); ?></a>
			
		</div>

		<footer class="merlin__content__footer">

			<?php if ( ! $is_child_theme ) : ?>

				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>
				
				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="install_child">
					<span class="merlin__button--loading__text"><?php echo esc_html( $install ); ?></span><?php echo $this->loading_spinner(); ?>
				</a>

			<?php else : ?>
				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html( $next ); ?></a>
			<?php endif; ?>
			<?php wp_nonce_field( 'merlin' ); ?>
		</footer>
	<?php
	}

	/**
	 * Theme plugins
	 */
	protected function plugins() {

		// Variables.
		$url     				= wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'merlin' );
		$method  				= '';
		$fields 				= array_keys( $_POST );
		$creds   				= request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields );

		tgmpa_load_bulk_installer();

		if ( false === $creds ) {
			return true;
		}

		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}

		// Are there plugins that need installing/activating?
		$plugins 				= $this->get_tgmpa_plugins();
		$count 					= count( $plugins['all'] );
		$class 					= $count ? null : 'no-plugins';

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header 				= $count ? $strings['plugins-header'] : $strings['plugins-header-success'];
		$paragraph 				= $count ? $strings['plugins'] : $strings['plugins-success%s'];
		$action 				= $strings['plugins-action-link'];
		$skip 					= $strings['btn-skip'];
		$next 					= $strings['btn-next'];
		$install 				= $strings['btn-plugins-install'];
		?>

		<div class="merlin__content--transition">
			
			<?php echo wp_kses( $this->svg( array( 'icon' => 'plugins' ) ), $this->svg_allowed_html() ); ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php echo esc_html( $header ); ?></h1>
				
			<p><?php echo esc_html( $paragraph ); ?></p>

			<?php if ( $count ) { ?>
				<a id="merlin__drawer-trigger" class="merlin__button merlin__button--knockout"><span><?php echo esc_html( $action ); ?></span><span class="chevron"></span></a>
			<?php  } ?>

		</div>

		<form action="" method="post">

			<?php if ( $count ) : ?>

				<ul class="merlin__drawer merlin__drawer--install-plugins">
				
				<?php foreach ( $plugins['all'] as $slug => $plugin ) : ?>

					<li data-slug="<?php echo esc_attr( $slug ); ?>">
						
						<?php echo esc_html( $plugin['name'] ); ?>

						<span>
							<?php
							$keys = array();

							if ( isset( $plugins['install'][ $slug ] ) ) {
								$keys[] = esc_html__( 'Install', '@@textdomain' );
							}
							if ( isset( $plugins['update'][ $slug ] ) ) {
								$keys[] = esc_html__( 'Update', '@@textdomain' );
							}
							if ( isset( $plugins['activate'][ $slug ] ) ) {
								$keys[] = esc_html__( 'Activate', '@@textdomain' );
							}
							echo implode( esc_html__( 'and', '@@textdomain' ) , $keys );
							?>
							
						</span>

						<div class="spinner"></div>

					</li>
				<?php endforeach; ?>

				</ul>

			<?php endif; ?>

			<footer class="merlin__content__footer <?php echo esc_attr( $class ); ?>">
				<?php if ( $count ) : ?>
					<a id="close" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--closer merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>
					<a id="skip" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="install_plugins">
						<span class="merlin__button--loading__text"><?php echo esc_html( $install ); ?></span><?php echo $this->loading_spinner(); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html( $next ); ?></a>
				<?php endif; ?>
				<?php wp_nonce_field( 'merlin' ); ?>
			</footer>
		</form>

	<?php
	}

	/**
	 * Page setup
	 */
	protected function content() {

		// Start the importing process.
		$this->importer->importStart();

		// Retrieve the content to import.
		$content = $this->get_base_content();

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header 				= $strings['import-header'];
		$paragraph 				= $strings['import'];
		$action 				= $strings['import-action-link'];
		$skip 					= $strings['btn-skip'];
		$next 					= $strings['btn-next'];
		$import 				= $strings['btn-import'];
		?>
		
		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'content' ) ), $this->svg_allowed_html() ); ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php echo esc_html( $header ); ?></h1>
		
			<p><?php echo esc_html( $paragraph ); ?></p>
			
			<a id="merlin__drawer-trigger" class="merlin__button merlin__button--knockout"><span><?php echo esc_html( $action ); ?></span><span class="chevron"></span></a>

		</div>

		<form action="" method="post">

			<ul class="merlin__drawer merlin__drawer--import-content">
				<?php
				foreach ( $content as $slug => $default ) :

					if ( 'baseurl' === $slug || 'version' === $slug ) {
						continue;
					}

					if ( 'users' === $slug ) {
						$default['checked'] = false;
					} ?>

					<li class="merlin__drawer--import-content__list-item status status--<?php echo esc_attr( $default['pending'] ); ?>" data-content="<?php echo esc_attr( $slug ); ?>">
						<input type="checkbox" name="default_content[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_content_<?php echo esc_attr( $slug ); ?>" value="1" <?php echo ( ! isset( $default['checked'] ) || $default['checked'] ) ? ' checked' : ''; ?>>
						<label for="default_content_<?php echo esc_attr( $slug ); ?>">
							<i></i><span><?php echo esc_html( $default['title'] ); ?></span>
						</label>
					</li>

				<?php endforeach; ?>
			</ul>
	
			<footer class="merlin__content__footer">
				
				<a id="close" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--closer merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>
				
				<a id="skip" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>
				
				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="install_content">
					<span class="merlin__button--loading__text"><?php echo esc_html( $import ); ?></span><?php echo $this->loading_spinner(); ?>
				</a>

				<?php wp_nonce_field( 'merlin' ); ?>
			</footer>
		</form>

	<?php
	}

	/**
	 * Final step
	 */
	protected function ready() {

		// Author name.
		$author = $this->theme->author;

		// Theme Name.
		$theme 					= ucfirst( $this->theme );

		// Remove "Child" from the current theme name, if it's installed.
		$theme 					= str_replace( ' Child','', $theme );

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header 				= $strings['ready-header'];
		$paragraph 				= $strings['ready%s'];
		$action 				= $strings['ready-action-link'];
		$skip 					= $strings['btn-skip'];
		$next 					= $strings['btn-next'];
		$big_btn 				= $strings['ready-big-button'];

		// Links.
		$link_1 				= $strings['ready-link-1'];
		$link_2 				= $strings['ready-link-2'];
		$link_3 				= $strings['ready-link-3'];

		$allowed_html_array = array(
			'a' => array(
				'href' 		=> array(),
				'title' 	=> array(),
				'target' 	=> array(),
			),
		);

		update_option( 'merlin_' . $this->slug . '_completed', time() ); ?>

		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'done' ) ), $this->svg_allowed_html() ); ?>
			
			<h1><?php echo esc_html( sprintf( $header, $theme ) ); ?></h1>

			<p><?php wp_kses(  printf( $paragraph, $author ), $allowed_html_array ); ?></p>

		</div>

		<footer class="merlin__content__footer merlin__content__footer--fullwidth">
			
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="merlin__button merlin__button--blue merlin__button--fullwidth merlin__button--popin"><?php echo esc_html( $big_btn ); ?></a>
			
			<a id="merlin__drawer-trigger" class="merlin__button merlin__button--knockout"><span><?php echo esc_html( $action ); ?></span><span class="chevron"></span></a>
			
			<ul class="merlin__drawer merlin__drawer--extras">

				<li><?php echo wp_kses( $link_1, $allowed_html_array ); ?></li>
				<li><?php echo wp_kses( $link_2, $allowed_html_array ); ?></li>
				<li><?php echo wp_kses( $link_3, $allowed_html_array ); ?></li>

			</ul>

		</footer>

	<?php
	}

	/**
	 * Get registered TGMPA plugins
	 *
	 * @return    array
	 */
	protected function get_tgmpa_plugins() {
		$plugins  = array(
			'all'      => array(), // Meaning: all plugins which still have open actions.
			'install'  => array(),
			'update'   => array(),
			'activate' => array(),
		);

		foreach ( $this->tgmpa->plugins as $slug => $plugin ) {
			if ( $this->tgmpa->is_plugin_active( $slug ) && false === $this->tgmpa->does_plugin_have_update( $slug ) ) {
				continue;
			} else {
				$plugins['all'][ $slug ] = $plugin;
				if ( ! $this->tgmpa->is_plugin_installed( $slug ) ) {
					$plugins['install'][ $slug ] = $plugin;
				} else {
					if ( false !== $this->tgmpa->does_plugin_have_update( $slug ) ) {
						$plugins['update'][ $slug ] = $plugin;
					}
					if ( $this->tgmpa->can_plugin_activate( $slug ) ) {
						$plugins['activate'][ $slug ] = $plugin;
					}
				}
			}
		}

		return $plugins;
	}

	/**
	 * Generate the child theme via AJAX.
	 */
	function generate_child() {

		// Strings passed in from the config file.
		$strings 		= $this->strings;

		// Text strings.
		$success 		= $strings['child-json-success%s'];
		$already 		= $strings['child-json-already%s'];

		$name 			= $this->theme . ' Child';
		$slug 			= sanitize_title( $name );

		$path 			= get_theme_root() . '/' . $slug;
		$screenshot_png 	= get_parent_theme_file_path( '/screenshot.png' );
		$screenshot_jpg 	= get_parent_theme_file_path( '/screenshot.jpg' );

		if ( ! file_exists( $path ) ) {

			WP_Filesystem();

			global $wp_filesystem;

			$wp_filesystem->mkdir( $path );
			$wp_filesystem->put_contents( $path . '/style.css', $this->generate_child_style_css( $this->theme->template, $this->theme->name, $this->theme->author, $this->theme->version ) );
			$wp_filesystem->put_contents( $path . '/functions.php', $this->generate_child_functions_php( $this->theme->template ) );

			if ( file_exists( $screenshot_png ) ) {
				copy( $screenshot_png, $path . '/screenshot.png' );
			} elseif ( file_exists( $screenshot_jpg ) ) {
				copy( $screenshot_png, $path . '/screenshot.jpg' );
			}

			$allowed_themes = get_option( 'allowedthemes' );
			$allowed_themes[ $slug ] = true;
			update_option( 'allowedthemes', $allowed_themes );

		} else {

			if ( $this->theme->template !== $slug ) :
				update_option( 'merlin_' . $this->slug . '_child', $name );
				switch_theme( $slug );
			endif;

			wp_send_json(
				array(
					'done' => 1,
					'message' => sprintf( esc_html( $success ), $slug
					),
				)
			);
		}

		if ( $this->theme->template !== $slug ) :
			update_option( 'merlin_' . $this->slug . '_child', $name );
			switch_theme( $slug );
		endif;

		wp_send_json(
			array(
				'done' => 1,
				'message' => sprintf( esc_html( $already ), $name
				),
			)
		);
	}

	/**
	 * Content template for the child theme functions.php file.
	 *
	 * @link https://gist.github.com/richtabor/688327dd103b1aa826ebae47e99a0fbe
	 *
	 * @param string $slug Parent theme slug.
	 */
	function generate_child_functions_php( $slug ) {

		$slug_no_hyphens = strtolower( preg_replace( '#[^a-zA-Z]#', '', $slug ) );

		$output = "
			<?php
			/**
			 * Theme functions and definitions.
			 * This child theme was generated by Merlin WP.
			 *
			 * @link https://developer.wordpress.org/themes/basics/theme-functions/
			 */

			/*
			 * If your child theme has more than one .css file (eg. ie.css, style.css, main.css) then 
			 * you will have to make sure to maintain all of the parent theme dependencies.
			 *
			 * Make sure you're using the correct handle for loading the parent theme's styles.
			 * Failure to use the proper tag will result in a CSS file needlessly being loaded twice.
			 * This will usually not affect the site appearance, but it's inefficient and extends your page's loading time.
			 *
			 * @link https://codex.wordpress.org/Child_Themes
			 */
			function {$slug_no_hyphens}_child_enqueue_styles() {
			    wp_enqueue_style( '{$slug}-style' , get_template_directory_uri() . '/style.css' );
			    wp_enqueue_style( '{$slug}-child-style',
			        get_stylesheet_directory_uri() . '/style.css',
			        array( '{$slug}-style' ),
			        wp_get_theme()->get('Version')
			    );
			}

			add_action(  'wp_enqueue_scripts', '{$slug_no_hyphens}_child_enqueue_styles' );\n
		";

		// Let's remove the tabs so that it displays nicely.
		$output = trim( preg_replace( '/\t+/', '', $output ) );

		// Filterable return.
		return apply_filters( 'merlin_generate_child_functions_php', $output, $slug );
	}

	/**
	 * Content template for the child theme functions.php file.
	 *
	 * @link https://gist.github.com/richtabor/7d88d279706fc3093911e958fd1fd791
	 *
	 * @param string $slug 	  Parent theme slug.
	 * @param string $parent  Parent theme name.
	 * @param string $author  Parent theme author.
	 * @param string $version Parent theme version.
	 */
	function generate_child_style_css( $slug, $parent, $author, $version ) {

		$output = "
			/**
			* Theme Name: {$parent} Child
			* Description: This is a child theme of {$parent}, generated by Merlin WP.
			* Author: {$author}
			* Template: {$slug}
			* Version: {$version}
			*/\n
		";

		// Let's remove the tabs so that it displays nicely.
		$output = trim( preg_replace( '/\t+/', '', $output ) );

		return apply_filters( 'merlin_generate_child_style_css', $output, $slug, $parent, $version );
	}

	/**
	 * Do plugins' AJAX
	 *
	 * @internal    Used as a calback.
	 */
	function _ajax_plugins() {

		if ( ! check_ajax_referer( 'merlin_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
			exit( 0 );
		}

		$json = array();
		$tgmpa_url = $this->tgmpa->get_tgmpa_url();
		$plugins = $this->get_tgmpa_plugins();

		foreach ( $plugins['activate'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = array(
					'url'           => $tgmpa_url,
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating', '@@textdomain' ),
				);
				break;
			}
		}

		foreach ( $plugins['update'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = array(
					'url'           => $tgmpa_url,
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating', '@@textdomain' ),
				);
				break;
			}
		}

		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = array(
					'url'           => $tgmpa_url,
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing', '@@textdomain' ),
				);
				break;
			}
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) );
			wp_send_json( $json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success', '@@textdomain' ) ) );
		}

		exit;
	}

	/**
	 * Do content's AJAX
	 *
	 * @internal    Used as a callback.
	 */
	function _ajax_content() {
		static $content = null;

		if ( null === $content ) {
			$content = $this->get_base_content();
		}

		if ( ! check_ajax_referer( 'merlin_nonce', 'wpnonce' ) || empty( $_POST['content'] ) && isset( $content[ $_POST['content'] ] ) ) {
			wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'Invalid content!', '@@textdomain' ) ) );
		}

		$json = false;
		$this_content = $content[ $_POST['content'] ];

		if ( isset( $_POST['proceed'] ) ) {
			if ( is_callable( $this_content['install_callback'] ) ) {
				$logs = call_user_func( $this_content['install_callback'], $this_content['data'] );
				if ( $logs ) {
					$json = array(
						'done'    => 1,
						'message' => $this_content['success'],
						'debug'   => '',
						'logs'    => $logs,
						'errors'  => '',
					);
				}
			}
		} else {
			$json = array(
				'url'      => admin_url( 'admin-ajax.php' ),
				'action'   => 'merlin_content',
				'proceed'  => 'true',
				'content'  => $_POST['content'],
				'_wpnonce' => wp_create_nonce( 'merlin_nonce' ),
				'message'  => $this_content['installing'],
				'logs'     => '',
				'errors'   => '',
			);
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) );
			wp_send_json( $json );
		} else {
			wp_send_json( array(
				'error'   => 1,
				'message' => esc_html__( 'Error', '@@textdomain' ),
				'logs'    => '',
				'errors'  => '',
			) );
		}
	}

	/**
	 * Get base sample data
	 *
	 * @return    array
	 */
	protected function get_base_content() {

		$content = array();

		$base_dir = get_parent_theme_file_path( $this->demo_directory );

		if ( file_exists( $base_dir . 'content.xml' ) ) {
			$xml_parser = new Merlin_WXR_Parser();
			$content = $xml_parser->parse( $base_dir . 'content.xml' );
		}

		if ( ! empty( $content ) && is_array( $content ) ) {
			foreach ( $content as $slug => $data ) {
				if ( 'baseurl' === $slug || 'version' === $slug ) {
					continue;
				}
				$content[ $slug ]['title'] = ucwords( $slug );
				$content[ $slug ]['description'] = sprintf( esc_html__( 'Sample %s data.', '@@textdomain' ), $slug );
				$content[ $slug ]['pending'] = esc_html__( 'Pending', '@@textdomain' );
				$content[ $slug ]['installing'] = esc_html__( 'Installing', '@@textdomain' );
				$content[ $slug ]['success'] = esc_html__( 'Success', '@@textdomain' );
				$content[ $slug ]['checked'] = $this->is_possible_upgrade() ? 0 : 1;
				$content[ $slug ]['install_callback'] = array( $this->importer, 'import' . ucfirst( $slug ) );
				$content[ $slug ]['data'] = $data;
			}
		}

		if ( file_exists( $base_dir . 'widgets.wie' ) ) {
			$content['widgets'] = array(
				'title'            	=> esc_html__( 'Widgets', '@@textdomain' ),
				'description'      	=> esc_html__( 'Sample widgets data.', '@@textdomain' ),
				'pending'          	=> esc_html__( 'Pending', '@@textdomain' ),
				'installing'       	=> esc_html__( 'Installing', '@@textdomain' ),
				'success'          	=> esc_html__( 'Success', '@@textdomain' ),
				'install_callback' 	=> array( $this->importer, 'importWidgets' ),
				'checked'          	=> $this->is_possible_upgrade() ? 0 : 1,
				'data'			=> $base_dir . 'widgets.wie',
			);
		}

		if ( file_exists( $base_dir . 'slider.zip' ) ) {
			$content['sliders'] = array(
				'title'            	=> esc_html__( 'Sliders', '@@textdomain' ),
				'description'     	=> esc_html__( 'Sample sliders data.', '@@textdomain' ),
				'pending'         	=> esc_html__( 'Pending', '@@textdomain' ),
				'installing'      	=> esc_html__( 'Installing', '@@textdomain' ),
				'success'          	=> esc_html__( 'Success', '@@textdomain' ),
				'install_callback' 	=> array( $this->importer, 'importRevSliders' ),
				'checked'          	=> $this->is_possible_upgrade() ? 0 : 1,
				'data' 			=> $base_dir . 'slider.zip',
			);
		}

		if ( file_exists( $base_dir . 'customizer.dat' ) ) {
			$content['options'] = array(
				'title'            	=> esc_html__( 'Options', '@@textdomain' ),
				'description'      	=> esc_html__( 'Sample theme options data.', '@@textdomain' ),
				'pending'          	=> esc_html__( 'Pending', '@@textdomain' ),
				'installing'       	=> esc_html__( 'Installing', '@@textdomain' ),
				'success'          	=> esc_html__( 'Success', '@@textdomain' ),
				'install_callback' 	=> array( $this->importer, 'importThemeOptions' ),
				'checked'          	=> $this->is_possible_upgrade() ? 0 : 1,
				'data' 			=> $base_dir . 'customizer.dat',
			);
		}

		$content = apply_filters( 'merlin_get_base_content', $content, $this );

		return $content;
	}
}
