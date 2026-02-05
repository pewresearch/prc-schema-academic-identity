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

$label = isset( $attributes['label'] ) ? $attributes['label'] : 'RECOMMENDED CITATION:';

$context_post_id = $block->context['postId'];

$text_align = isset( $attributes['textAlign'] ) ? $attributes['textAlign'] : 'left';

$citation_text = \PRC\Platform\Academic_Identity\Providers\Datacite::get_doi_citation( $context_post_id );

if ( empty( $citation_text ) ) {
	return;
}

echo wp_sprintf(
	'<div %1$s><h5>%2$s</h5><p>%3$s</p></div>',
	get_block_wrapper_attributes(
		array(
			'class' => 'has-text-align-' . $text_align,
		)
	),
	$label, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$citation_text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
);
