---
tags:
  - domain
  - content
---

# Downloads and mirrors

The `download.php` page ("Play") is driven entirely from the admin panel through the
`site_settings` table — changing links requires no code edit.

## Setting keys

| Key | Type | Contents |
|---|---|---|
| `download_client_mirrors` | JSON | game client mirrors |
| `download_patch_mirrors` | JSON | patch / `scripts_config.xml` mirrors |
| `download_video_url` | string | instructional video |
| `download_instructions` | text | instruction copy |

Mirror shape:

```json
[{ "name": "Download client", "url": "https://...", "enabled": true }]
```

Read via `get_setting_json($pdo, 'download_client_mirrors', [default])`. In `download.php` the
defaults are written inline at the call site, so the page works even against an empty database.

## Disabled mirror behavior

`enabled: false` doesn't hide the mirror — it renders as a **disabled button** labeled
"— unavailable". A line also appears below the list reading "N mirrors currently available"
when fewer than all are active.

That's deliberate: the player sees the mirror exists but is currently down, rather than
assuming it was removed for good.

## Admin panel

The `downloads` tab, `downloads.manage` permission, `save_downloads` action
(form plus redirect), audit key `downloads.save`.

In `js/admin.js` mirror rows are added and removed dynamically: `addMirrorRow()`,
`removeMirrorRow()`, `syncMirrorControls()`. The last one keeps field indices correct after a
row is deleted from the middle — which is exactly what
`tests/admin_mirror_lifecycle_test.js` verifies.

## Linux client: `/patch`

A separate mechanism that bypasses the database.

```
patch/setup.sh            install
patch/play.sh             launch
patch/uninstall.sh        remove
patch/scripts_config.xml  client config
patch/get.php             file delivery
```

`get.php` serves only two allowlisted files (`?f=config` → `scripts_config.xml`,
`?f=play_linux` → `play.sh`); anything else is a 404. It never builds a path from the parameter,
so the allowlist can't be bypassed.

On delivery it normalizes line endings: `.sh` → LF (otherwise bash won't run it),
everything else → CRLF. It also strips the BOM.

Related: [[Admin panel]], [[Database schema]]
