=== Postalicious ===
Contributors: neop
Donate link: http://neop.gbtopia.com/?p=108
Tags: bookmarks, delicious, pinboard, diigo, Google Reader, Reddit, Yahoo Pipes, Jumptags
Requires at least: 2.3
Tested up to: 3.0.4
Stable tag: 3.0

Postalicious is a WordPress plugin that automatically posts your delicious, Google Reader, Reddit, Yahoo Pipes, Jumptags, Pinboard, or Diigo bookmarks to your blog.

== Description ==

Postalicious is a WordPress plugin that automatically posts your delicious, Google Reader, Reddit, Yahoo Pipes, Jumptags, Pinboard, or Diigo bookmarks to your blog. The exact details of how your bookmarks are posted are very customizable and are designed to meet your specific needs. Postalicious uses the psuedo-cron functionality introduced in WordPress 2.1 to schedule automatic hourly updates. If you do not have WordPress 2.1 or later installed, you will still be able to use Postalicious, but you will have to perform the updates manually.

== Installation ==

Simply copy "wp-postalicious.php" to the "wp-content/plugins/" folder found in your WordPress installation. Then go to the "Plugins" tab inside the WordPress administration website and activate Postalicious. Finally you need to enter your account type (delicious, Google Reader, Reddit, Yahoo Pipes, Jumptags, Pinboard, or Diigo) and details as well as other Postalicious preferences in the by clicking the link labeled "Postalicious" inside the "Settings" menu on the navigation bar in the WordPress administration website.

== Frequently Asked Questions ==

= What's all this about SimplePie Core?  =

SimplePie is a PHP library that parses RSS feeds. In WordPress 2.8 and later SimplePie is already included, for previous versions of WordPress you need to install the SimplePie plugin, which you can get here: http://wordpress.org/extend/plugins/simplepie-core/

= Which time zone does Postalicious use?  =

All the dates in Postalicious use the time zone specified in the General Options tab in your WordPress administration website. Postalicious does not take into consideration Daylight Saving settings, but this shouldn't be a problem in most cases. Also, the dates used by all the templates in Postalicious are localized in the language of your WordPress installation.

= Does Postalicious work with special (non-English) characters? =

In theory, yes. Postalicious 2.0 introduced some new ways to handle special characters which should work in all WordPress installations. However, if you are having problems with special characters, feel free to contact me.

= Can I used different date formats? =

Yes. Please read the section titled "Custom date formats" for more information about this.

= Can I use HTML tags on the bookmark's description? =

Yes, however you need to specify which tags do you with to allow in the Postalicious preferences. Any tags that are not in the allowed tag will we'll be escaped and appear as-is the the bookmark's description.

= Can Postalicious post one post per bookmark? =

Yes. Just set the both the minimum and the maximum number of bookmarks per post to 1. Additionally, when limiting posts to one bookmark per post the %title% tag becomes available to be used in the post title template.

= Can Postalicious post bookmarks from multiple feeds? =

