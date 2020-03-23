# Reports


## List of reports
- coursedashboard
  - Dashboard report, linked from the course menu
- activities
  - List of all activities
  - Table of most used activities and resource types
- learners
  - Information about which courses are heard in parallel/before
- browser_os
  - Statistics regarding used browser/os
  - Uses the lalog/browser_os subplugin data


## Process when adding a report
You can add a report by adding a directory here. There will be a hook functionality added later to feature a link to the report in the dashboard.

Also, make sure to update `classes/report_list.php` and give your report a unique ID. Later, we also need to add them to the `db/upgrade.php` script. (TODO)