@echo off
REM Exposes the local server at http://localhost:3000 to the internet
REM using your free ngrok static domain.
REM
REM BEFORE FIRST USE:
REM 1) Install ngrok:  https://ngrok.com/download
REM 2) Sign up free, copy your authtoken, then run once:
REM        ngrok config add-authtoken YOUR_TOKEN_HERE
REM 3) In the ngrok dashboard, create one free static domain
REM    (Universal Gateway / Domains -> + Create Domain).
REM 4) Replace YOUR-DOMAIN.ngrok-free.app below with that domain.

ngrok http --domain=YOUR-DOMAIN.ngrok-free.app 3000
