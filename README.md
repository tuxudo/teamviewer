TeamViewer Module
==============

Collects TeamViewer preference data from the Mac and shows it in the client tab, listing, and report. Requires TeamViewer 10 or higher to be installed and configured on the client.

## Features

* Client tab with Connect button
* Listing (YAML) with status labels and filters
* Report with widgets: Update Available, Management, Always Online, Password Strength, Version graph
* Connect URL from `TEAMVIEWER_LINK` (default `teamviewer10://control?device=`)

## Configuration

Optional `.env` / config (`config.php`):

```
TEAMVIEWER_LINK=teamviewer10://control?device=
```

## Table Schema

* clientid - varchar(32) - Client ID (`ClientID_64` when present, otherwise `ClientID`)
* clientic - int - Client IC
* always_online - boolean - Always Online (`Always_Online`). Unreported treated as off on ingest
* autoupdatemode - int - Auto update mode
* version - varchar(255) - Last opened TeamViewer version (may include edition suffix, e.g. `H` / `HC`)
* update_available - boolean - Whether an update is available
* lastmacused - varchar(255) - Last MAC address(es) used (newline-separated; shown as `xx:xx:xx:xx:xx:xx`)
* security_adminrights - boolean - Security admin rights granted
* security_passwordstrength - int - Random password strength: `0` Disabled, `1` Low, `2` Medium, `3` High
* meeting_username - varchar(255) - Username for meetings
* ipc_port_service - int - IPC port
* licensetype - int - License type
* midversion - int - MID version
* moverestriction - boolean - Move restriction
* is_not_first_run_without_connection - boolean - Is not first run without a connection
* is_not_running_test_connection - boolean - Is not running on a test connection
* had_a_commercial_connection - boolean - Had a commercial connection before
* prefpath - string - Preference file path (used to label system vs user installs)
* updateversion - string - Advertised update version(s), newline-separated (stale/older than installed are dropped)
* owning_manager_account_name - varchar(255) - TeamViewer account that owns the device
* owning_manager_company_name - varchar(255) - Company of that account
* unmanaged - boolean - `1` when not assigned to a TeamViewer company. Unreported treated as unmanaged on ingest
* buddy_login_name - varchar(255) - TeamViewer account signed in on the Mac
* buddy_display_name - varchar(255) - Display name of the signed-in account
* ui_version - int - Interface version (`4` New, `2` Classic)
* use_new_ui - boolean - `1` when the new interface is in use
