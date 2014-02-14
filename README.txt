Local Delivery for secure code delivery on production Moodles

== Description ==

This toolkit proposes a practicle way to perform secure code delivery on a Moodle
running in production mode, lowering the technical transition footprint for users.

It relies on : 

- Moodle being run on Linux servers
- using CVS or SVN (svn_dav) versionning upstream integration code reference
- depending on the code base reference, CVS or subversion client is intalled.
- moodle virtual host pointing indirect code base volume, through a symlink 
- the appropriate toolkit is configured, using the "config" file provided
- toolkit being used from its original location in this tool, or being activated from elsewhere
- A CVS or SVN known read user
- all code bases allowed write at least from group "www-data" or whatever group identity the Web server is running
- for better control, all sticky bit of code directories set up (chmod g+s)

For detailed setup read the Tool setup section.

How it works
---------------

The tool provides 4 key commands

syncback: Syncbacks the running code base into a "safe" code base copy for pursuing service 
while delivery is in progress and may unstable the code base.

goback: Switches (instantly) between codebases, setting the "safe" copy to be operated in place
of the current one. Current production codebase is retired and moved to match the SVN or CVS update target.
This move is only directory renaming, so no files are really moved and the switch is quick enough to be
almost invisible to users.

update: Makes any partial or complete update of the production code base, while users safely use the "safe"
version. At this time, no real change is perceived from the running Moodle. 

backtoprod: this is the most critical moment in the delivery. By switching the new updated codebase to the
production state, notifications have to be performed to take into account all changes in te codebase, install
new features and activate new code. At this time Moodle database will probably change. Most of the time, 
changes, although not reversible, may still be compatible with the previous version. So gobacking in case
a really weird code has come in, breacking the production integrity, will allow reverting quickly to a 
running state, thus allowing to check delivery again and fix the new code.

