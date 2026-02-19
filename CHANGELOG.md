# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.1] - 2026-02-19

### Fixed
- Fixed regex lookbehind compatibility issue for PHP 7.2+ (variable-length lookbehind not supported)
- Fixed `array_pop` bug when handling standalone `[]` lines in markdown cleanup
- Fixed regex inconsistency in indentation reduction (now correctly matches 5+ spaces)
- Added positive lookbehind `(?<=\S)` to preserve leading indentation in markdown (prevents destruction of code block indentation)

### Added
- Markdown whitespace cleanup feature to reduce token waste:
  - Removes excessive blank lines (max 2 consecutive)
  - Merges split markdown links/images
  - Removes excessive indentation (beyond 4 spaces, except for lists)
  - Collapses multiple spaces to single space (preserves URLs and code blocks)

## [0.1.0] - Initial Release

### Added
- Initial release
- `/llms.txt` endpoint with llms.txt spec compliance
- Per-route Human/Machine markdown views
- `.md` extension support (e.g. `/pricing.md`)
- Query parameter support (`?view=machine`)
- Accept header support (`Accept: text/markdown`)
- Use-case presets (Docs, Business, E-commerce, Education, Legislation)
- Configurable cache headers for markdown responses
- Main content selector for token reduction
- Link header middleware for agent discovery
