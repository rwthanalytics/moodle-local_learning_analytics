# TODO

- [ ] Make sure only people enrolled can see the statistics
- [ ] Make `logstore_lanalytics` a dependency of this plugin
- [ ] Add event `view_analytics` (or a similar event)
- [ ] Figure out how we need to shift dates when showing hourly data: https://docs.moodle.org/dev/Time_API
- [ ] Check if the `requires` version in the plugins (and subplugins) are correct (check for which Moodle version the plugin still works)


## GDPR
- [ ] Verfahrensverzeichnis ausfüllen
- [ ] Create form for getting consent
- [ ] Implement setting to only track specific users/courses
- [ ] Moodle Privacy providers implementieren
- [ ] Make sure that groups < X (aks GDPR officer) are not shown
  - [ ] Ask GDPR officer what `X` is
  - [ ] Change accordingly, examples:
    - [ ] learners/courseparticipation list
    - [ ] learners language/countries list

## Big course test
- [ ] Generate a lot of data to test how the dashboard (especially the timecreated query) performs for big courses (> 1m events in a single course, power law regarding users)

## Release
- [ ] Check coding guidelines
- [ ] Check hurdles for Moodle store