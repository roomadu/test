# Deploying this app as a Hostinger Node.js Web App

This repo is already set up correctly for Hostinger's **Node.js Web App**
hosting (Business plan or any Cloud plan), deploying straight from GitHub.

## Repo / deploy settings (matches what you already selected)

| Setting          | Value                                      |
|------------------|---------------------------------------------|
| Framework        | Express                                     |
| Node version     | 22.x                                        |
| Root directory   | `webapp`                                    |
| Install command  | `npm install`                               |
| Start command    | `npm start`  (runs `node server.js`)        |
| Entry file       | `server.js`                                 |

hPanel → Websites → Add Website → Deploy Web App → Import Git Repository
→ pick `roomadu/test`, root directory `webapp`. Hostinger should
auto-detect Express; if it asks for an entry file, use `server.js`.

## Required Environment Variables

Set these in **hPanel → your Node.js website → Environment Variables**
(do NOT put real secrets in `.env` inside the repo — it's git-ignored on
purpose):

```
PORT=3000                      # Hostinger usually overrides this automatically
BASE_URL=https://your-app-domain.com

EMAIL_USER=your_gmail_address@gmail.com
EMAIL_PASS=your_gmail_app_password

DB_HOST=localhost              # or the host Hostinger gives your MySQL DB
DB_PORT=3306
DB_USER=your_db_user
DB_PASS=your_db_password
DB_NAME=your_db_name

# Recommended on this hosting - see note below
DISABLE_WHATSAPP=true
```

Use **hPanel → Databases** to create a MySQL database first, then copy
its host/user/password/name into the variables above.

## ⚠️ Important: the WhatsApp part will likely NOT work here

Hostinger's Node.js Web App hosting runs your app in a managed container:
- You cannot install a system-level Chrome/Chromium browser.
- Build files/session storage are not guaranteed to survive every
  redeploy or restart.

`whatsapp-web.js` (the real WhatsApp QR login) needs both of those things
to work reliably. So after deploying here:
- The visitor form, admin dashboard, and MySQL records will work fully.
- Email notifications will work (once EMAIL_USER/EMAIL_PASS are set).
- WhatsApp login will likely fail to start, or lose its session on the
  next redeploy/restart — this is a platform limitation, not a bug in
  the code (the app already handles this gracefully and won't crash;
  it just logs "WhatsApp NOT READY" and skips sending).

**For real, working WhatsApp**, keep using the free option already set
up in `FREE-WHATSAPP-SETUP.txt` (an always-on PC + free ngrok domain),
and point your **PHP** site's `WHATSAPP_BRIDGE_URL` /
`WHATSAPP_SETUP_URL` at that ngrok address — completely separate from
this Node deployment.

## Security note

`webapp/hostinger/public_html/includes/config.php` contains real
production secrets and is intentionally excluded from git
(`.gitignore`). Only `config.example.php` is tracked. Never commit
`config.php` or `.env` with real values.
