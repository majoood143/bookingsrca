# On-site print agent

The booking app is hosted in the cloud. The thermal receipt printer is on a
private, on-site network the cloud app cannot reach directly. This folder is
a small standalone script that runs on a PC at the printer's location: it
polls the cloud app for queued print jobs and delivers the raw ESC/POS bytes
straight to the printer over the local network (or a local/shared Windows
printer). It does not use Composer — only a plain PHP CLI is required.

## Setup (Windows PC)

1. Install PHP for Windows if it isn't already present (the CLI is enough,
   no web server needed): https://windows.php.net/download — pick the
   "Non Thread Safe" x64 zip, extract it somewhere like `C:\php`.
2. Copy this whole `print-agent` folder onto the on-site PC.
3. Copy `config.example.ini` to `config.ini` and fill in:
   - `CLOUD_BASE_URL` — the app's public URL.
   - `API_TOKEN` — from **Admin > Settings > Printer Settings > Agent
     Token** (use "Regenerate Agent Token" there if you need a new one).
   - `PRINT_MODE` — `network` if the printer has its own Ethernet/WiFi
     port and IP address, or `windows` if it's USB-attached to this PC
     (installed as a local or shared printer).
   - The matching `PRINTER_HOST`/`PRINTER_PORT` or `PRINTER_NAME` fields.
4. Test it manually first, in a console window:
   ```
   C:\php\php.exe print-agent.php
   ```
   Leave it running, then trigger "Print Attendee Tickets" from the admin
   app (or click "Send Test Print" on the Printer Settings page). Watch the
   console output and `agent.log` in this folder — you should see the job
   claimed and printed within one poll interval.

## Running it continuously

Once the manual test prints correctly, wrap it so it survives reboots and
restarts itself if it crashes. The simplest option is
[NSSM](https://nssm.cc/) (Non-Sucking Service Manager):

```
nssm install PrintAgent "C:\php\php.exe" "C:\print-agent\print-agent.php"
nssm set PrintAgent AppDirectory "C:\print-agent"
nssm set PrintAgent AppExit Default Restart
nssm start PrintAgent
```

This registers it as a Windows service named `PrintAgent` that starts on
boot and restarts itself if it exits unexpectedly. Check `agent.log` if
prints stop arriving — every poll, print attempt, and error is logged there
with a timestamp.

## Troubleshooting

- **401 responses in agent.log**: `API_TOKEN` doesn't match what's saved in
  Printer Settings — re-copy it (tokens are regenerated in full, not
  partially).
- **"Could not connect to printer..."**: check `PRINTER_HOST`/`PRINTER_PORT`
  are correct and that this PC can reach the printer (try `Test-NetConnection
  <PRINTER_HOST> -Port <PRINTER_PORT>` in PowerShell).
- **Windows mode fails to copy to the printer**: confirm the printer name
  matches exactly what's shown in "Devices and Printers", and that it's
  shared if another account/service will be printing to it.
- Nothing happening at all: confirm the service/console process is actually
  running, and that `CLOUD_BASE_URL` is reachable from this PC (it's just a
  normal HTTPS request — try opening it in a browser on the same machine).
