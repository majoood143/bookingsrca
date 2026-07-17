@echo off
REM Runs the print agent, restarting it automatically if it ever exits or
REM crashes. %~dp0 resolves to this .bat file's own folder, so it works no
REM matter where the print-agent folder is placed on the PC.

:loop
"%~dp0php\php.exe" "%~dp0print-agent.php"
echo print-agent.php exited — restarting in 5 seconds...
timeout /t 5 /nobreak >nul
goto loop
