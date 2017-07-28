<?php
/**
 * Available filters for extending Merlin WP.
 *
 * @package   Merlin WP
 * @version   @@pkg.version
 * @link      https://merlinwp.com/
 * @author    Richard Tabor, from ThemeBeans.com
 * @copyright Copyright (c) 2017, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for open source use, or Merlin WP Commercial License for commercial use
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
