<?php
/**
 * Merlin WP
 * Better WordPress Theme Onboarding
 *
 * @package   Merlin WP
 * @version   @@pkg.version
 * @link      https://merlinwp.com/
 * @author    Richard Tabor, from ThemeBeans.com
 * @copyright Copyright (c) 2017, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for open source use
 */

/**
 * XML Parser
 */
class Merlin_WXR_Parser {

	/**
	 * Parsing file
	 *
	 * @var string $file The XML file.
	 */
	protected $file;

	/**
	 * Constructor
	 *
	 * @var string $file Full path to XML file.
	 */
	function __construct() { }

	/**
	 * Parse
	 *
	 * @param string $file Maybe parse a custom file instead of the constructed file.
	 */
	function parse( $file ) {
		$file = wp_normalize_path( $file );

		if ( empty( $file ) ) {
			return new WP_Error( 'WXR_parse_error', esc_html__( 'Invalid WXR file.', '@@textdomain' ) );
		}

		if ( extension_loaded( 'simplexml' ) ) {
			return $this->simplexml_parse( $file );
		} elseif ( extension_loaded( 'xmlreader' ) ) {
			return $this->xml_reader_parse( $file );
		} else {
			return new WP_Error( 'WXR_parse_error', esc_html__( 'No XML parser found.', '@@textdomain' ) );
		}
	}

