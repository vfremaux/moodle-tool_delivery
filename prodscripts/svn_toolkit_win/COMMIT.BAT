
START /B /I CONFIG.BAT

IF EXISTS %SVNDIR% (
    BASEDIR=%SVNDIR%
)

IF %1% == '' (
   svn commit -m "backport from production" %BASEDIR%
) ELSE (
   svn commit -m "backport from production" %BASEDIR%\%1%
)
