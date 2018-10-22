<?php
/**
 * Merlin WP
 * Better WordPress Theme Onboarding
 *
 * The following code is a derivative work from the
 * Envato WordPress Theme Setup Wizard by David Baker.
 *
 * @package   Merlin WP
 * @version   @@pkg.version
 * @link      https://merlinwp.com/
 * @author    Rich Tabor, from ThemeBeans.com & the team at ProteusThemes.com
 * @copyright Copyright (c) 2018, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for Open Source Use
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
	 * WP Hook class.
	 *
	 * @var Merlin_Hooks
	 */
	protected $hooks;

	/**
	 * Holds the verified import files.
	 *
	 * @var array
	 */
	public $import_files;

	/**
	 * The base import file name.
	 *
	 * @var string
	 */
	public $import_file_base_name;

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
	 * The base path where Merlin is located.
	 *
	 * @var array $strings
	 */
	protected $base_path = null;

	/**
	 * The base url where Merlin is located.
	 *
	 * @var array $strings
	 */
	protected $base_url = null;

	/**
	 * The location where Merlin is located within the theme or plugin.
	 *
	 * @var string $directory
	 */
	protected $directory = null;

	/**
	 * Top level admin page.
	 *
	 * @var string $merlin_url
	 */
	protected $merlin_url = null;

	/**
	 * The wp-admin parent page slug for the admin menu item.
	 *
	 * @var string $parent_slug
	 */
	protected $parent_slug = null;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string $capability
	 */
	protected $capability = null;

	/**
	 * The URL for the "Learn more about child themes" link.
	 *
	 * @var string $child_action_btn_url
	 */
	protected $child_action_btn_url = null;

	/**
	 * The flag, to mark, if the theme license step should be enabled.
	 *
	 * @var boolean $license_step_enabled
	 */
	protected $license_step_enabled = false;

	/**
	 * The URL for the "Where can I find the license key?" link.
	 *
	 * @var string $theme_license_help_url
	 */
	protected $theme_license_help_url = null;

	/**
	 * Remove the "Skip" button, if required.
	 *
	 * @var string $license_required
	 */
	protected $license_required = null;

	/**
	 * The item name of the EDD product (this theme).
	 *
	 * @var string $edd_item_name
	 */
	protected $edd_item_name = null;

	/**
	 * The theme slug of the EDD product (this theme).
	 *
	 * @var string $edd_theme_slug
	 */
	protected $edd_theme_slug = null;

	/**
	 * The remote_api_url of the EDD shop.
	 *
	 * @var string $edd_remote_api_url
	 */
	protected $edd_remote_api_url = null;

	/**
	 * Turn on dev mode if you're developing.
	 *
	 * @var string $dev_mode
	 */
	protected $dev_mode = false;

	/**
	 * Ignore.
	 *
	 * @var string $ignore
	 */
	public $ignore = null;

	/**
	 * The object with logging functionality.
	 *
	 * @var Logger $logger
	 */
	public $logger;

	/**
	 * Setup plugin version.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function version() {

		if ( ! defined( 'MERLIN_VERSION' ) ) {
			define( 'MERLIN_VERSION', '@@pkg.version' );
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

		$config = wp_parse_args(
			$config, array(
				'base_path'            => get_parent_theme_file_path(),
				'base_url'             => get_parent_theme_file_uri(),
				'directory'            => 'merlin',
				'merlin_url'           => 'merlin',
				'parent_slug'          => 'themes.php',
				'capability'           => 'manage_options',
				'child_action_btn_url' => '',
				'dev_mode'             => '',
				'ready_big_button_url' => home_url( '/' ),
			)
		);

		// Set config arguments.
		$this->base_path              = $config['base_path'];
		$this->base_url               = $config['base_url'];
		$this->directory              = $config['directory'];
		$this->merlin_url             = $config['merlin_url'];
		$this->parent_slug            = $config['parent_slug'];
		$this->capability             = $config['capability'];
		$this->child_action_btn_url   = $config['child_action_btn_url'];
		$this->license_step_enabled   = $config['license_step'];
		$this->theme_license_help_url = $config['license_help_url'];
		$this->license_required       = $config['license_required'];
		$this->edd_item_name          = $config['edd_item_name'];
		$this->edd_theme_slug         = $config['edd_theme_slug'];
		$this->edd_remote_api_url     = $config['edd_remote_api_url'];
		$this->dev_mode               = $config['dev_mode'];
		$this->ready_big_button_url   = $config['ready_big_button_url'];

		// Strings passed in from the config file.
		$this->strings = $strings;

		// Retrieve a WP_Theme object.
		$this->theme = wp_get_theme();
		$this->slug  = strtolower( preg_replace( '#[^a-zA-Z]#', '', $this->theme->template ) );

		// Set the ignore option.
		$this->ignore = $this->slug . '_ignore';

		// Is Dev Mode turned on?
		if ( true !== $this->dev_mode ) {

			// Has this theme been setup yet?
			$already_setup = get_option( 'merlin_' . $this->slug . '_completed' );

			// Return if Merlin has already completed it's setup.
			if ( $already_setup ) {
				return;
			}
		}

		// Get the logger object, so it can be used in the whole class.
		require_once trailingslashit( $this->base_path ) . $this->directory . '/includes/class-merlin-logger.php';
		$this->logger = Merlin_Logger::get_instance();

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
		add_action( 'admin_init', array( $this, 'ignore' ), 5 );
		add_action( 'admin_footer', array( $this, 'svg_sprite' ) );
		add_filter( 'tgmpa_load', array( $this, 'load_tgmpa' ), 10, 1 );
		add_action( 'wp_ajax_merlin_content', array( $this, '_ajax_content' ), 10, 0 );
		add_action( 'wp_ajax_merlin_get_total_content_import_items', array( $this, '_ajax_get_total_content_import_items' ), 10, 0 );
		add_action( 'wp_ajax_merlin_plugins', array( $this, '_ajax_plugins' ), 10, 0 );
		add_action( 'wp_ajax_merlin_child_theme', array( $this, 'generate_child' ), 10, 0 );
		add_action( 'wp_ajax_merlin_activate_license', array( $this, '_ajax_activate_license' ), 10, 0 );
		add_action( 'wp_ajax_merlin_update_selected_import_data_info', array( $this, 'update_selected_import_data_info' ), 10, 0 );
		add_action( 'wp_ajax_merlin_import_finished', array( $this, 'import_finished' ), 10, 0 );
		add_filter( 'pt-importer/new_ajax_request_response_data', array( $this, 'pt_importer_new_ajax_request_response_data' ) );
		add_action( 'import_end', array( $this, 'after_content_import_setup' ) );
		add_action( 'import_start', array( $this, 'before_content_import_setup' ) );
		add_action( 'admin_init', array( $this, 'register_import_files' ) );
	}

	/**
	 * Require necessary classes.
	 */
	function required_classes() {
		if ( ! class_exists( '\WP_Importer' ) ) {
			require ABSPATH . '/wp-admin/includes/class-wp-importer.php';
		}

		require_once trailingslashit( $this->base_path ) . $this->directory . '/includes/class-merlin-downloader.php';

		$this->importer = new ProteusThemes\WPContentImporter2\Importer( array( 'fetch_attachments' => true ), $this->logger );

		require_once trailingslashit( $this->base_path ) . $this->directory . '/includes/class-merlin-widget-importer.php';

		if ( ! class_exists( 'WP_Customize_Setting' ) ) {
			require_once ABSPATH . 'wp-includes/class-wp-customize-setting.php';
		}

		require_once trailingslashit( $this->base_path ) . $this->directory . '/includes/class-merlin-customizer-option.php';
		require_once trailingslashit( $this->base_path ) . $this->directory . '/includes/class-merlin-customizer-importer.php';
		require_once trailingslashit( $this->base_path ) . $this->directory . '/includes/class-merlin-redux-importer.php';
		require_once trailingslashit( $this->base_path ) . $this->directory . '/includes/class-merlin-hooks.php';

		$this->hooks = new Merlin_Hooks();

		if ( class_exists( 'EDD_Theme_Updater_Admin' ) ) {
			$this->updater = new EDD_Theme_Updater_Admin();
		}
	}

	/**
	 * Set redirection transient on theme switch.
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

		wp_safe_redirect( menu_page_url( $this->merlin_url ) );

		exit;
	}

	/**
	 * Give the user the ability to ignore Merlin WP.
	 */
	public function ignore() {

		// Bail out if not on correct page.
		if ( ! isset( $_GET['_wpnonce'] ) || ( ! wp_verify_nonce( $_GET['_wpnonce'], 'merlinwp-ignore-nounce' ) || ! is_admin() || ! isset( $_GET[ $this->ignore ] ) || ! current_user_can( 'manage_options' ) ) ) {
			return;
		}

		update_option( 'merlin_' . $this->slug . '_completed', 'ignored' );
	}

	/**
	 * Conditionally load TGMPA
	 *
	 * @param string $status User's manage capabilities.
	 */
	public function load_tgmpa( $status ) {
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
	 * Add the admin menu item, under Appearance.
	 */
	public function add_admin_menu() {

		// Strings passed in from the config file.
		$strings = $this->strings;

		$this->hook_suffix = add_submenu_page(
			esc_html( $this->parent_slug ), esc_html( $strings['admin-menu'] ), esc_html( $strings['admin-menu'] ), sanitize_key( $this->capability ), sanitize_key( $this->merlin_url ), array( $this, 'admin_page' )
		);
	}

	/**
	 * Add the admin page.
	 */
	public function admin_page() {

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
		$suffix = ( ( true === $this->dev_mode ) ) ? '' : '.min';

		// Enqueue styles.
		wp_enqueue_style( 'merlin', trailingslashit( $this->base_url ) . $this->directory . '/assets/css/merlin' . $suffix . '.css', array( 'wp-admin' ), MERLIN_VERSION );

		// Enqueue javascript.
		wp_enqueue_script( 'merlin', trailingslashit( $this->base_url ) . $this->directory . '/assets/js/merlin' . $suffix . '.js', array( 'jquery-core' ), MERLIN_VERSION );

		$texts = array(
			'something_went_wrong' => esc_html__( 'Something went wrong. Please refresh the page and try again!', '@@textdomain' ),
		);

		// Localize the javascript.
		if ( class_exists( 'TGM_Plugin_Activation' ) ) {
			// Check first if TMGPA is included.
			wp_localize_script(
				'merlin', 'merlin_params', array(
					'tgm_plugin_nonce' => array(
						'update'  => wp_create_nonce( 'tgmpa-update' ),
						'install' => wp_create_nonce( 'tgmpa-install' ),
					),
					'tgm_bulk_url'     => $this->tgmpa->get_tgmpa_url(),
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'wpnonce'          => wp_create_nonce( 'merlin_nonce' ),
					'texts'            => $texts,
				)
			);
		} else {
			// If TMGPA is not included.
			wp_localize_script(
				'merlin', 'merlin_params', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'wpnonce' => wp_create_nonce( 'merlin_nonce' ),
					'texts'   => $texts,
				)
			);
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
				}
				?>

			<?php $this->step_output(); ?>

			</div>

			<?php echo sprintf( '<a class="return-to-dashboard" href="%s">%s</a>', esc_url( admin_url( '/' ) ), esc_html( $strings['return-to-dashboard'] ) ); ?>

			<?php $ignore_url = wp_nonce_url( admin_url( '?' . $this->ignore . '=true' ), 'merlinwp-ignore-nounce' ); ?>

			<?php echo sprintf( '<a class="return-to-dashboard ignore" href="%s">%s</a>', esc_url( $ignore_url ), esc_html( $strings['ignore'] ) ); ?>

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
		$current_step = strtolower( $this->steps[ $this->step ]['name'] );
		?>

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
		?>
		</body>
		<?php do_action( 'admin_footer' ); ?>
		<?php do_action( 'admin_print_footer_scripts' ); ?>
		</html>
		<?php
	}

	/**
	 * SVG
	 */
	public function svg_sprite() {

		// Define SVG sprite file.
		$svg = trailingslashit( $this->base_path ) . $this->directory . '/assets/images/sprite.svg';

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
	public function svg( $args = array() ) {

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
	 * Allowed HTML for sprites.
	 */
	public function svg_allowed_html() {

		$array = array(
			'svg' => array(
				'class'       => array(),
				'aria-hidden' => array(),
				'role'        => array(),
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
	public function loading_spinner() {

		// Define the spinner file.
		$spinner = $this->directory . '/assets/images/spinner';

		// Retrieve the spinner.
		get_template_part( apply_filters( 'merlin_loading_spinner', $spinner ) );
	}

	/**
	 * Allowed HTML for the loading spinner.
	 */
	public function loading_spinner_allowed_html() {

		$array = array(
			'span' => array(
				'class' => array(),
			),
			'cite' => array(
				'class' => array(),
			),
		);

		return apply_filters( 'merlin_loading_spinner_allowed_html', $array );
	}

	/**
	 * Setup steps.
	 */
	public function steps() {

		$this->steps = array(
			'welcome' => array(
				'name'    => esc_html__( 'Welcome', '@@textdomain' ),
				'view'    => array( $this, 'welcome' ),
				'handler' => array( $this, 'welcome_handler' ),
			),
		);

		$this->steps['child'] = array(
			'name' => esc_html__( 'Child', '@@textdomain' ),
			'view' => array( $this, 'child' ),
		);

		if ( $this->license_step_enabled ) {
			$this->steps['license'] = array(
				'name' => esc_html__( 'License', '@@textdomain' ),
				'view' => array( $this, 'license' ),
			);
		}

		// Show the plugin importer, only if TGMPA is included.
		if ( class_exists( 'TGM_Plugin_Activation' ) ) {
			$this->steps['plugins'] = array(
				'name' => esc_html__( 'Plugins', '@@textdomain' ),
				'view' => array( $this, 'plugins' ),
			);
		}

		// Show the content importer, only if there's demo content added.
		if ( ! empty( $this->import_files ) ) {
			$this->steps['content'] = array(
				'name' => esc_html__( 'Content', '@@textdomain' ),
				'view' => array( $this, 'content' ),
			);
		}

		$this->steps['ready'] = array(
			'name' => esc_html__( 'Ready', '@@textdomain' ),
			'view' => array( $this, 'ready' ),
		);

		$this->steps = apply_filters( $this->theme->template . '_merlin_steps', $this->steps );
	}

	/**
	 * Output the steps
	 */
	protected function step_output() {
		$ouput_steps  = $this->steps;
		$array_keys   = array_keys( $this->steps );
		$current_step = array_search( $this->step, $array_keys, true );

		array_shift( $ouput_steps );
		?>

		<ol class="dots">

			<?php
			foreach ( $ouput_steps as $step_key => $step ) :

				$class_attr = '';
				$show_link  = false;

				if ( $step_key === $this->step ) {
					$class_attr = 'active';
				} elseif ( $current_step > array_search( $step_key, $array_keys, true ) ) {
					$class_attr = 'done';
					$show_link  = true;
				}
				?>

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
	 * @param string $step Name of the step, appended to the URL.
	 */
	protected function step_link( $step ) {
		return add_query_arg( 'step', $step );
	}

	/**
	 * Get the next step link.
	 */
	protected function step_next_link() {
		$keys = array_keys( $this->steps );
		$step = array_search( $this->step, $keys, true ) + 1;

		return add_query_arg( 'step', $keys[ $step ] );
	}

	/**
	 * Introduction step
	 */
	protected function welcome() {

		// Has this theme been setup yet? Compare this to the option set when you get to the last panel.
		$already_setup = get_option( 'merlin_' . $this->slug . '_completed' );

		// Theme Name.
		$theme = ucfirst( $this->theme );

		// Remove "Child" from the current theme name, if it's installed.
		$theme = str_replace( ' Child', '', $theme );

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = ! $already_setup ? $strings['welcome-header%s'] : $strings['welcome-header-success%s'];
		$paragraph = ! $already_setup ? $strings['welcome%s'] : $strings['welcome-success%s'];
		$start     = $strings['btn-start'];
		$no        = $strings['btn-no'];
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
		$this->logger->debug( __( 'The welcome step has been displayed', '@@textdomain' ) );
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
	 * Theme EDD license step.
	 */
	protected function license() {
		$is_theme_registered = $this->is_theme_registered();
		$action_url          = $this->theme_license_help_url;
		$required            = $this->license_required;

		$is_theme_registered_class = ( $is_theme_registered ) ? ' is-registered' : null;

		// Theme Name.
		$theme = ucfirst( $this->theme );

		// Remove "Child" from the current theme name, if it's installed.
		$theme = str_replace( ' Child', '', $theme );

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = ! $is_theme_registered ? $strings['license-header%s'] : $strings['license-header-success%s'];
		$action    = $strings['license-tooltip'];
		$label     = $strings['license-label'];
		$skip      = $strings['btn-license-skip'];
		$next      = $strings['btn-next'];
		$paragraph = ! $is_theme_registered ? $strings['license%s'] : $strings['license-success%s'];
		$install   = $strings['btn-license-activate'];
		?>

		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'license' ) ), $this->svg_allowed_html() ); ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php echo esc_html( sprintf( $header, $theme ) ); ?></h1>

			<p id="license-text"><?php echo esc_html( sprintf( $paragraph, $theme ) ); ?></p>

			<?php if ( ! $is_theme_registered ) : ?>
				<div class="merlin__content--license-key">
					<label for="license-key"><?php echo esc_html( $label ); ?></label>

					<div class="merlin__content--license-key-wrapper">
						<input type="text" id="license-key" class="js-license-key" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
						<?php if ( ! empty( $action_url ) ) : ?>
							<a href="<?php echo esc_url( $action_url ); ?>" alt="<?php echo esc_attr( $action ); ?>" target="_blank">
								<span class="hint--top" aria-label="<?php echo esc_attr( $action ); ?>">
									<?php echo wp_kses( $this->svg( array( 'icon' => 'help' ) ), $this->svg_allowed_html() ); ?>
								</span>
							</a>
						<?php endif ?>
					</div>

				</div>
			<?php endif; ?>

		</div>

		<footer class="merlin__content__footer <?php echo esc_attr( $is_theme_registered_class ); ?>">

			<?php if ( ! $is_theme_registered ) : ?>

				<?php if ( ! $required ) : ?>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>
				<?php endif ?>

				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next js-merlin-license-activate-button" data-callback="activate_license">
					<span class="merlin__button--loading__text"><?php echo esc_html( $install ); ?></span>
					<?php echo wp_kses( $this->loading_spinner(), $this->loading_spinner_allowed_html() ); ?>
				</a>

			<?php else : ?>
				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html( $next ); ?></a>
			<?php endif; ?>
			<?php wp_nonce_field( 'merlin' ); ?>
		</footer>
		<?php
		$this->logger->debug( __( 'The license activation step has been displayed', '@@textdomain' ) );
	}


	/**
	 * Check, if the theme is currently registered.
	 *
	 * @return boolean
	 */
	private function is_theme_registered() {
		$is_registered = get_option( $this->edd_theme_slug . '_license_key_status', false ) === 'valid';
		return apply_filters( 'merlin_is_theme_registered', $is_registered );
	}

	/**
	 * Child theme generator.
	 */
	protected function child() {

		// Variables.
		$is_child_theme     = is_child_theme();
		$child_theme_option = get_option( 'merlin_' . $this->slug . '_child' );
		$theme              = $child_theme_option ? wp_get_theme( $child_theme_option )->name : $this->theme . ' Child';
		$action_url         = $this->child_action_btn_url;

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = ! $is_child_theme ? $strings['child-header'] : $strings['child-header-success'];
		$action    = $strings['child-action-link'];
		$skip      = $strings['btn-skip'];
		$next      = $strings['btn-next'];
		$paragraph = ! $is_child_theme ? $strings['child'] : $strings['child-success%s'];
		$install   = $strings['btn-child-install'];
		?>

		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'child' ) ), $this->svg_allowed_html() ); ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php echo esc_html( $header ); ?></h1>

			<p id="child-theme-text"><?php echo esc_html( sprintf( $paragraph, $theme ) ); ?></p>

			<a class="merlin__button merlin__button--knockout merlin__button--no-chevron merlin__button--external" href="<?php echo esc_url( $action_url ); ?>" target="_blank"><?php echo esc_html( $action ); ?></a>

		</div>

		<footer class="merlin__content__footer">

			<?php if ( ! $is_child_theme ) : ?>

				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>

				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="install_child">
					<span class="merlin__button--loading__text"><?php echo esc_html( $install ); ?></span>
					<?php echo wp_kses( $this->loading_spinner(), $this->loading_spinner_allowed_html() ); ?>
				</a>

			<?php else : ?>
				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html( $next ); ?></a>
			<?php endif; ?>
			<?php wp_nonce_field( 'merlin' ); ?>
		</footer>
	<?php
		$this->logger->debug( __( 'The child theme installation step has been displayed', '@@textdomain' ) );
	}

	/**
	 * Theme plugins
	 */
	protected function plugins() {

		// Variables.
		$url    = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'merlin' );
		$method = '';
		$fields = array_keys( $_POST );
		$creds  = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields );

		tgmpa_load_bulk_installer();

		if ( false === $creds ) {
			return true;
		}

		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}

		// Are there plugins that need installing/activating?
		$plugins          = $this->get_tgmpa_plugins();
		$required_plugins = $recommended_plugins = array();
		$count            = count( $plugins['all'] );
		$class            = $count ? null : 'no-plugins';

		// Split the plugins into required and recommended.
		foreach ( $plugins['all'] as $slug => $plugin ) {
			if ( ! empty( $plugin['required'] ) ) {
				$required_plugins[ $slug ] = $plugin;
			} else {
				$recommended_plugins[ $slug ] = $plugin;
			}
		}

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = $count ? $strings['plugins-header'] : $strings['plugins-header-success'];
		$paragraph = $count ? $strings['plugins'] : $strings['plugins-success%s'];
		$action    = $strings['plugins-action-link'];
		$skip      = $strings['btn-skip'];
		$next      = $strings['btn-next'];
		$install   = $strings['btn-plugins-install'];
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
			<?php } ?>

		</div>

		<form action="" method="post">

			<?php if ( $count ) : ?>

				<ul class="merlin__drawer merlin__drawer--install-plugins">

				<?php if ( ! empty( $required_plugins ) ) : ?>
					<?php foreach ( $required_plugins as $slug => $plugin ) : ?>
						<li data-slug="<?php echo esc_attr( $slug ); ?>">
							<input type="checkbox" name="default_plugins[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_plugins_<?php echo esc_attr( $slug ); ?>" value="1" checked>

							<label for="default_plugins_<?php echo esc_attr( $slug ); ?>">
								<i></i>

								<span><?php echo esc_html( $plugin['name'] ); ?></span>

								<span class="badge">
									<span class="hint--top" aria-label="<?php esc_html_e( 'Required', '@@textdomain' ); ?>">
										<?php esc_html_e( 'req', '@@textdomain' ); ?>
									</span>
								</span>
							</label>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( ! empty( $recommended_plugins ) ) : ?>
					<?php foreach ( $recommended_plugins as $slug => $plugin ) : ?>
						<li data-slug="<?php echo esc_attr( $slug ); ?>">
							<input type="checkbox" name="default_plugins[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_plugins_<?php echo esc_attr( $slug ); ?>" value="1" checked>

							<label for="default_plugins_<?php echo esc_attr( $slug ); ?>">
								<i></i><span><?php echo esc_html( $plugin['name'] ); ?></span>
							</label>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>

				</ul>

			<?php endif; ?>

			<footer class="merlin__content__footer <?php echo esc_attr( $class ); ?>">
				<?php if ( $count ) : ?>
					<a id="close" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--closer merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>
					<a id="skip" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="install_plugins">
						<span class="merlin__button--loading__text"><?php echo esc_html( $install ); ?></span>
						<?php echo wp_kses( $this->loading_spinner(), $this->loading_spinner_allowed_html() ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next merlin__button--proceed merlin__button--colorchange"><?php echo esc_html( $next ); ?></a>
				<?php endif; ?>
				<?php wp_nonce_field( 'merlin' ); ?>
			</footer>
		</form>

	<?php
		$this->logger->debug( __( 'The plugin installation step has been displayed', '@@textdomain' ) );
	}

	/**
	 * Page setup
	 */
	protected function content() {
		$import_info = $this->get_import_data_info();

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = $strings['import-header'];
		$paragraph = $strings['import'];
		$action    = $strings['import-action-link'];
		$skip      = $strings['btn-skip'];
		$next      = $strings['btn-next'];
		$import    = $strings['btn-import'];

		$multi_import = ( 1 < count( $this->import_files ) ) ? 'is-multi-import' : null;
		?>

		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'content' ) ), $this->svg_allowed_html() ); ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php echo esc_html( $header ); ?></h1>

			<p><?php echo esc_html( $paragraph ); ?></p>

			<?php if ( 1 < count( $this->import_files ) ) : ?>

				<div class="merlin__select-control-wrapper">

					<select class="merlin__select-control js-merlin-demo-import-select">
						<?php foreach ( $this->import_files as $index => $import_file ) : ?>
							<option value="<?php echo esc_attr( $index ); ?>"><?php echo esc_html( $import_file['import_file_name'] ); ?></option>
						<?php endforeach; ?>
					</select>

					<div class="merlin__select-control-help">
						<span class="hint--top" aria-label="<?php echo esc_attr__( 'Select Demo', '@@textdomain' ); ?>">
							<?php echo wp_kses( $this->svg( array( 'icon' => 'downarrow' ) ), $this->svg_allowed_html() ); ?>
						</span>
					</div>
				</div>
			<?php endif; ?>

			<a id="merlin__drawer-trigger" class="merlin__button merlin__button--knockout"><span><?php echo esc_html( $action ); ?></span><span class="chevron"></span></a>

		</div>

		<form action="" method="post" class="<?php echo esc_attr( $multi_import ); ?>">

			<ul class="merlin__drawer merlin__drawer--import-content js-merlin-drawer-import-content">
				<?php echo $this->get_import_steps_html( $import_info ); ?>
			</ul>

			<footer class="merlin__content__footer">

				<a id="close" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--closer merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>

				<a id="skip" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html( $skip ); ?></a>

				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="install_content">
					<span class="merlin__button--loading__text"><?php echo esc_html( $import ); ?></span>

					<div class="merlin__progress-bar">
						<span class="js-merlin-progress-bar"></span>
					</div>

					<span class="js-merlin-progress-bar-percentage">0%</span>
				</a>

				<?php wp_nonce_field( 'merlin' ); ?>
			</footer>
		</form>

	<?php
		$this->logger->debug( __( 'The content import step has been displayed', '@@textdomain' ) );
	}


	/**
	 * Final step
	 */
	protected function ready() {

		// Author name.
		$author = $this->theme->author;

		// Theme Name.
		$theme = ucfirst( $this->theme );

		// Remove "Child" from the current theme name, if it's installed.
		$theme = str_replace( ' Child', '', $theme );

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = $strings['ready-header'];
		$paragraph = $strings['ready%s'];
		$action    = $strings['ready-action-link'];
		$skip      = $strings['btn-skip'];
		$next      = $strings['btn-next'];
		$big_btn   = $strings['ready-big-button'];

		// Links.
		$links = array();

		for ( $i = 1; $i < 4; $i++ ) {
			if ( ! empty( $strings[ "ready-link-$i" ] ) ) {
				$links[] = $strings[ "ready-link-$i" ];
			}
		}

		$links_class = empty( $links ) ? 'merlin__content__footer--nolinks' : null;

		$allowed_html_array = array(
			'a' => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
			),
		);

		update_option( 'merlin_' . $this->slug . '_completed', time() );
		?>

		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'done' ) ), $this->svg_allowed_html() ); ?>

			<h1><?php echo esc_html( sprintf( $header, $theme ) ); ?></h1>

			<p><?php wp_kses( printf( $paragraph, $author ), $allowed_html_array ); ?></p>

		</div>

		<footer class="merlin__content__footer merlin__content__footer--fullwidth <?php echo esc_attr( $links_class ); ?>">

			<a href="<?php echo esc_url( $this->ready_big_button_url ); ?>" class="merlin__button merlin__button--blue merlin__button--fullwidth merlin__button--popin"><?php echo esc_html( $big_btn ); ?></a>

			<?php if ( ! empty( $links ) ) : ?>
				<a id="merlin__drawer-trigger" class="merlin__button merlin__button--knockout"><span><?php echo esc_html( $action ); ?></span><span class="chevron"></span></a>

				<ul class="merlin__drawer merlin__drawer--extras">

					<?php foreach ( $links as $link ) : ?>
						<li><?php echo wp_kses( $link, $allowed_html_array ); ?></li>
					<?php endforeach; ?>

				</ul>
			<?php endif; ?>

		</footer>

	<?php
		$this->logger->debug( __( 'The final step has been displayed', '@@textdomain' ) );
	}

	/**
	 * Get registered TGMPA plugins
	 *
	 * @return    array
	 */
	protected function get_tgmpa_plugins() {
		$plugins = array(
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
	public function generate_child() {

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$success = $strings['child-json-success%s'];
		$already = $strings['child-json-already%s'];

		$name = $this->theme . ' Child';
		$slug = sanitize_title( $name );

		$path = get_theme_root() . '/' . $slug;

		if ( ! file_exists( $path ) ) {

			WP_Filesystem();

			global $wp_filesystem;

			$wp_filesystem->mkdir( $path );
			$wp_filesystem->put_contents( $path . '/style.css', $this->generate_child_style_css( $this->theme->template, $this->theme->name, $this->theme->author, $this->theme->version ) );
			$wp_filesystem->put_contents( $path . '/functions.php', $this->generate_child_functions_php( $this->theme->template ) );

			$this->generate_child_screenshot( $path );

			$allowed_themes          = get_option( 'allowedthemes' );
			$allowed_themes[ $slug ] = true;
			update_option( 'allowedthemes', $allowed_themes );

		} else {

			if ( $this->theme->template !== $slug ) :
				update_option( 'merlin_' . $this->slug . '_child', $name );
				switch_theme( $slug );
			endif;

			$this->logger->debug( __( 'The existing child theme was activated', '@@textdomain' ) );

			wp_send_json(
				array(
					'done'    => 1,
					'message' => sprintf(
						esc_html( $success ), $slug
					),
				)
			);
		}

		if ( $this->theme->template !== $slug ) :
			update_option( 'merlin_' . $this->slug . '_child', $name );
			switch_theme( $slug );
		endif;

		$this->logger->debug( __( 'The newly generated child theme was activated', '@@textdomain' ) );

		wp_send_json(
			array(
				'done'    => 1,
				'message' => sprintf(
					esc_html( $already ), $name
				),
			)
		);
	}

	/**
	 * Activate the theme (license key) via AJAX.
	 */
	public function _ajax_activate_license() {

		if ( ! check_ajax_referer( 'merlin_nonce', 'wpnonce' ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => esc_html__( 'Yikes! The theme activation failed. Please try again or contact support.', '@@textdomain' ),
				)
			);
		}

		if ( empty( $_POST['license_key'] ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => esc_html__( 'Please add your license key before attempting to activate one.', '@@textdomain' ),
				)
			);
		}

		$license_key = sanitize_key( $_POST['license_key'] );

		if ( ! has_filter( 'merlin_ajax_activate_license' ) ) {
			$result = $this->edd_activate_license( $license_key );
		} else {
			$result = apply_filters( 'merlin_ajax_activate_license', $license_key );
		}

		$this->logger->debug( __( 'The license activation was performed with the following results', '@@textdomain' ), $result );

		wp_send_json( array_merge( array( 'done' => 1 ), $result ) );
	}

	/**
	 * Activate the EDD license.
	 *
	 * This code was taken from the EDD licensing addon theme example code
	 * (`activate_license` method of the `EDD_Theme_Updater_Admin` class).
	 *
	 * @param string $license The license key.
	 *
	 * @return array
	 */
	protected function edd_activate_license( $license ) {
		$success = false;

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Theme Name.
		$theme = ucfirst( $this->theme );

		// Remove "Child" from the current theme name, if it's installed.
		$theme = str_replace( ' Child', '', $theme );

		// Text strings.
		$success_message = $strings['license-json-success%s'];

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => rawurlencode( $license ),
			'item_name'  => rawurlencode( $this->edd_item_name ),
			'url'        => esc_url( home_url( '/' ) ),
		);

		$response = $this->edd_get_api_response( $api_params );

		// Make sure the response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = esc_html__( 'An error occurred, please try again.', '@@textdomain' );
			}
		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch ( $license_data->error ) {

					case 'expired':
						$message = sprintf(
							/* translators: Expiration date */
							esc_html__( 'Your license key expired on %s.', '@@textdomain' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked':
						$message = esc_html__( 'Your license key has been disabled.', '@@textdomain' );
						break;

					case 'missing':
						$message = esc_html__( 'This appears to be an invalid license key. Please try again or contact support.', '@@textdomain' );
						break;

					case 'invalid':
					case 'site_inactive':
						$message = esc_html__( 'Your license is not active for this URL.', '@@textdomain' );
						break;

					case 'item_name_mismatch':
						/* translators: EDD Item Name */
						$message = sprintf( esc_html__( 'This appears to be an invalid license key for %s.', '@@textdomain' ), $this->edd_item_name );
						break;

					case 'no_activations_left':
						$message = esc_html__( 'Your license key has reached its activation limit.', '@@textdomain' );
						break;

					default:
						$message = esc_html__( 'An error occurred, please try again.', '@@textdomain' );
						break;
				}
			} else {
				if ( 'valid' === $license_data->license ) {
					$message = sprintf( esc_html( $success_message ), $theme );
					$success = true;

					// Removes the default EDD hook for this option, which breaks the AJAX call.
					remove_all_actions( 'update_option_' . $this->edd_theme_slug . '_license_key', 10 );

					update_option( $this->edd_theme_slug . '_license_key_status', $license_data->license );
					update_option( $this->edd_theme_slug . '_license_key', $license );
				}
			}
		}

		return compact( 'success', 'message' );
	}

	/**
	 * Makes a call to the API.
	 *
	 * This code was taken from the EDD licensing addon theme example code
	 * (`get_api_response` method of the `EDD_Theme_Updater_Admin` class).
	 *
	 * @param array $api_params to be used for wp_remote_get.
	 * @return array $response JSON response.
	 */
	private function edd_get_api_response( $api_params ) {

		$verify_ssl = (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true );

		$response = wp_remote_post(
			$this->edd_remote_api_url,
			array(
				'timeout'   => 15,
				'sslverify' => $verify_ssl,
				'body'      => $api_params,
			)
		);

		return $response;
	}

	/**
	 * Content template for the child theme functions.php file.
	 *
	 * @link https://gist.github.com/richtabor/688327dd103b1aa826ebae47e99a0fbe
	 *
	 * @param string $slug Parent theme slug.
	 */
	public function generate_child_functions_php( $slug ) {

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

		$this->logger->debug( __( 'The child theme functions.php content was generated', '@@textdomain' ) );

		// Filterable return.
		return apply_filters( 'merlin_generate_child_functions_php', $output, $slug );
	}

	/**
	 * Content template for the child theme functions.php file.
	 *
	 * @link https://gist.github.com/richtabor/7d88d279706fc3093911e958fd1fd791
	 *
	 * @param string $slug    Parent theme slug.
	 * @param string $parent  Parent theme name.
	 * @param string $author  Parent theme author.
	 * @param string $version Parent theme version.
	 */
	public function generate_child_style_css( $slug, $parent, $author, $version ) {

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

		$this->logger->debug( __( 'The child theme style.css content was generated', '@@textdomain' ) );

		return apply_filters( 'merlin_generate_child_style_css', $output, $slug, $parent, $version );
	}

	/**
	 * Generate child theme screenshot file.
	 *
	 * @param string $path    Child theme path.
	 */
	public function generate_child_screenshot( $path ) {

		$screenshot = apply_filters( 'merlin_generate_child_screenshot', '' );

		if ( ! empty( $screenshot ) ) {
			// Get custom screenshot file extension
			if ( '.png' === substr( $screenshot, -4 ) ) {
				$screenshot_ext = 'png';
			} else {
				$screenshot_ext = 'jpg';
			}
		} else {
			if ( file_exists( $this->base_path . '/screenshot.png' ) ) {
				$screenshot     = $this->base_path . '/screenshot.png';
				$screenshot_ext = 'png';
			} elseif ( file_exists( $this->base_path . '/screenshot.jpg' ) ) {
				$screenshot     = $this->base_path . '/screenshot.jpg';
				$screenshot_ext = 'jpg';
			}
		}

		if ( ! empty( $screenshot ) && file_exists( $screenshot ) ) {
			$copied = copy( $screenshot, $path . '/screenshot.' . $screenshot_ext );

			$this->logger->debug( __( 'The child theme screenshot was copied to the child theme, with the following result', '@@textdomain' ), array( 'copied' => $copied ) );
		} else {
			$this->logger->debug( __( 'The child theme screenshot was not generated, because of these results', '@@textdomain' ), array( 'screenshot' => $screenshot ) );
		}
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

		$json      = array();
		$tgmpa_url = $this->tgmpa->get_tgmpa_url();
		$plugins   = $this->get_tgmpa_plugins();

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
			$this->logger->debug(
				__( 'A plugin with the following data will be processed', '@@textdomain' ),
				array(
					'plugin_slug' => $_POST['slug'],
					'message'     => $json['message'],
				)
			);

			$json['hash']    = md5( serialize( $json ) );
			$json['message'] = esc_html__( 'Installing', '@@textdomain' );
			wp_send_json( $json );
		} else {
			$this->logger->debug(
				__( 'A plugin with the following data was processed', '@@textdomain' ),
				array(
					'plugin_slug' => $_POST['slug'],
				)
			);

			wp_send_json(
				array(
					'done'    => 1,
					'message' => esc_html__( 'Success', '@@textdomain' ),
				)
			);
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

		$selected_import = intval( $_POST['selected_index'] );

		if ( null === $content ) {
			$content = $this->get_import_data( $selected_import );
		}

		if ( ! check_ajax_referer( 'merlin_nonce', 'wpnonce' ) || empty( $_POST['content'] ) && isset( $content[ $_POST['content'] ] ) ) {
			$this->logger->error( __( 'The content importer AJAX call failed to start, because of incorrect data', '@@textdomain' ) );

			wp_send_json_error(
				array(
					'error'   => 1,
					'message' => esc_html__( 'Invalid content!', '@@textdomain' ),
				)
			);
		}

		$json         = false;
		$this_content = $content[ $_POST['content'] ];

		if ( isset( $_POST['proceed'] ) ) {
			if ( is_callable( $this_content['install_callback'] ) ) {
				$this->logger->info(
					__( 'The content import AJAX call will be executed with this import data', '@@textdomain' ),
					array(
						'title' => $this_content['title'],
						'data'  => $this_content['data'],
					)
				);

				$logs = call_user_func( $this_content['install_callback'], $this_content['data'] );
				if ( $logs ) {
					$json = array(
						'done'    => 1,
						'message' => $this_content['success'],
						'debug'   => '',
						'logs'    => $logs,
						'errors'  => '',
					);

					// The content import ended, so we should mark that all posts were imported.
					if ( 'content' === $_POST['content'] ) {
						$json['num_of_imported_posts'] = 'all';
					}
				}
			}
		} else {
			$json = array(
				'url'            => admin_url( 'admin-ajax.php' ),
				'action'         => 'merlin_content',
				'proceed'        => 'true',
				'content'        => $_POST['content'],
				'_wpnonce'       => wp_create_nonce( 'merlin_nonce' ),
				'selected_index' => $selected_import,
				'message'        => $this_content['installing'],
				'logs'           => '',
				'errors'         => '',
			);
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) );
			wp_send_json( $json );
		} else {
			$this->logger->error(
				__( 'The content import AJAX call failed with this passed data', '@@textdomain' ),
				array(
					'selected_content_index' => $selected_import,
					'importing_content'      => $_POST['content'],
					'importing_data'         => $this_content['data'],
				)
			);

			wp_send_json(
				array(
					'error'   => 1,
					'message' => esc_html__( 'Error', '@@textdomain' ),
					'logs'    => '',
					'errors'  => '',
				)
			);
		}
	}


	/**
	 * AJAX call to retrieve total items (posts, pages, CPT, attachments) for the content import.
	 */
	public function _ajax_get_total_content_import_items() {
		if ( ! check_ajax_referer( 'merlin_nonce', 'wpnonce' ) && empty( $_POST['selected_index'] ) ) {
			$this->logger->error( __( 'The content importer AJAX call for retrieving total content import items failed to start, because of incorrect data.', '@@textdomain' ) );

			wp_send_json_error(
				array(
					'error'   => 1,
					'message' => esc_html__( 'Invalid data!', '@@textdomain' ),
				)
			);
		}

		$selected_import = intval( $_POST['selected_index'] );
		$import_files    = $this->get_import_files_paths( $selected_import );

		wp_send_json_success( $this->importer->get_number_of_posts_to_import( $import_files['content'] ) );
	}


	/**
	 * Get import data from the selected import.
	 * Which data does the selected import have for the import.
	 *
	 * @param int $selected_import_index The index of the predefined demo import.
	 *
	 * @return bool|array
	 */
	public function get_import_data_info( $selected_import_index = 0 ) {
		$import_data = array(
			'content'      => false,
			'widgets'      => false,
			'options'      => false,
			'sliders'      => false,
			'redux'        => false,
			'after_import' => false,
		);

		if ( empty( $this->import_files[ $selected_import_index ] ) ) {
			return false;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_file_url'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_file'] )
		) {
			$import_data['content'] = true;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_widget_file_url'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_widget_file'] )
		) {
			$import_data['widgets'] = true;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_customizer_file_url'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_customizer_file'] )
		) {
			$import_data['options'] = true;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_rev_slider_file_url'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_rev_slider_file'] )
		) {
			$import_data['sliders'] = true;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_redux'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_redux'] )
		) {
			$import_data['redux'] = true;
		}

		if ( false !== has_action( 'merlin_after_all_import' ) ) {
			$import_data['after_import'] = true;
		}

		return $import_data;
	}


	/**
	 * Get the import files/data.
	 *
	 * @param int $selected_import_index The index of the predefined demo import.
	 *
	 * @return    array
	 */
	protected function get_import_data( $selected_import_index = 0 ) {
		$content = array();

		$import_files = $this->get_import_files_paths( $selected_import_index );

		if ( ! empty( $import_files['content'] ) ) {
			$content['content'] = array(
				'title'            => esc_html__( 'Content', '@@textdomain' ),
				'description'      => esc_html__( 'Demo content data.', '@@textdomain' ),
				'pending'          => esc_html__( 'Pending', '@@textdomain' ),
				'installing'       => esc_html__( 'Installing', '@@textdomain' ),
				'success'          => esc_html__( 'Success', '@@textdomain' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'install_callback' => array( $this->importer, 'import' ),
				'data'             => $import_files['content'],
			);
		}

		if ( ! empty( $import_files['widgets'] ) ) {
			$content['widgets'] = array(
				'title'            => esc_html__( 'Widgets', '@@textdomain' ),
				'description'      => esc_html__( 'Sample widgets data.', '@@textdomain' ),
				'pending'          => esc_html__( 'Pending', '@@textdomain' ),
				'installing'       => esc_html__( 'Installing', '@@textdomain' ),
				'success'          => esc_html__( 'Success', '@@textdomain' ),
				'install_callback' => array( 'Merlin_Widget_Importer', 'import' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $import_files['widgets'],
			);
		}

		if ( ! empty( $import_files['sliders'] ) ) {
			$content['sliders'] = array(
				'title'            => esc_html__( 'Revolution Slider', '@@textdomain' ),
				'description'      => esc_html__( 'Sample Revolution sliders data.', '@@textdomain' ),
				'pending'          => esc_html__( 'Pending', '@@textdomain' ),
				'installing'       => esc_html__( 'Installing', '@@textdomain' ),
				'success'          => esc_html__( 'Success', '@@textdomain' ),
				'install_callback' => array( $this, 'import_revolution_sliders' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $import_files['sliders'],
			);
		}

		if ( ! empty( $import_files['options'] ) ) {
			$content['options'] = array(
				'title'            => esc_html__( 'Options', '@@textdomain' ),
				'description'      => esc_html__( 'Sample theme options data.', '@@textdomain' ),
				'pending'          => esc_html__( 'Pending', '@@textdomain' ),
				'installing'       => esc_html__( 'Installing', '@@textdomain' ),
				'success'          => esc_html__( 'Success', '@@textdomain' ),
				'install_callback' => array( 'Merlin_Customizer_Importer', 'import' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $import_files['options'],
			);
		}

		if ( ! empty( $import_files['redux'] ) ) {
			$content['redux'] = array(
				'title'            => esc_html__( 'Redux Options', '@@textdomain' ),
				'description'      => esc_html__( 'Redux framework options.', '@@textdomain' ),
				'pending'          => esc_html__( 'Pending', '@@textdomain' ),
				'installing'       => esc_html__( 'Installing', '@@textdomain' ),
				'success'          => esc_html__( 'Success', '@@textdomain' ),
				'install_callback' => array( 'Merlin_Redux_Importer', 'import' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $import_files['redux'],
			);
		}

		if ( false !== has_action( 'merlin_after_all_import' ) ) {
			$content['after_import'] = array(
				'title'            => esc_html__( 'After import setup', '@@textdomain' ),
				'description'      => esc_html__( 'After import setup.', '@@textdomain' ),
				'pending'          => esc_html__( 'Pending', '@@textdomain' ),
				'installing'       => esc_html__( 'Installing', '@@textdomain' ),
				'success'          => esc_html__( 'Success', '@@textdomain' ),
				'install_callback' => array( $this->hooks, 'after_all_import_action' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $selected_import_index,
			);
		}

		$content = apply_filters( 'merlin_get_base_content', $content, $this );

		return $content;
	}

	/**
	 * Import revolution slider.
	 *
	 * @param string $file Path to the revolution slider zip file.
	 */
	public function import_revolution_sliders( $file ) {
		if ( ! class_exists( 'RevSlider', false ) ) {
			return 'failed';
		}

		$importer = new RevSlider();

		$response = $importer->importSliderFromPost( true, true, $file );

		$this->logger->info( __( 'The revolution slider import was executed', '@@textdomain' ) );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return 'true';
		}
	}

	/**
	 * Change the new AJAX request response data.
	 *
	 * @param array $data The default data.
	 *
	 * @return array The updated data.
	 */
	public function pt_importer_new_ajax_request_response_data( $data ) {
		$data['url']      = admin_url( 'admin-ajax.php' );
		$data['message']  = esc_html__( 'Installing', '@@textdomain' );
		$data['proceed']  = 'true';
		$data['action']   = 'merlin_content';
		$data['content']  = 'content';
		$data['_wpnonce'] = wp_create_nonce( 'merlin_nonce' );
		$data['hash']     = md5( rand() ); // Has to be unique (check JS code catching this AJAX response).

		return $data;
	}

	/**
	 * After content import setup code.
	 */
	public function after_content_import_setup() {
		// Set static homepage.
		$homepage = get_page_by_title( apply_filters( 'merlin_content_home_page_title', 'Home' ) );

		if ( $homepage ) {
			update_option( 'page_on_front', $homepage->ID );
			update_option( 'show_on_front', 'page' );

			$this->logger->debug( __( 'The home page was set', '@@textdomain' ), array( 'homepage_id' => $homepage ) );
		}

		// Set static blog page.
		$blogpage = get_page_by_title( apply_filters( 'merlin_content_blog_page_title', 'Blog' ) );

		if ( $blogpage ) {
			update_option( 'page_for_posts', $blogpage->ID );
			update_option( 'show_on_front', 'page' );

			$this->logger->debug( __( 'The blog page was set', '@@textdomain' ), array( 'blog_page_id' => $blogpage ) );
		}
	}

	/**
	 * Before content import setup code.
	 */
	public function before_content_import_setup() {
		// Update the Hello World! post by making it a draft.
		$hello_world = get_page_by_title( 'Hello World!', OBJECT, 'post' );

		if ( ! empty( $hello_world ) ) {
			$hello_world->post_status = 'draft';
			wp_update_post( $hello_world );

			$this->logger->debug( __( 'The Hello world post status was set to draft', '@@textdomain' ) );
		}
	}

	/**
	 * Register the import files via the `merlin_import_files` filter.
	 */
	public function register_import_files() {
		$this->import_files = $this->validate_import_file_info( apply_filters( 'merlin_import_files', array() ) );
	}

	/**
	 * Filter through the array of import files and get rid of those who do not comply.
	 *
	 * @param  array $import_files list of arrays with import file details.
	 * @return array list of filtered arrays.
	 */
	public function validate_import_file_info( $import_files ) {
		$filtered_import_file_info = array();

		foreach ( $import_files as $import_file ) {
			if ( ! empty( $import_file['import_file_name'] ) ) {
				$filtered_import_file_info[] = $import_file;
			} else {
				$this->logger->warning( __( 'This predefined demo import does not have the name parameter: import_file_name', '@@textdomain' ), $import_file );
			}
		}

		return $filtered_import_file_info;
	}

	/**
	 * Set the import file base name.
	 * Check if an existing base name is available (saved in a transient).
	 */
	public function set_import_file_base_name() {
		$existing_name = get_transient( 'merlin_import_file_base_name' );

		if ( ! empty( $existing_name ) ) {
			$this->import_file_base_name = $existing_name;
		} else {
			$this->import_file_base_name = date( 'Y-m-d__H-i-s' );
		}

		set_transient( 'merlin_import_file_base_name', $this->import_file_base_name, MINUTE_IN_SECONDS );
	}

	/**
	 * Get the import file paths.
	 * Grab the defined local paths, download the files or reuse existing files.
	 *
	 * @param int $selected_import_index The index of the selected import.
	 *
	 * @return array
	 */
	public function get_import_files_paths( $selected_import_index ) {
		$selected_import_data = empty( $this->import_files[ $selected_import_index ] ) ? false : $this->import_files[ $selected_import_index ];

		if ( empty( $selected_import_data ) ) {
			return array();
		}

		// Set the base name for the import files.
		$this->set_import_file_base_name();

		$base_file_name = $this->import_file_base_name;
		$import_files   = array(
			'content' => '',
			'widgets' => '',
			'options' => '',
			'redux'   => array(),
			'sliders' => '',
		);

		$downloader = new Merlin_Downloader();

		// Check if 'import_file_url' is not defined. That would mean a local file.
		if ( empty( $selected_import_data['import_file_url'] ) ) {
			if ( ! empty( $selected_import_data['local_import_file'] ) && file_exists( $selected_import_data['local_import_file'] ) ) {
				$import_files['content'] = $selected_import_data['local_import_file'];
			}
		} else {
			// Set the filename string for content import file.
			$content_filename = 'content-' . $base_file_name . '.xml';

			// Retrieve the content import file.
			$import_files['content'] = $downloader->fetch_existing_file( $content_filename );

			// Download the file, if it's missing.
			if ( empty( $import_files['content'] ) ) {
				$import_files['content'] = $downloader->download_file( $selected_import_data['import_file_url'], $content_filename );
			}

			// Reset the variable, if there was an error.
			if ( is_wp_error( $import_files['content'] ) ) {
				$import_files['content'] = '';
			}
		}

		// Get widgets file as well. If defined!
		if ( ! empty( $selected_import_data['import_widget_file_url'] ) ) {
			// Set the filename string for widgets import file.
			$widget_filename = 'widgets-' . $base_file_name . '.json';

			// Retrieve the content import file.
			$import_files['widgets'] = $downloader->fetch_existing_file( $widget_filename );

			// Download the file, if it's missing.
			if ( empty( $import_files['widgets'] ) ) {
				$import_files['widgets'] = $downloader->download_file( $selected_import_data['import_widget_file_url'], $widget_filename );
			}

			// Reset the variable, if there was an error.
			if ( is_wp_error( $import_files['widgets'] ) ) {
				$import_files['widgets'] = '';
			}
		} elseif ( ! empty( $selected_import_data['local_import_widget_file'] ) ) {
			if ( file_exists( $selected_import_data['local_import_widget_file'] ) ) {
				$import_files['widgets'] = $selected_import_data['local_import_widget_file'];
			}
		}

		// Get customizer import file as well. If defined!
		if ( ! empty( $selected_import_data['import_customizer_file_url'] ) ) {
			// Setup filename path to save the customizer content.
			$customizer_filename = 'options-' . $base_file_name . '.dat';

			// Retrieve the content import file.
			$import_files['options'] = $downloader->fetch_existing_file( $customizer_filename );

			// Download the file, if it's missing.
			if ( empty( $import_files['options'] ) ) {
				$import_files['options'] = $downloader->download_file( $selected_import_data['import_customizer_file_url'], $customizer_filename );
			}

			// Reset the variable, if there was an error.
			if ( is_wp_error( $import_files['options'] ) ) {
				$import_files['options'] = '';
			}
		} elseif ( ! empty( $selected_import_data['local_import_customizer_file'] ) ) {
			if ( file_exists( $selected_import_data['local_import_customizer_file'] ) ) {
				$import_files['options'] = $selected_import_data['local_import_customizer_file'];
			}
		}

		// Get revolution slider import file as well. If defined!
		if ( ! empty( $selected_import_data['import_rev_slider_file_url'] ) ) {
			// Setup filename path to save the customizer content.
			$rev_slider_filename = 'slider-' . $base_file_name . '.zip';

			// Retrieve the content import file.
			$import_files['sliders'] = $downloader->fetch_existing_file( $rev_slider_filename );

			// Download the file, if it's missing.
			if ( empty( $import_files['sliders'] ) ) {
				$import_files['sliders'] = $downloader->download_file( $selected_import_data['import_rev_slider_file_url'], $rev_slider_filename );
			}

			// Reset the variable, if there was an error.
			if ( is_wp_error( $import_files['sliders'] ) ) {
				$import_files['sliders'] = '';
			}
		} elseif ( ! empty( $selected_import_data['local_import_rev_slider_file'] ) ) {
			if ( file_exists( $selected_import_data['local_import_rev_slider_file'] ) ) {
				$import_files['sliders'] = $selected_import_data['local_import_rev_slider_file'];
			}
		}

		// Get redux import file as well. If defined!
		if ( ! empty( $selected_import_data['import_redux'] ) ) {
			$redux_items = array();

			// Setup filename paths to save the Redux content.
			foreach ( $selected_import_data['import_redux'] as $index => $redux_item ) {
				$redux_filename = 'redux-' . $index . '-' . $base_file_name . '.json';

				// Retrieve the content import file.
				$file_path = $downloader->fetch_existing_file( $redux_filename );

				// Download the file, if it's missing.
				if ( empty( $file_path ) ) {
					$file_path = $downloader->download_file( $redux_item['file_url'], $redux_filename );
				}

				// Reset the variable, if there was an error.
				if ( is_wp_error( $file_path ) ) {
					$file_path = '';
				}

				$redux_items[] = array(
					'option_name' => $redux_item['option_name'],
					'file_path'   => $file_path,
				);
			}

			// Download the Redux import file.
			$import_files['redux'] = $redux_items;
		} elseif ( ! empty( $selected_import_data['local_import_redux'] ) ) {
			$redux_items = array();

			// Setup filename paths to save the Redux content.
			foreach ( $selected_import_data['local_import_redux'] as $redux_item ) {
				if ( file_exists( $redux_item['file_path'] ) ) {
					$redux_items[] = $redux_item;
				}
			}

			// Download the Redux import file.
			$import_files['redux'] = $redux_items;
		}

		return $import_files;
	}

	/**
	 * AJAX callback for the 'merlin_update_selected_import_data_info' action.
	 */
	public function update_selected_import_data_info() {
		$selected_index = ! isset( $_POST['selected_index'] ) ? false : intval( $_POST['selected_index'] );

		if ( false === $selected_index ) {
			wp_send_json_error();
		}

		$import_info      = $this->get_import_data_info( $selected_index );
		$import_info_html = $this->get_import_steps_html( $import_info );

		wp_send_json_success( $import_info_html );
	}

	/**
	 * Get the import steps HTML output.
	 *
	 * @param array $import_info The import info to prepare the HTML for.
	 *
	 * @return string
	 */
	public function get_import_steps_html( $import_info ) {
		ob_start();
		?>
			<?php foreach ( $import_info as $slug => $available ) : ?>
				<?php
				if ( ! $available ) {
					continue;
				}
				?>

				<li class="merlin__drawer--import-content__list-item status status--Pending" data-content="<?php echo esc_attr( $slug ); ?>">
					<input type="checkbox" name="default_content[<?php echo esc_attr( $slug ); ?>]" class="checkbox checkbox-<?php echo esc_attr( $slug ); ?>" id="default_content_<?php echo esc_attr( $slug ); ?>" value="1" checked>
					<label for="default_content_<?php echo esc_attr( $slug ); ?>">
						<i></i><span><?php echo esc_html( ucfirst( str_replace( '_', ' ', $slug ) ) ); ?></span>
					</label>
				</li>

			<?php endforeach; ?>
		<?php

		return ob_get_clean();
	}


	/**
	 * AJAX call for cleanup after the importing steps are done -> import finished.
	 */
	public function import_finished() {
		delete_transient( 'merlin_import_file_base_name' );
		wp_send_json_success();
	}
}
