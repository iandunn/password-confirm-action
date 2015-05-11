=== Password Confirm Action ===
Contributors: stephenharris
Requires at least: 4.2.2
Tested up to: 4.2.2
Stable tag: 0.2.0
License: GPLv2 or later

Prompts the user for their password whenever they try to perform an action which could be used by an attacker to escalate privileges or engineer future access.


== Description ==

= Context =

Please see [Trac Ticket 20140](https://core.trac.wordpress.org/ticket/20140).

XSS attacks and 'lunch time raid' attacks, among others, can allow an attacker to 'steal' a log-in session, and act as an authenticated user without knowing that user's password. 
The aim of this plugin is to prevent that user from being able to engineer permanent access to the site. They may attempt to do this by doing one or more of the following:
 
 - Setting the password of the hijacked user to one of their choosing
 - Changing the e-mail of the hijacked user
 - Creating a new user 
 - Changing the role of their account to escalate privileges
 
The plugin prevents the attacker from doing any of these by prompting them for the user's password.
 
= Caveat = 
 
Of course by default WordPress allows adminstrative users the ability to install arbitrary plugins and themes, and edit existing plugins/themes through in-built editors. These freedoms
render the above solution impotent. It is outside of the immediate scope of this plugin to password protect those features, though it may be considered at later date. 
It's the advice of the plugin author that you should disable such features in your site's `wp-config.php` by adding:

`
define( 'DISALLOW_FILE_MODS', true );
`

as outlined in [https://codex.wordpress.org/Editing_wp-config.php#Disable_plugin_and_Theme_Update_and_Installation](https://codex.wordpress.org/Editing_wp-config.php#Disable_plugin_and_Theme_Update_and_Installation).


**To report bugs or feature requests, please use [Github issues](http://github.com/stephenharris/password-confirm-action/issues).**

= Can I Help? =

**Yes! Please do!**. You could do either of the following:

1. Use the plugin and [report any issues](http://github.com/stephenharris/password-confirm-action/issues).
2. Find an [unassigned issue](http://github.com/stephenharris/password-confirm-action/issues) and start working on it (please make PRs to the develop branch).

If you have an expertise in accessibility I would welcome any suggestions or improvements. Or if you encounter any issues regarding accessibility please do report these.   

= A special thanks =

A special thanks to Human Made whose [Require Password](https://github.com/humanmade/hm-require-password) plugin (written by Jenny Wong) served as an inspiration for this plugin.

== Installation ==

= Manual Installation =

1. Upload the entire `/password-confirm-action` directory to the `/wp-content/plugins/` directory.
2. Activate Password Confirm Action through the 'Plugins' menu in WordPress.


== Screenshots ==


== Changelog ==

= 0.2.0 =
* Initial release on the wordpress.org repository.

= 0.1.1 =
* The 0.1.0 version didn't actually work...
* Fixes modal hiding highlighted invalid fields (See [#1](https://github.com/stephenharris/password-confirm-action/issues/1)).

= 0.1.0 =
* First release


== Upgrade Notice ==
