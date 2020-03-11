# TODO

- [x] **Add title to page**
- [ ] **Add English text for all missing strings**
- [ ] Create report "Weekly heatmap" -> better: browser/mobile usage in course
- [x] **Make sure only people enrolled can see the statistics**
  - [x] use capabilities for this -> by default, students and teachers can view statistics
- [x] **Make `logstore_lanalytics` a dependency of this plugin**
- [ ] **Add event `view_analytics` (or a similar event)**
- [ ] Figure out how we need to shift dates when showing hourly data: https://docs.moodle.org/dev/Time_API
- [ ] Check if the `requires` version in the plugins (and subplugins) are correct (check for which Moodle version the plugin still works)
- [x] **Give option to disable "Set course to compare" (as this makes no sense without data currently)**
- [ ] Improve "course to compare" option of graph: Wording, preselect course if already given
- [x] Make "Activities" box link to activity report even if no recently used activiy exists


## GDPR
- [ ] Create "Verfahrensverzeichnis"
- [ ] Create form for getting consent
- [ ] Implement setting to only track specific users/courses
- [ ] **Implement Moodle Privacy providers**
- [x] Make sure that groups < X (aks GDPR officer) are not shown
  - [X] Ask (meeting 2020-03-10) what `X` is, configurable -> `10` by default
  - [X] Is is okay to still track the data (even when groups < `X`) but don't display results for smaller courses
    - [X] If necessary, change tracking algorithm to check size of course
  - [x] Change accordingly, examples:
    - [x] learners/courseparticipation list
    - [x] learners language/countries list

## Big course test
- [ ] Generate a lot of data to test how the dashboard (especially the timecreated query) performs for big courses (> 1m events in a single course, power law regarding users)

## Release
- [x] **Check coding guidelines** => Done, but we might to do this again...
- [x] Check hurdles for Moodle store
- [x] Decide the name: rwthanalytics? aixanalytics? => decide later