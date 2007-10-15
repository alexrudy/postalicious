=== Postalicious ===
Contributors: neop
Donate link: http://neop.gbtopia.com/?p=108
Tags: bookmarks, del.icio.us
Requires at least: 2.1
Tested up to: 2.3
Stable tag: trunk

Postalicious is a WordPress plugin that automatically posts your del.icio.us bookmarks to your blog.

== Description ==

Postalicious is a WordPress plugin that automatically posts your del.icio.us bookmarks to your blog. Postalicious uses the psuedo-cron functionality new in WordPress 2.1 to schedule automatic daily updates. If you do not have WordPress 2.1 or later installed, you will still be able to use Postalicious, but you will have to perform the updates manually.

== Installation ==

To install Postalicious you only need to copy wp-postalicious.php to the wp-content/plugins/ folder found in your WordPress installation. Then go to the "Plugins" tab inside the WordPress administration website and activate Postalicious. Finally you need to enter your del.icio.us account details and other Postalicious preferences in the tab labeled "Postalicious" inside the "Options" tab in the WordPress administration website.

== Frequently Asked Questions ==

= I have some bookmarks in my del.icio.us account, however when I click the "Update Now" button, nothing happens. Whats wrong? =

Theres probably nothing wrong, however, you should understand how Postalicious works. When Postalicious does its daily update or when you click the "Update Now" button Postalicious finds out the current time in GMT, and then contacts del.icio.us to get the new bookmarks since the last time Postalicious updated your bookmarks up until the last day before the current day in GMT. If you are running Postalicious for the first time, then it will only get the bookmarks for the previous day according to GMT time. So no matter how many times you click the "Update Now" button, it will not fetch your recently added bookmarks until the day (in GMT) is over.
For those of you wondering why I chose to make Postalicious behave that way, the answer is very simple. The main purpose of Postalicious is to automatically create posts with your bookmarks without you having to worry about it. By updating with only the bookmarks up to the previous day, Postalicious makes sure that it only needs to run once per day, and that after adding the bookmarks for one day, there will be no more new bookmarks for that day. Also, for those of you wondering why I even bothered with an "Update Now" button when it does the exact same thing as the daily updates, the reason is that I wanted people who were not using WordPress 2.1 to be able to use Postalicious, with the only difference being that they have to do the updates manually.

= The "Last Update" time does match the actual time at which the update took place =

The last update time is set according to your servers time zone.

= Can Postalicious only post bookmarks that have certain tag? =

This feature was added on version 1.3.

= Can Postalicious stop bookmarks that have certain tag from being posted? =

This feature was added on version 1.5.

= Can I stop Postalicious from posting private bookmarks? =

This feature was added on version 1.5.

= Does Postalicious work with special (non-English) characters? =

In theory, yes. However, I have discovered that there are some issues with certain WordPress/PHP configurations where special characters are not displayed correctly. If you are experiencing any problems with special characters in Postalicious try uncommenting (remove the //) the line that reads:
//$rawxml = utf8_decode($rawxml);
around line 540 (exact line number varies depending on the version of Postalicious youre using). This may fix the problem. If anyone could shed some light on this problem I would appreciate it.

== Features ==

* Create posts in your blog with your del.icio.us bookmarks.
* Automatically fetch your new bookmarks every day. (Requires WordPress 2.1)
* Set a minimum number of bookmarks needed to create a new post. If the minimum number is not met, Postalicious will just create a draft with the new bookmarks and will continue to update the draft until the post reaches the minimum number of bookmarks per post.
* Customize how the posts created by Postalicious look.
* Uses WordPress tags in Wordpress 2.3 or later. Or it integrates with Ultimate Tag Warrior (does not work with embedded tags) and Simple Tagging plugins in earlier versions of WordPress.

== Version History ==

=Version 1.05=

* Fixed a bug where automatic updates did not work.
* Fixed a bug where del.icio.us usernames/passwords containing certain characters did not work with Postalicious.
* Added CURL support to fix the problems some hosts have with file_get_contents.

=Version 1.06=

* Fixed a bug where the date in single day posts was set to Jan 1, 1970.
* Added support for localized dates.
* Fixed a bug where some special characters (such as umlauts) were not dispayed correctly.
* Fixed a bug where the post would always use the default category.

=Version 1.07=

* Removed support for localized dates since apparently its messing up the dates for some people.

=Version 1.08=

* Added back support for localized dates, now it works.

=Version 1.09=

* Fixed yet more problems with dates.
* Fixed yet more problems with special characters.
* Fixed a bug where drafts could only be updated once.

=Version 1.1=

* Improved error descriptions.
* Other minor bug fixes.

=Version 1.2=

* Fixed a bug that could cause some of the dates to be off.
* Made some progress with encodings.
* Fixed an bug where some HTML tags where swallowed from the templates. (Thanks Jonas!)

=Version 1.21=

* Fixed a small bug I introduced in version 1.2 where too many tags where being escaped.
* Now only users with user levels greater or equal to 6 will be able to access the Postalicious options page.

=Version 1.3=

* Added support for Simple Tagging plugin. (Thanks Jonas!)
* Added new tag template to customize the way tags are displayed (supports linking the tags page in your del.icio.us). (Thanks Jonas!)
* Added the ability to only post bookmarks with certain tags.

=Version 1.31=

* Fixed a problem introduced in version 1.3 where sometimes no bookmarks would be posted.

=Version 1.5=

* Postalicious now works with WordPress 2.3.
* Added support for WordPress 2.3 tags.
* Added the ability to prevent bookmarks with certain tags from being posted.
* Added a preference to choose whether you want Postalicious to post private bookmarks or not.
* Post created by Postalicious can now have multiple categories.
* Fixed random bugs which didnt really make any difference to the end user.