	/**
	 * Parse XML file
	 *
	 * @param string $file Maybe parse a custom file instead of the constructed file.
	 */
	protected function simplexml_parse( $file ) {
		$authors = $posts = $categories = $tags = $terms = array();

		libxml_use_internal_errors( true );

		$xml = simplexml_load_file( $file );

		if ( ! $xml ) {
			return new WP_Error( 'WXR_parse_error', esc_html__( 'Invalid WXR file.', '@@textdomain' ), libxml_get_errors() );
		}

		$wxr_version = $xml->xpath( '/rss/channel/wp:wxr_version' );
		$wxr_version = (string) trim( $wxr_version[0] );
		$wxr_compare = version_compare( $wxr_version, '1.2' );

		if ( ! $wxr_version || 1 === $wxr_compare ) {
			return new WP_Error( 'WXR_parse_error', esc_html__( 'Invalid WXR file.', '@@textdomain' ) );
		}

		if ( ! preg_match( '/^\d+\.\d+$/', $wxr_version ) ) {
			return new WP_Error( 'WXR_parse_error', esc_html__( 'Invalid WXR file.', '@@textdomain' ) );
		}

		$base_url = $xml->xpath( '/rss/channel/wp:base_site_url' );
		$base_url = (string) trim( $base_url[0] );
		$namespaces = $xml->getDocNamespaces();

		if ( ! isset( $namespaces['wp'] ) ) {
			$namespaces['wp'] = 'http://wordpress.org/export/' . $wxr_version . '/';
		}

		if ( ! isset( $namespaces['excerpt'] ) ) {
			$namespaces['excerpt'] = 'http://wordpress.org/export/' . $wxr_version . '/excerpt/';
		}

		$wp_authors = $xml->xpath( '/rss/channel/wp:author' );

		foreach ( $wp_authors as $author ) {
			$author = $author->children( $namespaces['wp'] );
			$authors[] = array(
				'id'		=> (int) $author->author_id,
				'login'		=> (string) $author->author_login,
				'email'		=> (string) $author->author_email,
				'first_name'	=> (string) $author->author_first_name,
				'last_name'	=> (string) $author->author_last_name,
				'display_name' 	=> (string) $author->author_display_name,
			);
		}

		$wp_cats = $xml->xpath( '/rss/channel/wp:category' );

		foreach ( $wp_cats as $cat ) {
			$cat1 = $cat->children( $namespaces['wp'] );
			$cat2 = array(
				'id'		=> (int) $cat1->term_id,
				'name'		=> (string) $cat1->cat_name,
				'slug'		=> (string) $cat1->category_nicename,
				'parent'	=> (string) $cat1->category_parent,
				'taxonomy'	=> 'category',
				'description'	=> (string) $cat1->category_description,
			);
			foreach ( $cat1->termmeta as $meta ) {
				$cat2['meta'][] = array(
					'key'	 => (string) $meta->meta_key,
					'value'  => (string) $meta->meta_value,
				);
			}
			$categories[] = $cat2;
		}

		$wp_tags = $xml->xpath( '/rss/channel/wp:tag' );

		foreach ( $wp_tags as $tag ) {
			$tag1 = $tag->children( $namespaces['wp'] );
			$tag2 = array(
			'id'			=> (int) $tag1->term_id,
			'slug'			=> (string) $tag1->tag_slug,
			'name'			=> (string) $tag1->tag_name,
			'taxonomy'		=> 'post_tag',
			'description' 		=> (string) $tag1->tag_description,
			);
			foreach ( $tag1->termmeta as $meta ) {
				$tag2['meta'][] = array(
					'key'	=> (string) $meta->meta_key,
					'value' => (string) $meta->meta_value,
				);
			}
			$tags[] = $tag2;
		}

		$wp_terms = $xml->xpath( '/rss/channel/wp:term' );

		foreach ( $wp_terms as $term ) {
			$term1 = $term->children( $namespaces['wp'] );
			$term2 = array(
				'id'		=> (int) $term1->term_id,
				'slug'		=> (string) $term1->term_slug,
				'parent'	=> (string) $term1->term_parent,
				'name'		=> (string) $term1->term_name,
				'taxonomy'	=> (string) $term1->term_taxonomy,
				'description' 	=> (string) $term1->term_description,
			);
			foreach ( $term1->termmeta as $meta ) {
				$term2['meta'][] = array(
					'key'	 => (string) $meta->meta_key,
					'value' => (string) $meta->meta_value,
				);
			}
			$terms[] = $term2;
		}

		foreach ( $xml->channel->item as $item ) {
			$dc = $item->children( 'http://purl.org/dc/elements/1.1/' );
			$wp = $item->children( $namespaces['wp'] );
			$content = $item->children( 'http://purl.org/rss/1.0/modules/content/' );
			$excerpt = $item->children( $namespaces['excerpt'] );
			$post = array(
				'post_title'	 => (string) $item->title,
				'guid'		 => (string) $item->guid,
				'post_author'	 => (string) $dc->creator,
				'post_content'	 => (string) $content->encoded,
				'post_excerpt'	 => (string) $excerpt->encoded,
				'post_id'	 => (int) $wp->post_id,
				'post_date'	 => (string) $wp->post_date,
				'post_date_gmt'	 => (string) $wp->post_date_gmt,
				'comment_status' => (string) $wp->comment_status,
				'ping_status'	 => (string) $wp->ping_status,
				'post_name'	 => (string) $wp->post_name,
				'post_status'	 => (string) $wp->status,
				'post_parent'	 => (int) $wp->post_parent,
				'menu_order'	 => (int) $wp->menu_order,
				'post_type'	 => (string) $wp->post_type,
				'post_password'	 => (string) $wp->post_password,
				'is_sticky'	 => (int) $wp->is_sticky,
			);

			if ( isset( $wp->attachment_url ) ) {
				$post['attachment_url'] = (string) $wp->attachment_url;
			}
			foreach ( $item->category as $c ) {
				$att = $c->attributes();
				if ( isset( $att['nicename'] ) ) {
					$post['terms'][] = array(
						'name'		=> (string) $c,
						'slug'		=> (string) $att['nicename'],
						'taxonomy' 	=> (string) $att['domain'],
					);
				}
			}
			foreach ( $wp->postmeta as $meta ) {
				$post['meta'][] = array(
					'key'	 => (string) $meta->meta_key,
					'value'  => (string) $meta->meta_value,
				);
			}
			foreach ( $wp->comment as $comment ) {
				$meta = array();
				if ( isset( $comment->commentmeta ) ) {
					foreach ( $comment->commentmeta as $m ) {
						$meta[] = array(
							'key'	 => (string) $m->meta_key,
							'value'  => (string) $m->meta_value,
						);
					}
				}
				$post['comments'][] = array(
					'comment_id'		=> (int) $comment->comment_id,
					'comment_author'	=> (string) $comment->comment_author,
					'comment_author_email'	=> (string) $comment->comment_author_email,
					'comment_author_IP'	=> (string) $comment->comment_author_IP,
					'comment_author_url'	=> (string) $comment->comment_author_url,
					'comment_date'		=> (string) $comment->comment_date,
					'comment_date_gmt'	=> (string) $comment->comment_date_gmt,
					'comment_content'	=> (string) $comment->comment_content,
					'comment_approved'	=> (string) $comment->comment_approved,
					'comment_type'		=> (string) $comment->comment_type,
					'comment_parent'	=> (int) $comment->comment_parent,
					'comment_user_id'	=> (int) $comment->comment_user_id,
					'commentmeta'		=> $meta,
				);
			}
			$posts[] = $post;
		}

		return array(
			'users'			=> $authors,
			'categories' 		=> $categories,
			'tags'			=> $tags,
			'terms'			=> $terms,
			'posts'			=> $posts,
			'baseurl'		=> $base_url,
			'version'		=> $wxr_version,
		);
	}