Note that, for stable delivery cycle, the delivery tool must be in both "production" and "safe" version, or
the complete delivery cycle (goback => update => backtoprod => syncback) will fail (no code for the tools to 
finish the process in the "safe" unsynced copy. 

Tool install
------------------------

1. unzip the package into the admin/tool directory of your Moodle
2. Register the tool running administration notifications
3. Go to tool settings, though "Site Administration > Server > Code Delivery"
4. choose the delivery method you are using (cvs or svn)
5. choose the location where to setup the toolset, OUTSIDE the Moodle code base ! as tools will meta-process the moodle code, 
leaving tools inside leads to weird looping nesting situations. you'll have to copy the toolsets in that location. You will operate moodle from there... 
6. choose a name for the delivery directory symlink and feed the tooldeliverydir with it. You'll have to create that
link in the proper toolset (cvs, resp. svn) you choosed. This name will be the base name also for the "safe" code base copy.
7. If you copied just the toolset scripts in the delivery tool location you can check the "tooldirectdeliverytools"
8. Apply proper UID/GID policy upon resources to let tools operate (see last chapter)

Configuration sample.

Initial state : 

Moodle running in : /var/www/moodle

Tool settings : 

method : SVN
delivery tool path : /var/prodscripts/moodlesvn
delivery dir : moodle
direct delivery tools : yes

Detailed setup for toolset
--------------------------

Now the moodle tool is configured, (say we used the previous sample).

1. Copy the appropriate toolset (svn toolset scripts in /var/prodscripts/moodlesvn) in the toolset location.
2. go to that directory
3. Create a sym link over your moodle codebase : ln -s /var/www/moodle (symlink name must match your tool setting for delivery dir) 
4. Create a "safe" directory as your symlink name with -SAVE extension : moodle-SAVE
5. Accessorily create a "supersafe" directory as your symlink name with -SUPERSAVE extension : moodle-SUPERSAVE. This can be used 
for copying a "supersafe" third code base copy when a complex delivery, multiple stage, need to be performed. 
6. Give those dirs group owner as apache server group (f.e. www-data);
7. Give those dirs group write and sticky permission (chmod g+rwsx)
8. Give symlink and all content group owner as apache server group (f.e. chgrp -RL www-data);
9. Give symlink and all content write and sticky permission (f.e. chmod -RL g+rwsx);

Sample state : 

now you have this directory : 

/var/prodscripts/moodlesvn
  -> <scripts>
  -> moodle => /var/www/moodle
  -> moodle-SAVE
  -> moodle->SUPERSAVE 

with proper write permissions by the apache server.

Now last, before configuring the versionning settings, we have to make apache use our indirect reference. instruction
are given for standard Debian like distribution, but can be easily shifted to CentOs or other Linux.

In /etc/apache2/sites-available, find the vhost definitions for your Moodle, say moodle.vhost

10. Change DocumentRoot to use the symlink : 

	DocumentRoot /var/prodscripts/moodlesvn/moodle
	
11. Change Directory statements accordingly.
12. Restart server and check moodle is still running. 

Some old versions of Moodle may complain they cannot run with a virtual dir routing. This has fortunately gone with Moodle 2. 	

At this point we are ready to setup our CVS (here SVN) toolset.

13. go back to the toolset dir (/var/prodscripts/moodlesvn)
14. edit the config file
15. set REALBASEDIR : (used to checkout the moodle code base. must point to real physical path where moodle resides. /var/www/moodle in the sample).
16. set BASEDIR : (use the . /var/www/moodle in the sample).
17. Give your SVN credentials and repository definitions

All should be ready for delivery experience.

Trying tools
---------------

Browse in moodle to Site Administration -> Server -> Code Delivery

Try syncback your whole code base (empty component). This will create a safe copy. This is IMPORTANT to 
do the first time, or you will not maintain a proper running codebase accessible once plugin out...

Then try goback (plug out) (if succeeds, you'll notice you cannot syncback nor goback 
anymore). You can control in your server that moodle has become moodle-SVN and
moodle-SAVE has become moodle. So your production is runing on the safe codebase.

Now try pluging in back your original production code base. 

System users and group setting requirements
===========================================

On Linux systems, files permission and cecurity concerns can lead to tricky configurations. 

Here come two methods to get tools working.  

Easy but unsafe method
----------------------

When operating delivery interfaces from the Web, you will be acting as the web server's user (typically
www-data:www-data). But usual web setup would not allow www-data to modify source files. You can let
this permission by running all Moodle files using www-data:www-data property scheme.

This is of course not the safest way, as some security leaks in some page may let injection to change
other code in Moodle and f.e. let show admin or system authent information. 

Sudoing delivery
----------------

The safest way to setup tools is using a sudoer user to control Moodle codebase that WILL NOT be www-data,
but to which www-data can temporarily sudo (just from this tools, in a very controlled way), and keep 
constrainted to classical www-data:www-data processing ownership elsewhere.

For this you'll need : 

1 - create a sudoer user (e.g. moodleadm)
2 - give all moodle code and tools the moodleadm ownership
3 - check code files are readable and traversable for "other" so www-data can use all scripts of Moodle normally
4 - Define sudo targets for tools
5 - Give sudo permissions to www-data to endore moodleadm on those tools

Points 4 and 5 typically are possible adding a file to /etc/sudoers.d

Cmnd_Alias GOBACK = /var/prodscripts/*/goback
Cmnd_Alias UPDATE = /var/prodscripts/*/update
Cmnd_Alias SVNTOPROD = /var/prodscripts/*/svntoprod
Cmnd_Alias SYNCBACK = /var/prodscripts/*/syncback
Cmnd_Alias SUPERSYNCBACK = /var/prodscripts/*/supersyncback
Cmnd_Alias DELIVERY = GOBACK,UPDATE,SYNCBACK,SVNTOPROD,SUPERSYNCBACK

# Allows www-data to run as moodleadm for running the delivery commands
www-data ALL=(moodleadm) NOPASSWD: DELIVERY