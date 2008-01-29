=== Postalicious ===
Contributors: neop
Donate link: http://neop.gbtopia.com/?p=108
Tags: bookmarks, del.icio.us, ma.gnolia, Google Reader
Requires at least: 2.1
Tested up to: 2.3.2
Stable tag: 2.0

Postalicious is a WordPress plugin that automatically posts your del.icio.us, ma.gnolia, or Google Reader bookmarks to your blog.

== Description ==

Postalicious is a WordPress plugin that automatically posts your del.icio.us, ma.gnolia, or Google Reader bookmarks to your blog. The exact details of how your bookmarks are posted is very customizable and is designed to meet your specific needs. Postalicious uses the psuedo-cron functionality introduced in WordPress 2.1 to schedule automatic hourly updates. If you do not have WordPress 2.1 or later installed, you will still be able to use Postalicious, but you will have to perform the updates manually.

== Installation ==

To install Postalicious copy "wp-postalicious.php" to the "wp-content/plugins/" folder found in your WordPress installation. Then go to the "Plugins" tab inside the WordPress administration website and activate Postalicious. Additionally, if you want to use the tag related features in Postalicious with ma.gnolia or if you want to use Postalicious with Google Reader you need to installed the modified version of "rss.php" that comes with Postalicious. To do this, simply copy the "rss.php" file that comes with Postalicious to the the "wp-includes" folder in your WordPress installation, replacing the existing file. For more information about the "rss.php" file, please refer to the section titled "About the rss.php file". Finally you need to enter your account type (del.icio.us, ma.gnolia, or Google Reader) and details as well as other Postalicious preferences in the tab labeled "Postalicious" inside the "Options" tab in the WordPress administration website.

== Frequently Asked Questions ==

= I just added some bookmarks but Postalicious is not picking them up right away, what's wrong? =

Postalicious 2.0 uses the MagpieRSS version included with WordPress. One of the features of MagpieRSS is that it caches the feeds it retrieves so that they can be used often without having to download the feed every time. By default, the cache created by MagpieRSS lasts one hour, which means that any new bookmarks added within one hour of the last update will not be picked up until the cache expires one hour later. Additionally, some services, such as del.icio.us, don't immediately update the rss feeds for the user's bookmarks which may also cause a delay between the time you post the bookmarks and the time when Postalicious is able to grab them.

= Which time zone does Postalicious use?  =

All the dates in Postalicious use the time zone specified in the General Options tab in your WordPress administration website. Postalicious does not take into consideration Daylight Saving settings, but this shouldn't be a problem in most cases. Also, the dates used by all the templates in Postalicious are localized in the language of your WordPress installation.

= Does Postalicious work with special (non-English) characters? =

In theory, yes. Postalicious 2.0 introduced some new ways to handle special characters which should work in all WordPress installations. However, if you are having problems with special characters, feel free to contact me.

= How do all the limiting options work? =

"No limit" - publishes posts as soon after an update that adds enough bookmarks to meet the minimum number of bookmarks per post.
"At most # bookmarks per post" - Publishes posts when they reach the maximum number of posts, or after an update if the number of bookmarks is greater than the minimum, even if it is less than the maximum. If the minimum number of posts and the maximum number of posts are the same, then posts will have exactly that number of bookmarks.
"Keep at least # hours between posts" - The same as "No Limit", but it will hold posts back until the determined number of hours has passed after the last published posts. It is important to note, that if a post has the minimum number of bookmarks, it will be posted as soon as the determined number of  hours have passed even if no new bookmarks were added to the post in the last update.
"Only post once every # hours at (time)" - Similar to the previous option, but in this case posts will only be published once every certain number of days at the first update after the specified time. As with the previous option, if an unpublished posts has enough bookmarks to meet the required minimum it will be published during the first update after the timing conditions are met. This behavior is intended to emulate previous version of Postalicious which only attempted to publish one post per day after 12:30 GMT. It should be noted that the time must be specified in the same time zone that established in the General Options tab in WordPress.

= Can I used different date formats? =

Yes. Please read the section titled "Custom date formats" for more information about this.

= Can I use HTML tags on the bookmark's description? =

Yes, however you need to specify which tags do you with to allow in the Postalicious preferences. Any tags that are not in the allowed tag will we'll be escaped and appear as-is the the bookmark's description.

= Can Postalicious post one post per bookmark? =

Yes. Just set the both the minimum and the maximum number of bookmarks per post to 1. Additionally, when limiting posts to one bookmark per post the %title% tag becomes available to be used in the post title template.

= Why is Google Reader support disabled? =

You need to install the modified version of rss.php bundled with Postalicious to enable Google Reader support. For information on how to install it, please refer to the "Installation" section. For more information about why a modified version of rss.php is needed, please read the secion titled "About the rss.php file".

= Why are tag-related features disabled? =

If you are using ma.gnolia, then you need to install the modifed version of rss.php bundled with Postalicious to enable tag-related features. For information on how to install it, please refer to the "Installation" section. For more information about why a modified version of rss.php is needed, please read the secion titled "About the rss.php file".

If you are using Google Reader, then tag-related features are unavailable since the bookmark's tags are not published in the RSS feed provided by Google Reader.

== Usage ==

Postalicious is very easy to use. You only need to set up your preferences and then click on either the "Activate Hourly Updates" or "Update Now" buttons.  "Activate Daily Updates" will schedule automatic updates every hour and fetch any new bookmarks you have added. The "Update Now" button retrieves any new bookmarks found when you click it. Due to the caching features of MagpieRSS, any updates within one hour of the previous update will not fetch any new features.

== Features ==

