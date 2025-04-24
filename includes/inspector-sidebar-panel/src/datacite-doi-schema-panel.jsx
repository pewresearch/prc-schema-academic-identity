/**
 * External Dependencies
 */
import { MediaDropZone } from '@prc/components';
import { useDebounce } from '@prc/hooks';

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { PanelBody, TextareaControl, CardDivider } from '@wordpress/components';

/**
 * Internal Dependencies
 */
import { Citation, extractDoiCitation } from '../../../shared/citation';

const ALLOWED_TYPES = ['application/json'];

export default function DataciteDOISchemaPanel({
	meta,
	setMeta,
	postTitle,
	postDate,
}) {
	const [dataciteDoi, setDataciteDoi] = useState(meta.datacite_doi || '');
	const debouncedDataciteDoi = useDebounce(dataciteDoi, 500);

	const [dataciteDoiCitation, setDataciteDoiCitation] = useState(
		meta.datacite_doi_citation || ''
	);
	const debouncedDataciteDoiCitation = useDebounce(dataciteDoiCitation, 500);

	useEffect(() => {
		const payload = {
			...meta,
		};
		let extractedCitation = null;
		// Quick diff check to avoid unnecessary updates
		if (debouncedDataciteDoi !== meta.datacite_doi) {
			payload.datacite_doi = debouncedDataciteDoi;
			console.log('debouncedDataciteDoi', debouncedDataciteDoi);
			extractedCitation = extractDoiCitation(debouncedDataciteDoi);
		}
		if (debouncedDataciteDoiCitation !== meta.datacite_doi_citation) {
			payload.datacite_doi_citation = debouncedDataciteDoiCitation;
		}
		// This ensures the citation id always matches the DOI data.
		if (
			extractedCitation &&
			extractedCitation !== payload.datacite_doi_citation
		) {
			payload.datacite_doi_citation = extractedCitation;
			setDataciteDoiCitation(extractedCitation);
		}
		setMeta(payload);
	}, [debouncedDataciteDoi, debouncedDataciteDoiCitation]);

	return (
		<PanelBody title={__('DataCite DOI Schema')}>
			<TextareaControl
				label={__('DataCite DOI Schema')}
				help={__('Enter the DataCite DOI schema as JSON')}
				value={dataciteDoi}
				onChange={(value) => {
					setDataciteDoi(value);
				}}
				rows={10}
			/>
			<CardDivider />
			<Citation
				date={postDate}
				title={postTitle}
				doiCitation={dataciteDoiCitation}
				allowEditing={true}
				onChange={(value) => {
					setDataciteDoiCitation(value);
				}}
			/>
		</PanelBody>
	);
}
