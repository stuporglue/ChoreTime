ChoreTime is a (currently) undocumented very user-specific kids chore tracking app.

Features
========
 * Lock users out of the computer if they're out of time.
 * Track screen time earned
 * Track money earned
 * Hard coded sparsly commented PHP!
 * Trakcs Family Home Evening assignments 
 * Different chores for Sunday


Setup
=====

It's a web app. Point your server config at the app directory. 

The apache user needs access to xscreensaver-command, so add this to your sudo config: 

    www-data localhost = (root) NOPASSWD: /usr/bin/xscreensaver-command


You'll also need a cron job to check who is logged in every minute, so add this to crontab:

    * * * * * /usr/local/bin/screen_check.php


I suppose that means you'll need to copy screen_check.php to /usr/local/bin/


Edit app/lib.inc and change up the list of kids, chores, weekly chores and sunday chores

Create a database with the structure in db.sql and add entries in scren_time and allowance for each user. 
User names have to match the Linux user names. Probably change the default un/pw in app/db.php 
