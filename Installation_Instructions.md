# Details #

NOTES:

Installation of mythepisode only takes a few minutes and can be accomplished
with the 5 steps listed below in the installation section.

After an initial install when you first select TV Episodes mythepisode has to go to TVRage.com and grab a list of shows for the first time so there might be a slight delay(~5-10 seconds).  The complete show listing is only grabbed one time unless you explicitly request a new update by selecting "Update Show Listing".  You shouldn't have to do this very often.

When you click on a show link for the first time episode information about that show will be retrieved from TVRage.com/TheTVDB.com.  After you retrieve the data the first time any subsequent returns to the show won't require data grabbing.  You do have the option to repull the data at anytime.  Data will also be pulled again if you visit the show page and the data is older than 7 days(configurable) and the show is still a currently running series.  Shows that have ended or canceled won't reupdate unless you explicity request an update.

If summary information doesn't exist at TVRage.com for a pariticular episode an attempt will be made to get it from TheTVDB.com.  The user has the option of selecting TVRage.com or TheTVDB.com as the primary source for episode information.

I have tested mythepisode with the following browsers(although not thoroughly)
```
Firefox - very fast on my system
Safari  - very fast on my system
Chrome  - very fast on my system
IE      - very slow on my system
```
All Configuration settings are set through the mythweb settings menu.  Listed under the installation instructions is all of the configuration options and their purpose.



&lt;BR&gt;



---


---

# Installation Instructions: #

---


---

Note:  If you have never installed mythepisode you should perform all steps.
If you are upgrading from a previous version of mythepisode you will only
need to perform step 1.  If you have upgraded mythtv/mythweb to a new version you will need to do steps 1,2 and 4. In all cases you should review the configuration options listed after the installation instructions.

As installed on my system(Ubuntu)


---

Step 1:

---

Download mythepisode and place it in /tmp.  Find the path where mythweb is
installed on your system.  I'm using Ubuntu and mine is installed under
/usr/share/mythtv/mythweb.  Under the mythweb directory you will find a
directory called modules.  This is where you will install mythepisode.

  * **cd /usr/share/mythtv/mythweb/modules**
  * **tar -zxvf /tmp/episode.tar.gz**


---

Step 2:

---

Add mythepisode to the mythweb menu by adding the 2 NEW LINE entries listed
below to the header.php.  For my mythweb installation the file is located in
/usr/share/mythtv/mythweb/modules/`_`shared/tmpl/default/header.php.

  * **cd /usr/share/mythtv/mythweb/modules/`_`shared/tmpl/default**
Update the header.php file with the NEW LINES listed below.
```
EXISTING LINE:  <a href="tv/recorded"><?php echo t('Recorded Programs') ?></a>
EXISTING LINE:  &nbsp; | &nbsp;
NEW LINE:       <a href="episode"><?php echo t('TV Episodes') ?></a>
NEW LINE:       &nbsp; | &nbsp;
EXISTING LINE:  <?php } ?>
```

---

Step 3:

---

Create a directory where you want your show/episode information stored.  I use the same directory path where my tv and video recordings are stored.  This will ensure that if you update mythweb or mythepisode your data files will not be removed.

  * **mkdir /var/lib/mythtv/episode**
> > This can be wherever you want.  My mythtv recordings, movies, etc are stored in directories located under /var/lib/mythtv so this is where I want to create the episode dir.

  * **chmod 777 /var/lib/mythtv/episode**
  * **cd /usr/share/mythtv/mythweb/data**
> > The path to the mythweb/data dir may be different for your mythweb installation.

  * **ln -s /var/lib/mythtv/episode  episode**

This is what mine looks like after the above link is created and I run an ls -lrt command in /usr/share/mythtv/mythweb/data directory

  * **ls -lrt /usr/share/mythtv/mythweb/data**
```
lrwxrwxrwx 1 root  root  26 2010-07-04 15:21 recordings -> /var/lib/mythtv/recordings
lrwxrwxrwx 1 root  root  21 2010-07-04 15:21 music -> /var/lib/mythtv/music
lrwxrwxrwx 1 root  root  22 2010-07-04 15:21 video -> /var/lib/mythtv/videos
lrwxrwxrwx 1 root  root  22 2010-07-04 15:21 video_covers -> /var/lib/mythtv/videos
lrwxrwxrwx 1 root  root  30 2010-07-04 15:21 tv_icons -> /var/cache/mythweb/image_cache
lrwxrwxrwx 1 root  root  30 2010-07-04 15:21 cache -> /var/cache/mythweb/image_cache
lrwxrwxrwx 1 root  root  23 2010-12-04 22:01 episode -> /var/lib/mythtv/episode
```

---

Step 4:

---

Run the mythweb build\_translation script.

  * **cd /usr/share/mythtv/mythweb/modules/`_`shared/lang**
  * **./build\_translation.pl**


