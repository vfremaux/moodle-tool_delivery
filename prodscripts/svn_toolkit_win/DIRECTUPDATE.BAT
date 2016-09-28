
START /B /I CONFIG.BAT

if EXISTS %SVNDIR% (
   ECHO "A SVN dir is in place. Please svntoprod first."
   EXIT
)

IF %1% == '' ( 
    IF %BRANCH% == '' (
        svn update %BASEDIR%
    ) ELSE (
        svn update -R %BRANCH% %BASEDIR%
    )
) ELSE (
    IF %BRANCH == '' (
        svn update %BASEDIR%\%1%
    ) ELSE (
        svn update -R $BRANCH ${BASEDIR}/${1}
    )
)

chown -R ${FILESERVUSER}:${FILESERVGROUP} $BASEDIR
chmod -R g+w $BASEDIR
chmod -R ugo+r $BASEDIR
