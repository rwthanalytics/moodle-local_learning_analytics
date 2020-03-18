# Learning Analytics for Moodle

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
- `MOODLE_PATH/local/learning_analytics` contains the contents of the `local_learning_analytics` plugin

Now visit the Moodle administration page or the Moodle starting page and Moodle should detect the new plugins and install them. After installation, there should be a link in each course menu called "Learning Analytics". Alternatively, visit the following page in your browser (replace `COURSE_ID` with a course id):

```
https://MOODLE_INSTALLATION/local/learning_analytics/index.php/reports/coursedashboard?course=COURSE_ID
```

## Contributing
Checkout [Contributing guide](./CONTRIBUTING).