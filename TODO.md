# TO*DOs (spelled like this, so it doesn't show when searching for the word...)

## WS 20/21 Before publish
- [x] License
  - [x] Check license
- [x] Create "demo course" for screenshots/documentation purposes
  - [x] Constant date on dashboard page, with faked number of students, accesses, etc...
- [ ] Make a nice README
  - [x] Add data privacy information and German data privacy note for Datenschutzerklärung
  - [ ] Screenshots of pages
  - [x] More information on data storage/use
- [x] customfield rename to "course statistics"
- [x] Heatmap
  - [x] Heatmap with zero hits everywhere looks odd...
- [x] Contributing
  - [x] Explain versions
- [x] Privacy Provider
  - [x] Check implementation, we probably don't need one as there is no personal data in there...
- [x] HTML elements
  - [x] Remove div-soup on top-level and use Moodle div's instead
- [x] Add uninstall script that removes our tables
  - [x] Removes all creates tables -> this is actually done automatically by Moodle
  - [x] Removes all data -> same
  - [x] Removes customfield
  - [x] Removes user tour
- [ ] Add new reports to upgrade.php script
- [x] ~~Explain capability "learning_analytics:view_statistics" in README (by default students can view statistics)~~ => it's already in there

## WS 20/21 Before Moodle store
- [ ] Wording: "Learning Analytics Angebot" / "Modul" / "Service"
- [x] Test plugin with more multi-language content and check where to use `format_string` when printing activity names
- [ ] Administration
  - [ ] Option: Start of the week: Sunday/Monday => respect Setting of Calender in Moodle
  - [ ] Option: Number of weeks shown in dashboard
- [x] Accessibility: Add alt texts to images/icons, add aria-labels where needed
  - [x] Check if color blindness is respected
  - [x] Give graph meaningful aria labels
  - [x] Add aria-hidden to data that is not needed for screen readers
- [x] Check Moodle guidelines => we should be fine, expect for some naming conventions, but we ignore them for now...
- [ ] If customfield setting is used, add information to course backup / import (if that is even possible...)

-------------------

## For later
- [ ] Tests
- [ ] Ideas
  - [ ] Bring back course to compare?
- [x] Provide an option to allow or disable tracking teachers
- [x] ~~"Consent-API"~~ Not until Moodle supports optional policies...
- [ ] Remove any magic numbers and strings and use constants file
- [x] ~~Put subplugin settings (like `allow_dashboard_compare`) in the actual subplugins (check if that really makes sense)~~ -> This would just make it harder for administrators...
- [x] Check and update CLI scripts of logstore
- [ ] Create a special class directory for pages (classes/pages) so that they don't get mixed up with other classes

## For much later: API structure
- [ ] Make reports to show configurable on dashboard
  - [ ] Each report should have a "small" stats to be shown on dashboard and a "report" page
- [ ] Users of our plugin still have the `lalog_browser_os` table installed (RWTH + HRW)
  - [ ] Plan: Get the data and then put uninstall routine into upgrade
  - [ ] Remove this in a future upgrade?
  - [ ] Rethink the current structure of sub plugins?

-------------------

## WS 20/21
- [x] Logstore plugin: Database changes (wait for ITC answer)
  - [x] Change OS/browser column to a single column and just use 100 values for browser and 100 for OS (?)
  - [x] Remove objectid, this would not allow looking "into" modules anymore, but this will probably not happen anyway
  - [x] Remove `userid`
- [x] Bug: Loading indicator wird beim resizen sichtbar
- [x] UX
  - [x] Finish help page
  - [x] Embed help into website (FAQ?)
  - [x] Communication to users: Not possible to show more data due to privacy (and what data is being logged)
  - [x] Don't make icons on dashboard clickable (confusion to users, looks like there are two clickable items per field)
  - [x] Add loading animation to plots
- [x] Remove browser report
- [x] For other universities
  - [x] Postgres: Check if database still works
    - [x] TO DO merge from Thorbens branch
  - [x] Log: Improve import script to specify number of weeks to import
- [x] Change activities report
  - [x] ~~Vertical list?~~ => Not for now
  - [x] Use vertical divider for each section
  - [x] Add search
  - [x] ~~Make plot at the top scrollable (?)~~ => Probably not for now
  - [x] ~~Add "remove filter" link when filter was applied~~ => not needed for now (I guess?)
- [x] Check out all "TO DO"s in code, especially "TO DO lang" for language strings
  - [x] Fix "TO DO lang"
  - [x] Add a small info text to each report to give an overview
    - [x] Activities: Small text explaining that the user can also filter by clicking on the names
    - [x] Quiz, assign: Short explanation
    - [x] Weekheatmap: Short explanation
    - [x] Learners: Short explanation
  - [x] Check all other TO DOs
  - [x] Check if existing lang-strings should be replaced by Moodle native strings
    - [x] "Registrierte Nutzer"
    - [x] "Lernende"
    - [x] "Quizze" / "Aufgaben"
    - [x] "Punkte" ...
- [x] Dashboard last changes
  - [x] Change "quiz/assign" "<1" look, more it more to the middle (?)
- [x] "Weekly heatmap" instead of browsers
  - [x] Figure out how we need to shift dates when showing hourly data: https://docs.moodle.org/dev/Time_API
- [x] New report for quiz statistics
  - [x] Add new report IDs (to the logged reports)
- [x] Fix problems of "Parallel heard / So far heard courses"
- [x] Custom course settings to give option

---------------

## Pre SS 2020
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
  - [x] Mabye add this information on the dashboard (?)
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
- [x] External database support => no feedback from ITC (not needed I guess..)

## Logstore
- [x] **Implement setting to only track specific courses** (as specified in `lanalytics/course_ids`)
- [x] **Don't track teachers for now**
- [x] Detect Moodle-API use

## GDPR
- [x] Create "Verfahrensverzeichnis"
- [x] Datenschutzerklärung erstellen
- [x] Implement Moodle Privacy providers (new item further below)
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
