# TODO

- [ ] **Add title to page**
- [ ] **Add English text for all missing strings**
- [ ] Create report "Weekly heatmap"
- [ ] **Make sure only people enrolled can see the statistics**
  - [ ] use capabilities for this
- [ ] **Make `logstore_lanalytics` a dependency of this plugin**
- [ ] **Add event `view_analytics` (or a similar event)**
- [ ] Figure out how we need to shift dates when showing hourly data: https://docs.moodle.org/dev/Time_API
- [ ] Check if the `requires` version in the plugins (and subplugins) are correct (check for which Moodle version the plugin still works)
- [ ] **Give option to diable "Set course to compare" (as this makes no sense without data currently)**
- [ ] Improve "course to compare" option of graph: Wording, preselect course if already given


## GDPR
- [ ] Create "Verfahrensverzeichnis"
- [ ] Create form for getting consent
- [ ] Implement setting to only track specific users/courses
- [ ] **Implement Moodle Privacy providers**
- [ ] Make sure that groups < X (aks GDPR officer) are not shown
  - [ ] Ask GDPR officer what `X` is
  - [ ] Is is okay to still track the data (even when groups < `X`) but don't display results for smaller courses
    - [ ] If necessary, change tracking algorithm to check size of course
  - [ ] Change accordingly, examples:
    - [ ] learners/courseparticipation list
    - [ ] learners language/countries list

## Big course test
- [ ] Generate a lot of data to test how the dashboard (especially the timecreated query) performs for big courses (> 1m events in a single course, power law regarding users)

## Release
- [ ] **Check coding guidelines**
- [ ] Check hurdles for Moodle store
- [ ] Decide the name: rwthanalytics? aixanalytics? 