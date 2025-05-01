# PRC Schema - Academic Identity

A plugin for PRC Platform that manages academic identity metadata like DOI, ORCID, etc. This plugin integrates with multiple PRC Platform plugins, including Staff Bylines, Post Like Types, and more.

## Overview

The PRC Schema Academic Identity plugin provides a structured way to manage and display academic identity metadata across the PRC Platform. It enhances content with scholarly identifiers and academic attribution data.

## Features

- Integration with academic identifiers (DOI, ORCID)
- Staff byline academic metadata management
- Schema.org markup for academic content
- Integration with PRC Platform post types
- Block editor support for academic metadata

## Requirements

- WordPress 6.7 or higher
- PHP 8.2 or higher
- PRC Platform Core plugin
- PRC Staff Bylines plugin

## Integration Points

The plugin integrates with several PRC Platform components:

- Staff Bylines: Adds academic identity metadata to author profiles
- Post Like Types: Enhances content types with academic schema
- Block Editor: Provides blocks for academic metadata display and management

## Technical Details

This plugin follows WordPress VIP coding standards and is optimized for the VIP platform. It uses:

- Server-side rendered blocks for dynamic content
- WordPress REST API for data management
- Proper cache handling for VIP infrastructure
- TypeScript for enhanced type safety
- Modern functional programming patterns

## Blocks

The plugin provides several Gutenberg blocks for managing and displaying academic identity metadata:

### DOI Citation Block

A server-side rendered block that displays the DOI (Digital Object Identifier) citation for a post. 

**Features:**
- Automatically retrieves and displays DOI information
- Supports custom typography settings
- Configurable spacing (margin and padding)
- Text and link color customization
- Single instance per post (non-multiple)
- Anchor support for deep linking

**Technical Implementation:**
- Uses WordPress Block JSON configuration
- Server-side rendered via PHP
- Supports block context for post type and ID
- Built with TypeScript
- Follows modern WordPress block architecture

### Block Architecture

The blocks system uses:
- WordPress Block Metadata Collection for registration
- JSON-based block configuration
- Server-side rendering for dynamic content
- TypeScript for enhanced type safety
- Webpack for build process

## Inspector Sidebar Panel

The plugin adds a dedicated "Academic Identity" sidebar panel to the WordPress block editor, providing an interface for managing academic metadata.

### DataCite DOI Schema Panel

A specialized panel for managing DataCite DOI (Digital Object Identifier) metadata:

**Features:**
- JSON-based DOI schema input
- Real-time citation preview
- Automatic citation generation from DOI data
- Editable citation text
- Integration with post title and date
- Support for PRC's Open Science initiative

**Technical Implementation:**
- Built with React and WordPress components
- Uses WordPress Plugin API for registration
- Implements debounced updates for performance
- Integrates with WordPress post meta
- Supports custom PRC components (@prc/components)
- Real-time validation and preview

### Architecture

The inspector panel system uses:
- WordPress Plugin API for sidebar registration
- React hooks for state management
- WordPress data layer integration
- Custom PRC component library
- Webpack for build process

### Integration with Open Science

The Academic Identity panel supports PRC's commitment to open science and data accessibility, with direct links to documentation and best practices through the platform wiki.

## License

GPL-2.0-or-later

