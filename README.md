# PRC Schema – Academic Identity

Manages academic identity metadata (DOI, ORCID) for PRC Platform content and outputs Schema.org JSON-LD structured data.

## Overview

This plugin integrates with DataCite to attach DOI schema metadata to any public post type. It registers post meta for the raw DataCite JSON payload and the extracted citation ID, exposes both via the REST API, injects Schema.org `application/ld+json` into `wp_head` on singular and archive pages, and provides a block editor sidebar panel for editors to enter DOI data. It also ships a `prc-block/doi-citation` dynamic block that renders a formatted recommended citation on the frontend.

The plugin is part of PRC's Open Science initiative. See the [Wiki](https://platform.pewresearch.org/wiki/open-science) for background.

### Dependencies

- **Upstream**: `prc-platform-core`, `prc-staff-bylines`, `prc-taxonomies` (Term Data Store / `TDS` for dataset taxonomy lookups), VIP Block Data API (`vip_block_data_api__sourced_block_result`)
- **Downstream**: Any template or pattern that places the `prc-block/doi-citation` block; any consumer of the `datacite_doi` REST field on post objects

## Architecture

The plugin loads two parallel subsystems through a central `Plugin` class:

1. **DataCite provider** (`includes/providers/datacite/class-datacite.php`) — owns post meta registration, REST fields, the block editor sidebar panel script, and Schema.org JSON-LD output via `wp_head`.
2. **DOI Citation block** (`src/doi-citation/`) — a dynamic block that reads `datacite_doi_citation` post meta and renders a formatted citation paragraph on the frontend. The block is intentionally stripped from `core/post-content` on singular `post` pages and is expected to appear via a template pattern instead.

The inspector sidebar panel is built separately (its own `wp-scripts` entry point under `includes/inspector-sidebar-panel/`) and registered as a block editor plugin (`PluginSidebar`). It shares UI components with the block via `shared/citation.jsx`.

### Key Files

| Path | Purpose |
|------|---------|
| `prc-schema-academic-identity.php` | Plugin entry point; defines constants, bootstraps `Plugin` class |
| `includes/class-plugin.php` | Loads dependencies, instantiates providers and blocks |
| `includes/providers/datacite/class-datacite.php` | Registers `datacite_doi` / `datacite_doi_citation` post meta; REST fields; `wp_head` JSON-LD; editor panel enqueueing |
| `includes/inspector-sidebar-panel/src/index.js` | Block editor `PluginSidebar` registration ("Academic Identity") |
| `includes/inspector-sidebar-panel/src/datacite-doi-schema-panel.jsx` | Panel UI — JSON textarea for raw DataCite payload, live citation preview |
| `shared/citation.jsx` | Shared `Citation` component and `extractDoiCitation` utility (parses JSON-LD, DataCite, and legacy `data.id` formats) |
| `src/doi-citation/block.json` | Block metadata — `prc-block/doi-citation` |
| `src/doi-citation/edit.jsx` | Block editor view; reads `datacite_doi_citation` meta via `useEntityProp` |
| `src/doi-citation/render.php` | Server-side render; calls `Datacite::get_doi_citation()` to produce formatted citation HTML |
| `build/doi-citation/class-doi-citation.php` | Block registration class; hooks `render_block` and `vip_block_data_api__sourced_block_result` |

## Hooks & Filters

| Hook | Type | Description |
|------|------|-------------|
| `init` | action | Registers `datacite_doi` and `datacite_doi_citation` post meta on all public post types; registers the `prc-block/doi-citation` block |
| `rest_api_init` | action | Registers a `datacite_doi` REST field on all public post types, returning the formatted plain-text citation string |
| `enqueue_block_editor_assets` | action | Enqueues the inspector sidebar panel JS on supported post types |
| `wp_head` | action | Outputs `<script type="application/ld+json">` with DataCite schema on singular posts, dataset taxonomy pages, and the dataset post type archive |
| `render_block` | filter | Strips `prc-block/doi-citation` from `core/post-content` on singular `post` pages so the citation doesn't appear twice when a template pattern also includes it |
| `vip_block_data_api__sourced_block_result` | filter | Injects the formatted citation string into the `content` attribute when the VIP Block Data API processes a `prc-block/doi-citation` block |

## Local Development

```bash
# Build blocks and inspector panel
npm run build -w @prc/schema-academic-identity

# Watch mode (blocks and inspector panel run separately)
npm run start:blocks -w @prc/schema-academic-identity
npm run start:inspector-panel -w @prc/schema-academic-identity
```

The `build` script runs two entries: `build:blocks` (the `doi-citation` block via `wp-scripts` with `--experimental-modules`) and `build:inspector-panel` (the sidebar panel from `includes/inspector-sidebar-panel/src`). Run both when making changes to either subsystem.

## Troubleshooting

### Schema.org JSON-LD not appearing on the page

**Symptom**: No `<script type="application/ld+json">` tag in page source for a post with a DOI.  
**Cause**: `schema_ld_json()` calls `json_decode()` on the stored `datacite_doi` meta and bails if it returns `false`. This means the stored value is either empty or contains invalid JSON.  
**Fix**: Open the post in the editor, open the "Academic Identity" sidebar, and verify the DataCite DOI Schema field contains valid JSON. Re-paste the raw DataCite JSON payload and save.

### DOI Citation block is visible in post content and also in the template

**Symptom**: The recommended citation appears twice on the frontend.  
**Cause**: The `render_block` hook strips `prc-block/doi-citation` from `core/post-content` only on `is_singular('post')`. Other post types or template configurations where the block lives outside `core/post-content` will not be stripped.  
**Fix**: Confirm the block is only placed inside the template pattern, not manually inserted into the post body. On non-`post` post types, adjust the `remove_doi_citation_from_post_content` condition in `class-doi-citation.php` if needed.

### Citation not resolving on dataset taxonomy pages

**Symptom**: `Datacite::get_doi_citation()` returns nothing on a `datasets` taxonomy archive page.  
**Cause**: The method calls `TDS\get_related_post()` to map the taxonomy term to its related dataset post. If Term Data Store returns `null` or the returned object has no `ID`, the method returns early.  
**Fix**: Verify the `datasets` term has a related post configured via the Term Data Store plugin. Check that `TDS\get_related_post( $term_id, 'datasets' )` returns a valid post object.

### Inspector panel not appearing in the block editor

**Symptom**: The "Academic Identity" sidebar menu item is missing.  
**Cause**: `enqueue_inspector_panel_assets()` checks `\PRC\Platform\get_wp_admin_current_post_type()` against the list of enabled post types (all public post types). If the helper returns an empty or unexpected value, enqueueing is skipped.  
**Fix**: Confirm the `prc-platform-core` helper `get_wp_admin_current_post_type()` is available and returning the correct post type slug for the current admin screen. Also confirm `includes/inspector-sidebar-panel/build/index.js` exists (run `npm run build`).

## Related Docs

- [Open Science Wiki](https://platform.pewresearch.org/wiki/open-science)
- [DataCite Schema documentation](https://schema.datacite.org/)
- [Schema.org Dataset type](https://schema.org/Dataset)
- [`prc-taxonomies` / Term Data Store](../prc-taxonomies/README.md)
