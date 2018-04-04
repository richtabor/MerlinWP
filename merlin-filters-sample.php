<?php
/**
 * Available filters for extending Merlin WP.
 *
 * @package   Merlin WP
 * @version   @@pkg.version
 * @link      https://merlinwp.com/
 * @author    Rich Tabor, from ThemeBeans.com & the team at ProteusThemes.com
 * @copyright Copyright (c) 2018, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for Open Source Use
 */

/**
 * Filter the home page title from your demo content.
 * If your demo's home page title is "Home", you don't need this.
 *
 * @param string $output Home page title.
 */
function prefix_merlin_content_home_page_title( $output ) {
	return 'My front page';
}
add_filter( 'merlin_content_home_page_title', 'prefix_merlin_content_home_page_title' );

/**
 * Filter the blog page title from your demo content.
 * If your demo's blog page title is "Blog", you don't need this.
 *
 * @param string $output Index blogroll page title.
 */
function prefix_merlin_content_blog_page_title( $output ) {
	return 'Journal';
}
add_filter( 'merlin_content_blog_page_title', 'prefix_merlin_content_blog_page_title' );

/**
 * Add your widget area to unset the default widgets from.
 * If your theme's first widget area is "sidebar-1", you don't need this.
 *
 * @see https://stackoverflow.com/questions/11757461/how-to-populate-widgets-on-sidebar-on-theme-activation
 *
 * @param  array $widget_areas Arguments for the sidebars_widgets widget areas.
 * @return array of arguments to update the sidebars_widgets option.
 */
function prefix_merlin_unset_default_widgets_args( $widget_areas ) {

	$widget_areas = array(
		'sidebar-1' => array(),
	);

	return $widget_areas;
}
add_filter( 'merlin_unset_default_widgets_args', 'prefix_merlin_unset_default_widgets_args' );

/**
 * Custom content for the generated child theme's functions.php file.
 *
 * @param string $output Generated content.
 * @param string $slug Parent theme slug.
 */
function prefix_generate_child_functions_php( $output, $slug ) {

	$slug_no_hyphens = strtolower( preg_replace( '#[^a-zA-Z]#', '', $slug ) );

	$output = "
		<?php
		/**
		 * Theme functions and definitions.
		 */
		function {$slug_no_hyphens}_child_enqueue_styles() {

		    if ( SCRIPT_DEBUG ) {
		        wp_enqueue_style( '{$slug}-style' , get_template_directory_uri() . '/style.css' );
		    } else {
		        wp_enqueue_style( '{$slug}-minified-style' , get_template_directory_uri() . '/style.min.css' );
		    }

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
	return $output;
}
add_filter( 'merlin_generate_child_functions_php', 'prefix_generate_child_functions_php', 10, 2 );

/**
 * Define the demo import files (remote files).
 *
 * To define imports, you just have to add the following code structure,
 * with your own values to your theme (using the 'merlin_import_files' filter).
 */
function prefix_merlin_import_files() {
	return array(
		array(
			'import_file_name'           => 'Demo Import 1',
			'import_file_url'            => 'https://www.example.com/merlin/demo-content.xml',
			'import_widget_file_url'     => 'https://www.example.com/merlin/widgets.json',
			'import_customizer_file_url' => 'https://www.example.com/merlin/customizer.dat',
			'import_redux'               => array(
				array(
					'file_url'    => 'https://www.example.com/merlin/redux_options.json',
					'option_name' => 'redux_option_name',
				),
			),
			'import_preview_image_url'   => 'https://www.example.com/merlin/preview_import_image1.jpg',
			'import_notice'              => __( 'A special note for this import.', 'your-textdomain' ),
			'preview_url'                => 'https://www.example.com/my-demo-1',
		),
		array(
			'import_file_name'           => 'Demo Import 2',
			'import_file_url'            => 'https://www.example.com/merlin/demo-content2.xml',
			'import_widget_file_url'     => 'https://www.example.com/merlin/widgets2.json',
			'import_customizer_file_url' => 'https://www.example.com/merlin/customizer2.dat',
			'import_redux'               => array(
				array(
					'file_url'    => 'https://www.example.com/merlin/redux_options2.json',
					'option_name' => 'redux_option_name2',
				),
			),
			'import_preview_image_url'   => 'https://www.example.com/merlin/preview_import_image2.jpg',
			'import_notice'              => __( 'A special note for this import.', 'your-textdomain' ),
			'preview_url'                => 'https://www.example.com/my-demo-2',
		),
	);
}
add_filter( 'merlin_import_files', 'prefix_merlin_import_files' );

/**
 * Define the demo import files (local files).
 *
 * You have to use the same filter as in above example,
 * but with a slightly different array keys: local_*.
 * The values have to be absolute paths (not URLs) to your import files.
 * To use local import files, that reside in your theme folder,
 * please use the below code.
 * Note: make sure your import files are readable!
 */
function prefix_merlin_local_import_files() {
	return array(
		array(
			'import_file_name'             => 'Demo Import 1',
			'local_import_file'            => trailingslashit( get_template_directory() ) . 'merlin/demo-content.xml',
			'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'merlin/widgets.json',
			'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'merlin/customizer.dat',
			'local_import_redux'           => array(
				array(
					'file_path'   => trailingslashit( get_template_directory() ) . 'merlin/redux_options.json',
					'option_name' => 'redux_option_name',
				),
			),
			'import_preview_image_url'     => 'https://www.example.com/merlin/preview_import_image1.jpg',
			'import_notice'                => __( 'After you import this demo, you will have to setup the slider separately.', 'your-textdomain' ),
			'preview_url'                  => 'https://www.example.com/my-demo-1',
		),
		array(
			'import_file_name'             => 'Demo Import 2',
			'local_import_file'            => trailingslashit( get_template_directory() ) . 'merlin/demo-content2.xml',
			'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'merlin/widgets2.json',
			'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'merlin/customizer2.dat',
			'local_import_redux'           => array(
				array(
					'file_path'   => trailingslashit( get_template_directory() ) . 'merlin/redux_options2.json',
					'option_name' => 'redux_option_name2',
				),
			),
			'import_preview_image_url'     => 'https://www.example.com/merlin/preview_import_image2.jpg',
			'import_notice'                => __( 'A special note for this import.', 'your-textdomain' ),
			'preview_url'                  => 'https://www.example.com/my-demo-2',
		),
	);
}
add_filter( 'merlin_import_files', 'prefix_merlin_local_import_files' );

/**
 * Execute custom code after the whole import has finished.
 */
function prefix_merlin_after_import_setup() {
	// Assign menus to their locations.
	$main_menu = get_term_by( 'name', 'Main Menu', 'nav_menu' );

	set_theme_mod(
		'nav_menu_locations', array(
			'main-menu' => $main_menu->term_id,
		)
	);

	// Assign front page and posts page (blog page).
	$front_page_id = get_page_by_title( 'Home' );
	$blog_page_id  = get_page_by_title( 'Blog' );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $front_page_id->ID );
	update_option( 'page_for_posts', $blog_page_id->ID );

}
add_action( 'merlin_after_all_import', 'prefix_merlin_after_import_setup' );
