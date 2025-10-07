/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */

/**
 * WordPress Dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import Icon from '../../shared/icon';

/**
 * Internal Dependencies
 */

import './style.scss';
import edit from './edit';

import metadata from './block.json';

const { name } = metadata;

const settings = {
	icon: <Icon />,
	edit,
};

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
registerBlockType(name, { ...metadata, ...settings });
