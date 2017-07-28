# Merlin WP
Better WordPress Theme Onboarding https://merlinwp.com

## License

## Add Merlin WP to your theme
Let's get you set up to use Merlin WP! First off, download!

1. Add the /merlin/ folder in your theme's directory
2. Add the merlin-config.php file in your theme's directory

### Include Merlin WP and the configuration file
Include the Merlin class (`merlin/merlin.php`) and the `merlin-config.php` file. Refer to the example below:
```
require get_parent_theme_file_path( '/inc/merlin/merlin.php' );
require get_parent_theme_file_path( '/inc/merlin-config.php' );
```

> Note: In the example above, the `/merlin/` folder and the `merlin-config.php` file are both placed within the theme's `/inc/` folder directory location. 
>

If you have TGMPA included within your theme, please ensure Merlin WP is included after it.

### Configure Merlin WP
The `merlin-config.php` file tells Merlin WP where the class is installed and where your demo content is located. It also let's you modify any of the text strings throughout the wizard.

**The important configuration settings:**
* `directory` — the location in your theme where the `/merlin/` folder is placed
* `demo_directory` — the folder location of where your demo content is located

Other settings:
* `merlin_url` — the admin url where Merlin WP will exist
* `child_action_btn_url` — the url for the child theme generator's "Learn more" link
* `help_mode` — a wizard for your wizard, if you need help *(beta)*
* `branding` — show Merlin WP's logo or not *(beta)*

## Demo Content
Add your theme's demo content to the demo directory location specificed in the `merlin-config.php` file.

You'll want to add the following files:
* `content.xml` — Exported demo content using the WordPress Exporter
* `widgets.wie` — Exported widgets using [Widget Importer & Exporter](https://wordpress.org/plugins/widget-importer-exporter/)
* `customizer.dat` — Exported Customizer settings using [Customizer Export/Import](https://wordpress.org/plugins/customizer-export-import/)

## Contributions
Anyone is welcome to contribute to Merlin WP. Please read the [guidelines for contributing](https://github.com/richtabor/MerlinWP/blob/master/.github/contributing.md) to this repository.

There are various ways you can contribute:

1. Raise an [Issue](https://github.com/richtabor/MerlinWP/issues) on GitHub
2. Send a Pull Request with your bug fixes and/or new features
3. Provide feedback and suggestions on [enhancements](https://github.com/richtabor/MerlinWP/issues?direction=desc&labels=Enhancement&page=1&sort=created&state=open)
