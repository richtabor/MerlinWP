<?php
/**
 * Merlin
 * A magical on-boarding experience for WordPress themes.
 *
 * The following code is a derivative work of the code from the Envato WordPress Theme
 * Setup Wizard by David Baker, which is licensed GPLv2. This code therefore is also
 * licensed under the terms of the GNU Public License, verison 2.
 *
 * @package   Merlin
 * @version   1.0
 * @link      https://merlinwp.com/
 * @author    Richard Tabor, from ThemeBeans.com
 * @copyright Copyright (c) 2017, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for open source use, or Merlin WP Commercial License for commercial use
 */

if ( ! function_exists( 'WP_Filesystem' ) ) {
	require ABSPATH . 'wp-admin/includes/file.php';
}

if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
	require ABSPATH . 'wp-admin/includes/image.php';
}

if ( ! function_exists( 'wp_read_audio_metadata' ) ) {
	require ABSPATH . 'wp-admin/includes/media.php';
}

/**
 * Merlin_Importer
 */
class Merlin_Importer {
	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing.
	 */
	function import( $file ) {

		$this->importStart();

		$parser = new Merlin_WXR_Parser();
		$content = $parser->parse( $file );

		if ( is_wp_error( $content ) ) {
			return false;
		}

		if ( ! empty( $content['users'] ) ) {
			$this->import_users( $content['users'] );
		}

		if ( ! empty( $content['categories'] ) ) {
			$this->importTerms( $content['categories'] );
		}

		if ( ! empty( $content['tags'] ) ) {
			$this->importTerms( $content['tags'] );
		}

		if ( ! empty( $content['terms'] ) ) {
			$this->importTerms( $content['terms'] );
		}

		if ( ! empty( $content['posts'] ) ) {
			$this->importPosts( $content['posts'] );
		}

		$this->remapImportedData();

		$this->importEnd();
	}

