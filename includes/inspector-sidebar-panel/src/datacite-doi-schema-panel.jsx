/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import { PanelBody, TextareaControl, CardDivider } from '@wordpress/components';

/**
 * External Dependencies
 */
import { useDebounce } from '@prc/hooks';

/**
 * Internal Dependencies
 */
import { Citation, extractDoiCitation } from '../../../shared/citation';

export default function DataciteDOISchemaPanel({
	meta,
	setMeta,
	postTitle,
	postDate,
}) {
	const metaDoi = meta?.datacite_doi ?? '';
	const metaCitation = meta?.datacite_doi_citation ?? '';

	const [localDoi, setLocalDoi] = useState(metaDoi);
	const [localCitation, setLocalCitation] = useState(metaCitation);

	// Keep editor inputs aligned with canonical meta when it changes (RTC, load, other panels).
	useEffect(() => {
		setLocalDoi(metaDoi);
	}, [metaDoi]);

	useEffect(() => {
		setLocalCitation(metaCitation);
	}, [metaCitation]);

	const debouncedDoi = useDebounce(localDoi, 500);
	const debouncedCitation = useDebounce(localCitation, 500);

	const metaRef = useRef(meta);
	metaRef.current = meta;

	// Write settled local edits to meta. Compare against metaRef (not metaDoi /
	// metaCitation deps) so a meta update after DOI extraction cannot re-enter
	// this effect while debouncedCitation is still stale and wipe the citation.
	useEffect(() => {
		const m = metaRef.current;
		const currentDoi = m?.datacite_doi ?? '';
		const currentCitation = m?.datacite_doi_citation ?? '';
		let nextDoi = currentDoi;
		let nextCitation = currentCitation;
		let extractedCitation = null;

		if (debouncedDoi !== currentDoi) {
			nextDoi = debouncedDoi;
			extractedCitation = extractDoiCitation(debouncedDoi);
		}
		if (debouncedCitation !== currentCitation) {
			nextCitation = debouncedCitation;
		}
		if (extractedCitation && extractedCitation !== nextCitation) {
			nextCitation = extractedCitation;
			// Keep local citation aligned immediately so the citation debounce
			// cannot later overwrite meta with a stale empty value.
			setLocalCitation(extractedCitation);
		}

		if (nextDoi === currentDoi && nextCitation === currentCitation) {
			return;
		}

		setMeta({
			...m,
			datacite_doi: nextDoi,
			datacite_doi_citation: nextCitation,
		});
	}, [debouncedDoi, debouncedCitation, setMeta]);

	return (
		<PanelBody title={__('DataCite DOI Schema')}>
			<TextareaControl
				label={__('DataCite DOI Schema')}
				help={__('Enter the DataCite DOI schema as JSON')}
				value={localDoi}
				onChange={(value) => {
					setLocalDoi(value);
				}}
				rows={10}
			/>
			<CardDivider />
			<Citation
				date={postDate}
				title={postTitle}
				doiCitation={localCitation}
				allowEditing={true}
				onChange={(value) => {
					setLocalCitation(value);
				}}
			/>
		</PanelBody>
	);
}
