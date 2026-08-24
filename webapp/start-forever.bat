@echo off
REM Keeps the Visitor System server running.
REM If it ever crashes, it restarts automatically after 5 seconds.
cd /d "%~dp0"

:loop
echo Starting Visitor System server...
node server.js
echo.
echo Server stopped or crashed. Restarting in 5 seconds...
timeout /t 5 /nobreak > nul
goto loop
