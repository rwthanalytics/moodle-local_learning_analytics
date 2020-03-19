# Learning Analytics for Moodle

This plugin really consists of two plugins:

- `logstore_lanalytics`: Logs the events to the database
- `local_learning_analytics`: Shows the Learning Analytics user interface

The first plugin (`logstore`) is a dependecy of the second one. Therefore, it should be installed first.

## Installation

First, download the dependency (`moodle-logstore_lanalytics`) and place the files in the `admin/tool/log/store/lanalytics` folder.

```
$ cd MOODLE_PATH
$ cd admin/tool/log/store
$ git clone https://git.rwth-aachen.de/laixmo/moodle-logstore_lanalytics.git lanalytics
```

After that repeat the steps for this plugin and place the files in the `local/learning_analytics` folder.

```
$ cd MOODLE_PATH
$ cd local
$ git clone https://git.rwth-aachen.de/laixmo/moodle-local_learning_analytics.git learning_analytics
```

The end result should look like this:

- `MOODLE_PATH/admin/tool/log/store/lanalytics` contains the contents of the `moodle-logstore_lanalytics` plugin
- `MOODLE_PATH/local/learning_analytics` contains the contents of the `moodle-local_learning_analytics` plugin

Now visit the Moodle administration page or the Moodle starting page and Moodle should detect the new plugins and install them. After installation, there should be a link in each course menu called "Learning Analytics" leading to the following page (`COURSE_ID` is the course id of the corresponding course):

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

- `course_ids`: Only track the courses with the given IDs. The order of the IDs does not matter. IDs should be separated by a single comma. By default, the plugin tracks alls courses. Example: `10,80,10`.
- `nontracking_roles`: Define which roles should *not* be tracked. This is useful if you don't want to track specific roles (like managers or teachres). Specify the role by using the "shortname" (can be found via *Site Administration* -> *Users* tab -> *Permissions* category -> *Define roles*). By default, no roles are ignored. Example: `teacher,editingteacher,manager`
- `buffersize`: Same as `buffersize` of other loggers. In case a single page fires more than one event, this is the number of events that will be buffered before writing them to database. Defaults to `50`.

### Local Plugin (`moodle-logstore_lanalytics`)

The settings page can be found in *Site Administration* -> *Plugins* tab -> *Local plugins* category -> *Learning Analytics*. The plugin has the following options:

- `dataprivacy_threshold`: This value determines how many "data points" a "data set" needs to contain before the data is displayed. See the data privacy section below for more information. By default, the value is `10`.
- `allow_dashboard_compare`: Activate this options, to allow teachers to compare their course with another one of their courses in the dashboad. The option adds a link to the dashboard allowing the teachers to select another one of their courses. After selecting another course, the week plot will show a dashed line in the background in addition to the current course. By default, the option is disabled.

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

## Capabilities

There is currently only a single capability `local/learning_analytics:view_statistics` that decides who is allowed to view the dashboard. By default, the following roles have the cabability (as defined in [access.php](db/access.php)):

- `student`
- `student`
- `editingteacher`
- `manager`

## Contributing
Checkout [Contributing guide](./CONTRIBUTING).