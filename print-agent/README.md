# On-site print agent

The booking app is hosted in the cloud. The thermal receipt printer is on a
private, on-site network the cloud app cannot reach directly. This folder is
a small, self-contained agent that runs on a PC at the printer's location: it
polls the cloud app for queued print jobs and delivers the raw ESC/POS bytes
straight to the printer over the local network (or a local/shared Windows
printer). Nothing needs to be installed system-wide — the whole thing is one
folder you copy onto the PC.

## Setup (Windows PC)

1. **Get a portable PHP** — no installer needed. Go to
   https://windows.php.net/download/ and download the **"Non Thread Safe"
   x64 .zip** (not the installer .msi). Extract it, then copy the extracted
   contents into a `php` subfolder here, so you end up with
   `print-agent/php/php.exe`.
2. Copy `config.example.ini` to `config.ini` and fill in:
   - `CLOUD_BASE_URL` — the app's public URL.
   - `API_TOKEN` — from **Admin → Settings → Printer Settings → Agent
     Token** (use "Regenerate Agent Token" there if you need a new one).
   - `PRINT_MODE` — `network` if the printer has its own Ethernet/WiFi
     port and IP address, or `windows` if it's USB-attached to this PC
     (installed as a local or shared printer).
   - The matching `PRINTER_HOST`/`PRINTER_PORT` or `PRINTER_NAME` fields.
3. Copy this whole `print-agent` folder (now including `php/` and
   `config.ini`) onto the on-site PC. That's the entire install — no admin
   rights, no system PATH changes, nothing registered in Windows yet.
4. Double-click `run.bat` to test it. A console window opens and stays open,
   logging `Print agent started...`. Leave it running, then trigger "Print
   Attendee Tickets" from the admin app (or click "Send Test Print" on the
   Printer Settings page). You should see the job claimed and printed
   within one poll interval, both in the console and in `agent.log`.

## Running it continuously

Once the manual test in `run.bat` prints correctly, double-click
`install-startup-task.bat` **once**. It registers a Windows scheduled task
(via `schtasks`, built into every Windows install — nothing else to
download) that starts `run.bat` automatically whenever this account logs in.
`run.bat` itself loops forever and restarts `print-agent.php` if it ever
exits or crashes, so between the two you get "starts on boot/login" and
"recovers from a crash" without needing a real Windows service or any extra
tooling like NSSM.

To stop it from starting automatically, run `uninstall-startup-task.bat`.
To start it immediately without logging off/on: `schtasks /run /tn "PrintAgent"`,
or just double-click `run.bat` directly.

(If you'd prefer a true background Windows service instead of a scheduled
task — e.g. so it runs even when nobody is logged in — you can still wrap
`run.bat` with [NSSM](https://nssm.cc/): `nssm install PrintAgent
"C:\path\to\print-agent\run.bat"`. Most front-desk/box-office PCs stay
logged in permanently, so the scheduled-task approach above is usually
enough.)

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
- Nothing happening at all: confirm `run.bat` (or the scheduled task) is
  actually running, and that `CLOUD_BASE_URL` is reachable from this PC
  (it's just a normal HTTPS request — try opening it in a browser on the
  same machine).
