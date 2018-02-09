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
}
