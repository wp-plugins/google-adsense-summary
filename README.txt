=== Google Adsense Summary ===
Contributors:  agentc0re
Website:  http://learnix.net
Donate link:  http://learnix.net
Email:  gas (at) learnix (dot) net
Tags:  google, adsense, stats, summary, tracking, wordpress, plugin, widget, admin, advertising
Requires at least:  2.9.2
Tested up to:  2.9.2
Stable version:  1.0.3
Stable Tag: 1.0.3

== Description ==
Adds a dashboard widget displaying your adsense data in the following ranges: TODAY, YESTERDAY, LAST7DAYS, THISMONTH, LASTMONTH and ALLTIME.

This plugin requires that you have curl on your webserver and that php has the curl module loaded.  On a linux webserver you can find this out by typing the following at a command prompt (minus the quotes):
* "php -i | grep curl"

For the module:

* "cat /etc/httpd/php.ini | grep curl"
OR
* "cat ~/php.ini | grep curl"

If you have all that, it's possible that if curlwrappers was not build into php that it will not work.  This hasn't been verified yet and if anyone that does have this working could check their phpinfo() and notices that they don't have it enabled, let me know so we can confirm it.

If the last two options don't work, consult your hosting provider as your php.ini is in some none standard location.

**IMPORTANT**

Upgrade to 1.0.3!  Important Bug Fix.  Please read the changelog before upgrading!

== Features ==

* Allows you to pick which time line of data that you would like to support
* Displays the information in a widget on the dashboard for easy viewing

= Future Features =

* If all time line ranges are selected, it will be better formatted on the widget
* Better format in general.
* More options to choose and display EXACTLY what you want.  IE:  not displaying "DOMAINS" information and only showing your "Earnings"
* Storing the data in the database and only updating it ever 30 minutes

== Installation ==

Just like any other wordpress plugin:

* Download it.
* Unzip into your wordpress/wp-content/plugins directory
* Active the plugin via your Administrative Interface.
* SEE -> [Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

Once activated, all  you need to do is enter your google adsense **Username** and **Password** in the settings page.  Wala!  Go to the dashboard to see your stats.

== Upgrade Notice ==

N/A

== Screenshots ==

Screenshots violate the Adsense ToC.

== Frequently Asked Questions ==

= Q. Is this plugin support by you? =

A.  Yes it is.  However since I do this in my spare time, I can't guarentee that I will be able to help everyone.



= Q.  Do I have to pay for the support? =

A.  No.


= Q.  I am getting a message that says "Login Attempt Failed"  What should I do? =

A.  First thing is first, check your Username and Password.  Are they correct?  Do NOT email those to me!!!  Email me ONLY if they're wrong.  Next, lets go through this little checklist.

* If your User/Pass were showing incorrectly, try reentering them in the options page.  If it's still showing incorrect after that, E-Mail me letting me know (Do not send me your user/pass).  I'll need to know your Wordpress version and MySQL version.
* Does your webserver support curl with php?  Verify with "php -i | grep curl" and checking to make sure your curl module is loaded in your php.ini.
* Save all errors you get and email them to me.  They will look something like this:  "wp-content/plugins/google-adsense-summary/google_adsense_summary.php:778"  <--That is very important



= Q.  I love your plugin and I want to donate for your efforts so far. =

A.  Cool.  Contact me at gas (at) learnix (dot) net.  If requests to donate are frequent enough I may setup a paypall donation link.



= Q.  I freaking donated money for your plugin but you still haven't done X! =

A.  A donation is just that, donating for my efforts thus far and saying thank you.  I will not give special treatment to those that donate over those that do not.  I will consider all suggestions equally and if I like it, I will mention your name when it's implimented.



= Q.  Why haven't you fixed X yet?  I could have done it by now you worthless POS!! =

A.  I'm busy, I have a life and I can't possibly test everything.  If you happen to find a bug and/or know how to fix it, please just email me the code.  If you don't know how to fix it, I need a very good description of what is going on and how to reproduce it.

== Changelog ==

= 1.0.0 =
* Initial creataion and release

= 1.0.1 =
* Bug fix.  Had a comment on the last php closing tag which caused a few issues with other plugins and feed's.  -Thanks Eric!
* Added some extra checks if the login process doesn't work.
* Updated the README.txt for some troubleshooting steps

= 1.0.2 =
* Bug Fix.  Fixed a possible issue that may cause a problem on where the cookie files were trying to be saved.  It now should check your system paths for your temp directory and save the cookie files there
* Bug Fix.  Fixed an issue where multiple 0 byte cookie files were being created and not deleted.  You will need to manually delete all of these files.  The name starts with, "adsense_" and after the underscore will be some random numbers and letters.  Delete all that you can find.  If you are unsure how to do that, ask your hosting provider how you can find all files that match "adsense_" and delete them.
On a linux host you can try a few things if you know how to ssh into your server:
find /tmp -type -f -name "adsense_*"
If files are found you can:
rm /tmp/adsense_*
Some hosting providers might have your tmp directory else where.  You can change the find to your home dir.
find ~ -type -f -name "adsense_*"
If files are found and are in the same tmp dir and nothing that looks important you can do:
find ~ -type -f -name "adsense_*" -exec rm {} \;
This finds them and deletes them at the same time.  Or you can use the rm command to take care of them

= 1.0.3 =
* Made it so that the cookie files are getting temporarily saved in the google-adsense-summary plugin directory.