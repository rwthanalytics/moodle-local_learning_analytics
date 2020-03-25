# Changelog
All notable changes to this project will be documented in this file.

## [0.3.0] - 2020-03-25
### Added
- Option `navigation_position_beforekey` added to specify where the link to the dashboard in the navigation should be added

### Changed
- Activities report was only available for teachers. Report is now available for everyone, but hidden activities are excluded for students.
- If `navigation_position_beforekey` is not specified, the plugin will add the link above the first `TYPE_SECTION` node. If there is no `TYPE_SECTION` node in the navigation, the link will be added at the end.

## [0.2.1] - 2020-03-24
### Fixed
- Added missing language strings

## [0.2.0] - 2020-03-24
First public release.
