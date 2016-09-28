
START /B /I CONFIG.BAT

IF EXISTS %SVNDIR% (
   ECHO "No SVN volume in place. Please goback first."
   EXIT
)

IF %1% == '' (
    svn update %SVNDIR% --username=%SVNUSER% --password=%SVNPASS% --non-interactive --trust-server-cert
) ELSE (
    svn update %SVNDIR%/%1% --username=%SVNUSER% --password=%SVNPASS% --non-interactive --trust-server-cert
)
