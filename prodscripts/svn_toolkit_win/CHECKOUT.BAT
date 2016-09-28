
START /B /I CONFIG.BAT

IF %BRANCH% == '' (
   svn co --username=%SVNUSER% --password=%SVNPASS% %SVNROOT%/%SVNREPO%/%MODULE%/trunk  %REALBASEDIR% --non-interactive --trust-server-cert
) ELSE (
   svn co --username=%SVNUSER% --password=%SVNPASS% %SVNROOT%/%SVNREPO%/%MODULE%/branches/%BRANCH% %REALBASEDIR% --non-interactive --trust-server-cert
)