/**
 * WordPress Dependencies
 */
import { RichText } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';

const DOI_CITATION_REGEX =
	/(https:\/\/doi\.org\/)?(\d{2}\.\d{5}\/[A-Za-z0-9-]+)/;

/**
 * Validates if a string is valid JSON.
 *
 * @param {string} str - The string to validate.
 * @return {boolean} True if valid JSON, false otherwise.
 */
const isValidJSON = (str) => {
	try {
		JSON.parse(str);
		return true;
	} catch {
		return false;
	}
};

/**
 * Extracts DOI citation from a JSON string.
 * Supports three formats: JSON-LD (@id), DataCite (id), and legacy (data.id).
 *
 * @param {string} value - JSON string containing DOI data.
 * @return {string|null} The extracted DOI citation or null if not found.
 */
const extractDoiCitation = (value) => {
	if (!value || typeof value !== 'string' || !isValidJSON(value)) {
		return null;
	}

	let parsed;
	try {
		parsed = JSON.parse(value);
	} catch {
		return null;
	}

	// Check for JSON-LD format (@id property)
	if (parsed['@id'] && DOI_CITATION_REGEX.test(parsed['@id'])) {
		return parsed['@id'].match(DOI_CITATION_REGEX)[2];
	}

	// Check for DataCite format (direct id property)
	if (parsed.id && DOI_CITATION_REGEX.test(parsed.id)) {
		return parsed.id.match(DOI_CITATION_REGEX)[2];
	}

	// Check for legacy format (nested data.id property)
	if (parsed.data?.id && DOI_CITATION_REGEX.test(parsed.data.id)) {
		return parsed.data.id.match(DOI_CITATION_REGEX)[2];
	}

	return null;
};

/**
 * Renders the DOI citation link or editor.
 *
 * @param {Object}   props                   Component props.
 * @param {string}   props.doiCitation       The DOI citation string.
 * @param {boolean}  props.allowEditing      Whether editing is allowed.
 * @param {boolean}  props.editingAsRichText Whether to use RichText editor.
 * @param {Function} props.onChange          Change handler for editable citation.
 * @return {JSX.Element} The citation input/display element.
 */
function CitationInput({
	doiCitation,
	allowEditing,
	editingAsRichText,
	onChange,
}) {
	if (!allowEditing) {
		return (
			<span>
				<a href={`https://doi.org/${doiCitation}`}>{doiCitation}</a>
			</span>
		);
	}

	if (editingAsRichText) {
		return (
			<RichText
				tagName="span"
				onChange={onChange}
				allowedFormats={[]}
				value={doiCitation}
				placeholder={__(
					'Citation ID Here…',
					'prc-schema-academic-identity'
				)}
				disableLineBreaks
			/>
		);
	}

	return <TextControl value={doiCitation} onChange={onChange} />;
}

/**
 * Citation component for displaying DOI citations.
 *
 * @param {Object}   props                   Component props.
 * @param {string}   props.date              Publication date/year.
 * @param {string}   props.title             Article title.
 * @param {string}   props.doiCitation       DOI citation string.
 * @param {boolean}  props.allowEditing      Whether to allow editing.
 * @param {boolean}  props.editingAsRichText Whether to use RichText for editing.
 * @param {Function} props.onChange          Handler for citation changes.
 * @return {JSX.Element} The citation paragraph element.
 */
function Citation({
	date = '2025',
	title = 'Title of the Article',
	doiCitation = 'XX.XXX/XXXXX',
	allowEditing = false,
	editingAsRichText = false,
	onChange = () => {},
}) {
	/* translators: %s: article title wrapped in quotes with period */
	const formattedTitle = sprintf(
		/* translators: %s: article title */
		__('"%s."', 'prc-schema-academic-identity'),
		title
	);

	return (
		<p>
			<span>
				Doe, John. {date}. {formattedTitle} Pew Research Center. doi:
			</span>{' '}
			<CitationInput
				doiCitation={doiCitation}
				allowEditing={allowEditing}
				editingAsRichText={editingAsRichText}
				onChange={onChange}
			/>
		</p>
	);
}

export { extractDoiCitation, Citation };

export default Citation;