	/**
	 * Parse XML with XMLReader
	 *
	 * @param string $file Maybe parse a custom file instead of the constructed file.
	 */
	protected function xml_reader_parse( $file ) {
		$data = array();
		$reader = new XMLReader();
		$status = $reader->open( $file );

		if ( ! $status ) {
			return new WP_Error( 'WXR_parse_error', esc_html__( 'Could not open the file for parsing', '@@textdomain' ) );
		}

		while ( $reader->read() ) {
			if ( XMLReader::ELEMENT !== $reader->nodeType ) {
				continue;
			}
			switch ( $reader->name ) {
				case 'wp:wxr_version':
					$version = $reader->readString();

					if ( version_compare( $version, '1.2', '>' ) ) {
						return new WP_Error( 'WXR_parse_error', esc_html__( 'Invalid WXR file.', '@@textdomain' ) );
					}

					$data['version'] = $version;
					$reader->next();
				break;
				case 'wp:base_site_url':
					$base_url = $reader->readString();
					$data['baseurl'] = $base_url;
					$reader->next();
				break;
				case 'wp:author':
					$node = $reader->expand();
					$data['users'][] = $this->parseUser( $node );
					$reader->next();
				break;
				case 'wp:category':
					$node = $reader->expand();
					$data['categories'][] = $this->parseTerm( $node, 'category' );
					$reader->next();
				break;
				case 'wp:tag':
					$node = $reader->expand();
					$data['tags'][] = $this->parseTerm( $node, 'tag' );
					$reader->next();
				break;
				case 'wp:term':
					$node = $reader->expand();
					$data['terms'][] = $this->parseTerm( $node );
					$reader->next();
				break;
				case 'item':
					$node = $reader->expand();
					$data['posts'][] = $this->parsePost( $node );
					$reader->next();
				break;
				default:
				break;
			}
		}

		return $data;
	}

	/**
	 * Parse author node
	 */
	protected function parseUser( $node ) {

		$data = $meta = array();

		foreach ( $node->childNodes as $child ) {
			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				continue;
			}
			switch ( $child->tagName) {
				case 'wp:author_id':
					$data['id'] = (int)$child->textContent;
				break;
				case 'wp:author_login':
					$data['login'] = $child->textContent;
				break;
				case 'wp:author_email':
					$data['email'] = $child->textContent;
				break;
				case 'wp:author_display_name':
					$data['display_name'] = $child->textContent;
				break;
				case 'wp:author_first_name':
					$data['first_name'] = $child->textContent;
				break;
				case 'wp:author_last_name':
					$data['last_name'] = $child->textContent;
				break;
				default:
			break;
			}
		}

		$data['meta'] = $meta;

