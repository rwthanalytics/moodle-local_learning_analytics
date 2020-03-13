# TODO

- [x] **Add title to page**
- [x] **Make sure only people enrolled can see the statistics**
  - [x] use capabilities for this -> by default, students and teachers can view statistics
- [x] **Make `logstore_lanalytics` a dependency of this plugin**
- [x] **Give option to disable "Set course to compare" (as this makes no sense without data currently)**
- [x] Make "Activities" box link to activity report even if no recently used activiy exists
- [ ] Report: Browser/mobile usage in course
  - [ ] Integrate Moodle-API into desktop/mobile statitics
- [ ] **Add event `view_analytics` (or a similar event)**
- [ ] **Add English text for all missing strings**
- [ ] Update README
- [ ] Add information to tables: "Only data > 10 is shown"

## Logstore
- [ ] Implement setting to only track specific courses
- [ ] Don't track teachers for now
- [ ] Detect Moodle-API use

## GDPR
- [ ] Create "Verfahrensverzeichnis"
- [ ] Datenschutzerklärung erstellen
- [ ] **Implement Moodle Privacy providers**
- [x] Make sure that groups < X (aks GDPR officer) are not shown
  - [X] Ask (meeting 2020-03-10) what `X` is, configurable -> `10` by default
  - [X] Is is okay to still track the data (even when groups < `X`) but don't display results for smaller courses
    - [X] If necessary, change tracking algorithm to check size of course
  - [x] Change accordingly, examples:
    - [x] learners/courseparticipation list
    - [x] learners language/countries list

## Release
- [x] **Check coding guidelines** => Done, but we might to do this again...
- [x] Check hurdles for Moodle store
- [x] Decide the name: rwthanalytics? aixanalytics? => decide later

-------------------------------

# TODOs after first use

## Other report ideas
- [ ] "Weekly heatmap"
  - [ ] Figure out how we need to shift dates when showing hourly data: https://docs.moodle.org/dev/Time_API

## Big course test
- [x] Generate a lot of data to test how the dashboard (especially the timecreated query) performs for big courses (> 1m events in a single course, power law regarding users)
- [ ] Also generate users

## For later...
- [ ] Check if the `requires` version in the plugins (and subplugins) are correct (check for which Moodle version the plugin still works)
- [ ] Improve "course to compare" option of graph: Wording, preselect course if already given
- [ ] Provide an option to allow or disable tracking teachers
- [ ] "Consent-API"
- [ ] Remove any magic numbers and strings and use constants file
- [ ] Put subplugin settings (like `allow_dashboard_compare`) in the actual subplugins
- [ ] Check and update CLI scripts of logstore
- [ ] Make reports to show configurable on dashboard
  - [ ] Each report should have a "small" stats to be shown on dashboard and a "report" page
- [ ] Create a special class directory for pages (classes/pages) so that they don't get mixed up with other classes