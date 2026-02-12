# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
