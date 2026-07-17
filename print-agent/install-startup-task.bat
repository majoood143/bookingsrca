@echo off
REM Registers run.bat to start automatically whenever this Windows account
REM logs in, so the print agent comes back up after a reboot without anyone
REM having to remember to start it manually. Uses only schtasks, which is
REM built into every Windows install — no extra tools to download.

schtasks /create /tn "PrintAgent" /tr "\"%~dp0run.bat\"" /sc onlogon /rl highest /f

if %errorlevel% == 0 (
    echo.
    echo Installed. The print agent will now start automatically at logon.
    echo To start it right now without logging off/on, run: schtasks /run /tn "PrintAgent"
) else (
    echo.
    echo Failed to register the scheduled task — see the error above.
)

pause
