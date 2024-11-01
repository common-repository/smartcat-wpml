=== Smartcat Integration for WPML ===
Contributors: m1nyasha
Tags: translation, localization, multilingual, languages, translators, automatic translation, continuous localization
Requires at least: 5.3
Tested up to: 6.4
Requires PHP: 7.0
Requires PHP extensions: dom, openssl, json
Stable tag: 3.1.55
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The easiest way to translate your WPML-enabled WordPress site into various languages.

== Description ==

[Smartcat](https://www.smartcat.com/?utm_source=connectors&utm_medium=referral&utm_campaign=wordpress) connects linguists, companies, and agencies to streamline the translation of any content into any language, on demand. Our platform puts your translation process on autopilot, from content creation to payments.

The plugin allows you to set up an integration between your WPML-enabled WordPress site and Smartcat. The integration itself is configured in Smartcat. Once configured, it will automatically pull translatable content from your website to Smartcat, where you can translate it with your own linguists or the ones you pick from our Marketplace. Alternatively, you can use the power of machine translation and post-edit just the most important pages to reduce translation costs. Once your content is translated, Smartcat will push it back to WordPress, instantly publishable in the target language(s) of choice.

== Account & pricing ==

You first need to create a Smartcat account as one is not automatically created when installing the extension.
To create an account, go to [smartcat.com](https://www.smartcat.com). All translation features in Smartcat are free to use.

== Features ==

- Connect your WordPress website with a specific Smartcat account
- Choose the translation supplier from your own team or Smartcat Marketplace
- Automatically send new or updated content for translation
- Reuse existing translations thanks to Translation Memory
- Choose translation workflow stages — translation, editing, proofreading, etc.

== Benefits of Smartcat ==

- No document re-formatting required
- Easy-to-use multilingual translation editor
- Multi-stage translation process — e.g., translation, editing, proofreading
- Free collaboration with your own volunteers or coworkers
- [Marketplace](https://www.smartcat.com/marketplace/?utm_source=connectors&utm_medium=referral&utm_campaign=wordpress)
 of 350,000+ translators and 2,000+ agencies in 100+ language pairs
- Track progress by language, document, or person
- Automated payments to translation suppliers
- Free support to optimize localization processes

== Installation ==

Important: for the Smartcat Integration for WPML to work properly, you need to install the WPML plugin first.

1. Install, activate, and configure the WPML plugin.
2. Install and activate Smartcat Integration for WPML.
3. Go to Settings > Smartcat Integration for and follow the instructions.

You’re all set! The integration between your site and Smartcat is now configured. The world is waiting for your content, so go ahead and translate it in a simple and efficient way.

== Support ==

Contact us at [support@smartcat.com](mailto:support@smartcat.com) with any questions related to:

- Module issues
- Assistance in vendor management (freelancers or LSPs)
- Use of the module for your clients needs

== Changelog ==
= 3.1.55 =
* Wpml connector stopped trying to sync if project is removed

= 3.1.54 =
* Fixed bug with LocaleMapper

= 3.1.53 =
* Content sent for translation to Smartcat has been optimized

= 3.1.52 =
* Improved mapping of Smartcat languages

= 3.1.51 =
* Security fixes

= 3.1.50 =
* Added setting: Maximum number of translated items to pull from Smartcat per request

= 3.1.49 =
* Mapping Burmese locale for Smartcat

= 3.1.48 =
* Fixed "fatal error" when WPML is not installed

= 3.1.47 =
* Added option "Disable WPML strings registration for a post when sending content to Smartcat" to settings

= 3.1.46 =
* Added the ability to automatically import documents from Smartcat

= 3.1.45 =
* Important fix related to importing translations from Smartcat

= 3.1.44 =
* Added the ability to skip WPML package strings when importing translations from Smartcat

= 3.1.43 =
* Minor functional improvements

= 3.1.42 =
* Added the ability to update the original content of all posts in a translation request

= 3.1.41 =
* Added the ability to add and remove languages for all documents in a translation request

= 3.1.38 =
* Added Smartcat Helper (FAQ)

= 3.1.37 =
* Fixed an issue where in some cases not all content was imported from Smartcat to WordPress
* Added "Use always classic WordPress editor" option that disables the ability to use the WPML editor

= 3.1.36 =
* Fixed issue with assigning post status based on WPML settings

= 3.1.35 =
* Fix some bugs when getting content from Smartcat

= 3.1.34 =
* Fixed a timeout error when updating the original translations of a post for a large number of languages.

= 3.1.33 =
* Fixed issue with registering WPML strings

= 3.1.32 =
* Improved content submission to Smartcat for large number of posts/pages
* Fixed an error that occurred when the WPML plugin was not installed

= 3.1.30 =
* Improve performance when loading dashboard page
* Content is retrieved directly through WPML tools
* Improved app UX
* Added bulk actions for translation requests

= 3.1.27 =
* Fixed an issue with displaying Smartcat metabox in some post types

= 3.1.26 =
* Fixed issue with duplicate translations when sending posts via bulk actions

= 3.1.25 =
* Minor fixes

= 3.1.24 =
* Added mapping for af_ZA locale

= 3.1.23 =
* Fixed a bug due to which the post creation page was unavailable

= 3.1.22 =
* Improved UX
* Fixed issues with project selector

= 3.1.20 =
* Fixed manual authorization

= 3.1.19 =
* Minor fixes

= 3.1.18 =
* Added "Manual translation" workflow stage

= 3.1.17 =
* Minor fixes

= 3.1.16 =
* Fixed various bugs
* Improved user interface
* Improved plugin stability

= 3.1.12 =
* Fixed GB locale

= 3.1.11 =
* Minor bugs fixed

= 3.1.10 =
* Fixed issue with exporting translations from Smartcat when Elementor plugin is not installed.

= 3.1.9 =
* Fixed bug with exporting translations from Smartcat

= 3.1.8 =
* New settings UI

= 3.1.6 =
* Fixed issues with Chinese languages in the app

= 3.1.5 =
* Fixed an issue with saving content for Elementor posts whose author does not have edit rights

= 3.1.4 =
* Added workflow stages for the application
* Added segment description in Smartcat editor

= 3.1.2 =
* Support WPBakery Page Builder / Visual Composer

= 3.1.1 =
* Fixed issue with Chinese language in Smartcat App

= 3.1.0 =
* Support Elementor in Smartcat WPML App

= 3.0.2 =
* Fixed bug after pushing translations to WordPress without Elementor

= 3.0.1 =
* Fixed bug with translations receive in PHP <= 8.0

= 3.0.0 =
* Support Elementor Pro
* Migration to LocJSON format.

= 2.2.3 =
* Fixed problem with saving articles

= 2.2.2 =
* Fixed problem with importing translations

= 2.2.1 =
* Fixed issues with article content loading

= 2.2.0 =
* New work flow with Smartcat App
* Added ability to create translation requests in Smartcat
* Ability to follow translations in real time
* Added ability to translate "String translations" from WPML

= 2.1.2 =
* Security fixes

= 2.1.1 =
* Fixed problem with importing existing translations into Smartcat

= 2.1.0 =
* Changed export and import translations. Now the content of posts/pages is unloaded as a list of blocks from the Gutenberg editor, which allows you not to violate the integrity of the data.

= 2.0.4 =
* Support for custom post types
* Support for translating WPML strings (widgets)

= 2.0.2 =
* Ignoring WordPress System Metadata
* Uploading custom fields (metadata) will only be done if it is selected as "Translatable" in the WPML settings
* Support for Yoast fields (focuskeywords, metadesc, title)

= 2.0.0 =
* Improved speed when loading the necessary data for integration
* Improved security
* Added the ability to create an integration for a post or page from WordPress itself
* Universal upload of custom fields (ACF, CMB2, etc.)

= 1.4.1 =
* Ability to skip uploading custom fields in the Smartcat filter.

= 1.4.0 =
* New system for unloading custom fields

= 1.3.48 =
* Fixed issue with too much metadata

= 1.3.47 =
* Fixed a bug with getting a list of posts in the Smartcat filter

= 1.3.46 =
* Fixed problem with unloading all fields from page metadata

= 1.3.45 =
* Support for meta information of pages and posts

= 1.3.44 =
* Changed the formation of the name of documents in Smartcat

= 1.3.43 =
* Added the ability to filter content when creating an integration in Smartcat

= 1.3.42 =
* Removed sending fields "image", "file" and so on to Smartcat

= 1.3.41 =
* Fixed problem with saving pages.

= 1.3.40 =
* Fixed issues that appeared in WordPress < 5.5

= 1.3.39 =
* Minor fix

= 1.3.38 =
* Fixed a bug that occurred on PHP version 7.1 and below

= 1.3.37 =
* Added support for Wysiwyg Editor, Repeater, Group and Flexible Content types for ACF plugin.

= 1.3.5 =
* Returned PHP 7.0 compatibility

= 1.3.4 =
* Improved Smartcat support

= 1.3.21 =
* Minor fix

= 1.3.2 =
* Fixed bug when pushing new documents with different source languages to Smartcat and WPML

= 1.3.1 =
* Fixed errors when importing posts to Smartcat

= 1.3.0 =
* Added support for the Advanced Custom Fields plugin

= 1.2.8 =
* Minor bug fixes

= 1.2.7 =
* Improved plugin version checking for Smartcat integration to work correctly

= 1.2.6 =
* Added support for importing categories
* Added checking of the current version of the plugin for the integration to work correctly

= 1.2.4 =
* Added a plugin health check when creating an integration in Smartcat
* Fixed "More" link in missing Friendly URL message
* Fixed bug - Undefined array key "smartcat_notice_status"

= 1.2.1 =
* Hotfix error - "failed to open stream"

= 1.2.0 =
* Added the tracking of deleted posts and pages to Smartcat

= 1.1.3 =
* Minor changes for the Import command

= 1.1.0 =
* Fixed a problem with importing ready-made WPML translations

= 1.0.5 =
* Added a message about the copied Secret Key
* Optimized the code

= 1.0.0 =
* First plugin release
