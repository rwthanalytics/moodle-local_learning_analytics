# CONTRIBUTING

## Branches
The `master` branch contains the latest release. The `dev` branch contains the development. Features are merged into `dev`, before `dev` gets merged into `master` right before the release happens. A special branch is the `demo` branch which will modify some code lines in the project to use fake data. The purpose of this is to have an easy way to create screenshots for documentation.

## Relase process

- Check if there are any breaking changes that should be handled by a `upgrade.php` file
- Make sure all changes are documented in `CHANGELOG.md`
- Change `version.php`
  - Set `$plugin->version`
  - Set `$plugin->release`
  - Check if dependency `logstore_lanalytics` needs to be changed to a new version
- `git commit -m "Version release: vX.X.X"`
- `git tag vX.X.X`
- `git push`
- `git push --tags`

### Update files on Moodle Plugin store
- Go to the `developer zone`: https://moodle.org/plugins/local_learning_analytics/devzone
  - Click `Add a new version` below `Releasing a new version`
- Click on `Release` next to the github window (make sure the correct version is listed)
  - Moodle will now download the ZIP
- Make sure `Rename root directory` in advanced settings
- Select supported Moodle versions
- Click `Continue`
- No need to do anything on the next screen -> Just press `Release`

## Changelog
Every changelog should be noted in the [CHANGELOG](./CHANGELOG.md) file. The logstore plugin has it's own changelog file.

## Versioning
- We'll release version 1 as soon as we want to publish the plugin in the plugin store.
- Major release (1 -> 2): When the plugin structure changes or any *major* or breaking changes happen.
- Minor release (1.1 -> 1.2): New features.
- Patch release (1.1.0 -> 1.1.1): In case of bugs that are found outside of our normal release plan.

## JavaScript Development

- Follow the [steps in the Moodle docs](https://docs.moodle.org/dev/Javascript_Modules#How_do_I_write_a_Javascript_module_in_Moodle.3F).
- Depending on the Node.js version, you are using you might want to [comment the lines that check the Node.js version](https://github.com/moodle/moodle/blob/800563e415f64d1cb36bbf9294dc94fdcd6feb84/Gruntfile.js#L41-L45).

When actively developing JavaScript files, use the following command (on Windows) to start the `grunt watch`. Make sure you are in the plugin directory (`moodle/local/learning_anyltics`). This will only watch the plugin directory and apply changes on the fly.

```
grunt watch --root=local/learning_analytics
```

When the development is done, run the following command to generate the `build` directory:

```
grunt amd --root=local/learning_analytics/amd
```
