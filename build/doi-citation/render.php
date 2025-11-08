<?php
/**
 * Render the sub title block.
 *
 * @package PRC\Platform\Blocks
 */

namespace PRC\Platform\Academic_Identity;

if ( ! array_key_exists( 'postId', $block->context ) ) {
	return;
}

$context_post_id = $block->context['postId'];

$text_align = isset( $attributes['textAlign'] ) ? $attributes['textAlign'] : 'left';

$citation_text = \PRC\Platform\Academic_Identity\Providers\Datacite::get_doi_citation( $context_post_id );

if ( empty( $citation_text ) ) {
	return;
}

echo wp_sprintf(
	'<div %1$s><strong>RECOMMENDED CITATION:</strong><p>%2$s</p></div>',
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	get_block_wrapper_attributes(
		array(
			'class' => 'has-text-align-' . $text_align,
		)
	),
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$citation_text
);
