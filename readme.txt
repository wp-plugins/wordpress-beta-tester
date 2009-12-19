=== WordPress Beta Tester  ===
Tags: beta, advanced, testing
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal%40ftwr%2eco%2euk&item_name=Peter%20Westwood%20WordPress%20Plugins&no_shipping=1&cn=Donation%20Notes&tax=0&currency_code=GBP&bn=PP%2dDonationsBF&charset=UTF%2d8
Contributors: westi
Tested up to: 2.9
Requires at least: 2.7.1
Stable Tag: 0.81

== Description ==
This plugin provides an easy way to get involved with Beta testing WordPress.

Once installed it will enable you to upgrade your blog to the latest Beta or Release candidate at the click of a button using the built in upgrader. 

By default once enabled it switches your blog onto the point release development track.

For the more adventerous there is the option to switch to the bleeding edge of development

Don't forget to backup before your start!

== Changelog ==

= v0.81 =
* Fixed an issue in the version mangling for the bleeding edge develpment track which didn't handle the x.9 => y.0 transition
* Added translation files for Albanian and French.

= v0.8 = 
* Fixed noticed on dashboard after upgrade and before the update api data was refreshed
* Added translation files for German, Bosnian, Italian, Polish and Romanian.

= v0.7 =
* Completed support for translations
* Added translation files for Japanese
* Fixed issue with calls to get_preferred_from_update_core() when running on a cron hook.

= v0.6 = 
* Update the code to validate the returned upgrade url so as to ensure that we only offer to upgrade to builds that exist.

= v0.5 =
* Initial Release containing support for switching your blog to point release nightlies or bleeding edge nightlies

== Installation ==

1. Upload to your plugins folder, usually `wp-content/plugins/`
2. Activate the plugin on the plugin screen.
3. Navigate to Tools ... Beta Testing to configure the plugin.
4. Visit Tools ... Upgrade and update to the latest Beta Release.

== Screenshots ==

1. This shows the administration page for the plugin
