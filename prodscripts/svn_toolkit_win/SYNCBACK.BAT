
START /B /I CONFIG.BAT

IF %1% == '' (
    ROBOCOPY /MIR %BASEDIR%\* %BASEDIR%-SAVE
) ELSE (
    ROBOCOPY /MIR %BASEDIR%\%1%\* %BASEDIR%-SAVE\%1%
)