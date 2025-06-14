<?php
/**
 * Render the sub title block.
 *
 * @package PRC\Platform\Blocks
 */

namespace PRC\Platform\Academic_Identity;

$context_post_id = $block->context['postId'];

$text_align = isset( $attributes['textAlign'] ) ? $attributes['textAlign'] : 'left';

$citation_text = \PRC\Platform\Academic_Identity\Providers\Datacite::get_doi_citation( $context_post_id );

echo wp_sprintf(
	'<div %1$s><h5>Recommended Citation:</h5><p>%2$s</p></div>',
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	get_block_wrapper_attributes(
		array(
			'class' => 'has-text-align-' . $text_align,
		)
	),
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$citation_text
);
