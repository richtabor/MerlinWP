# Merlin WP

[Merlin WP](https://merlinwp.com) aims to address the tedious and exhausting WordPress theme setup and onboarding process. It makes installing a new WordPress theme, recommended WordPress plugins, Customizer settings, widgets, and demo content an exciting and gratifying user experience. [Read more...](https://richtabor.com/merlin-wp)

## Beta & Testing

Merlin WP is currently in beta, it's not suggested to use Merlin WP in production just yet, as there's still a few kinks and features to be added. If you run into anything, raise an [issue](https://github.com/richtabor/MerlinWP/issues) and let's work on it.

## Usage

### 1. Add Merlin WP to your WordPress theme

First, [download the latest release](https://github.com/richtabor/MerlinWP/releases) from the Merlin WP GitHub reposity.  Next, add all of the files within the release into your theme.

Now all you need to do is require the `class-merlin.php` class, `merlin-config.php` and the composer autoload files in your `functions.php`, like this:

```php
require_once get_parent_theme_file_path( '/inc/merlin/vendor/autoload.php' );
require_once get_parent_theme_file_path( '/inc/merlin/class-merlin.php' );
require_once get_parent_theme_file_path( '/inc/merlin-config.php' );
```

In the example above, the `/merlin/` directory and the `merlin-config.php` file are both placed within the theme's `/inc/` directory location. Also, if you have TGMPA included within your theme, ensure Merlin WP is included after it.

### 2. Configure Merlin WP

The `merlin-config.php` file tells Merlin WP where the class is installed. In this config file, you can also enable the Easy Digital Downloads Software license activation step.

The config file also let's you modify any of the text strings.

* `directory` — The location in your theme where the merlin code directory is placed (example: `inc/merlin`, if you placed the `merlin` folder in your theme's `inc` folder)
* `merlin_url` — The admin url slug where Merlin WP will exist
* `child_action_btn_url` — The url for the child theme generator's "Learn more" link
* `dev_mode` — Retain the "Theme Setup" menu item under the WordPress Admin > Appearance section for testing
* `license_step` — Turn on license activation (compatible with Easy Digital Downloads Software Licensing)
* `license_help_url` — A custom help link regarding licensing
* `edd_item_name` — The EDD item name, has to be the same as item_name in the config parameter in the EDD_Theme_Updater_Admin class.
* `edd_theme_slug` — The EDD slug, has to be the same as theme_slug in the config parameter in the EDD_Theme_Updater_Admin class.
* `edd_remote_api_url` — The EDD remote API URL, has to be the same as remote_api_url in the config parameter in the EDD_Theme_Updater_Admin class.

### 3. Define your demo content import files

You'll need the following files:
* `content.xml` — Exported demo content using the WordPress Exporter
* `widgets.wie` — Exported widgets using [Widget Importer & Exporter](https://wordpress.org/plugins/widget-importer-exporter/)
* `customizer.dat` — Exported Customizer settings using [Customizer Export/Import](https://wordpress.org/plugins/customizer-export-import/)

Once you have those files, you can upload them to your server (recommeded), or include them somewhere in your theme. Next, define a filter in your theme to let WP Merlin know where these files are located. Depending on where you placed the import files, you have two ways to define the filter:

1\. If you uploaded the import files to your server, then use this code example and edit it, to suit your file locations:

```
function merlin_import_files() {
	return array(
		array(
			'import_file_name'           => 'Demo Import',
			'import_file_url'            => 'http://www.your_domain.com/merlin/demo-content.xml',
			'import_widget_file_url'     => 'http://www.your_domain.com/merlin/widgets.json',
			'import_customizer_file_url' => 'http://www.your_domain.com/merlin/customizer.dat',
			'import_preview_image_url'   => 'http://www.your_domain.com/merlin/preview_import_image1.jpg',
			'import_notice'              => __( 'A special note for this import.', 'your-textdomain' ),
			'preview_url'                => 'http://www.your_domain.com/my-demo-1',
		),
	);
}
add_filter( 'merlin_import_files', 'merlin_import_files' );
```

2\. If you included the import files somewhere in the theme, then use this code example:

```
function merlin_local_import_files() {
	return array(
		array(
			'import_file_name'             => 'Demo Import',
			'local_import_file'            => get_parent_theme_file_path( '/inc/demo/content.xml' ),
			'local_import_widget_file'     => get_parent_theme_file_path( '/inc/demo/widgets.wie' ),
			'local_import_customizer_file' => get_parent_theme_file_path( '/inc/demo/customizer.dat' ),
			'import_preview_image_url'     => 'http://www.your_domain.com/merlin/preview_import_image1.jpg',
			'import_notice'                => __( 'A special note for this import.', 'your-textdomain' ),
			'preview_url'                  => 'http://www.your_domain.com/my-demo-1',
		),
	);
}
add_filter( 'merlin_import_files', 'merlin_local_import_files' );
```

#### Multiple demo imports
If you have multiple demo imports, then just define multiple arrays with appropriate data. For an example of two predefined demo imports, please look at the `merlin-filters-sample.php` file.

#### Redux framework import
If you are using the [Redux Framework](https://wordpress.org/plugins/redux-framework/) in your theme, then you can import it as well. Please look at the `merlin-filters-sample.php` file for an example on how to define the Redux import files.

### 4. Easy Digital Downloads Software License activation
You will need to use the EDD and the EDD Software Licensing add-on to deploy this step within the setup wizard. By default this step is disabled, so you have to enable it in the `merlin-config.php` file (look at the the _Configure Merlin WP_ step above).

Once all the needed settings are configured in the `merlin-config.php` file, the license activation step will show up, right after the child theme step.

The integration is done for the [EDD licensing theme example](https://docs.easydigitaldownloads.com/article/382-automatic-upgrades-for-wordpress-themes), which you can also add to your theme. This will add a **Theme license** page, where the user can deactivate or check the license expiration date.

### 5. Add filters

Inside the package download exists a `merlin-filters-sample.php` file which includes examples of the different filters you may use to modify Merlin. A primary example would be to use to `merlin_generate_child_functions_php` filter to modify the contents of the generated child theme's `functions.php` file.

You may also need to filter your theme demo's home page, so that Merlin WP knows which pages to set as the home page once it's done running.

### 6. Debugging/log file

A log file is created in `.../wp-content/uploads/merlin-wp/main.log`. In the log file you will see, where something went wrong.

### 7. Testing

To test, you'll want to create a new standard WordPress installation and add your theme build with Merlin WP integrated. You can then use the [WP Reset](https://wordpress.org/plugins/wp-reset/) plugin to reset and run through more tests.

## Contributions

Anyone is welcome to contribute to Merlin WP. Please read the [guidelines for contributing](https://github.com/richtabor/MerlinWP/blob/master/.github/contributing.md) to this repository.

There are various ways you can contribute:

1. Raise an [Issue](https://github.com/richtabor/MerlinWP/issues) on GitHub
2. Send a Pull Request with your bug fixes and/or new features
3. Provide feedback and suggestions on [enhancements](https://github.com/richtabor/MerlinWP/issues?direction=desc&labels=Enhancement&page=1&sort=created&state=open)

## License

The open source license is designed for you to use Merlin WP to build open source and personal projects. The Merlin WP open source license is [GPLv3](https://www.gnu.org/licenses/gpl-3.0.html). The GPLv3 has many terms, but the most important is how [it is sticky when you distribute your work publicly](https://www.gnu.org/licenses/gpl-3.0.html#section5). From the [GPL FAQ](https://www.gnu.org/licenses/gpl-faq.html#GPLRequireSourcePostedPublic "GPL FAQ"):

> If you release the modified version to the public in some way, the GPL requires you to make the modified source code available to the program's users, under the GPL.

Releasing your project that uses Merlin WP under the GPLv3, in turn, requires your project to be licensed under the GPLv3.
