/**
 * External Dependencies
 */
import classnames from 'classnames';

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import {
	useBlockProps,
	BlockControls,
	AlignmentControl,
} from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

/**
 * Internal Dependencies
 */
import Citation from '../../shared/citation';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props                   Properties passed to the function.
 * @param {Object}   props.attributes        Available block attributes.
 * @param            props.className
 * @param            props.insertBlocksAfter
 * @param            props.context
 * @param {Function} props.setAttributes     Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({
	attributes,
	className,
	setAttributes,
	insertBlocksAfter,
	context,
	__unstableLayoutClassNames: layoutClassNames,
}) {
	const { textAlign } = attributes;
	const { postId, postType } = context;
	const [postTitle] = useEntityProp('postType', postType, 'title', postId);
	const [postDate] = useEntityProp('postType', postType, 'date', postId);
	const [meta, setMeta] = useEntityProp('postType', postType, 'meta', postId);

	const doiCitation = meta?.datacite_doi_citation || 'XX.XXX/XXXXX';

	const blockProps = useBlockProps({
		className: classnames(className, layoutClassNames, {
			[`has-text-align-${textAlign}`]: textAlign,
		}),
	});

	const allowEditing = useMemo(() => {
		return ((!postType || !postId) || postType === 'wp_template');
	}, [postType, postId]);

	const title = useMemo(() => {
		return postTitle || __('Title of the Article', 'prc-academic-identity');
	}, [postTitle]);

	const date = useMemo(() => {
		return new Date(postDate || null).getFullYear();
	}, [postDate]);

	return (
		<>
			<BlockControls>
				<AlignmentControl
					value={textAlign}
					onChange={(nextAlign) => {
						setAttributes({ textAlign: nextAlign });
					}}
				/>
			</BlockControls>
			<div {...blockProps}>
				<h5>Recommended Citation:</h5>
				<Citation
					date={date}
					title={title}
					doiCitation={doiCitation}
					allowEditing={allowEditing}
					editingAsRichText={true}
					onChange={(t) =>
						undefined !== postId &&
						setMeta({ ...meta, datacite_doi_citation: t })
					}
				/>
			</div>
		</>
	);
}
