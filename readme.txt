=== Postalicious Readme ===

Postalicious is a WordPress plugin that automatically posts your del.icio.us bookmarks to your blog. Postalicious uses the psuedo-cron functionality new in WordPress 2.1 to schedule automatic daily updates. If you do not have WordPress 2.1 or later installed, you will still be able to use Postalicious, but you will have to perform the updates manually.

=== Installation ===

To install Postalicious you only need to copy wp-postalicious.php to the wp-content/plugins/ folder found in your WordPress installation. Then go to the “Plugins” tab inside the WordPress administration website and activate Postalicious. Finally you need to enter your del.icio.us account details and other Postalicious preferences in the tab labeled “Postalicious” inside the “Options” tab in the WordPress administration website.

=== Usage ===

Postalicious is very easy to use. You only need to set up your preferences and then click on either the “Activate Daily Updates” or “Update Now” buttons. “Activate Daily Updates” will schedule automatic updates every day at 00:30 GMT and fetch yesterday’s bookmarks. Since del.icio.us sorts all bookmarks based on GMT time, this will ensure that none of your del.icio.us bookmarks goes unnoticed by Postalicious. The “Update Now” button will fetch all the del.icio.us bookmarks that were posted between the last time Postalicious was updated and the last day based on GMT time.

=== Features ===

* Create posts in your blog with your del.icio.us bookmarks.

* Automatically fetch your new bookmarks every day. (Requires WordPress 2.1)

* Set a minimum number of bookmarks needed to create a new post. If the minium number is not met, Postalicious will just create a draft with the new bookmarks and will continue to update the draft until the post reaches the minimum number of bookmarks per post.

* Customize how the posts created by Postalicious look.

* Uses WordPress tags in Wordpress 2.3 or later. Or it integrates with Ultimate Tag Warrior (does not work with embedded tags) and Simple Tagging plugins in earlier versions of WordPress.

=== Version History ===

Version 1.05

* Fixed a bug where automatic updates did not work.

* Fixed a bug where del.icio.us usernames/passwords containing certain characters did not work with Postalicious.

* Added CURL support to fix the problems some hosts have with file_get_contents.

Version 1.06

* Fixed a bug where the date in single day posts was set to Jan 1, 1970.

* Added support for localized dates.

* Fixed a bug where some special characters (such as umlauts) were not dispayed correctly.

* Fixed a bug where the post would always use the default category.

Version 1.07

* Removed support for localized dates since apparently it's messing up the dates for some people.

Version 1.08

* Added back support for localized dates, now it works.

Version 1.09

* Fixed yet more problems with dates.
* Fixed yet more problems with special characters.
* Fixed a bug where drafts could only be updated once.

Version 1.1

* Improved error descriptions.
* Other minor bug fixes.

Version 1.2

* Fixed a bug that could cause some of the dates to be off.
* Made some progress with encodings.
* Fixed an bug where some HTML tags where swallowed from the templates. (Thanks Jonas!)

Version 1.21

* Fixed a small bug I introduced in version 1.2 where too many tags where being escaped.
* Now only users with user levels greater or equal to 6 will be able to access the Postalicious options page.

Version 1.3

* Added support for Simple Tagging plugin. (Thanks Jonas!)
* Added new tag template to customize the way tags are displayed (supports linking the tag's page in your del.icio.us). (Thanks Jonas!)
* Added the ability to only post bookmarks with certain tags.

Version 1.31

* Fixed a problem introduced in version 1.3 where sometimes no bookmarks would be posted.

Version 1.5

* Postalicious now works with WordPress 2.3.
* Added support for WordPress 2.3 tags.
* Added the ability to prevent bookmarks with certain tags from being posted.
* Added a preference to choose whether you want Postalicious to post private bookmarks or not.
* Post created by Postalicious can now have multiple categories.
* Fixed random bugs which didn't really make any difference to the end user.

=== Disclaimer ===

Postalicious is provided on an “as-is” basis and no warranties or guarantees of any kind are promised as to its performance, reliability or suitability.

=== Credits ===

Plugin created by Pablo Gomez (http://neop.gbtopia.com/)

Simple Tagging support and tag templates by Jonas Neugebauer (http://www.aksolutions.de/)

Icon used for the download button created by Utom (http://utombox.com/2006/09/23/my-works/)

Special thanks to everyone who has helped me track bugs down.

Check the latest news and updates regarding Postalicious at http://neop.gbtopia.com/?p=108