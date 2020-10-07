# TODO

- [x] **Add title to page**
- [x] **Make sure only people enrolled can see the statistics**
  - [x] use capabilities for this -> by default, students and teachers can view statistics
- [x] **Make `logstore_lanalytics` a dependency of this plugin**
- [x] **Give option to disable "Set course to compare" (as this makes no sense without data currently)**
- [x] Make "Activities" box link to activity report even if no recently used activiy exists
- [x] Report: Browser/mobile usage in course
  - [x] Integrate Moodle-API into desktop/mobile statistics
  - [x] Add language keys
- [x] **Add event `view_analytics` (or a similar event)**
- [x] Check localization
  - [x] **Add English text for all missing strings**
  - [x] **Add German texts for all missing strings**
- [x] Update README
- [x] Improve README
  - [x] Add info on installation
    - [x] Enable logstore
    - [x] Setup logstore (go over settings)
  - [x] Add info on settings of main plugin
    - [x] privacy threshold
    - [x] compare_options
- [x] Add information to tables: "Only data >= X is shown"
  - [ ] Mabye add this information on the dashboard (?)
- [x] Only show dashboard link in selected courses
- [x] Update README.md in reports folder
- [x] Don't show empty tables or empty data plots
- [x] Tracking roles whitelist (in addition to blacklist)
  - [x] Provide better options for which courses are logged:
    - [x] Option "Log"
      - [x] All courses
      - [x] Only the courses specified below (whitelist)
      - [x] Only the courses NOT specified  below (blacklist)
- [x] Local plugin:
  - [x] If logstore is disabled, don't show in the navigation
  - [x] Show analytics: (enables the Analytics page and add the link to the navigation)
    - [x] Show navigation link if logging is enabled for the course
    - [x] Show navigation link if course is specified below via course_ids
    - [x] Show navigation link even if logging is disabled for the course (only enable this, if you already logged data before)
    - [x] Hide navigation link but keep the page enabled (only if you know the link, you can access Analytics)
    - [x] Hide navigation link and also disable the page itself (even if logging happens in the background)
- [x] Make table with loggable courseids (instead of single setting)
  - After checking, I'll leave it like this for now as it's probably faster this way. The advantage will probably only come when there are a lot of courses added in this way...
- [x] Update README with new settings
- [x] Logstore plugin: Make sure it also works on its own (without this plugin)
  - [x] Make sure it only loads `lalog` plugins if this plugin is installed
- [x] Page that shows what kind of data is being tracked and whether one is being tracked => only anonymous data is being saved, not implemented
- [ ] External database support => no feedback from ITC

## Logstore
- [x] **Implement setting to only track specific courses** (as specified in `lanalytics/course_ids`)
- [x] **Don't track teachers for now**
- [x] Detect Moodle-API use

## GDPR
- [x] Create "Verfahrensverzeichnis"
- [x] Datenschutzerklärung erstellen
- [ ] **Implement Moodle Privacy providers** (later, when we log personalized data)
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

## WS 20/21
- [x] Change activities report
  - [x] ~~Vertical list?~~ => Not for now
  - [x] Use vertical divider for each section
  - [x] Add search
  - [x] ~~Make plot at the top scrollable (?)~~ => Probably not for now
  - [x] ~~Add "remove filter" link when filter was applied~~ => not needed for now (I guess?)
- [ ] Check out all "TODO"s in code, especially "TODO lang" for language strings
  - [ ] Fix "TODO lang"
  - [ ] Check all other TODOs
  - [ ] Check if existing lang-strings should be replaced by Moodle native strings
    - [ ] "Registrierte Nutzer"
    - [ ] "Lernende"
    - [ ] "Quizze" / "Aufgaben"
    - [ ] "Punkte" ...
- [x] "Weekly heatmap" instead of browsers
  - [x] Figure out how we need to shift dates when showing hourly data: https://docs.moodle.org/dev/Time_API
- [x] New report for quiz statistics
  - [x] Add new report IDs (to the logged reports)
- [x] Fix problems of "Parallel heard / So far heard courses"
- [x] Custom course settings to give option
- [ ] Database changes (wait for ITC answer)
  - [ ] Change OS/browser column to a single column and just use 100 values for browser and 100 for OS (?)
  - [ ] Remove objectid, this would not allow looking "into" modules anymore, but this will probably not happen anyway
- [ ] UX
  - [x] Embed help into website (FAQ?)
  - [ ] Communication to users: Not possible to show more data due to privacy (and what data is being logged)
  - [x] Don't make icons on dashboard clickable (confusion to users, looks like there are two clickable items per field)
  - [x] Add loading animation to plots
- [ ] Remove browser report / replace with heatmap
- [ ] For other universities
  - [ ] Postgres: Check if database still works
  - [ ] Log: Improve import script to specify number of weeks to import

## WS 20/21 Before publish
- [ ] Privacy Provider
  - [ ] Check implementation, we probably don't need one as there is no personal data in there...
- [ ] HTML elements
  - [ ] Remove div-soup on top-level and use Moodle div's instead
- [ ] Administration
  - [ ] Option: Start of the week: Sunday/Monday
  - [ ] Option: Number of weeks shown in dashboard
- [ ] Add uninstall script that removes our tables
  - [ ] Removes all creates tables
  - [ ] Removes all data
  - [ ] Removes customfield
  - [ ] Removes user tour
- [ ] Add new reports to upgrade.php script
- [ ] Explain capability "learning_analytics:view_statistics" in README (by default students can view statistics)
- [ ] Accessibility: Add alt texts to images/icons, add aria-labels where needed
- [ ] Check Moodle guidelines

## Big course test
- [x] Generate a lot of data to test how the dashboard (especially the timecreated query) performs for big courses (> 1m events in a single course, power law regarding users)
- [ ] Also generate users

## For later...
- [ ] Describe subplugins in README
  - [ ] `lareport`
  - [ ] `lalog`
- [ ] Check if the `requires` version in the plugins (and subplugins) are correct (check for which Moodle version the plugin still works)
- [ ] Ideas
  - [ ] Bring back course to compare?
- [x] Provide an option to allow or disable tracking teachers
- [x] ~~"Consent-API"~~ Not until Moodle supports optional policies...
- [ ] Remove any magic numbers and strings and use constants file
- [ ] Put subplugin settings (like `allow_dashboard_compare`) in the actual subplugins (check if that really makes sense)
- [x] Check and update CLI scripts of logstore
- [ ] Make reports to show configurable on dashboard
  - [ ] Each report should have a "small" stats to be shown on dashboard and a "report" page
- [ ] Create a special class directory for pages (classes/pages) so that they don't get mixed up with other classes
