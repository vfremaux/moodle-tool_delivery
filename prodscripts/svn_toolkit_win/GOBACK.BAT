
CMD /K CONFIG.BAT

ECHO SAVEDIR %SAVEDIR%

IF EXISTS %SAVEDIR% (
    RENAME %BASEDIR% %SVNDIR%
    RENAME %SAVEDIR% %BASEDIR%
) ELSE (
    ECHO "Nothing done. No save"
)
