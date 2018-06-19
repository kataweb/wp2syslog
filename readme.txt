=== wp2syslog ===
Contributors: psicosi448
Donate link: 
Tags: logging,events,actions,syslog
Requires at least: 3.3
Tested up to: 4.2
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

wp2syslog -- Global logging facility for WordPress (WPsyslog revisited)

== Description ==

**End users can benefit from wp2syslog:**   

*	Log entries for core events help to keep track of the daily business, especially on multiuser blogs.
*	Log entries for core events can raise awareness for threats and problems, for example abuse attempts.
*	Log entries triggered by plugins allow the user to comprehend the functionality of the plugin.

**Developers can benefit in two ways from wp2syslog:**  

* At each point of the code, a log entry can be triggered. No need to print to the browser, just let wp2syslog put it in the database, and you can have a look later.
* You can develop your plugins with support for wp2syslog. This will give your users the chance to better understand what your plugin is doing (see doc/specs.html).

== Installation ==

1. Just copy wp2syslog folder to the wp-content/plugins/ folder
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently asked questions ==



== Screenshots ==

1. Main configuration page
2. Show log from database
2. Show log, with query filter

== Changelog ==

= 1.0.5 =
* fix: compatibility with php 7
* sec: better input sanitize

= 1.0.4 =
* fix: load css only in showlog page

= 1.0.3 =
* fix: syntax error in table creation

= 1.0.2 =
* fix: compatibility with php 5.3 (sorry, php -l *php won't work as I expected).

= 1.0.1 =
* fix: Backward compatibility with php 5.3. Warranty: I've not fully tested with php 5.3.

= 1.0.0 =
* changes: Refactoring with helping of some phpunit tests.
* changes: The string ident 'core', which is added to each wp core message, is changed to more eloquent 'wpcore'.
* new: added client ip and user-agent to infos logged by default

= 0.2.3 =
* bugfix: error in wp2syslog function (thanks Bobo)
          Now is possible to use it in others plugins.

= 0.2.2 =
* bugfix: typo in event function declaration (thanks Chris)

= 0.2.1 =
* fix annoying deprecated function


== Upgrade notice ==



== Credits ==

This plugin is forked from first 0.2 version of http://www.ossec.net/wpsyslog2/
already forked from the wpsyslog plugin by Alex Guensche ( http://www.zirona.com/software/donate ).

The firts version of this plugin was developed during a project with the German "Menschen für Tierrechte" (Humans for Animal Rights).
It is an organisation of activists who advocate animal rights and fight against abuse of animals. They generously decided to donate
this work to the general public by letting Zirona put it under the GNU GPL and allowing us to promote and distribute it.

Please support the work of Humans for Animal Rights, e.g. by placing a link to [Humans for Animal Rights](http://www.tierrechte.de/ "www.tierrechte.de") and
spreading the word. Not (only) because of this plugin, but generally for a better treatment of the beings on this planet.
