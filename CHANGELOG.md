# Changelog
All notable changes to this project will be documented in this file.

## [0.8.0] - TODO
### Fixed
- Support for Postgres added
- If a person is enrolled multiple times into a course, they are counted only once now.
### Removed
- Removed notice in the UI introduced in version `v0.7.3`

## [0.7.4] - 2020-05-15
### Fixed
- Notice regarding reset of browser/OS report is also shown in test environment and when there is no data to show

## [0.7.3] - 2020-05-13
Note: This version was released and tagged as `v0.7.3` but accidentally marked as `v0.8.0` in the `version.php`.
### Fixed
- Clears `browser_os` logs (if upgraded) due to errors in the analysis (see logstore plugin)
- Added related notice in the UI (only to `RWTHmoodle` page)

## [0.7.2] - 2020-04-27
### Fixed
- Using `format_string` before displaying courses in learners report
- Invisible courses and courses without startdate ae excluded from learners report

## [0.7.1] - 2020-04-27
### Fixed
- Learners report uses option `student_rolenames` for calculating total number of students
- Added missing language keys for learners page

## [0.7.0] - 2020-04-24
### Added
- Option `student_rolenames` added. In case the system uses special names for users (instead of the default role name `student`), this setting can be used to configure the option. So far, the learners report was not able to show data in RWTHmoodle due to this.
- Colors added for activities report: `assign`, `pdfannotator`
### Fixed
- Students are now able to filter by module type in activities report again

## [0.6.1] - 2020-04-20
### Removed
- Option `allow_dashboard_compare` is also removed form the settings page. Previously, the option was still shown.

## [0.6.0] - 2020-04-20
### Changed
- Changed how activities report works. It now uses the `get_fast_modinfo` function of Moodle to list courses. This allows a better order and uses the Moodle cache.
### Removed
- Option `allow_dashboard_compare` is removed for now as it is currently in an unstable state.

## [0.5.0] - 2020-04-03
### Added
- Settings added: `status` and `course_ids`
  - `status` defines in which courses the Learning Analytics UI is linked/shown
  - `course_ids` can be used to only show the UI in specific courses
### Changed
- If the `lanalytics` logstore is disabled and the default `status` option is selected, no UI will be linked/shown.
- Dependency `logstore_lanalytics` changed to: `2020040300` (`v0.4.0`)

## [0.4.2] - 2020-03-31
### Fixed
- Hard-coded database prefix `mdl_` removed

## [0.4.2] - 2020-03-31
### Changed
- CSS change due to RWTH layout design

## [0.4.1] - 2020-03-30
### Changed
- Minor language string changes

## [0.4.0] - 2020-03-30
### Changed
- Tables are changed to better fit with RWTH layout
- Improved activities report
  - Before this version, "Unknown" was shown for non-default activities, now the names for all activites are displayed
  - Added icons to the modname table

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
