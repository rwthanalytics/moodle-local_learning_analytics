# Learning Analytics for Moodle

This project offers a privacy-friendly Learning Analytics solution for Moodle. The plugins integrate into Moodle courses and offer statistics to teachers and students.

<p align="center">
  <img src="https://files.lfi.rwth-aachen.de/learning-analytics/2020-10-21-preview.gif" height="300">
</p>

This Learning Analytics solution consists of two plugins.

- [`local_learning_analytics`](https://github.com/rwthanalytics/moodle-local_learning_analytics): User Interface (this plugin), simply called `local` plugin in the docs.
- [`logstore_lanalytics`](https://github.com/rwthanalytics/moodle-logstore_lanalytics): Logs the events to the database, simply called `logstore` plugin in the docs.

To keep documentation in one place, you find all documentation in this repository.

----------

- [Installation](#installation)
  - [Download](#download)
  - [Activating the logstore](#activating-the-logstore)
  - [Import data from `logstore_standard`](#import-data-from-logstore_standard)
- [Configuration](#configuration)
  - [Plugin `logstore_lanalytics` configuration](#plugin-logstore_lanalytics-configuration)
  - [Plugin `local_learning_analytics` configuration](#plugin-local_learning_analytics-configuration)
- [Data storage](#data-storage)
  - [What is being stored?](#what-is-being-stored)
  - [Resulting storage size](#resulting-storage-size)
  - [Data Privacy](#data-privacy)
  - [Access / Capabilities](#access--capabilities)
- [Development](#development)
  - [Language support](#language-support)
  - [Changelog](#changelog)
  - [Third-party libraries and resources](#third-party-libraries-and-resources)
  - [Contributing](#contributing)
- [Provided by](#provided-by)
- [License](#license)

## Installation

Requirements:

- Moodle: Version 3.2 or higher (currently testing with 3.6 and 3.9)
- Database: MySQL/MariaDB or Postgres (other database types have not been tested)

### Download

First, download the dependency (`moodle-logstore_lanalytics`) and place the files in the `admin/tool/log/store/lanalytics` folder.

```
$ cd MOODLE_PATH
$ cd admin/tool/log/store
$ git clone https://github.com/rwthanalytics/moodle-logstore_lanalytics.git lanalytics
```

After that repeat the steps for this plugin and place the files in the `local/learning_analytics` folder.

```
$ cd MOODLE_PATH
$ cd local
$ git clone https://github.com/rwthanalytics/moodle-local_learning_analytics.git learning_analytics
```

The end result should look like this:

- `MOODLE_PATH/admin/tool/log/store/lanalytics` contains the contents of the `moodle-logstore_lanalytics` plugin
- `MOODLE_PATH/local/learning_analytics` contains the contents of the `moodle-local_learning_analytics` plugin

Now visit the Moodle administration page or the Moodle starting page and Moodle should detect the new plugins and install them. Alternatively, you can install the plugins via [CLI](https://docs.moodle.org/en/Administration_via_command_line). As both plugins are very modular, the local plugin comes with several subplugins (of type `lareport` and `lalog`)


### Activating the logstore

After installation you need to enable the logstore plugin.

1. Go to Moodle Site administration page
2. In the *Plugins* tab, scroll down to *Logging*
3. Click on *Manage log stores*
4. There should be a row for the installed `logstore` plugin with the name `Learning Analytics Log`
5. Click on the eye icon (`👁`) to enable the log store.

The log store is now activated and will log events.

After installation and activation of the logstore, there should be a link in each course menu called "Coure Statistics" leading to the following page (`COURSE_ID` is the course id of the corresponding course):

```
https://MOODLE_INSTALLATION/local/learning_analytics/index.php/reports/coursedashboard?course=COURSE_ID
```

### Import data from `logstore_standard`

After installation, all statistics are empty as no data has been logged so far. But your Moodle site might log data through Moodle's own logging system, the `logstore_standard_log`. The `logstore` plugin offers a simple way to import that data by using the [`import.php`](https://github.com/rwthanalytics/moodle-logstore_lanalytics/blob/master/cli/import.php) script. It can be called from the shell like this:

```
$ cd MOODLE_PATH/admin/tool/log/store/lanalytics
$ php cli/import.php
```

This will immediately start the import process. Instead you can also call `php cli/import.php --help` to see a list of possible options. You can for example only import the last `X` weeks or import only events starting from a specific ID to limit the amount of data you import.

## Configuration

You should configure both plugins. The logstore plugin has options related to logging data and writing data to the database. The local plugin has options related to displaying the data.

### Plugin `logstore_lanalytics` configuration

You can find the administration page of the logstore plugin at the same position where you activiated it:

> *Administration* -> *Plugins* -> *Logging* -> *Learning Analytics Log*

#### Options

The logstore has the following options:

- `log_scope`: One of `all`, `include`, `exclude`. Defines the scope of the logging process. By default, everything is logged.
  - Option `all`: Logs all events
  - Option `include`: Log events only in courses specified via `course_ids`
  - Option `exclude`: Log events *excluding* the courses specified via `course_ids`
- `course_ids`: To be used with the `log_scope` option `include` or `exclude` to only track specific courses. Example: `10,153,102`.
- `tracking_roles`: Define which roles should be tracked (whitelist) unless specified via `nontracking_roles`. This is useful if you only want to track specific roles (like students or guests). By default, all roles are tracked. Example: `student,guest`. See [Role Tracking](#roletracking) for more information.
- `nontracking_roles`: Define which roles should *not* be tracked. This is useful if you don't want to track specific roles (like managers or teachers). By default, no roles are ignored. Example: `teacher,editingteacher,manager`. See [Role Tracking](#roletracking) for more information.
- `buffersize`: Same as `buffersize` of other loggers. In case a single page fires more than one event, this is the number of events that will be buffered before writing them to database. Defaults to `50`.

#### Logging everything vs. only a few courses
When deciding on what to log, keep in mind that there are two plugins:

- The `logstore` plugin (this one) that decides which courses are logged.
- The `local` plugin that decides in which courses a link is added to the course navigation.

In case a teacher wants to use Learning Analytics while the semester is already running, you better already have activated the logging in before. That's why we recommend to activiate the logging for all courses by setting `log_scope` to `all` and then use the options of the `local` plugin to decide who has access to the user interface.

#### Role Tracking
<a name="roletracking"></a>
There are two settings to define which roles are to be tracked and which not. Specify the role by using the "shortname" (can be found via *Site Administration* -> *Users* tab -> *Permissions* category -> *Define roles*).

- `tracking_roles`: Whitelist (Only track these roles unless specified by `nontracking_roles`)
- `nontracking_roles`: Blacklist (don't track these roles)

The blacklist has priority over the whitelist. Keep in mind that a user can be a `teacher` in one course and a `student` in another course meanining that a user might be tracked in one course while being excluded from tracking in another.

If `tracking_roles` is not set, all roles are assumed to be tracked (unless roles are given in `nontracking_roles`). If `tracking_roles` and `nontracking_roles` are unset, all roles are tracked.

For an example, let's assume the following settings:

| Setting | Value|
|---|---|
| `tracking_roles` | `guest,student` |
| `nontracking_roles` | `teacher,manager` |

In words, this setting translates to:

> Only track users in the course that have the role `guest` or `student`, unless they also have the role `teacher` or `manager`.


### Plugin `local_learning_analytics` configuration

The settings page can be found in:

> *Site Administration* -> *Plugins* tab -> *Local plugins* category -> *Learning Analytics*

#### Options

The plugin has the following options:

- `status`: One of `show_if_enabled`, `show_courseids`, `show_always`, `hide_link`, `disable`. This value sets whether the user interface should be activated and whether a links should be shown in the navigation. By default, the link and page are visible if logging is enabled for the course. You can use this option, if you want to enabled logging in all courses, but only want to enable the user interface on specific courses.
  - Option `show_if_enabled`: Show navigation link and page if logging is enabled for the course
  - Option `show_courseids`: Show navigation link and page if course is specified below via course_ids
  - Option `show_always`: Show navigation link and page for all courses, even if logging is disabled for the course (only enable this, if you already logged data before)
  - Option `hide_link`: Hide navigation link but keep the page enabled for all courses (only if you know the link, you can access the page)
  - Option `disable`: Hide navigation link and disable the page for all courses. This will completly disable the User Interface for everyone.
  - Option `course_customfield`: Add `customfield` to all course settings. When selected teachers can select in their course settings if the link should be shown. You should read the notes [below](#customfield) before switching to this option.
- `course_ids`: To be used with the `status` option `show_courseids` to only show the UI in specific courses. Example: `10,153,102`.
- `navigation_position_beforekey`: Allows to specify where in the course navigation the link to the page is added. By default, the link is added before the first `section` node. Example value: `grades` to be shown before the link to grades. You can find the key of a node in the navigation by using the developer tools. Right-click on a link in the navigation, press *Inspect* and check the attribute `data-key` of the corresponding `a` element.
- `dataprivacy_threshold`: This value determines how many "data points" a "data set" needs to contain before the data is displayed. See the data privacy section below for more information. By default, the value is `10`.
- `student_rolenames`: In case the role(s) for students/users in a course is not `student`, you can specify the corresponding role name. In case there are multiple roles that students have, use a single comma. Example: `student,customrole`. By default, the value is `student`.
- `student_enrols_groupby`: Option to allow merging of multiple courses with the same `shortname` or `fullname` in the parallel/previously heard courses. By default, the value is `course->id` which will not merge any courses by comparing their name.
- `setting_dashboard_boxes`: Determines which boxes are displayed in the dashboard, in which order and how big the boxes are. The specification is in the format `reportname:width`, separated by commas. A line has a maximum width of 12, after which it breaks. Example: `learners:8,activities:4` displays two boxes in the dashboard, where the first one is much wider than the second one. The value only needs to be changed if other subplugins are installed or if the layout of the dashboard needs to be changed.

#### Option `course_customfield`
<a name="customfield"></a>

**This feature requires Moodle 3.7. In older version of Moodle, this option will be unavailable.** As described above, the option `course_customfield` adds a Moodle customfield to the settings page of your course. Technical details how this works can be found in the Moodle Wiki on [Custom fields API](https://docs.moodle.org/dev/Custom_fields_API).

Before switching to this option, you should be aware of the following:

- When switching from another option to `course_customfield`, the courses specified in `course_ids` will automatically be set to activated.
- When switching from `course_customfield` to another option, the (previously created) customfield **will be deleted and with it the information which course had this setting enabled**. That means, before switching from this setting back to another setting, you want to take a look at the database table `customfield_data` and see which course had this setting enabled (course = `instanceid` column, activiated = `intvalue` column)

Unfortunately, Moodle does not allow to use multi-language strings here, therefore the option will use the primary language of the Moodle platform here. In case you have the `multilang` filter activated for content and titles, the function will concatenate existing language strings to provide a similar experience to the use of normal language strings. You can always edit the customfield on your own. You find the options under *Site administration* -> *Courses* -> *Course custom fields*. You can change the description of the field as well as the name of the category. But you should not delete the category or customfield and should not add additional custom fields to the category.

#### Option `dataprivacy_threshold`

Our plugin logs no personal data. For privacy reasons, you probably still want to restrict when aggregated data is shown. You can do this by setting the `dataprivacy_threshold` option. This option will hide datasets with less than `dataprivacy_threshold` data points. Depending on what is shown, the dataset will be completly hidden or shown as `< 10` (with the threshold being `10`). Whether data is hidden or not depends on the following:

- Analytics related to data that is unknown to the teacher (like what other courses the users are enroled into) is not displayed if the number of data points is below the threshold.
- Analytics related to data that is known to the teacher (like his own learning material) is displayed as `< THRESHOLD`

You should ask your data privacy officer what value should be used here. From our experience, `5` or `10` are common values.

##### Examples

Let's consider an example for each case. Assuming, the value is set to `10`, this will have the following effects:

- **Unknown data**: In the Learners report that shows the number of courses users have heard before, only courses with at least (`>=`) `10` users in common will be shown. Courses with less than (`<`) `10` users will not be shown at all.
- **Known data**: In the activities report that shows the number of clicks for each learning materials, clicks are shown as `< 10` if the number of clicks is less than `10`.

##### Aggregated Data

In addition, **aggregated data** (like the number of total clicks on all quizzes) is rounded (down) to a multiple of the threshold. By doing that, the teacher cannot use the aggregated data points to esimated the data points of a specific activity.


## Data storage

### What is being stored?

When an event is triggered inside of Moodle, the following data is logged by the `logstore` plugin:

#### Table: `logstore_lanalytics_log`

| Field name | Type | Explanation |
|---|---|---|
| id | BIGINT |  |
| eventid | INT | Type of action, e.g. "Resource viewed" |
| timecreated | BIGINT | Date and time, exact to the second |
| courseid | BIGINT | Corresponding course |
| contextid | BIGINT | Corresponding context, e.g. ID of the resource that was viewed |
| device | SMALLINT | Operating system and browser, e.g. "Windows 10" and "Firefox", detailed browser or operating system versions are not stored |

In addition, there are helper tables, that do not store data-privacy related data and only exist to speed up queries or to minimize storage requirements.

#### Table: `logstore_lanalytics_evtname`

This table serves as a reference for the type of actions. It maps the `eventid` from the above table to the actual `eventname`. The table contains only two columns and is simply used to minimize the needed storage space inside the database.

#### Table: `lalog_browser_os`

This tables serves as a memory for devices per course. It uses the device from `logstore_lanalytics_log` and stores an aggregation per course. This is simply done to minimize requests to the `logstore_lanalytics_log` table.


### Resulting storage size

Our Learning Analytics plugins were developed to log as little data as possible and to be as space-efficient as possible. A single table row needs 38 byte. Additionally, you should expect that you need roughly twice as much space after incorporating storage space for database indexes. All other (helper) tables are not worth mentioning and will need less than a few MB of space.

Some real numbers: The plugin has been used for multiple semesters at the RWTH Aachen University. The RWTH has roughly 45,000 students. In one year (two consecutive semesters), about 115 million rows were inserted into the log table. The needed storage was around 6.9 GB (including data and indices).

### Data Privacy
This plugin was developed with data privacy in mind. It does not log any user ids. All data is logged anonymously. As this plugin logs no personal data, you don't need the consent of users to log the data.

After consultation of our data privacy officer, the following information was added to our data privacy statement (Datenschutzerklärung):

> Im Rahmen von Learning Analytics werden anonymisierte Statistiken zum Zugriff auf Lernräume gespeichert. Bei jedem Aufruf in Moodle werden dabei folgende Daten ohne einen Bezug zu Nutzenden geloggt:
> 
> - Typ der Aktion (z.B. ob ein Quiz durchgeführt wurde oder ein PDF runtergeladen wurde)
> - Uhrzeit (sekundengenau)
> - ID des Lernraums, in dem die Aktion durchgeführt wurde
> - Betroffener Kontext (z.B. die ID des Quiz, das gestartet wurde)
> - Genutztes Betriebssystem (z.B. Windows oder Linux) und genutzter Browser (z.B. Firefox oder Edge), detaillierte Versionen werden nicht gespeichert
> 
> Sämtliche Daten werden anonym gespeichert und lassen keinen Rückschluss auf Nutzende zu. Die Statistiken können in den teilnehmenden Lernräumen über einen Link in der Navigation von allen Teilnehmenden der Veranstaltung abgerufen werden. Aggregierte Daten werden daher in den Statistiken erst dann angezeigt, wenn mindestens 10 Datensätze vorhanden sind. Andernfalls wird nur "< 10" angegeben.


### Access / Capabilities

There is a single capability `local/learning_analytics:view_statistics` that decides who is allowed to view the course statistics. By default, the following roles have the cabability (as defined in [access.php](db/access.php)):

- `student`
- `teacher`
- `editingteacher`
- `manager`

That means that, by default students and teachers both have access to the statistics. This was done on purpose for maximal transparency.

## Development

### Language support
The plugin comes with German and English language files.

### Changelog
Every change is documented via GIT. In addition, we create a summary for every version in separate changelog files. All important changes will be documented there. We follow the guides from [keepachangelog](https://keepachangelog.com/).

Each of the plugins has a separate changelog and separate versioning. You can find the changelogs of the projects here:

- `local_learning_analytics`: [CHANGELOG](./CHANGELOG.md)
- `logstore_lanalytics`: [CHANGELOG](https://github.com/rwthanalytics/moodle-logstore_lanalytics/blob/master/CHANGELOG.md)

### Third-party libraries and resources

The following third-party libraries and resources are used in this project:

- Plotly.js: <https://plotly.com/javascript/>
- Material.io icons: <https://material.io/resources/icons/>

See [CREDITS](./CREDITS.md) for more information including full licenses.

### Contributing
Checkout the [contributing guide](./CONTRIBUTING).

## Provided by

<a href="https://lfi.rwth-aachen.de/"><img src="https://files.lfi.rwth-aachen.de/learning-analytics/lfi.png" alt="Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University" height="80"></a> <a href="https://cls.rwth-aachen.de/"><img src="https://files.lfi.rwth-aachen.de/learning-analytics/cls.png" alt="Center für Lern- und Lehrservices - RWTH Aachen University" height="80"></a>

## License
[GPL](./LICENSE)
