<?php
/**
 * Class for the custom WP hooks.
 *
 * @package Merlin WP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Merlin_Hooks {
	/**
	 * The class constructor.
	 */
	public function __construct() {
		add_action( 'merlin_widget_settings_array', array( $this, 'fix_custom_menu_widget_ids' ) );
		add_action( 'import_start', array( $this, 'maybe_disable_creating_different_size_images_during_import' ) );
	}

	/**
	 * Change the menu IDs in the custom menu widgets in the widget import data.
	 * This solves the issue with custom menu widgets not having the correct (new) menu ID, because they
	 * have the old menu ID from the export site.
	 *
	 * @param array $widget The widget settings array.
	 */
	public function fix_custom_menu_widget_ids( $widget ) {
		// Skip (no changes needed), if this is not a custom menu widget.
		if ( ! array_key_exists( 'nav_menu', $widget ) || empty( $widget['nav_menu'] ) || ! is_int( $widget['nav_menu'] ) ) {
			return $widget;
		}

		// Get import data, with new menu IDs.
		$importer = new ProteusThemes\WPContentImporter2\Importer( array( 'fetch_attachments' => true ), new ProteusThemes\WPContentImporter2\WPImporterLogger() );
		$importer->restore_import_data_transient();

		$importer_mapping = $importer->get_mapping();
		$term_ids         = empty( $importer_mapping['term_id'] ) ? array() : $importer_mapping['term_id'];

		// Set the new menu ID for the widget.
		$widget['nav_menu'] = empty( $term_ids[ $widget['nav_menu'] ] ) ? $widget['nav_menu'] : $term_ids[ $widget['nav_menu'] ];

		return $widget;
	}

	/**
	 * Wrapper function for the after all import action hook.
	 *
	 * @param int $selected_import_index The selected demo import index.
	 */
	public function after_all_import_action( $selected_import_index ) {
		do_action( 'merlin_after_all_import', $selected_import_index );

		return true;
	}

	/**
	 * Maybe disables generation of multiple image sizes (thumbnails) in the content import step.
	 */
	public function maybe_disable_creating_different_size_images_during_import() {
		if ( ! apply_filters( 'merlin_regenerate_thumbnails_in_content_import', true ) ) {
			add_filter( 'intermediate_image_sizes_advanced', '__return_null' );
		}
	}
}
