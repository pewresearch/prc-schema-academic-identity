/**
 * WordPress Dependencies
 */
import {
	RichText,
} from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { createBlock, getDefaultBlockName } from '@wordpress/blocks';

const DOI_CITATION_REGEX = /(https:\/\/doi\.org\/)?(\d{2}\.\d{5}\/[A-Za-z0-9-]+)/;

const isValidJSON = (str) => {
	try {
		JSON.parse(str);
		return true;
	} catch (e) {
		return false;
	}
};

const extractDoiCitation = (value) => {
	console.log('extractedCitation in:', value);
	if (value && typeof value === 'string' && isValidJSON(value)) {
		try {
			value = JSON.parse(value);
		} catch (e) {
			console.error('Failed to parse DOI JSON:', e);
			return null;
		}
	} else {
		console.warn('Invalid JSON provided for DOI citation');
		return null;
	}
	// Check if data is a valid property of the object
	console.log('passed...', value);
	if (value && value['@id']) {
		// Check if the data.id is a valid DOI citation
		if (DOI_CITATION_REGEX.test(value['@id'])) {
			// Then extract the DOI citation
			return value['@id'].match(DOI_CITATION_REGEX)[2];
		}
	}
	console.log('passed...', value.data);
	if (value && value.data.id) {
		if (DOI_CITATION_REGEX.test(value.data.id)) {
			// Then extract the DOI citation
			return value.data.id.match(DOI_CITATION_REGEX)[2];
		}
	}
	return null;
};

function Citation({
	date = '2025',
	title = 'Title of the Article',
	doiCitation = 'XX.XXX/XXXXX',
	allowEditing = false,
	editingAsRichText = false,
	onChange = () => {},
}) {
	const t = sprintf(`"%s"`, title);
	return(
		<p>
			<span>Doe, John. {date}. {t}. Pew Research Center. doi:</span>
			{' '}
			{ !allowEditing ? (
				<span>
					<a href={`https://doi.org/${doiCitation}`}>{doiCitation}</a>
				</span>
			) : (
				editingAsRichText ? (
					<RichText
						tagName="span"
						onChange={onChange}
						allowedFormats={[]}
						keepPlaceholderOnFocus
						value={doiCitation}
						placeholder={'Citation ID Here...'}
						disableLineBreaks
						__unstableOnSplitAtEnd={() =>
							insertBlocksAfter(createBlock(getDefaultBlockName()))
						}
					/>
				) : (
					<TextControl
						value={doiCitation}
						onChange={onChange}
					/>
				)
			)}
		</p>
	);
}

export { extractDoiCitation, Citation };

export default Citation;