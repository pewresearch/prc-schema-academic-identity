# @prc/schema-academic-identity

## 1.0.3

### Patch Changes

- 1d96db5: These plugins now require the renamed PRC Shared plugin, `prc-shared`, instead of `prc-scripts`.
- f8730c1: PRC Shared now loads the shared PRC primitives library for every PRC plugin when the plugins run outside PRC Platform. Plugins no longer bundle their own copy, so standalone installs need PRC Shared active.
