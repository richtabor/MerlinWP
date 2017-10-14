<?php
/**
 * Merlin WP configuration file.
 *
 * @package @@pkg.name
 * @version @@pkg.version
 * @author  @@pkg.author
 * @license @@pkg.license
 */

if ( ! class_exists( 'Merlin' ) ) {
	return;
}

/**
 * Set directory locations, text strings, and other settings for Merlin WP.
 */
$wizard = new Merlin(
	// Configure Merlin with custom settings.
	$config = array(
		'directory'			=> '',						// Location where the 'merlin' directory is placed.
		'demo_directory'		=> 'demo/',					// Location where the theme demo files exist.
		'merlin_url'			=> 'merlin',					// Customize the page URL where Merlin WP loads.
		'child_action_btn_url'		=> 'https://codex.wordpress.org/Child_Themes',  // The URL for the 'child-action-link'.
		'help_mode'			=> false,					// Set to true to turn on the little wizard helper.
		'dev_mode'			=> true,					// Set to true if you're testing or developing.
		'branding'			=> false,					// Set to false to remove Merlin WP's branding.
	),
	// Text strings.
	$strings = array(
		'admin-menu' 			=> esc_html__( 'Theme Setup' , '@@textdomain' ),
		'title%s%s%s%s' 		=> esc_html__( '%s%s Themes &lsaquo; Theme Setup: %s%s' , '@@textdomain' ),

		'return-to-dashboard' 		=> esc_html__( 'Return to the dashboard' , '@@textdomain' ),

		'btn-skip' 			=> esc_html__( 'Skip' , '@@textdomain' ),
		'btn-next' 			=> esc_html__( 'Next' , '@@textdomain' ),
		'btn-start' 			=> esc_html__( 'Start' , '@@textdomain' ),
		'btn-no' 			=> esc_html__( 'Cancel' , '@@textdomain' ),
		'btn-plugins-install' 		=> esc_html__( 'Install' , '@@textdomain' ),
		'btn-child-install' 		=> esc_html__( 'Install' , '@@textdomain' ),
		'btn-content-install' 		=> esc_html__( 'Install' , '@@textdomain' ),
		'btn-import' 			=> esc_html__( 'Import' , '@@textdomain' ),

		'welcome-header%s' 		=> esc_html__( 'Welcome to %s' , '@@textdomain' ),
		'welcome-header-success%s' 	=> esc_html__( 'Hi. Welcome back' , '@@textdomain' ),
		'welcome%s' 			=> esc_html__( 'This wizard will set up your theme, install plugins, and import content. It is optional & should take only a few minutes.' , '@@textdomain' ),
		'welcome-success%s' 		=> esc_html__( 'You may have already run this theme setup wizard. If you would like to proceed anyway, click on the "Start" button below.' , '@@textdomain' ),

		'child-header' 			=> esc_html__( 'Install Child Theme' , '@@textdomain' ),
		'child-header-success' 		=> esc_html__( 'You\'re good to go!' , '@@textdomain' ),
		'child' 			=> esc_html__( 'Let\'s build & activate a child theme so you may easily make theme changes.' , '@@textdomain' ),
		'child-success%s' 		=> esc_html__( 'Your child theme has already been installed and is now activated, if it wasn\'t already.' , '@@textdomain' ),
		'child-action-link' 		=> esc_html__( 'Learn about child themes' , '@@textdomain' ),
		'child-json-success%s' 		=> esc_html__( 'Awesome. Your child theme has already been installed and is now activated.' , '@@textdomain' ),
		'child-json-already%s' 		=> esc_html__( 'Awesome. Your child theme has been created and is now activated.' , '@@textdomain' ),

		'plugins-header' 		=> esc_html__( 'Install Plugins' , '@@textdomain' ),
		'plugins-header-success' 	=> esc_html__( 'You\'re up to speed!' , '@@textdomain' ),
		'plugins' 			=> esc_html__( 'Let\'s install some essential WordPress plugins to get your site up to speed.' , '@@textdomain' ),
		'plugins-success%s' 		=> esc_html__( 'The required WordPress plugins are all installed and up to date. Press "Next" to continue the setup wizard.' , '@@textdomain' ),
		'plugins-action-link' 		=> esc_html__( 'Advanced' , '@@textdomain' ),

		'import-header' 		=> esc_html__( 'Import Content' , '@@textdomain' ),
		'import' 			=> esc_html__( 'Let\'s import content to your website, to help you get familiar with the theme.' , '@@textdomain' ),
		'import-action-link' 		=> esc_html__( 'Advanced' , '@@textdomain' ),

		'ready-header' 			=> esc_html__( 'All done. Have fun!' , '@@textdomain' ),
		'ready%s' 			=> esc_html__( 'Your theme has been all set up. Enjoy your new theme by %s.' , '@@textdomain' ),
		'ready-action-link' 		=> esc_html__( 'Extras' , '@@textdomain' ),
		'ready-big-button' 		=> esc_html__( 'View your website' , '@@textdomain' ),

		'ready-link-1'            	=> wp_kses( sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://wordpress.org/support/', esc_html__( 'Explore WordPress', '@@textdomain' ) ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
		'ready-link-2'            	=> wp_kses( sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://themebeans.com/contact/', esc_html__( 'Get Theme Support', '@@textdomain' ) ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
		'ready-link-3'           	=> wp_kses( sprintf( '<a href="'.admin_url( 'customize.php' ).'" target="_blank">%s</a>', esc_html__( 'Start Customizing', '@@textdomain' ) ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
	)
);
