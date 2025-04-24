<?php
/**
 * The blocks class.
 *
 * @package PRC\Platform\Academic_Identity
 */

namespace PRC\Platform\Academic_Identity;

/**
 * The blocks class.
 */
class Blocks {
	/**
	 * The loader object.
	 *
	 * @var object
	 */
	protected $loader;

	/**
	 * Constructor.
	 *
	 * @param object $loader The loader object.
	 */
	public function __construct( $loader ) {
		$this->loader = $loader;

		require_once PRC_ACADEMIC_IDENTITY_BLOCKS_DIR . '/build/doi-citation/class-doi-citation.php';

		$this->init();
	}

	/**
	 * Initialize the class.
	 */
	public function init() {
		wp_register_block_metadata_collection(
			plugin_dir_path( __FILE__ ) . 'build',
			plugin_dir_path( __FILE__ ) . 'build/blocks-manifest.php'
		);

		new DOI_Citation( $this->loader );
	}

	/**
	 * Get the block JSON.
	 *
	 * @param string $block_name The block name.
	 * @return array
	 */
	public static function get_block_json( $block_name ) {
		$manifest = include PRC_ACADEMIC_IDENTITY_BLOCKS_DIR . '/build/blocks-manifest.php';
		if ( ! isset( $manifest[ $block_name ] ) ) {
			return array();
		}
			$manifest = array_key_exists( $block_name, $manifest ) ? $manifest[ $block_name ] : array();
		if ( ! empty( $manifest ) ) {
			$manifest['file'] = wp_normalize_path( realpath( PRC_ACADEMIC_IDENTITY_BLOCKS_DIR . '/build/' . $block_name . '/block.json' ) );
		}
		return $manifest;
	}
}