---

Step 5: (optional)

---

This step is optional, but required if you want to use the tvwish funtion in
mythepisode.  If you choose not to use this you can disable and hide all tvwish functions by updating the tvwishhide configuration option in the mythweb settings.  If you don't install tvwish now you can always do it later if you choose to

download tvwish from http://www.templetons.com/brad/myth/tvwish.html and install it under /usr/share/mythtv/tvwish

Add a cronjob to roots crontab
```
00 12 * * * /usr/share/mythtv/mythweb/modules/episode/utils/runwish > /dev/null 2>&1
```
**Note:**
If your paths differ from mine above then you will have to make a few changes to paths in the cronjob and the scripts below.  I plan to fix this in the future.
- runwish

INSTALLATION IS NOW DONE.  CHECKOUT THE CONFIGURATION OPTIONS LISTED BELOW.



&lt;BR&gt;



---


---

# Configuration options: #

---


---

The only setting that you potentially need to change is the value for the mythtv version.  You should take a look at these and understand what each setting is used for so that you can make configuration changes to suit your needs.  All of these configuration options should be changed from the mythweb settings utility.


## Settings Tab ##


---

#### MythTV Version: ####

---

This option is used to set your currently running mythtv version.  The default value is .24+
```
options:
.21
.22
.23
.24+  - Used for .24 and above
```


---

#### Countries (space seperated): ####

---

This option is used to set the country or countries shows that will be retrieved from TVRage.com and displayed in mythepisode.  The default is set to "US".  Each country should only be 2 characters and seperated by a space if more than one country is selected.
```
examples:
US
US CA
US UK CA
```
After making a change to this setting you will need to do an "Update Shows" from the main mythepisode page.


---

#### Default page view: ####

---

This option is used to determine which page is displayed by default when you select "TV Episodes".  The default setting is "recorded".
```
options:
recorded - Only display shows that have previously recorded
current  - Display shows that are currently running tv shows
all      - Display every(I'm sure some are missing) TV series that has ever aired
```


---

#### Default data site: ####

---

This option is used to choose the query site used for grabbing episode information.  The default setting is TVRage.com
```
options:
TVRage.com
TheTVDB.com
```


---

#### Display tvwish options: ####

---

This option is used to turn on/off the display of tvwish options and menus.  Some mythepisode users don't want to use tvwish and don't want it displayed.  The default is set "yes" to display tvwish options



---

#### Episode matching accuracy (%): ####

---

This option is used to help improve episode matching.  Because of spelling differences from different sources there are times when episodes fail to be marked as previously recorded or scheduled for recording.  To overcome this we compare the episode name from TVRage.com/TheTVDB.com against the episode name from mythtv and label as a match if the character match between the two is equal to or greater than the episode matching percentage.  The default setting is 85% which means the two must match 85% or higher to get labeled as a match.
```
example:  A King of Queens episode called "Alter Ego"

mythtv listing:         TVRage.com listing:
---------------         ---------------
Altar Ego               Alter Ego
```
87.5% of the characters match so the default setting of 85% would mark these as a match.

Setting this setting too low can result in many false matches.  Setting it too high can result in missed matches.  A setting of 100% is used for exact matches.  85% is a good balance.



---

#### Update episode info if older than (days): ####

---

This option is used to set how often episode information is automatically grabbed from TVRage.com/TheTVDB.com.  The default setting is 7 days.  When you click on a TV show, mythepisode will verify that you have downloaded episode information and that it is not older than 7 days.  If it is older than 7 days mythepisode will go to TVRage.com/TheTVDB.com and get updated information.



---

#### Size of episode thumbnail (pixels): ####

---

This option is used to set the size of the thumbnail displayed on the episode listing page.  The default settings is "250" pixels.



## Override Tab ##

This tab is used to configure shows to display properly in mythepisode that have names that don't match.  When mythtv records a show it has a specific title that may not match exactly with what is listed in TVRage.com.  As a result mythepisode doesn't know how to display that show on the recorded page.
```
Examples:
recorded by mythtv as:          listed in mythepisode as:
----------------------          -------------------------
Survivor: Nicaragua             Survivor
2010 MTV Video Music Awards     MTV Video Music Awards
Hawaii Five-0                   Hawaii Five-0 (2010)
The Amazing Race 17             The Amazing Race
Big Brother                     Big Brother (US)
Hell's Kitchen                  Hell's Kitchen (UK)
```
This problem can be overcome by adding them in the override tab.  By default if mythepisode finds an issue with a show that can't be matched it will add it to the override tab.  It is up to you to fill in the missing show title as displayed in mythepisode and save the configuration.  You can easily find it in mythepisode by selecting "All Shows" on the mainpage and finding the show title as displayed in mythepisode.  Now add the mythepisode show title next to the show title in the override tab and save the configuration.