# Merlin WP

[Merlin WP](https://merlinwp.com) aims to address the tedious and exhausting WordPress theme setup and onboarding process. It makes installing a new WordPress theme, recommended WordPress plugins, Customizer settings, widgets, and demo content an exciting and gratifying user experience. [Read more...](https://richtabor.com/merlin-wp)


## Beta & Testing

Merlin WP is currently in beta, it's not suggested to use Merlin WP in production just yet, as there's still a few kinks and features to be added. If you run into anything, raise an [issue](https://github.com/richtabor/MerlinWP/issues) and let's work on it.

In the package, I've included a fork of one of my themes, [York Pro](https://themebeans.com/themes/york-pro), with Merlin WP already integrated. If you want to run a live test of Merlin WP, install this theme anywhere and give it a spin.

## Usage

### 1. Add Merlin WP to your theme

First, download the `merlin.zip` file located within the `/dist/` directory. Next, copy all of the files within this archive into your themes `inc` sub-directory.

Now all you need to do is require the `merlin.php` class and `merlin-config.php` file in your `functions.php`.

Refer to the example below:

```php
require get_parent_theme_file_path( '/inc/merlin/merlin.php' );
require get_parent_theme_file_path( '/inc/merlin-config.php' );
```

> Note: In the example above, the `/merlin/` directory and the `merlin-config.php` file are both placed within the theme's `/inc/` directory location. 
>

If you have TGMPA included within your theme, please ensure Merlin WP is included after it.

### 2. Configure Merlin WP

The `merlin-config.php` file tells Merlin WP where the class is installed and where your demo content is located. It also let's you modify any of the text strings throughout the wizard.

** The important configuration settings: **
* `directory` — The location in your theme where the `/merlin/` directory is placed
* `demo_directory` — The directory location of where your demo content is located

Other settings:
* `merlin_url` — The admin url where Merlin WP will exist
* `child_action_btn_url` — The url for the child theme generator's "Learn more" link
* `help_mode` — A wizard for your wizard, if you need help *(beta)*
* `dev_mode` — Retain the "Theme Setup" menu item under the WordPress Admin > Appearance section for testing. Also enables JS/CSS minified files. This is on by default during the beta.
* `branding` — Show Merlin WP's logo or not *(beta)*

### 3. Add your demo content

Add your theme's demo content to the demo directory location specificed in the `merlin-config.php` file.

You'll want to add the following files:
* `content.xml` — Exported demo content using the WordPress Exporter
* `widgets.wie` — Exported widgets using [Widget Importer & Exporter](https://wordpress.org/plugins/widget-importer-exporter/)
* `customizer.dat` — Exported Customizer settings using [Customizer Export/Import](https://wordpress.org/plugins/customizer-export-import/)

### 4. Add filters

Inside the package download exists a `merlin-filters.php` file which includes examples of the different filters you may use to modify Merlin. A primary example would be to use to `merlin_generate_child_functions_php` filter to modify the contents of the generated child theme's `functions.php` file. 

You may also need to filter your theme demo's home page, so that Merlin WP knows which pages to set as the home page once it's done runing.

### 5. Testing

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
