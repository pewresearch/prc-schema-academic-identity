/**
 * External Dependencies
 */

/**
 * WordPress Dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	store as editorStore,
} from '@wordpress/editor';
import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { PanelRow } from '@wordpress/components';

/**
 * Internal Dependencies
 */
import DataciteDOISchemaPanel from './datacite-doi-schema-panel';
import Icon from '../../../shared/icon';

const PLUGIN_NAME = 'prc-schema-academic-identity-panel';

const Panel = () => {
	const { postType, postId } = useSelect((select) => {
		const currentPostType = select(editorStore).getCurrentPostType();
		const currentPostId = select(editorStore).getCurrentPostId();
		return {
			postType: currentPostType,
			postId: currentPostId,
		};
	}, []);
	const [postTitle] = useEntityProp('postType', postType, 'title', postId);
	const [postDate] = useEntityProp('postType', postType, 'date', postId);
	const [meta, setMeta] = useEntityProp('postType', postType, 'meta', postId);
	const date = useMemo(() => {
		return new Date(postDate || null).getFullYear();
	}, [postDate]);

	return (
		<>
			<PluginSidebarMoreMenuItem target={PLUGIN_NAME} icon={<Icon />}>
				{__('Academic Identity')}
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name={PLUGIN_NAME}
				title="Academic Identity"
				icon={<Icon />}
			>
				<PanelRow>
					<p style={{ padding: '1em' }}>
						The Academic Identity plugin supports our commitment to
						open science and data accessibility. For details about
						our "Open Science" initiative, visit the{' '}
						<a href="https://platform.pewresearch.org/wiki/open-science">
							Wiki
						</a>
						.
					</p>
				</PanelRow>
				<DataciteDOISchemaPanel
					meta={meta}
					setMeta={setMeta}
					postTitle={postTitle}
					postDate={date}
				/>
			</PluginSidebar>
		</>
	);
};

registerPlugin(PLUGIN_NAME, {
	icon: <Icon />,
	render: () => <Panel />,
});