		return $data;
	}

	/**
	 * Parse term node
	 */
	protected function parseTerm( $node, $type = 'term' ) {

		$data = $meta = array();

		$tag_name = array(
			'id'		=> 'wp:term_id',
			'taxonomy'	=> 'wp:term_taxonomy',
			'slug'		=> 'wp:term_slug',
			'parent'	=> 'wp:term_parent',
			'name'		=> 'wp:term_name',
			'description' 	=> 'wp:term_description',
		);

		switch ( $type ) {
			case 'category':
				$tag_name['slug']	 = 'wp:category_nicename';
				$tag_name['parent']	 = 'wp:category_parent';
				$tag_name['name']	 = 'wp:cat_name';
				$tag_name['description'] = 'wp:category_description';
				$tag_name['taxonomy']	 = null;
				$data['taxonomy']	 = 'category';
			break;
			case 'tag':
				$tag_name['slug']	 = 'wp:tag_slug';
				$tag_name['parent']	 = null;
				$tag_name['name']	 = 'wp:tag_name';
				$tag_name['description'] = 'wp:tag_description';
				$tag_name['taxonomy']	 = null;
				$data['taxonomy']	 = 'post_tag';
			break;
			default:
			break;
		}

		foreach ( $node->childNodes as $child ) {
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}
			$key = array_search( $child->tagName, $tag_name );
			if ( $key ) {
				$data[$key] = $child->textContent;
			} elseif ( $child->tagName === 'wp:termmeta' ) {
				$meta_item = $this->parseMeta( $child );
				if ( !empty( $meta_item ) ) {
					$meta[] = $meta_item;
				}
			}
		}

		if ( empty( $data['taxonomy'] ) ) {
			return null;
		}

		$data['meta'] = $meta;

		return $data;
	}

	/**
	 * Parse post node
	 */
	protected function parsePost( $node ) {
		$data = $meta = $comments = $terms = array();

		foreach ($node->childNodes as $child) {
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}
			switch ($child->tagName) {
				case 'wp:post_type':
					$data['post_type'] = $child->textContent;
				break;
				case 'title':
					$data['post_title'] = $child->textContent;
				break;
				case 'guid':
					$data['guid'] = $child->textContent;
				break;
				case 'dc:creator':
					$data['post_author'] = $child->textContent;
				break;
				case 'content:encoded':
					$data['post_content'] = $child->textContent;
				break;
				case 'excerpt:encoded':
					$data['post_excerpt'] = $child->textContent;
				break;
				case 'wp:post_id':
					$data['post_id'] = (int)$child->textContent;
				break;
				case 'wp:post_date':
					$data['post_date'] = $child->textContent;
				break;
				case 'wp:post_date_gmt':
					$data['post_date_gmt'] = $child->textContent;
				break;
				case 'wp:comment_status':
					$data['comment_status'] = $child->textContent;
				break;
				case 'wp:ping_status':
					$data['ping_status'] = $child->textContent;
				break;
				case 'wp:post_name':
					$data['post_name'] = $child->textContent;
				break;
				case 'wp:status':
					$data['post_status'] = $child->textContent;
				break;
				case 'wp:post_parent':
					$data['post_parent'] = (int)$child->textContent;
				break;
				case 'wp:menu_order':
					$data['menu_order'] = $child->textContent;
				break;
				case 'wp:post_password':
					$data['post_password'] = $child->textContent;
				break;
				case 'wp:is_sticky':
					$data['is_sticky'] = $child->textContent;
				break;
				case 'wp:attachment_url':
					$data['attachment_url'] = $child->textContent;
				break;
				case 'wp:postmeta':
					$meta_item = $this->parseMeta( $child );
					if ( !empty($meta_item) ) {
						$meta[] = $meta_item;
					}
				break;
				case 'wp:comment':
					$comment_item = $this->parseComment( $child );
					if ( ! empty( $comment_item ) ) {
						$comments[] = $comment_item;
					}
				break;
				case 'category':
					$term_item = $this->parsePostTerm( $child );
					if ( ! empty( $term_item ) ) {
						$terms[] = $term_item;
					}
				break;
				default:
				break;
			}
		}

		$data['terms'] = $terms;
		$data['comments'] = $comments;
		$data['meta'] = $meta;

		return $data;
	}

	/**
	 * Parse meta node
	 */
	protected function parseMeta( $node ) {

		foreach ( $node->childNodes as $child ) {

			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				continue;
			}
			switch ( $child->tagName ) {
				case 'wp:meta_key':
					$key = $child->textContent;
				break;
				case 'wp:meta_value':
					$value = $child->textContent;
				break;
				default:
				break;
			}
		}

		if ( empty( $key ) || empty( $value ) ) {
			return null;
		}

		return array(
			'key' => $key,
			'value' => $value,
		);
	}

	/**
	 * Parse comment node
	 */
	protected function parseComment( $node ) {
		$data = array(
			'commentmeta' => array(),
		);
		foreach ( $node->childNodes as $child ) {
			if ( $child->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}
			switch ( $child->tagName ) {
				case 'wp:comment_id':
					$data['comment_id'] = (int)$child->textContent;
				break;
				case 'wp:comment_author':
					$data['comment_author'] = $child->textContent;
				break;
				case 'wp:comment_author_email':
					$data['comment_author_email'] = $child->textContent;
				break;
				case 'wp:comment_author_IP':
					$data['comment_author_IP'] = $child->textContent;
				break;
				case 'wp:comment_author_url':
					$data['comment_author_url'] = $child->textContent;
				break;
				case 'wp:comment_user_id':
					$data['comment_user_id'] = (int)$child->textContent;
				break;
				case 'wp:comment_date':
					$data['comment_date'] = $child->textContent;
				break;
				case 'wp:comment_date_gmt':
					$data['comment_date_gmt'] = $child->textContent;
				break;
				case 'wp:comment_content':
					$data['comment_content'] = $child->textContent;
				break;
				case 'wp:comment_approved':
					$data['comment_approved'] = $child->textContent;
				break;
				case 'wp:comment_type':
					$data['comment_type'] = $child->textContent;
				break;
				case 'wp:comment_parent':
					$data['comment_parent'] = (int)$child->textContent;
				break;
				case 'wp:commentmeta':
					$meta_item = $this->parseMeta( $child );
					if ( ! empty( $meta_item ) ) {
						$data['commentmeta'][] = $meta_item;
					}
				break;
		default:
		break;
			}
		}

		return $data;
	}

	/**
	 * Parse post term
	 */
	protected function parsePostTerm( $node ) {
		$data = array(
			'taxonomy' => 'category',
		);

		if ( $node->hasAttribute( 'domain' ) ) {
			$data['taxonomy'] = $node->getAttribute( 'domain' );
		}
		if ( $node->hasAttribute( 'nicename' ) ) {
			$data['slug'] = $node->getAttribute( 'nicename' );
		}

		$data['name'] = $node->textContent;

		if ( empty( $data['slug'] ) ) {
			return null;
		}

		if ( 'tag' === $data['taxonomy'] ) {
			$data['taxonomy'] = 'post_tag';
		}

		return $data;
	}
}
