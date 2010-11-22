IRCBOT
======
So, this is a simple IRC bot what I wrote. Needs some work.

Requirements
------------
At the moment, it requires. Net/SmartIRC.php (http://pear.php.net/package/Net_SmartIRC/). Install like so:

    pear install Net_SmartIRC-1.0.2

You'll also need Ruby for the time being, I was practising using Rubys mechanize.

Usage
-----
Modify the code you see in newPrimeBot.php to reflect names, servers and default channels, then run it with

    %> php newPrimeBot.php channel1 channeltwo

With the names of the channels as arguments. You shouldn't add a #

Contact me on mike@mikepearce.net for help, or @MikePearce on twitter.

TODO
----
* Use /me for shakes head
* !messagefor
* Store settings in the db and manipulate them via !commands (from registeredusers)
* Remove need for ruby support, use PEAR libraries for scraping (or removing scraping)

Changelog
---------
v2.4 - Added support for /invite and !whereareyou
v2.3 - Made it less annoying by only messaging you if you talk to it.
v1.0 - Release!