* Automatically create posts in your blog with your bookmarks that allows you to keep your blog updated by just bookmarking your favorite websites!
* Complete control over how often your bookmarks are posted and how many bookmarks should appear on each post.
* If the post is not ready for prime time, Postalicious creates a draft with the pending bookmarks which you can publish any time or wait for Postalicious to publish it when it meets your publishing settings.
* Full customization on the look of posts created by Postalicious, including templates for the post slug, post title, and post body.
* Integrates with WordPress 2.3 tags, or with Ultimate Tag Warrior and Simple Tagging plugins in earlier version of WordPress.
* Support for del.icio.us, ma.gnolia and Google Reader.
* Filter the bookmarks that are posted to your blog depending on how you tagged them.
* Logs all the activity so that you know what Postalicious did and when.

== Version History ==

= Version 1.05 =

* Fixed a bug where automatic updates did not work.
* Fixed a bug where del.icio.us usernames/passwords containing certain characters did not work with Postalicious.
* Added CURL support to fix the problems some hosts have with file_get_contents.

= Version 1.06 =

* Fixed a bug where the date in single day posts was set to Jan 1, 1970.
* Added support for localized dates.
* Fixed a bug where some special characters (such as umlauts) were not dispayed correctly.
* Fixed a bug where the post would always use the default category.

= Version 1.07 =

* Removed support for localized dates since apparently its messing up the dates for some people.

= Version 1.08 =

* Added back support for localized dates, now it works.

= Version 1.09 =

* Fixed yet more problems with dates.
* Fixed yet more problems with special characters.
* Fixed a bug where drafts could only be updated once.

= Version 1.1 =

* Improved error descriptions.
* Other minor bug fixes.

= Version 1.2 =

* Fixed a bug that could cause some of the dates to be off.
* Made some progress with encodings.
* Fixed an bug where some HTML tags where swallowed from the templates. (Thanks Jonas!)

= Version 1.21 =

* Fixed a small bug I introduced in version 1.2 where too many tags where being escaped.
* Now only users with user levels greater or equal to 6 will be able to access the Postalicious options page.

= Version 1.3 =

* Added support for Simple Tagging plugin. (Thanks Jonas!)
* Added new tag template to customize the way tags are displayed (supports linking the tags page in your del.icio.us). (Thanks Jonas!)
* Added the ability to only post bookmarks with certain tags.

= Version 1.31 =

* Fixed a problem introduced in version 1.3 where sometimes no bookmarks would be posted.

= Version 1.5 =

* Postalicious now works with WordPress 2.3.
* Added support for WordPress 2.3 tags.
* Added the ability to prevent bookmarks with certain tags from being posted.
* Added a preference to choose whether you want Postalicious to post private bookmarks or not.
* Post created by Postalicious can now have multiple categories.
* Fixed random bugs which didnt really make any difference to the end user.

= Version 2.0rc1 =

* Added native support for ma.gnolia and support for Google Reader feeds.
* Postalicious now comes with a modified version of rss.php which is required for Google Reader support and tag support in ma.gnolia.
* RSS feeds are now used instead of the del.icio.us and ma.gnolia APIs.
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

== About the rss.php file ==

Postalicious 2.0 uses the MagpieRSS version bundled with WordPress to fetch the RSS feeds for del.icio.us, ma.gnolia and Google Reader. Unfortunately, the original way in which MagpieRSS handles more than one child elements with the same name in the same item on the feed does not allow Postalicious to work properly with ma.gnolia and Google Reader feeds. In the case of ma.gnolia, the tags for the bookmark can not be retrieved, in the case of Google Reader, the bookmark's title and description can not be retrieved. So if you want to use these features with Postalicious you must install the modified rss.php file which comes with Postalicious. Postalicious will automatically detect if the modified version is installed or not, and if it isn't Google Reader and tag-related support in ma.gnolia will be disabled. The modified version is backwards compatible with the unmodified version so any other plugins that use MagpieRSS should be unaffected. I am aware that there are other plugins which use their own modified versions of rss.php file, if you have already installed a modified version of rss.php and want to use Postalicious, please feel free to contact me and I'll see what can I do to help you. For instructions on how to install the rss.php file, please read the "Installation" section.

Important Note: Updating WordPress to a newer version will remove the modified version of rss.php so if you wish to continue using Postalicious you will have to re-install it. The version of Postalicious that can be found on my website will always contain an updated version of rss.php which works with both Postalicious and the latest WordPress version. The current modified version of rss.php is based on the rss.php file found in WordPress 2.3.2, but since the rss.php file has remained relatively unchanged in the last few versions of WordPress, then there should be no problem if you replace this file in an older version of WordPress.

There's also some debate about MagpieRSS support vs SimplePie for future version of WordPress, and it might be better to bundle RSS-handling functions with Postalicious instead of changing a core WordPress file, but most people use Postalicious with del.icio.us, so this shouldn't be much of a problem for now. Future version of Postalicious might take a different approach to solving this problem though.

== Custom date formats ==

Postalicious 2.0 introduces the ability to display dates in different formats. All of the templates in Postalicious that allow %date%, %datestart% or %dateend% now support custom formats. If used by themselves, %date%, %datestart% and %dateend% will use the default date format template to display the date. To use a custom format, you only need to add the date format you wish to be used to display the date enclosed by '{' and '}' before the second '%' sign. For example %datestart{F jS}% will be replaced by the start date using the format: 'F jS'. The formatting options are the ones used by PHP which can be found here: http://www.php.net/date . You can use as many custom dates as you want in each template, and you can combine the use of custom and default dates.

== Credits ==

Plugin created by Pablo Gomez (http://neop.gbtopia.com/)

Simple Tagging support and tag templates by Jonas Neugebauer (http://www.aksolutions.de/)

Icon used for the download button created by Utom (http://utombox.com/my-works/)

Special thanks to everyone who has helped me track bugs down.