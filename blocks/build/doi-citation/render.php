<?php
/**
 * Render the sub title block.
 *
 * @package PRC\Platform\Blocks
 */

namespace PRC\Platform\Academic_Identity;

$context_post_id = $block->context['postId'];
$parent_post_id  = wp_get_post_parent_id( $context_post_id );
// If the post is a child do not render the sub title.
if ( 0 !== $parent_post_id ) {
	return;
}
$doi_citation = get_post_meta( $context_post_id, 'datacite_doi_citation', true );
if ( ! $doi_citation ) {
	return;
}

$post_title = get_the_title( $context_post_id );

$bylines = new \PRC\Platform\Staff_Bylines\Bylines( $context_post_id );
$bylines = $bylines->format( 'string' );

$text_align = isset( $attributes['textAlign'] ) ? $attributes['textAlign'] : 'left';

$citation_text = sprintf(
	'%1$s "%2$s". Pew Research Center. doi: <a href="https://doi.org/%3$s">%3$s</a>',
	$bylines ? $bylines . '. ' : '',
	$post_title,
	$doi_citation
);

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
