#!/bin/bash

# Real physical dir of the code (absolute path)
SET REALBASEDIR=

# The dir of the real running alias
SET BASEDIR=moodle

# The SVN working name
SET SVNDIR=%BASEDIR%-SVN

# The name for the SAVE copy
SET SAVEDIR=%BASEDIR%-SAVE

# The name for the SUPERSAVE copy
# The SUPERSAVE copy allows making long term super secured copy
SET SUPERSAVEDIR=${BASEDIR}-SUPERSAVE

# The name for the CVSD reading user
# You will need to know the remote accepted password for that user
SET SVNUSER=svnuser
SET SVNPASS=svnpassword

# CVS Repository information. Not used for all commands // Svn running in webdav mode
SET SVNREPO=mdl-svnrepo
SET SVNROOT=http://www.mysvn.me/svn
SET MODULE=WWW-MYMOODLE-PHP
SET BRANCH=MYBRANCH