	/**
	 * Import users
	 *
	 * @param array $users WordPress users.
	 */
	function import_users( array $users ) {

		$imported_users = get_transient( '_wxr_imported_users' ) ? : array();

		foreach ( $users as $user ) {
			$user_login = $user['login'];
			$original_id = isset( $user['id'] ) ? $user['id'] : 0;

			if ( isset( $imported_users[ $original_id ] )|| isset( $imported_users[ $user_login ] ) ) {
				continue;
			}

			$userdata = array(
				'user_login'   => $user_login,
				'user_pass'    => wp_generate_password(),
				'user_email'   => isset( $user['email'] ) ? $user['email'] : '',
				'display_name' => $user['display_name'],
				'first_name'   => isset( $user['first_name'] ) ? $user['first_name'] : '',
				'last_name'    => isset( $user['last_name'] ) ? $user['last_name'] : '',
				'role'         => 'subscriber',
				'rich_editing' => false,
				'description'  => esc_html__( 'This user is created while installing demo content. You should delete or modify this user&#8217;s information now.', '@@textdomain' ),
			);
			$user_id = wp_insert_user( $userdata );
			if ( is_wp_error( $user_id ) ) {
				continue;
			}

			$imported_users[ $original_id ] = $user_id;
			$imported_users[ $user_login ] = $user_id;
		}

		set_transient( '_wxr_imported_users', $imported_users, DAY_IN_SECONDS );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return 'true';
		}
	}

	/**
	 * Import terms
	 */
	function importTerms( array $terms ) {
		global $wpdb;

		$imported_terms = get_transient( '_wxr_imported_terms' ) ? : array();
		$orphaned_terms = get_transient( '_wxr_orphaned_terms' ) ? : array();

		foreach ( $terms as $term ) {
			$taxonomy 		= $term['taxonomy'];
			$original_id 	= isset( $term['id'] ) ? (int) $term['id'] : 0;
			$term_slug   	= isset( $term['slug'] ) ? $term['slug'] : '';
			$parent_slug 	= isset( $term['parent'] ) ? $term['parent'] : '';
			$mapping_key 	= crc32( $taxonomy . ' : ' . $term['slug'] );
			$existing 		= $this->termExists( $term );

			if ( $existing ) {
				$imported_terms[ $mapping_key ] = $existing;
				$imported_terms[ $original_id ] = $existing;
				$imported_terms[ $term_slug ] = $existing;
				continue;
			}
			if ( isset( $imported_terms[ $mapping_key ] ) ) {
				continue;
			}
			if ( ! taxonomy_exists( $taxonomy ) ) {
				if ( false !== strpos( $taxonomy, 'pa_' ) && class_exists( 'WooCommerce', false ) ) {
				$attribute_name = str_replace('pa_', '', $taxonomy);
				$attribute_args = array(
				  'attribute_label'   => ucwords($attribute_name),
				  'attribute_name'    => $attribute_name,
				  'attribute_type'    => 'select',
				  'attribute_orderby' => 'menu_order',
				  'attribute_public'  => 0
				);
				$inserted = $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute_args);
				delete_transient('wc_attribute_taxonomies');
				$registered = $this->register_custom_taxonomy($taxonomy, 'product', array(
				  'hierarchical' => true,
				  'show_ui'      => false,
				  'query_var'    => true,
				  'rewrite'      => false,
				));
			  }
			}
			$requires_remapping = false;
			if ( $parent_slug ) {
				if ( isset( $imported_terms[$parent_slug] ) ) {
					$term['parent'] = $imported_terms[$parent_slug];
				} else {
					$term['meta'][] = array('key' => '_wxr_import_parent', 'value' => $parent_slug);
					$requires_remapping = true;
					$term['parent'] = 0;
				}
			}
			$termdata = array(
				'slug' => $term_slug,
				'parent' => $parent_slug ? $term['parent'] : 0,
				'description' => isset($term['description']) ? $term['description'] : '',
			);
			$result = wp_insert_term($term['name'], $taxonomy, $termdata);
			if (is_wp_error($result)) {
				continue;
			}
			$imported_terms[$term_slug] = $result['term_id'];
			$imported_terms[$mapping_key] = $result['term_id'];
			$imported_terms[$original_id] = $result['term_id'];
			if ($requires_remapping) {
				$orphaned_terms[$result['term_id']] = $taxonomy;
			}
			$this->importTermMeta($result['term_id'], $term);
		}

		set_transient('_wxr_imported_terms', $imported_terms, DAY_IN_SECONDS);
		set_transient('_wxr_orphaned_terms', $orphaned_terms, DAY_IN_SECONDS);

		if (defined('DOING_AJAX') && DOING_AJAX) {
		  return 'true';
		}
	}

	/**
	 * Import categories
	 */
	function importCategories( array $cats ) {
		return $this->importTerms( $cats );
	}

	/**
	 * Import categories
	 */
	function importTags( array $tags ) {
		return $this->importTerms( $tags );
	}

	/**
	 * Import posts
	 */
	function importPosts( array $posts ) {
		$imported_users = get_transient('_wxr_imported_users') ? : array();
		$imported_terms = get_transient('_wxr_imported_terms') ? : array();
		$imported_posts = get_transient('_wxr_imported_posts') ? : array();
		$orphaned_posts = get_transient('_wxr_orphaned_posts') ? : array();

		add_filter('http_request_timeout', array($this, '_bumpHttpRequestTimeout'));

		foreach ($posts as $post) {
			$original_id = isset($post['post_id'])     ? $post['post_id']     : 0;
			$parent_id   = isset($post['post_parent']) ? $post['post_parent'] : 0;
			$author_id   = isset($post['post_author']) ? $post['post_author'] : 0;
			if (isset($imported_posts[$original_id])) {
				continue;
			}
			$post_type_object = get_post_type_object($post['post_type']);
			if (!$post_type_object) {
				continue;
			}
			$post_exists = $this->postExists($post);
			if ($post_exists) {
				if (!empty($post['comments'])) {
					$this->importPostComments($original_id, $post, $imported_users, $post_exists);
				}
				continue;
			}
			$requires_remapping = false;
			if ($parent_id) {
				if (isset($imported_posts[$parent_id])) {
					$post['post_parent'] = $imported_posts[$parent_id];
				} else {
					$post['meta'][] = array('key' => '_wxr_import_parent', 'value' => $parent_id);
					$requires_remapping = true;
					$post['post_parent'] = 0;
				}
			}
			if (isset($imported_users[$post['post_author']])) {
				$post['post_author'] = $imported_users[$post['post_author']];
			} else {
				$post['meta'][] = array('key' => '_wxr_import_user_slug', 'value' => $post['post_author'] );
				$requires_remapping = true;
				$post['post_author'] = get_current_user_id();
			}
			$postdata = array(
				'import_id'      => $post['post_id'],
				'post_author'    => $post['post_author'],
				'post_date'      => $post['post_date'],
				'post_date_gmt'  => $post['post_date_gmt'],
				'post_content'   => $post['post_content'],
				'post_excerpt'   => $post['post_excerpt'],
				'post_title'     => $post['post_title'],
				'post_status'    => $post['post_status'],
				'post_name'      => $post['post_name'],
				'comment_status' => $post['comment_status'],
				'ping_status'    => $post['ping_status'],
				'guid'           => $post['guid'],
				'post_parent'    => $post['post_parent'],
				'menu_order'     => $post['menu_order'],
				'post_type'      => $post['post_type'],
				'post_password'  => $post['post_password'],
			);
			if ('attachment' === $postdata['post_type']) {
				$remote_url = !empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];
				$post_id = $this->importPostAttachment($postdata, $post['meta'], $remote_url);
			} else {
				$post_id = wp_insert_post($postdata, true);
			}
			if ( is_wp_error( $post_id ) ) {
				continue;
			}
			if ( '1' === $post['is_sticky'] ) {
				stick_post( $post_id );
			}
			$imported_posts[$original_id] = (int)$post_id;
			if ( $requires_remapping ) {
				$orphaned_posts[$post_id] = true;
			}

			if (!empty($post['terms'])) {
				$p_t_ids = array();
				foreach ($post['terms'] as $p_term) {
					$p_t_tax = $p_term['taxonomy'];
					$p_t_key = crc32($p_t_tax . ':' . $p_term['slug']);
					if (isset($imported_terms[$p_t_key])) {
						$p_t_ids[$p_t_tax][] = $imported_terms[$p_t_key];
					} else {
						if ('post_format' === $p_t_tax) {
							$p_t_exists = term_exists($p_term['slug'], $p_t_tax);
							$p_t_id = is_array($p_t_exists) ? $p_t_exists['term_id'] : $p_t_exists;
							if (empty($p_t_id)) {
								$p_t = wp_insert_term($p_term['name'], $p_t_tax, array('slug' => $p_term['slug']));
								if (!is_wp_error($p_t)) {
									$p_t_id = $p_t['term_id'];
									$imported_terms[$p_t_key] = $p_t_id;
								} else {
									continue;
								}
							}
							if (! empty($p_t_id)) {
								$p_t_ids[$p_t_tax][] = intval($p_t_id);
							}
						} else {
							$post['meta'][] = array('key' => '_wxr_import_term', 'value' => $p_term);
							$requires_remapping = true;
						}
					}
				}
				foreach ($p_t_ids as $tax => $ids) {
					$tt_ids = wp_set_post_terms($post_id, $ids, $tax);
				}
			}
			if (!empty($post['comments'])) {
				$this->importPostComments($post_id, $post, $imported_users, $post_exists);
			}
			if (!empty($post['meta'])) {
				$this->importPostMeta($post_id, $post, $imported_users, $imported_terms);
				if ('nav_menu_item' === $post['post_type']) {
					$_requires_remapping = $this->importMenuItemMeta($post_id, $post, $imported_terms, $imported_posts);
					if ($_requires_remapping) {
						$orphaned_posts[$post_id] = true;
					}
				}
			}
		}



		//Assign default pages.
		$shoppage = get_page_by_title( 'Shop' );
		if ( $shoppage ) {
			update_option( 'woocommerce_shop_page_id', $shoppage->ID );
		}
		$shoppage = get_page_by_title( 'Cart' );
		if ( $shoppage ) {
			update_option( 'woocommerce_cart_page_id', $shoppage->ID );
		}
		$shoppage = get_page_by_title( 'Checkout' );
		if ( $shoppage ) {
			update_option( 'woocommerce_checkout_page_id', $shoppage->ID );
		}
		$shoppage = get_page_by_title( 'My Account' );
		if ( $shoppage ) {
			update_option( 'woocommerce_myaccount_page_id', $shoppage->ID );
		}
		$homepage = get_page_by_title( apply_filters( 'merlin_content_home_page_title', 'Home' ) );
		if ( $homepage ) {
			update_option( 'page_on_front', $homepage->ID );
			update_option( 'show_on_front', 'page' );
		}
		$blogpage = get_page_by_title( apply_filters( 'merlin_content_blog_page_title', 'Blog' ) );
		if ( $blogpage ) {
			update_option( 'page_for_posts', $blogpage->ID );
			update_option( 'show_on_front', 'page' );
		}

		// Update the Hello World! post by making it a draft.
		$hello_world = get_page_by_title( 'Hello World!', OBJECT, 'post' );

		if ( $blogpage ) {
			$my_post = array(
				'ID'           => 1,
				'post_status'   => 'draft',
			);

			// Update the post into the database.
			wp_update_post( $my_post );
		}

		set_transient('_wxr_imported_posts', $imported_posts, DAY_IN_SECONDS);
		set_transient('_wxr_orphaned_posts', $orphaned_posts, DAY_IN_SECONDS);

		if (defined('DOING_AJAX') && DOING_AJAX) {
		  return 'true';
		}
	}

	/**
	 * Import widgets
	 */
	function importWidgets( $file ) {
		global $wp_filesystem, $wp_registered_widget_controls, $wp_registered_sidebars;

		//add_filter( 'sidebars_widgets', array( $this, '_unset_sidebar_widget' ) );
		
		$valid_sidebar = false;
		$widget_instances = array();
		$imported_terms = get_transient('_wxr_imported_terms') ? : array();

		WP_Filesystem();

		if ( file_exists( $file ) ) {
			$file_contents = $wp_filesystem->get_contents($file);
			$data = json_decode($file_contents, true);
			if (null === $data) {
				$data = maybe_unserialize($file_contents);
			}
		} else {
			$data = array();
		}

		foreach ( $wp_registered_widget_controls as $widget_id => $widget ) {
			$base_id = isset($widget['id_base']) ? $widget['id_base'] : null;
			if (!empty($base_id) && !isset($widget_instances[$base_id])) {
				$widget_instances[$base_id] = get_option('widget_' . $base_id);
			}
		}

		foreach ( $data as $sidebar_id => $widgets ) {
			if ('wp_inactive_widgets' === $sidebar_id) {
				continue;
			}
			if (isset($wp_registered_sidebars[$sidebar_id])) {
				$valid_sidebar = true;
				$_sidebar_id = $sidebar_id;
			} else {
				$_sidebar_id = 'wp_inactive_widgets';
			}
			foreach ($widgets as $widget_instance_id => $widget) {
				if (false !== strpos($widget_instance_id, 'nav_menu') && !empty($widget['nav_menu'])) {
					$widget['nav_menu'] = isset($imported_terms[$widget['nav_menu']]) ? $imported_terms[$widget['nav_menu']] : 0;
				}
				$base_id = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
				if (isset($widget_instances[$base_id])) {
					$single_widget_instances = get_option('widget_' . $base_id);
					$single_widget_instances = !empty($single_widget_instances) ? $single_widget_instances : array('_multiwidget' => 1);
					$single_widget_instances[] = $widget;
					end($single_widget_instances);
					$new_instance_id_number = key($single_widget_instances);
					if ('0' === strval($new_instance_id_number)) {
						$new_instance_id_number = 1;
						$single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
						unset($single_widget_instances[0]);
					}
					if (isset($single_widget_instances['_multiwidget'])) {
						$multiwidget = $single_widget_instances['_multiwidget'];
						unset($single_widget_instances['_multiwidget']);
						$single_widget_instances['_multiwidget'] = $multiwidget;
					}
					$updated = update_option('widget_' . $base_id, $single_widget_instances);
					$sidebars_widgets = get_option('sidebars_widgets');
					$sidebars_widgets[$_sidebar_id][] = $base_id . '-' . $new_instance_id_number;
					update_option('sidebars_widgets', $sidebars_widgets);
				}
			}
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return 'true';
		}
	}

	/**
	 * Re-map imported data.
	 */
	// function _unset_sidebar_widget( $sidebars_widgets ) {
	// 	unset( $sidebars_widgets[ 'sidebar-1' ] );
	// 	return $sidebars_widgets;
	// }

	/**
	 * Import revo sliders
	 */
	function importRevSliders( $file ) {
		if (!class_exists('RevSlider', false)) {
			return 'failed';
		}

		$importer = new RevSlider();
		$response = $importer->importSliderFromPost(true, true, $file);

		if (defined('DOING_AJAX') && DOING_AJAX) {
			return 'true';
		}
	}

	/**
	 * Import theme options
	 */
	function importThemeOptions( $file ) {
		global $wp_filesystem, $wp_customize;

		WP_Filesystem();

		if ( file_exists( $file ) ) {
			$file_contents = $wp_filesystem->get_contents($file);
			$customize_data = json_decode($file_contents, true);
			if (null === $customize_data) {
				$customize_data = maybe_unserialize($file_contents);
			}
		} else {
			$customize_data = array();
		}

		if (!empty($customize_data)) {
			if (!empty($customize_data['mods'])) {
				foreach ($customize_data['mods'] as $mod_key => $mod_value) {
					if (is_string($mod_value) && preg_match('/\.(jpg|jpeg|png|gif)/i', $mod_value)) {
						$attachment = $this->fetchCustomizeImage($mod_value);
						if ( !is_wp_error($attachment) ) {
							$mod_value = $attachment->url;
							$index_key = $mod_key . '_data';
							if ( isset($customize_data['mods'][$index_key]) ) {
								$customize_data['mods'][$index_key] = $attachment;
								update_post_meta($attachment->attachment_id, '_wp_attachment_is_custom_header', get_option('stylesheet'));
							}
						}
					}
					if ('nav_menu_locations' === $mod_key) {
						$imported_terms = get_transient('_wxr_imported_terms') ? : array();
						foreach ($mod_value as $menu_location => $menu_term_id) {
							$mod_value[$menu_location] = isset($imported_terms[$menu_term_id]) ? $imported_terms[$menu_term_id] : $menu_term_id;
						}
					}
					set_theme_mod($mod_key, $mod_value);
				}
			}
		}
	}






	/**
	 * Re-map imported data.
	 */
	function remapImportedData() {
		$orphaned_terms = get_transient('_wxr_orphaned_terms');
		$orphaned_posts = get_transient('_wxr_orphaned_posts');
		$orphaned_comments = get_transient('_wxr_orphaned_comments');

		if (!empty($orphaned_posts)) {
			$this->remapImportedPosts($orphaned_posts);
		}
		if (!empty($orphaned_comments)) {
			$this->remapImportedComments($orphaned_comments);
		}
		if (!empty($orphaned_terms)) {
			$this->remapImportedTerms($orphaned_terms);
		}
	}

	/**
	 * Start importing
	 */
	function importStart()
	{
		wp_defer_term_counting(true);
		wp_defer_comment_counting(true);
		wp_suspend_cache_invalidation(true);
	}

	/**
	 * Import end
	 */
	function importEnd()
	{
		delete_transient('_wxr_imported_users');
		delete_transient('_wxr_imported_terms');
		delete_transient('_wxr_imported_posts');
		delete_transient('_wxr_orphaned_terms');
		delete_transient('_wxr_orphaned_posts');
		delete_transient('_wxr_imported_comments');
		delete_transient('_wxr_orphaned_comments');

		wp_suspend_cache_invalidation(false);

		wp_cache_flush();

		$taxonomies = get_taxonomies();

		foreach ($taxonomies as $tax) {
			delete_option("{$tax}_children");
			_get_term_hierarchy($tax);
		}

		wp_defer_term_counting(false);
		wp_defer_comment_counting(false);

		flush_rewrite_rules();
	}

	/**
	 * Attempt to create a new menu item from import data
	 *
	 * Fails for draft, orphaned menu items and those without an associated nav_menu
	 * or an invalid nav_menu term. If the post type or term object which the menu item
	 * represents doesn't exist then the menu item will not be imported (waits until the
	 * end of the import to retry again before discarding).
	 *
	 * @param array $item Menu item details from WXR file
	 */
	protected function importMenuItemMeta($post_id, $post, array $imported_terms, array $imported_posts)
	{
		$item_type = get_post_meta($post_id, '_menu_item_type', true);
		$original_object_id = get_post_meta($post_id, '_menu_item_object_id', true);
		$object_id = null;
		$requires_remapping = false;

		switch ($item_type) {
			case 'taxonomy':
				if (isset($imported_terms[$original_object_id])) {
					$object_id = $imported_terms[$original_object_id];
				} else {
					add_post_meta($post_id, '_wxr_import_menu_item', $original_object_id);
					$requires_remapping = true;
				}
			break;
			case 'post_type':
				if (isset($imported_posts[$original_object_id])) {
					$object_id = $imported_posts[$original_object_id];
				} else {
					add_post_meta($post_id, '_wxr_import_menu_item', $original_object_id);
					$requires_remapping = true;
				}
			break;
			case 'custom':
				$object_id = $post_id;
			break;
			default:
			break;
		}

		if (empty($object_id)) {
			return $requires_remapping;
		}

		update_post_meta($post_id, '_menu_item_object_id', $object_id);

		return $requires_remapping;
	}

	/**
	 * Import attachment
	 */
	protected function importPostAttachment($post, $meta, $remote_url)
	{
		$post['upload_date'] = $post['post_date'];

		foreach ($meta as $meta_item) {
			if ('_wp_attached_file' !== $meta_item['key']) {
				continue;
			}
			break;
		}

		$upload = $this->fetchRemoteFile($remote_url, $post);

		if (is_wp_error($upload)) {
			return $upload;
		}

		$info = wp_check_filetype($upload['file']);

		if (!$info) {
			return new WP_Error('attachment_processing_error', esc_html__('Invalid attachment file type!', '@@textdomain'));
		}

		$post['post_mime_type'] = $info['type'];

		$post_id = wp_insert_attachment($post, $upload['file']);

		if (is_wp_error($post_id)) {
			return $post_id;
		}

		$attachment_metadata = wp_generate_attachment_metadata($post_id, $upload['file']);

		wp_update_attachment_metadata($post_id, $attachment_metadata);

		return $post_id;
	}

	/**
	 * Import post metadata
	 */
	protected function importPostMeta($post_id, $post, array $imported_users, array $imported_terms)
	{
		foreach ($post['meta'] as $meta) {
			if (empty($meta['key']) || in_array($meta['key'], array('_wp_attached_file', '_wp_attachment_metadata', '_edit_lock'))) {
				continue;
			}
			if ('_edit_last' === $meta['key']) {
				$value = intval($meta['value']);
				if (!isset($imported_users[$value])) {
					continue;
				}
				$value = $imported_users[$value];
			} else {
				$value = maybe_unserialize($meta['value']);
			}
			if ('_clever_mega_menu_item_meta_content' === $meta['key']) {
				preg_match_all('~\[vc_wp_custommenu(\s*)[^\]]+\]~', $value, $matches);
				if ( !empty($matches[0]) ) {
					$custom_menu_shortcodes = array();
					foreach ($matches[0] as $match) {
						$custom_menu_id = filter_var($match, FILTER_SANITIZE_NUMBER_INT);
						if (isset($imported_terms[$custom_menu_id])) {
							$custom_menu_shortcodes[] = str_replace($custom_menu_id, $imported_terms[$custom_menu_id], $match);
						}
					}
					if ($custom_menu_shortcodes) {
						$value = str_replace($matches[0], $custom_menu_shortcodes, $value);
					}
				}
			}
			if ($meta['key']) {
				add_post_meta($post_id, $meta['key'], $value);
				if (('clever_menu_theme' === $post['post_type']) && class_exists('Clever_Mega_Menu_Theme_Meta', false) && (Clever_Mega_Menu_Theme_Meta::META_KEY === $meta['key'])) {
					$theme_post = get_post($post_id);
					$theme_meta = new Clever_Mega_Menu_Theme_Meta(array());
					$theme_meta->generate_css($value, $theme_post);
				}
			}
		}
	}

	/**
	 * Import post comments
	 */
	protected function importPostComments($post_id, $post, array $imported_users, $post_exists = false)
	{
		$imported_comments = get_transient('_wxr_imported_comments') ? : array();
		$orphaned_comments = get_transient('_wxr_orphaned_comments') ? : array();

		$comments = $post['comments'];

		usort($comments, array($this, '_sortCommentsById'));

		foreach ($comments as $key => $comment) {
			if (empty($comment)) {
				continue;
			}
			$original_id = isset($comment['comment_id'])      ? $comment['comment_id']      : 0;
			$parent_id   = isset($comment['comment_parent'])  ? $comment['comment_parent']  : 0;
			$author_id   = isset($comment['comment_user_id']) ? $comment['comment_user_id'] : 0;
			if ($post_exists) {
				$existing = $this->commentExists($comment);
				if ($existing) {
					$imported_comments[$original_id] = $existing;
					continue;
				}
			}
			$meta = isset($comment['commentmeta']) ? $comment['commentmeta'] : array();
			unset($comment['commentmeta']);
			$requires_remapping = false;
			if ($parent_id) {
				if (isset($imported_comments[$parent_id])) {
					$comment['comment_parent'] = $imported_comments[$parent_id];
				} else {
					$meta[] = array('key' => '_wxr_import_parent', 'value' => $parent_id);
					$requires_remapping = true;
					$comment['comment_parent'] = 0;
				}
			}
			if ($author_id) {
				if (isset($imported_users[$author_id])) {
					$comment['user_id'] = $imported_users[$author_id];
				} else {
					$meta[] = array('key' => '_wxr_import_user', 'value' => $author_id);
					$requires_remapping = true;
					$comment['user_id'] = 0;
				}
			}
			$comment['comment_post_ID'] = $post_id;
			$comment_id = wp_insert_comment($comment);
			$imported_comments[$original_id] = $comment_id;
			if ($requires_remapping) {
				$orphaned_comments[$comment_id] = true;
			}
			foreach ($meta as $meta_item) {
				$value = maybe_unserialize($meta_item['value']);
				add_comment_meta($comment_id, $meta_item['key'], $value);
			}
		}

		set_transient('_wxr_imported_comments', $imported_comments, DAY_IN_SECONDS);
		set_transient('_wxr_orphaned_comments', $orphaned_comments, DAY_IN_SECONDS);
	}

	/**
	 * Import term meta.
	 */
	protected function importTermMeta($term_id, array $term_meta)
	{
		if (empty($term_meta)) {
			return;
		}

		foreach ($term_meta as $meta) {
			if (empty($meta['key'])) {
				continue;
			}
			$value = maybe_unserialize($meta['value']);
			if ($meta['key']) {
				$result = add_term_meta($term_id, $meta['key'], $value);
			}
		}
	}

	/**
	 * Attempt to download a remote file attachment
	 *
	 * @param string $url URL of item to fetch
	 * @param array $post Attachment details
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	protected function fetchRemoteFile($url, $post)
	{
		$file_name = basename($url);

		$upload = wp_upload_bits($file_name, 0, '', $post['upload_date']);

		if ($upload['error']) {
			return new WP_Error('upload_dir_error', $upload['error']);
		}

		$response = wp_remote_get($url, array(
			'stream' => true,
			'filename' => $upload['file'],
		));

		if (is_wp_error($response)) {
			unlink($upload['file']);
			return $response;
		}

		$code = (int)wp_remote_retrieve_response_code($response);

		if (200 !== $code) {
			unlink($upload['file']);
			return new WP_Error('import_file_error', sprintf(esc_html__('Remote server returned %1$d %2$s for %3$s', '@@textdomain'), $code, get_status_header_desc($code), $url));
		}

		$filesize = filesize($upload['file']);
		$headers = wp_remote_retrieve_headers($response);

		if (isset($headers['content-length']) && $filesize !== (int) $headers['content-length']) {
			unlink($upload['file']);
			return new WP_Error('import_file_error', esc_html__('Remote file is incorrect size', '@@textdomain'));
		}

		if (0 === $filesize) {
			unlink($upload['file']);
			return new WP_Error('import_file_error', esc_html__('Zero size file downloaded.', '@@textdomain'));
		}

		$max_size = apply_filters('_wxr_import_attachment_size_limit', 8*MB_IN_BYTES);

		if (!empty($max_size) && $filesize > $max_size) {
			unlink($upload['file']);
			$message = sprintf(esc_html__('Remote file is too large, limit is %s.', '@@textdomain'), size_format($max_size));
			return new WP_Error('import_file_error', $message);
		}

		return $upload;
	}

	/**
	 * Re-map imported posts
	 */
	protected function remapImportedPosts($mapping_posts)
	{
		$imported_users = get_transient('_wxr_imported_users') ? : array();
		$imported_terms = get_transient('_wxr_imported_terms') ? : array();
		$imported_posts = get_transient('_wxr_imported_posts') ? : array();

		foreach ($mapping_posts as $post_id => $mapped) {
			$data = array();
			$_post = WP_Post::get_instance($post_id);
			$parent_id = get_post_meta($post_id, '_wxr_import_parent', true);
			if (! empty($parent_id)) {
				if (isset($imported_posts[$parent_id])) {
					$data['post_parent'] = $imported_posts[$parent_id];
				}
			}
			$author_slug = get_post_meta($post_id, '_wxr_import_user_slug', true);
			if (!empty($author_slug)) {
				if (isset($imported_users[$author_slug])) {
					$data['post_author'] = $imported_users[$author_slug];
				}
			}
			if ('nav_menu_item' === $_post->post_type) {
				$this->remapImportedMenuItem($post_id, $imported_terms, $imported_posts);
			}
			if (empty($data)) {
				continue;
			}
			$data['ID'] = $post_id;
			$result = wp_update_post($data, true);
			if (is_wp_error($result)) {
				continue;
			}
			delete_post_meta($post_id, '_wxr_import_parent');
			delete_post_meta($post_id, '_wxr_import_user_slug');
		}
	}

	protected function remapImportedMenuItem($post_id, array $imported_terms, array $imported_posts)
	{
		$menu_object_id = get_post_meta($post_id, '_wxr_import_menu_item', true);

		if (empty($menu_object_id)) {
			return;
		}

		$menu_item_type = get_post_meta($post_id, '_menu_item_type', true);

		switch ($menu_item_type) {
			case 'taxonomy':
				if (isset($imported_terms[$menu_object_id])) {
					$menu_object = $imported_terms[$menu_object_id];
				}
			break;
			case 'post_type':
				if (isset($imported_posts[$menu_object_id])) {
					$menu_object = $imported_posts[$menu_object_id];
				}
			break;
			default:
			return;
		}

		if (!empty($menu_object)) {
			update_post_meta($post_id, '_menu_item_object_id', $menu_object);
		}

		delete_post_meta($post_id, '_wxr_import_menu_item');
	}

	/**
	 * Re-map comments
	 */
	protected function remapImportedComments(array $comments_to_update)
	{
		$imported_users = get_transient('_wxr_imported_users') ? : array();
		$imported_comments = get_transient('_wxr_imported_comments') ? : array();

		foreach ($comments_to_update as $comment_id => $update) {
			$data = array();
			$parent_id = get_comment_meta($comment_id, '_wxr_import_parent', true);
			if (! empty($parent_id)) {
				if (isset($imported_comments[$parent_id])) {
					$data['comment_parent'] = $imported_comments[$parent_id];
				}
			}
			$author_id = get_comment_meta($comment_id, '_wxr_import_user', true);
			if (! empty($author_id)) {
				if (isset($imported_users[$author_id])) {
					$data['user_id'] = $imported_users[$author_id];
				}
			}
			if (empty($data)) {
				continue;
			}
			$data['comment_ID'] = $comment_ID;
			$result = wp_update_comment($data);
			if (empty($result)) {
				continue;
			}

			delete_comment_meta($comment_id, '_wxr_import_parent');
			delete_comment_meta($comment_id, '_wxr_import_user');
		}
	}

	/**
	 * There is no explicit 'top' or 'root' for a hierarchy of WordPress terms
	 * Terms without a parent, or parent=0 are either unconnected (orphans)
	 * or top-level siblings without an explicit root parent
	 * An unconnected term (orphan) should have a null parent_slug
	 * Top-level siblings without an explicit root parent, shall be identified
	 * with the parent_slug: top
	 * [we'll map parent_slug: top into parent 0]
	 */
	protected function remapImportedTerms($terms_to_be_remapped)
	{
		$imported_terms = get_transient('_wxr_imported_terms') ? : array();

		foreach ($terms_to_be_remapped as $termid => $term_taxonomy) {
			$imported_terms['top'] = 0;
			if(empty($termid) || !is_numeric($termid)) {
				continue;
			}
			$term_id = (int)$termid;
			if(empty($term_taxonomy)){
				continue;
			}
			$data = array();
			$parent_slug = get_term_meta($term_id, '_wxr_import_parent', true);
			if (empty($parent_slug)) {
				continue;
			}
			if (!isset($imported_terms[$parent_slug]) || !is_numeric($imported_terms[$parent_slug])) {
				continue;
			}
			$mapped_parent = $imported_terms[$parent_slug];
			$termattributes = get_term_by('id', $term_id, $term_taxonomy, ARRAY_A);
			if (empty($termattributes)) {
				continue;
			}
			if (isset($termattributes['parent']) &&  $termattributes['parent'] == $mapped_parent) {
				delete_term_meta($term_id, '_wxr_import_parent');
				continue;
			}
			$termattributes['parent'] = $mapped_parent;
			$result = wp_update_term($term_id, $termattributes['taxonomy'], $termattributes);
			if (is_wp_error($result)) {
				continue;
			}
			delete_term_meta($term_id, '_wxr_import_parent');
		}
	}

	/**
	 * Does a post already exist?
	 */
	protected function postExists(array $post)
	{
		static $exists = null;

		if (null === $exists) {
			global $wpdb;
			$exists = array();
			$db_posts = $wpdb->get_results("SELECT ID, guid FROM {$wpdb->posts}");
			foreach ($db_posts as &$db_post) {
				$exists[$db_post->guid] = $db_post->ID;
			}
		}

		return isset($exists[$post['guid']]) ? $exists[$post['guid']] : false;
	}

	/**
	 * Does a comment already exist?
	 */
	protected function commentExists($comment)
	{
		static $exists = null;

		if (null === $exists) {
			global $wpdb;
			$exists = array();
			$db_comments = $wpdb->get_results("SELECT comment_ID, comment_author, comment_date FROM {$wpdb->comments}");
			foreach ($db_comments as &$db_comment) {
				$db_hash = crc32($db_comment->comment_author . ':' . $db_comment->comment_date);
				$exists[$db_hash] = $db_comment->comment_ID;
			}
		}

		$hash = crc32($comment['comment_author'] . ':' . $comment['comment_date']);

		return isset($exists[$hash]) ? $exists[$hash] : false;
	}

	/**
	 * Does a term already exist?
	 */
	protected function termExists(array $term)
	{
		static $exists = null;

		if (null === $exists) {
			global $wpdb;
			$exists = array();
			$db_terms = $wpdb->get_results("SELECT t.term_id, tt.taxonomy, t.slug FROM {$wpdb->terms} AS t JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id");
			foreach ($db_terms as &$db_term) {
				$db_hash = crc32($db_term->taxonomy . ':' . $db_term->slug);
				$exists[$db_hash] = $db_term->term_id;
			}
		}

		$hash = crc32($term['taxonomy'] . ':' . $term['slug']);

		return isset($exists[$hash]) ? $exists[$hash] : false;
	}

	/**
	 * Sideload customizer image
	 *
	 * @author  ProteusThemes
	 */
	protected function fetchCustomizeImage($file)
	{
		$data = new \stdClass();

		if ( !function_exists('media_handle_sideload') ) {
			require ABSPATH . 'wp-admin/includes/media.php';
			require ABSPATH . 'wp-admin/includes/file.php';
			require ABSPATH . 'wp-admin/includes/image.php';
		}

		if ( !empty($file) ) {
			preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches);
			$file_array = array();
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = download_url($file);
			if ( is_wp_error($file_array['tmp_name']) ) {
				return $file_array['tmp_name'];
			}
			$id = media_handle_sideload($file_array, 0);
			if ( is_wp_error($id) ) {
				unlink($file_array['tmp_name']);
				return $id;
			}
			$meta                = wp_get_attachment_metadata($id);
			$data->attachment_id = $id;
			$data->url           = wp_get_attachment_url($id);
			$data->thumbnail_url = wp_get_attachment_thumb_url($id);
			$data->height        = $meta['height'];
			$data->width         = $meta['width'];
		}

		return $data;
	}

	/**
	 * register_custom_taxonomy
	 *
	 * To bypass theme check
	 *
	 * @see    https://developer.wordpress.org/reference/functions/register_taxonomy/
	 */
	protected function register_custom_taxonomy($taxonomy, $object_type, $args = array())
	{
		global $wp_taxonomies;

		$args = wp_parse_args($args);

		if ( !is_array($wp_taxonomies) ) {
			$wp_taxonomies = array();
		}

		if ( empty($taxonomy) || strlen( $taxonomy ) > 32 ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Taxonomy names must be between 1 and 32 characters in length.', '@@textdomain' ), '4.2.0' );
			return new WP_Error( 'taxonomy_length_invalid', esc_html__( 'Taxonomy names must be between 1 and 32 characters in length.', '@@textdomain' ) );
		}

		$taxonomy_object = new WP_Taxonomy( $taxonomy, $object_type, $args );
		$taxonomy_object->add_rewrite_rules();
		$wp_taxonomies[$taxonomy] = $taxonomy_object;
		$taxonomy_object->add_hooks();

		do_action( 'registered_taxonomy', $taxonomy, $object_type, (array)$taxonomy_object );
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 *
	 * @access protected
	 * @return int 60
	 */
	function _bumpHttpRequestTimeout($val)
	{
		return 120;
	}

	/**
	 * Callback for `usort` to sort comments by ID
	 *
	 * @param array $a Comment data for the first comment
	 * @param array $b Comment data for the second comment
	 * @return int
	 */
	function _sortCommentsById($a, $b)
	{
		if (empty($a['comment_id'])) {
			return 1;
		}

		if (empty($b['comment_id'])) {
			return -1;
		}

		return $a['comment_id'] - $b['comment_id'];
	}
}
