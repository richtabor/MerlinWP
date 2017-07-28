# [Merlin WP](https://merlinwp.com)
Better WordPress Theme Onboarding — Read about it here: https://richtabor.com/merlin-wp

## License

Merlin WP has three kinds of licenses: open-source, commercial, and OEM.

### 1. Open Source License

The open source license is designed for you to use Merlin WP to build open source and personal projects. The Merlin WP open source license is [GPLv3](https://www.gnu.org/licenses/gpl-3.0.html). The GPLv3 has many terms, but the most important is how [it is sticky when you distribute your work publicly](https://www.gnu.org/licenses/gpl-3.0.html#section5). From the [GPL FAQ](https://www.gnu.org/licenses/gpl-faq.html#GPLRequireSourcePostedPublic "GPL FAQ"):

> If you release the modified version to the public in some way, the GPL requires you to make the modified source code available to the program's users, under the GPL.

Releasing your project that uses Merlin WP under the GPLv3, in turn, requires your project to be licensed under the GPLv3. If you are okay with this, feel free to use Merlin WP under the GPLv3, without purchasing a commercial license.

### 2. Commercial License

The commercial license is designed to for you to use Merlin WP in commercial products and applications, without the provisions of the GPLv3. With the commercial license, your code is kept propietary, to yourself. If you want to use Merlin WP to develop commercial sites, themes, projects, and applications, the commercial license is the appropriate license.

### 3. Commercial OEM license
  
If you want to include Merlin WP as part of a commercial interface builder, SDK, or toolkit, choose the Commercial OEM license. Commercial OEM licenses are customized for each customer. Contact hi@merlinwp.com.


## Usage

### Add Merlin WP to your theme
Let's get you set up to use Merlin WP! First off, download and add the add the /merlin/ folder and the merlin-config.php file into your theme. Next, include the Merlin class (`merlin/merlin.php`) and the `merlin-config.php` file. 

Refer to the example below:
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

## Add your demo content
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
