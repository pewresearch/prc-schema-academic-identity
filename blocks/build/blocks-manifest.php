<?php
// This file is generated. Do not modify it manually.
return array(
	'doi-citation' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'prc-block/doi-citation',
		'version' => '1.0.0',
		'title' => 'DOI Citation',
		'description' => 'Displays the DOI citation of a post.',
		'category' => 'layout',
		'attributes' => array(
			
		),
		'example' => array(
			'attributes' => array(
				
			)
		),
		'supports' => array(
			'anchor' => true,
			'html' => false,
			'multiple' => false,
			'color' => array(
				'text' => true,
				'background' => false,
				'link' => true,
				'heading' => true
			),
			'spacing' => array(
				'margin' => array(
					'top',
					'bottom'
				),
				'padding' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'letterSpacing' => true,
				'defaultControls' => array(
					'fontSize' => true
				)
			)
		),
		'usesContext' => array(
			'postType',
			'postId'
		),
		'textdomain' => 'post-doi-citation',
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);
