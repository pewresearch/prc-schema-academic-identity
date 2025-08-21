<?php
/**
 * DataCite Provider Class
 *
 * This class handles the creation, validation and manipulation of DataCite DOI schema data.
 *
 * @package PRC\Platform\Academic_Identity\Providers
 */

namespace PRC\Platform\Academic_Identity\Providers;

/**
 * Class Datacite
 *
 * Handles the creation, validation and manipulation of DataCite DOI schema data.
 */
class Datacite {

	/**
	 * The meta key for the Datacite DOI.
	 *
	 * @var string
	 */
	public static $schema_meta_key = 'datacite_doi';

	/**
	 * Constructor
	 *
	 * @param Loader $loader The loader instance.
	 */
	public function __construct( $loader ) {
		$loader->add_action( 'init', $this, 'register_post_meta' );
		$loader->add_action( 'rest_api_init', $this, 'register_rest_fields' );
		$loader->add_action( 'enqueue_block_editor_assets', $this, 'enqueue_inspector_panel_assets' );
		$loader->add_action( 'wp_head', $this, 'schema_ld_json' );
	}

	/**
	 * Get's all enabled post types that can have a DOI.
	 * Currently all public post types are opted-in.
	 *
	 * @return array
	 */
	public static function get_enabled_post_types() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		return $post_types;
	}

	/**
	 * Sanitizes the citation.
	 * Checks it's in this format:
	 * XX.XXX/XXXXX
	 *
	 * @param string $meta_value The citation to sanitize.
	 * @return string
	 */
	public function sanitize_citation( $meta_value ) {
		// First sanitize the basic text.
		$meta_value = sanitize_text_field( $meta_value );

		// Extract just the DOI part using regex.
		if ( preg_match( '/([0-9]{2}\.[0-9]{3}\/[A-Za-z0-9-]+)/', $meta_value, $matches ) ) {
			return $matches[1];
		}

		// If no match found, return false.
		return false;
	}

	/**
	 * Registers the post meta for the Datacite DOI.
	 */
	public function register_post_meta() {
		foreach ( self::get_enabled_post_types() as $post_type ) {
			$post_type_supports_revisions = post_type_supports( $post_type, 'revisions' );
			register_post_meta(
				$post_type,
				self::$schema_meta_key,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
					'revisions_enabled' => $post_type_supports_revisions,
				)
			);
			register_post_meta(
				$post_type,
				self::$schema_meta_key . '_citation',
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => 'string',
					'sanitize_callback' => function ( $value ) {
						return sanitize_text_field( $value );
					},
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
					'revisions_enabled' => $post_type_supports_revisions,
				)
			);
		}
	}

	/**
	 * Registers the rest fields for the Datacite DOI.
	 *
	 * @hook rest_api_init
	 */
	public function register_rest_fields() {
		foreach ( self::get_enabled_post_types() as $post_type ) {
			register_rest_field(
				$post_type,
				self::$schema_meta_key,
				array(
					'get_callback' => array( $this, 'get_datacite_doi_field' ),
					'schema'       => array(
						'type' => 'string',
					),
				)
			);
		}
	}

	/**
	 * Gets the DOI citation for the given post.
	 *
	 * @param int $post_id The post ID.
	 * @return string
	 */
	public static function get_doi_citation( $post_id ) {
		$parent_id = get_post_parent( $post_id );
		$parent_id = get_post_field( 'ID', $parent_id );

		// If the post is a dataset taxonomy term, get the DOI data from the related dataset post.
		if ( is_tax( 'datasets' ) ) {
			$dataset_term_id = get_queried_object_id();
			$dataset         = \TDS\get_related_post( $dataset_term_id, 'datasets' );

			// Check if we got a valid dataset object before accessing its ID.
			if ( $dataset && is_object( $dataset ) && isset( $dataset->ID ) ) {
				$post_id = $dataset->ID;
			} else {
				// If we can't get a valid dataset, return early.
				return;
			}
		}

		// First check if the post contains schema data.
		$doi_data = get_post_meta( $post_id, self::$schema_meta_key, true );
		// If the post contains no schema data and it's not a dataset taxonomy term and it has a parent post, check the parent post.
		if ( ! $doi_data && 0 !== $parent_id && ! is_tax( 'datasets' ) ) {
				$post_id  = $parent_id;
				$doi_data = get_post_meta( $post_id, self::$schema_meta_key, true );
		}

		$doi_data = json_decode( $doi_data, true );
		if ( ! $doi_data ) {
			return;
		}
		$doi_citation = get_post_meta( $post_id, self::$schema_meta_key . '_citation', true );
		if ( ! $doi_citation ) {
			return;
		}
		if ( empty( $doi_citation ) ) {
			return;
		}

		$post_title = get_the_title( $post_id );
		$post_date  = get_the_date( 'Y', $post_id );

		// Add functionality to get the "author" property out of the citation data.
		// If "pew" is the author then drop the second pew research center from citaiton text.
		$doi_author = '';
		if ( isset( $doi_data['author'] ) ) {
			$authors = $doi_data['author'];
			// Check if author is an array of arrays.
			if ( is_array( $authors ) && isset( $authors[0] ) && is_array( $authors[0] ) ) {
				// Multiple authors case.
				$author_names = array();
				foreach ( $authors as $index => $author ) {
					if ( 0 === $index ) {
						$author_names[] = $author['familyName'] . ', ' . $author['givenName'];
					} else {
						// Remove any trailing spaces.
						$name           = $author['givenName'] . ' ' . $author['familyName'];
						$author_names[] = trim( $name );
					}
				}
				if ( count( $author_names ) >= 3 ) {
					$doi_author = implode( ', ', array_slice( $author_names, 0, -1 ) ) . ', and ' . $author_names[ count( $author_names ) - 1 ];
				} else {
					$doi_author = implode( ', and ', $author_names );
				}
			} elseif ( is_array( $authors ) && ! empty( $authors ) ) {
				if ( array_key_exists( 'familyName', $authors ) && array_key_exists( 'givenName', $authors ) ) {
					$doi_author = $authors['familyName'] . ', ' . $authors['givenName'];
				} else {
					$doi_author = $authors['name'];
				}
			}
		}
		$doi_org = ' Pew Research Center. ';
		if ( strpos( $doi_author, 'Pew Research' ) !== false ) {
			$doi_org = '';
		}

		$post_title = $post_title . ( ! preg_match( '/[.!?]$/', $post_title ) ? '.' : '' );

		return wp_sprintf(
			'%1$s %2$s "%3$s" %4$sdoi: <a href="https://doi.org/%5$s">%5$s</a>.',
			$doi_author ? $doi_author . '. ' : '',
			$post_date ? $post_date . '. ' : '',
			$post_title,
			$doi_org,
			$doi_citation
		);
	}

	/**
	 * Gets the DOI citation for the given post.
	 *
	 * @param int             $object The post object.
	 * @param string          $field_name The field name.
	 * @param WP_REST_Request $request The request.
	 * @return string
	 */
	public function get_datacite_doi_field( $object, $field_name, $request ) {
		return wp_strip_all_tags( self::get_doi_citation( $object['id'] ) );
	}

	/**
	 * Enqueues the inspector panel assets.
	 *
	 * @hook enqueue_block_editor_assets
	 */
	public function enqueue_inspector_panel_assets() {
		// Check the asset file exists.
		$asset_file = include PRC_ACADEMIC_IDENTITY_DIR . '/includes/inspector-sidebar-panel/build/index.asset.php';
		if ( ! $asset_file ) {
			return;
		}
		// Double check the current post type is one of the enabled post types.
		$current_admin_screen_post_type = \PRC\Platform\get_wp_admin_current_post_type();
		if ( ! in_array( $current_admin_screen_post_type, self::get_enabled_post_types() ) ) {
			return;
		}
		wp_enqueue_script(
			'prc-academic-identity-inspector-panel',
			plugin_dir_url( PRC_ACADEMIC_IDENTITY_FILE ) . '/includes/inspector-sidebar-panel/build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
	}

	/**
	 * Generate the Dataset Schema.org JSON-LD for the current post.
	 *
	 * @hook wp_head
	 * @TODO: hook into yoast json ld filter.
	 */
	public function schema_ld_json() {
		$schema_json  = null;
		$schema_class = null;
		if ( is_singular() || is_tax() ) {
			if ( is_tax() ) {
				$term_id  = get_queried_object_id();
				$taxonomy = get_queried_object()->taxonomy;
				$post     = \TDS\get_related_post( $term_id, $taxonomy );
				$post_id  = get_post_field( 'ID', $post );
			} else {
				$post_id = get_the_ID();
			}
			$post_type   = get_post_type( $post_id );
			$schema_json = get_post_meta( $post_id, self::$schema_meta_key, true );
			// Validate that the schema json is valid json.
			if ( ! json_decode( $schema_json ) ) {
				$schema_json = null;
			}
			$schema_class = $post_type . '-schema-single';
		} elseif ( is_post_type_archive( 'dataset' ) ) {
			$id              = 'https://www.pewresearch.org/datasets/';
			$creator         = wp_json_encode(
				array(
					'@type' => 'Organization',
					'@id'   => 'https://www.pewresearch.org',
					'name'  => 'Pew Research Center',
				)
			);
			$description     = 'Pew Research Center makes the case-level microdata for much of its research available to the public for secondary analysis after a period of time.';
			$fund_pool_terms = get_terms(
				array(
					'taxonomy'   => '_fund_pool',
					'hide_empty' => false,
				)
			);
			$funders         = array_map(
				function ( $fund_pool_term ) {
					return array(
						'@type' => 'Organization',
						'@id'   => get_term_meta( $fund_pool_term->term_id, 'funder_id', true ),
						'name'  => $fund_pool_term->name,
					);
				},
				$fund_pool_terms
			);

			$about = array(
				array(
					'@id'  => 'http://id.loc.gov/authorities/subjects/sh85112549',
					'name' => 'religion data',
				),
				array(
					'@id'  => 'http://id.loc.gov/authorities/subjects/sh85127580',
					'name' => 'religion surveys',
				),
				array(
					'@id'  => 'http://id.loc.gov/authorities/subjects/sh85124003',
					'name' => 'social science surveys',
				),
				array(
					'@id'  => 'http://id.loc.gov/authorities/subjects/sh85104459',
					'name' => 'political surveys',
				),
			);

			$genre = array(
				array(
					'@id'  => 'http://id.loc.gov/authorities/genreForms/gf2014026059',
					'name' => 'Census data',
				),
			);

			ob_start();
			?>
				{
					"@context" : "https://schema.org",
					"@id" : <?php echo $id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>,
					"@type" : "DataCatalog",
					"name" : "Pew Research Center - Datasets",
					"creator" : <?php echo $creator; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>,
					"description" : <?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>,
					"funder" : [
						<?php foreach ( $funders as $funder ) : ?>
							<?php echo wp_json_encode( $funder ); ?>,
						<?php endforeach; ?>
					],
					"about" :[
						<?php foreach ( $about as $about_item ) : ?>
							<?php echo wp_json_encode( $about_item ); ?>,
						<?php endforeach; ?>
					],
					"genre" : [
						<?php foreach ( $genre as $genre_item ) : ?>
							<?php echo wp_json_encode( $genre_item ); ?>,
						<?php endforeach; ?>
					]
				}
			<?php
			$schema_json  = ob_get_clean();
			$schema_class = 'dataset-schema-archive';
		}

		if ( $schema_json ) {
			// Decode and re-encode with pretty print option.
			$formatted_json = 'local' === wp_get_environment_type() ? wp_json_encode(
				json_decode( $schema_json ),
				JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
			) : $schema_json;

			echo wp_sprintf(
				'<script type="application/ld+json" class="%s">%s</script>',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$schema_class,
				wp_kses_data( $formatted_json ),
			);
		}
	}
}