Yes, thanks to [Meitar Moscovitz](http://maymay.net/), Postalicious can now post bookmarks from multiple feeds. Each WordPress user can specify their own feed in the "Postalicious User Options" section in their profile inside the WordPress admin area. Postalicious will then fetch the newest bookmarks from each user's feed (as well as the default feed in Postalicious settings page) and post all of the bookmarks together in a single post. Unfortunately, all of the specified feeds must use the same service for this to work properly.

= Why aren't the feeds from some of my users publishing? =

Postalicious is careful to honor the blog's user permissions. Double-check that the user whose feed content is not getting published [has the capability to publish posts](http://codex.wordpress.org/Roles_and_Capabilities#publish_posts). Typically, this requires the user to be an `Author`, `Editor`, or `Administrator` on your blog. In a default installation, `Contributor` or `Subscriber` feed content will be added to your WordPress blog as "Pending Review" even if Postalicious is configured to publish posts instead of creating new drafts.

= Why are tag-related features disabled? =

Not all services support tags, other services do support tags but don't make them available on the RSS feed. In each case I've tried to support tags whenever possible.

== Usage ==

Postalicious is very easy to use. You only need to set up your preferences and then click on either the "Activate Hourly Updates" or "Update Now" buttons.  "Activate Daily Updates" will schedule automatic updates every hour and fetch any new bookmarks you have added. The "Update Now" button retrieves any new bookmarks found when you click it.

== Features ==

* Automatically create posts in your blog with your bookmarks that allow you to keep your blog updated just by bookmarking your favorite websites!
* Works with delicious, Google Reader, Reddit, Yahoo Pipes, Jumptags, Pinboard, and Diigo.
* Complete control over how often your bookmarks are posted and how many bookmarks should appear on each post.
* If the post is not ready for prime time, Postalicious creates a draft with the pending bookmarks which you can publish any time or wait for Postalicious to publish it when it meets your publishing settings.
* Full customization on the look of posts created by Postalicious, including templates for the post slug, post title, and post body.
* Integrates with WordPress 2.3 tags, or with Ultimate Tag Warrior and Simple Tagging plugins in earlier version of WordPress.
* Filter the bookmarks that are posted to your blog depending on how you tagged them.
* Logs all the activity so that you know what Postalicious did and when.

== Changelog ==

= Version 3.0 =

* Removed gnolia support.
* Added support for Pinboard and Diigo.
* Added a button to reset all settings.
* Cleanup and minor bug fixes.

= Version 2.9.3 =

* Add `%author_name%` and `%source_link%` template variables (in Delicious.com service only). (Thanks Meitar!)
* Minor formatting improvements on settings page. (Thanks Meitar!)

= Version 2.9.2 =

* Fixed tag urls for delicious.

= Version 2.9.1 =

* Postalicious now honors user capabilities; if a user can not publish posts on the blog, then their feed content will be inserted as Pending Review instead of Published even if Postalicious is configured to publish posts (instead of creating draft posts).

= Version 2.9 =

* Added support for publishing bookmarks from multiple sources. For details on how this works, check the FAQ. (Thanks Meitar!)
* Added %currentdate% to the title and body templates.

= Version 2.8.3 =

* Added a possible fix for the "500 internal server error" issue some people have been reporting. (Thanks Rick!)

= Version 2.8.2 =

* Changed the user-agent used to fetch the feeds, maybe this will fix the recent problems between Postalicioius and delicious feeds.

= Version 2.8.1 =

* Fixed a bug where Postalicious kept updating the delicious URL.

= Version 2.8 =

* Postalicious now uses the bundled SimplePie version when available.
* Some people where having problems with Postalicious being unable to fetch the delicious feeds, unfortunately there's not much I can do about it since it's caused by a change in the delicious's server configuration. A workaround is to route the delicious RSS feed through some service such as Feedburner. To enable this functionality, I've changed delicious to be a URL service.

= Version 2.7rc1 =

* Completely revamped the limiting system. The new system is much more flexible and simpler to use.
* Postalicious now uses the post scheduling provided by WordPress to publish posts at the right time.
* Added custom fields with the bookmark's info to posts created by Postalicious with only one bookmark.
* Improved the excerpt template. Leaving it blank will now let WordPress automatically generate it.
* Added Jumptags support.
* Fixed a few minor bugs.
* Fixed a few typos.
* General code cleanup.

= Version 2.6 =

* Added post excerpt template.

= Version 2.5 =

* Fix a really weird bug which probably no one would have encountered anyway.
* Fixed a typo in the options page.
* Did a bit of code cleanup.
* I think we are done with bugs, so I'm tagging this as the final 2.5 release.

= Version 2.5rc4 =

* Fixed a typo in the code which prevented new users from updating.

= Version 2.5rc3 =

* Fixed a bug with some dates not being parsed correctly.
* Fixed a bug which terminated execution with certain services.

= Version 2.5rc2 =

* Switched to SimplePie Core instead of including a copy of SimplePie.

= Version 2.5rc1 =

* Got rid of the rss.php file, updating it every time a new version of WordPress was released was not very user-friendly.
* Postalicious now uses SimplePie to handle RSS feeds.
* Updated the settings page interface to match the new interface of Settings in the latest versions of WordPress.
* Improved the wording of the settings both to match the style WordPress uses and to make the settings page easier to understand.
* Fixed a lot of typos in the readme file.
* Postalicious now asks if you want to save any changes before (de)activating hourly updates or doing an update if any unsaved changes were made.
* Improved support for Google Reader, ma.gnolia and Reddit.
* Added a donation button to the Postalicious settings page...because the newer versions of WordPress are making it harder for Postalicious users to reach my website where I can mind-trick them into donating (hopefully).

= Version 2.0rc6 =

* Postalicious now works with delicious 2.0 (aka. delicious.com).
* Updated rss.php file to be based on WordPress 2.6 modified file.

= Version 2.0rc5 =

* Added some safeguards to prevent the "Potalicious is already updating at the moment." message after there was a PHP error in the last update.
* Fixed some more problems in PHP4 installations.
* Fixed a typo in the code which made the "bookmark's tags" option not work.

= Version 2.0rc4 =

* Fixed another double posting bug when certain combination of settings was being used.
* Removed the "Enable Tags" option since it didn't really make much sense since WordPress added native tag support.

= Version 2.0rc3 =* Added Yahoo Pipes support.* Fixed a bug where the options page did not show up in some WordPress installations.* Fixed a bug where the tags for all posts were ignored.</li>* Fixed a bug where some bookmarks could keep getting posted repeatedly or be skipped.

= Version 2.0rc2 =

* Fixed a bug where the user list did not show up in WordPress installations with custom table prefixes.* Fixed several issues when running Postalicious on PHP 4.** The options page is now valid XHTML1.0 Transitional. (Thanks Shelly)* Fixed some issues with the allowed HTML tags.* Added support for Reddit.* Made the service-related code more easily customizable.* Fixed a bug where Postalicious ignored the settings for what to do when a blog author publishes a draft.* Fixed a bug with updating maximum/minimum number of bookmarks per post when the minimum is greater than the maximum.

= Version 2.0rc1 =

* Added native support for ma.gnolia and support for Google Reader feeds.
* Postalicious now comes with a modified version of rss.php which is required for Google Reader support and tag support in ma.gnolia.
* RSS feeds are now used instead of the delicious and ma.gnolia APIs.
* Private bookmark posting is now no longer possible because of the switch to RSS feeds.
* Automatic updates are now done every hour instead of every day.
* Added support for custom date formats in all templates.
* Added slug template.
* Added several post limiting options.
* Changed templates from single date and double date to single day and multiple days.
* Added support for %date% in the bookmark template.
* %title% is now replaced in the one day post title template if Postalicious is set to post exactly one bookmark per post.
* Added activity logging.
* Added an option to allow certain HTML tags in the bookmark's description.
* All of the code that handles bookmark fetching was rewritten and is now much cleaner.
* The code is now understandable, lots of comments were added to explain what the code does.
* Improved handling of character encodings.
* Switched from directly using cURL of file_get_contents to using the MagpieRSS version included in WordPress which uses Snoopy to fetch the RSS feeds.
* Improved the handling of tags, many tag-related bugs were solved.
* Added some safety checks to prevent function redeclaring if the plugin was loaded twice and to prevent Postalicious from starting an update while an update is already in progress.
* Note: Although I did some extensive bug testing to try to find as many bugs as possible, it's likely that a few bugs still persist which is why I decided to release this version as 2.0 release candidate 1. Please let me know if you find any bugs with this version of Postalicious.

= Version 1.5 =

* Postalicious now works with WordPress 2.3.
* Added support for WordPress 2.3 tags.
* Added the ability to prevent bookmarks with certain tags from being posted.
* Added a preference to choose whether you want Postalicious to post private bookmarks or not.
* Post created by Postalicious can now have multiple categories.
* Fixed random bugs which didnt really make any difference to the end user.

= Version 1.31 =

* Fixed a problem introduced in version 1.3 where sometimes no bookmarks would be posted.

= Version 1.3 =

* Added support for Simple Tagging plugin. (Thanks Jonas!)
* Added new tag template to customize the way tags are displayed (supports linking the tags page in your delicious). (Thanks Jonas!)
* Added the ability to only post bookmarks with certain tags.

= Version 1.21 =

* Fixed a small bug I introduced in version 1.2 where too many tags where being escaped.
* Now only users with user levels greater or equal to 6 will be able to access the Postalicious options page.

= Version 1.2 =

* Fixed a bug that could cause some of the dates to be off.
* Made some progress with encodings.
* Fixed an bug where some HTML tags where swallowed from the templates. (Thanks Jonas!)

= Version 1.1 =

* Improved error descriptions.
* Other minor bug fixes.

= Version 1.09 =

* Fixed yet more problems with dates.
* Fixed yet more problems with special characters.
* Fixed a bug where drafts could only be updated once.

= Version 1.08 =

* Added back support for localized dates, now it works.

= Version 1.07 =

* Removed support for localized dates since apparently its messing up the dates for some people.

= Version 1.06 =

* Fixed a bug where the date in single day posts was set to Jan 1, 1970.
* Added support for localized dates.
* Fixed a bug where some special characters (such as umlauts) were not dispayed correctly.
* Fixed a bug where the post would always use the default category.

= Version 1.05 =

* Fixed a bug where automatic updates did not work.
* Fixed a bug where delicious usernames/passwords containing certain characters did not work with Postalicious.
* Added CURL support to fix the problems some hosts have with file_get_contents.

== Custom date formats ==

Postalicious 2.0 introduces the ability to display dates in different formats. All of the templates in Postalicious that allow %date%, %datestart% or %dateend% now support custom formats. If used by themselves, %date%, %datestart% and %dateend% will use the default date format template to display the date. To use a custom format, you only need to add the date format you wish to be used to display the date enclosed by '{' and '}' before the second '%' sign. For example %datestart{F jS}% will be replaced by the start date using the format: 'F jS'. The formatting options are the ones used by PHP which can be found here: http://www.php.net/date . You can use as many custom dates as you want in each template, and you can combine the use of custom and default dates.

== Credits ==

Plugin created by Pablo Gomez (http://neop.gbtopia.com/)

Simple Tagging support and tag templates by Jonas Neugebauer (http://www.aksolutions.de/)

SimplePie is © 2004Ð2008 Ryan Parman, Geoffrey Sneddon and contributors (http://simplepie.org/) Licensed under the BSD License (http://www.opensource.org/licenses/bsd-license.php)

Icon used for the download button created by Utom (http://utombox.com/my-works/)

Special thanks to everyone who has helped me track bugs down.
