=== Simple Settings ===
Contributors: jklatt86
Donate link: http://www.ilikemustard.com
Tags: settings
Requires at least: 3.0.1
Tested up to: 3.7.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to create, modify, and retrieve basic settings for use in templates,
posts, and pages.

== Description ==

A plugin to create, modify, and retrieve basic settings for use in templates,
posts, and pages. This plugin also supports token replacement in template files
as well as via the WYSIWYG.

This plugin is especially useful if you would like to store basic settings for
your site but don't want or don't know how to write the code to do it yourself.
Simply create settings for anything you would like to display on your site, add
the appropriate function calls (see [FAQ](http://wordpress.org/plugins/simple-settings/faq/))
in your template (or use token replacement via the WYSIWYG), and you'll have
your settings working with your site in no time!

= Common uses for this plugin: =

* Displaying contact information (Phone, Fax, Address, etc),
* Displaying a temporary message or daily special,
* Turning features on and off on the fly,
* Displaying your social network links,
* Displaying your current favorite color,
* ... or any other content that may change over time.
* Meow.

== Installation ==

1. Upload the `ilm-simple-settings` plugin directory to the `/wp-content/plugins/`
directory on your server.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How do I make a new setting? =

To make a new setting, click `Simple Settings` in the WordPress admin menu,
then click `Add New`. Give your setting a name, type, and content, then click `Publish`.

= How do I delete a setting? =
To delete a setting, click on the setting name to edit it, then click `Move to
Trash` on the next screen.

= What types of settings can I make? =

Currently, this plugin supports the ability to create `text` and `boolean`
settings. Text settings will return a string and boolean settings will return
true or false.

= Can my setting include HTML and JavaScript? =

Yes it can! It's also handy for adding third-party JavaScript widgets to posts
or pages.

= What is a "slug"? =

A slug is a unique computer-readable name for each setting that is created.
Slugs are used by the `get_setting()` function and by token replacement.

= How do I use a setting in my template? =

To use a setting in your template, first create the setting (i.e. "My Setting"),
then paste the code below into your template. <em>Remember to change the example
slug to the one for your setting!</em>

`<?php echo get_setting('my_setting'); ?>`

Alternatively, you can also use the value of a setting to affect the output of
a page. For example:

`
<?php

    if (get_setting('do_something') == true) {
        echo 'Doing something!';
    } else {
        echo 'Not doing something.';
    }

?>
`

= How do I use token replacement via the WYSIWYG? =

To use token replacement via the WYSIWYG, first create the setting (i.e. "My
Setting"), edit the page you would like your setting to be displayed on, paste
the text below into the WYSIWYG, and save. The plugin will automatically
replace the token with the corresponding setting value when viewing the page.
It's as easy as that!  <em>Remember to change the example slug to the one for
your setting!</em>

`{my_setting}`

== Screenshots ==

1. The admin interface showing all the settings that have been created.
2. The admin interface showing sample search results.
3. The admin interface showing a setting being edited.
4. The admin interface showing an example of token replacement via the WYSIWYG.

== Changelog ==

= 1.0 =
The initial release of this plugin.

= 1.1 =
Added appropriate documentation. Updated the readme.

= 1.2 =
Fixed getSetting() not returning correct boolean value.
