===Easy Sign Up===
Contributors: Greenweb
Donate link: http://www.beforesite.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: auto responder, auto-responder, autoresponder, html autoresponder, jump page, squeeze page, squeeze form, form, emailer, email, redirection, leads, mailing list, newsletter, newsletter signup, sign up, beforesite, mailchimp
Requires at least: 4.3
Tested up to: 4.8.9
Stable tag: trunk

This Plugin creates a form to collect the name and email from your visitors, who are then redirected to the web address of your choice.

== Description ==
This plugin generates a customizable HTML thank you email that is sent to the visitor, the visitor's email and name are sent to an email address of your choosing.
Possible use is collecting email address for a newsletter, or leads for your sales force before redirecting to a brochure.

= Main Functions =
 * Email address collection
 * User redirection
 * Auto-responder
 * Lead collection
 * Squeeze Page

= Extending Easy Sign Up =
  * Plugins that integrate, Database, Customizable layouts, and easy translation can be found at [beforesite.com](http://www.beforesite.com/)

Additional form fields, check boxes, text boxes etc can be added with the integration of [WordPress Hooks, Actions and Filters](http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters)

== Installation ==
 * Upload the easy_sign_up folder to the /wp-content/plugins/ directory
 * Activate the plugin through the 'Plugins' menu in WordPress
 * Change the options under the Easy Sign Up Menu

== Frequently Asked Questions ==

= Use =
 * Add the form to your site as a widget.
 * Use the following short code in your pages and posts:
 * `[easy_sign_up]`
 * Optional short tag values are:
  * **title** allows you to customize the title. The short code `[easy_sign_up]` default title is "Easy Sign Up"
  * **from_id** (removed) allowed you add a custom id to the form - if you need this for CSS layout please use the `esu_class` attribute instead as this will be available in the future
  * **esu_class** allows you add a custom class
  * **fnln** splits the Name field into first and Last name fields
  * **phone** adds a phone field to the form
  * **esu_label** an unique identifier for your form, useful if you have forms on multiple pages
  * **esu_r_url** allows for the redirection URL to be overridden in the shortcode, added in update 3.2

= Example =
 `[easy_sign_up]`

 `[easy_sign_up title="Your Title Here" fnln="1" phone="1" esu_label="A admin only label" esu_class="my-class" esu_r_url="www.redirecting.com"]`
  * [Documentation on Easy Sign Up's Shortcode](http://www.beforesite.com/docs/easy-sign-up-short-code/)

= Emails are not working =

This plugin uses WordPress' [wp_mail()](http://codex.wordpress.org/Function_Reference/wp_mail) function to send out the auto-responder. If WordPress can send out an email then this plugin can too.
  * Check your spam folders
  * Ensure that your website can send emails
  * Have a look at the WordPress support forum topics: [1](http://wordpress.org/support/topic/not-recieving-email-want-to-remove-all-other-fields-except-email) [2](https://wordpress.org/support/topic/not-receiving-email-3)

= Problems with the shortcode =

  * Make sure your shortcode is on one single line

= Customize the Easy Sign Up form styles CSS =

The basic styles for the Easy Sign Up form comes from your Theme.
You can add a custom css class to the Easy Sign Up form via the shortcode attribute *esu_class* to apply your own styles e.g.: `esu_class="my-class"`
For inspiration and information please vist the following web pages:

  * [Easy Sign Up Custom CSS Example](http://www.beforesite.com/2015/02/03/customise-easy-sign-form/)
  * [Easy Sign Up's CSS](http://www.beforesite.com/docs/easy-sign-up-default-css/)

= The plugin is not working =

 * Make sure the plugin is up to date
 * Have a look around the forums for a similar topic as your problem may already be solved
 * How to test what is wrong:
  * **Disable all your other plugins**
  * **Switch to the default WordPress theme**
 * If the problem does not go away, open a support topic, if not, figure out which plugin is the cause of the problem and open a thread mentioning both plugins
 * Finally, if you decide to open a new thread

= Use this template in the support forum: =

`WordPress version:
Plugin Name and version:
I did this:
I expected the plugin to do this:
Instead it did this:`

[Easy Sign Up Support Forum](http://wordpress.org/support/plugin/easy-sign-up)

== Upgrade Notice ==
Changes to support users still using IE
Changes to address PHP 7.x depreciation notices. 

== Screenshots ==
1. Easy Sign Up Options Page
2. Contextual help
3. Sign up data screen
4. Shortcode button
5. Easy Sign Up Form


== Changelog ==
= 3.4.1 =
= 3.4 =
  Added new custom tags to the auto reply email, #firstname# & #lastname#.
= 3.3.9 =
  Changed permissions for options page from 'administrator' to 'add_users'
= 3.3.8 =
  Renamed a few functions
= 3.3.7 =
  * deleted unused code
= 3.3.6 =
  * Changed the text domain to match the plugin slug, this is to enable the importation the plugin into the translate.wordpress.org translation system
= 3.3.5 =
  * Improvements made to the data sanitization & and handling of single & double quotes in email from & subject fields
= 3.3.4 =
  * Bug fix:
    * added an other check to ensure that a clean up routine for the spam honeypot does not run if the user has turned off this feature.
= 3.3.3 =
* Updated the widget class, removing the PHP 4 constructor to get inline with current WordPress standards

= 3.3.2 =
* Issue with data page on IIS - thanks to WordPress.org member jpsteinwand
  * File updated lib/esu-simple-data-page.php see Trac for details
  * Tested on Apache 2.2.29 + PHP 5.3.29, 5.4.39, 5.5.23, 5.6.6

= 3.3.1 =
Bug fix addressing an issue found by webify https://wordpress.org/support/topic/issues-with-my-easy-sign-up

= 3.3 =
* Added a customizable subject field to for your auto responder, just make the change in the Easy Sign Up Options page.
* Changes to the Simple Data Panel:
  * The form results additionally show just the name and email
  * The raw data is still accessible
* Added a new Spam control method using an optional honey pot field
* Formatting change to Easy Sign Up Options page form fields
* Fixed links to beforesite.com
* Developers: added support for creating hidden fields to the 'esu_add_extra_options' filter
* Added support for the front end customizer dynamic widget interface

= 3.2 =
* NEW **Multiple Redirection Locations**
  * I have added the ability for you to override the default redirection web address in the shortcode. You do this by adding the new attribute **esu_r_url** and set it's value to a web address of your choosing.
  * `[easy_sign_up esu_r_url="www.redirecting.com"]`
* NEW **Simple Sign Up Data** page had been added under the options area.
  * This will create a comma separated list of all your sign ups.
  * This is meant as simple system to keep track of who has signed up.
* If you want more control data including please see the [Easy Data Extra](http://www.beforesite.com/downloads/easy-sign-up-data-extra/)
  * the ability to export your data
  * view referring web pages
  * View user OS (operating system) data
= 3.1.5.3 =
  * Updated Read me file and changed shortcode use instructions
  * Added link to the WordPress support forums
= 3.1.5.2 =
*  Bug fix:
   * Added in a conditional statement to check that the array used by the Update Widget API has no unset variables.
= 3.1.5.1 =
  * Widget:
   * Fixed E_STRICT notices under WP 3.8 running on PHP 5.4.x
= 3.1.5 =
 * fixed bug introduced by 3.1.4 update that prevented generation of the phone field in the shortcode
= 3.1.4 =
 * Deleted unused transient function and hook
 * Fixed E_STRICT notices under WP 3.8 running on PHP 5.4.x
= 3.1.2 =
  * Added Dutch language support thanks to __Luc van Geenen__ @ [decoda](http://www.decoda.nl)
= 3.1.1 =
 * Corrected Language domain
= 3.1 =
 * Removed support for depreciated shortcode attribute
 * Added a forward slash (/) on the end of the website's URL as this was in a few rare cases tripping servers and preventing them from posting the from data
= 3.0 =
 * 3.0 is coded to take full advantage of WordPress's API - keeping the plugin fast and lightweight and eliminating redundant code.
 * New validation JavaScript code
 * New default layout
 * Screen reader support
 * Language support - feel free to translate
 * New shortcode tag attributes
 * New add Easy Sign Up shortcode via the page/post editor
 * New form fields, *phone, first* and *last name*
 * HTML auto responder emails
 * WYSWYG editor for auto responder email
 * Change the way extra's are installed, these are now stand alone plugins and are installed like any other WordPress plugin
 * Added Spam protection via the **Akismet** API. *This requires that Akismet is installed and activated.*
 * Added Hooks and Filters to allow other Plugin and Theme Developers to interact with the plugin, adding extra fields and capturing form data
  * Go to [www.beforesite.com](http://www.beforesite.com/) for more info
= 2.1.1 =
  * Added [stripslashes_deep()](http://codex.wordpress.org/Function_Reference/stripslashes_deep) function to remove the the backslash character used to escape quotes in auto-responder email - March 14, 2012
= 2.1 =
  * Fixed a IIS bug with the way the unzip class was handling the windows folder system
  * Getting the plugin ready to work in 3.3
   * Ensuring that the code contains no deprecated functions or hooks
= 2 =
 * Replaced jQuery validation with standard JavaScript. Saving 35 kb and minimizing clashes with other JavaScript libraries used by themes and plugins
 * Replaced mail() with wp_mail()
 * Added support for Easy Extras
  * [More info here](http://www.beforesite.com)
 * Added support for localization.
  * To translate into your language use the easy-sign-up.pot file in /languages folder.
  * Poedit is a good tool to for translating. @link http://poedit.net
  * Please contact me at [www.beforesite.com/support](http://www.beforesite.com/support) with any translations so I can make them available to others.

= 1.2 =
 * Bug fix: Fixed a problem with a clash between the Easy Sign Up plugin and the Atahualpa Theme.

 * Added the form as a widget.

 * Changed the validation script to jQuery validation plugin 1.7

 * Added form_id as an optional attribute to the short code, this will allow the form to be in more then one area of your page without confusing the form validation script.
  * Note that you need to make your id one word i.e. form_id="my_id" OR form_id="myID" is correct, But form_id="My ID" is wrong.

= 1.1 =
  * Removed direct access to the wp-config.php file.
  * Added custom tag to the auto reply email. If you would like to include the name person who signed up in the *Thank You Email* just paste #fullname# into the Thank You Email text field where you'd like to see it.

= 1 =
First Version
