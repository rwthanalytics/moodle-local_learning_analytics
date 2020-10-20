# Learning Analytics for Moodle

This Learning Analytics solution consists of two plugins:

- [`local_learning_analytics`](https://github.com/rwthanalytics/moodle-local_learning_analytics): User Interface (this plugin)
- [`logstore_lanalytics`](https://github.com/rwthanalytics/moodle-logstore_lanalytics): Logs the events to the database

The second plugin (`logstore`) is a dependecy of the first one. To keep documentation in one place, you find all documentation in this repository.

## Installation

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

After installation, there should be a link in each course menu called "Learning Analytics" leading to the following page (`COURSE_ID` is the course id of the corresponding course):

```
https://MOODLE_INSTALLATION/local/learning_analytics/index.php/reports/coursedashboard?course=COURSE_ID
```

## Activate the logstore

After installation you need to enable the logstore plugin.

1. Go to Moodle Site administration page
2. In the *Plugins* tab, scroll down to *Logging*
3. Click on *Manage log stores*
4. There should be a row for the installed `logstore` plugin with the name `Learning Analytics Log`
5. Click on the eye icon (`👁`) to enable the log store.

The log store is now activated and will log events.

## Configuration

Optionally, you can configure both plugins. The logstore plugin has options related to logging data and writing data to the database. The other plugin has options related to displaying the data.

### Logstore Plugin (`moodle-logstore_lanalytics`)

You can find the same page as mentioned above (where the plugin is activated). The logstore has the following options:

- `log_scope`: One of `all`, `include`, `exclude`. Defines the scope of the logging process. By default, everything is logged.
  - Option `all`: Logs all events
  - Option `include`: Log events only in courses specified via `course_ids`
  - Option `exclude`: Log events *excluding* the courses specified via `course_ids`
- `course_ids`: To be used with the `log_scope` option `include` or `exclude` to only track specific courses. Example: `10,153,102`.
- `tracking_roles`: Define which roles should be tracked (whitelist) unless specified via `nontracking_roles`. This is useful if you only want to track specific roles (like students or guests). By default, all roles are tracked. Example: `student,guest`. See [Role Tracking](#roletracking) for more information.
- `nontracking_roles`: Define which roles should *not* be tracked. This is useful if you don't want to track specific roles (like managers or teachers). By default, no roles are ignored. Example: `teacher,editingteacher,manager`. See [Role Tracking](#roletracking) for more information.
- `buffersize`: Same as `buffersize` of other loggers. In case a single page fires more than one event, this is the number of events that will be buffered before writing them to database. Defaults to `50`.

### Local Plugin (`moodle-logstore_lanalytics`)

The settings page can be found in *Site Administration* -> *Plugins* tab -> *Local plugins* category -> *Learning Analytics*. The plugin has the following options:

- `status`: One of `show_if_enabled`, `show_courseids`, `show_always`, `hide_link`, `disable`. This value sets whether the user interface should be activated and whether a links should be shown in the navigation. By default, the link and page are visible if logging is enabled for the course. You can use this option, if you want to enabled logging in all courses, but only want to enable the user interface on specific courses.
  - Option `show_if_enabled`: Show navigation link and page if logging is enabled for the course
  - Option `show_courseids`: Show navigation link and page if course is specified below via course_ids
  - Option `show_always`: Show navigation link and page for all courses, even if logging is disabled for the course (only enable this, if you already logged data before)
  - Option `hide_link`: Hide navigation link but keep the page enabled for all courses (only if you know the link, you can access the page)
  - Option `disable`: Hide navigation link and disable the page for all courses. This will completly disable the User Interface for everyone.
  - Option `course_customfield`: Add `customfield` to all course settings. When selected teachers can select in their course settings if the link should be shown. Important information:
    - When switching from another option to this option, the courses specified in `course_ids` will automatically be set to activated.
    - When switching from this option to another option, the (previously created) customfield will be deleted and with it the information which course had this setting enabled. That means, before switching from this setting back to another setting, you want to take a look at the database table `customfield_data` and see which course had this setting enabled (course = `instanceid` column, activiated = `intvalue` column)
    - The created customfield (checkbox) and the corresponding category ("Learning Analytics") can be renamed by you. You can also change the description. But you should not delete the category or customfield as this option is relying on their presence.
- `course_ids`: To be used with the `status` option `show_courseids` to only show the UI in specific courses. Example: `10,153,102`.
- `navigation_position_beforekey`: Allows to specify where in the course navigation the link to the page is added. By default, the link is added before the first `section` node. Example value: `grades` to be shown before the link to grades. You can find the key of a node in the navigation by using the developer tools. Right-click on a link in the navigation, press *Inspect* and check the attribute `data-key` of the corresponding `a` element.
- `dataprivacy_threshold`: This value determines how many "data points" a "data set" needs to contain before the data is displayed. See the data privacy section below for more information. By default, the value is `10`.
- `student_rolenames`: In case the role(s) for students/users in a course is not `student`, you can specify the corresponding role name. In case there are multiple roles that students have, use a single comma. Example: `student,customrole`. By default, the value is `student`.
- `student_enrols_groupby`: Option to allow merging of multiple courses with the same `shortname` or `fullname` in the parallel/previously heard courses. By default, the value is `course->id` which will not merge any courses by comparing their name.

## Logstore: Role Tracking
<a name="roletracking"></a>
There are two settings to define which roles are to be tracked and which not. Specify the role by using the "shortname" (can be found via *Site Administration* -> *Users* tab -> *Permissions* category -> *Define roles*).

- `tracking_roles`: Whitelist (Only track these roles unless specified by `nontracking_roles`)
- `nontracking_roles`: Blacklist (don't track these roles)

The blacklist has priority over the whitelist. Keep in mind that a user can be a `teacher` in one course and a `student` in another course meanining that a user might be tracked in one course while being excluded from tracking in another.

If `tracking_roles` is not set, all roles are assumed to be tracked (unless roles are given in `nontracking_roles`). If `tracking_roles` and `nontracking_roles` are unset, all roles are tracked.

### Example

For an example, let's assume the following settings:

| Setting | Value|
|---|---|
| `tracking_roles` | `guest,student` |
| `nontracking_roles` | `teacher,manager` |

In words, this setting means:

> Only track users in the course that have the role `guest` or `student`, unless they also have the role `teacher` or `manager`.

See below examples of what would be tracked:

| User Roles | Tracked |
|---|:---:|
| `student` | ✔ |
| `student,manager` | ✘ |
| `manager` | ✘ |
| `coursecreator` | ✘ |
| `student,coursecreator` | ✔ |
| `student,coursecreator,teacher` | ✘ |

## Data Privacy
This plugin was developed with data privacy in mind. It does not log any user ids. All data is logged anonymously. You still have some flexibility in how strict the data is handled by using the `dataprivacy_threshold` option.

- Analytics related to data that is unknown to the teacher (like what other courses the users are enroled into) is not displayed if the number of data points is below the threshold.
- Analytics related to data that is known to the teacher (like his own learning material) is displayed as `< THRESHOLD`

### Examples

Let's consider an example for each case. Assuming, the value is set to `10`, this will have the following effects:

- **Unknown data**: In the Learners report that shows the number of courses users have heard before, only courses with at least (`>=`) `10` users in common will be shown. Courses with less than (`<`) `10` users will not be shown at all.
- **Known data**: In the activities report that shows the number of clicks for each learning materials, clicks are shown as `< 10` if the number of clicks is less than `10`.

### Aggregated Data

In addition, **aggregated data** (like the number of total clicks on all quizzes) is rounded (down) to a multiple of the threshold. By doing that, the teacher cannot use the aggregated data points to esimated the data points of a specific activity. Let's take the example from above and assume the threshold is set to `10`. Assuming two quiz activities have `22` and `6` clicks on them, they would show as `22` and `< 10` on the individual clicks. If the aggregated data would now show `28`, the teacher could easily calculate the number of clicks on the second quiz. That's why the aggregated shown number is displayed as `20` (rounded down to multiples of the threshold `10`).

## Language support
The plugin comes with German and English language files.

## Capabilities

There is currently only a single capability `local/learning_analytics:view_statistics` that decides who is allowed to view the dashboard. By default, the following roles have the cabability (as defined in [access.php](db/access.php)):

- `student`
- `teacher`
- `editingteacher`
- `manager`

## Changelog
Every change is documented via GIT. In addition, we create a summary for every version in separate changelog files. All important changes will be documented there. We follow the guides from [keepachangelog](https://keepachangelog.com/).

Each of the plugins has a separate changelog, which you find here:

- `local_learning_analytics`: [CHANGELOG](./CHANGELOG.md)
- `logstore_lanalytics`: [CHANGELOG](https://github.com/rwthanalytics/moodle-logstore_lanalytics/blob/master/CHANGELOG.md)

## Third-party libraries and resources

The following third-party libraries are used in this project:

- Plotly.js: <https://plotly.com/javascript/>
- Material.io icons: <https://material.io/resources/icons/>

See [CREDITS](./docs/CREDITS.md) for more information including full licenses.

## Provided by

<a href="https://cls.rwth-aachen.de/"><img src="https://cls.rwth-aachen.de/wp-content/uploads/2019/06/rwth_cls_de_rgb.png" alt="Center für Lern- und Lehrservices - RWTH Aachen University" height="80"></a>

## License
[GPL](./LICENSE)

## Contributing
Checkout the [contributing guide](./CONTRIBUTING).
