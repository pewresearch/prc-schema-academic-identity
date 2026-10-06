<?php
/**
 * DOI Citation Block
 *
 * @package PRC\Platform\Academic_Identity
 */

namespace PRC\Platform\Academic_Identity;

use function PRC\Primitives\BlockUtils\strip_block_from_post_content;

/**
 * Block Name:        Post DOI Citation
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      8.1
 * Author:            Seth Rubenstein
 *
 * @package           prc-schema-academic-identity
 */
class DOI_Citation {
	/**
	 * Constructor
	 *
	 * @param mixed $loader Loader.
	 */
	public function __construct( $loader ) {
		$this->init( $loader );
	}

	/**
	 * Initialize the block
	 *
	 * @param mixed $loader Loader.
	 */
	public function init( $loader = null ) {
		if ( null !== $loader ) {
			$loader->add_action( 'init', $this, 'block_init' );
			$loader->add_filter( 'vip_block_data_api__sourced_block_result', $this, 'add_data_to_vip_blocks_api', 10, 4 );
			strip_block_from_post_content( 'prc-block/doi-citation', array( $this, 'is_singular_post' ) );
		}
	}

	/**
	 * Whether the DOI citation is in the post content of a single post.
	 *
	 * The template displays the citation through a pattern, so the block in
	 * the content would print it twice.
	 *
	 * @return bool
	 */
	public function is_singular_post(): bool {
		return is_singular( 'post' );
	}

	/**
	 * Add data to VIP blocks API
	 *
	 * @hook vip_block_data_api__sourced_block_result
	 * @param array  $sourced_block Sourced block.
	 * @param string $block_name Block name.
	 * @param int    $post_id Post ID.
	 * @param array  $block Block.
	 * @return array
	 */
	public function add_data_to_vip_blocks_api( $sourced_block, $block_name, $post_id, $block ) {
		if ( 'prc-block/doi-citation' !== $block_name ) {
			return $sourced_block;
		}

		// Add custom attribute to REST API result.
		$sourced_block['attributes']['content'] = \PRC\Platform\Academic_Identity\Providers\Datacite::get_doi_citation( $post_id );

		return $sourced_block;
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	public function block_init() {
		register_block_type_from_metadata( PRC_ACADEMIC_IDENTITY_BLOCKS_DIR . '/doi-citation' );
	}
}
