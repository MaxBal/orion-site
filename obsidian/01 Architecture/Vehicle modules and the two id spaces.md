---
tags:
  - architecture
  - db
  - game
---

# Vehicle modules and the two id spaces

`vehicle_modules` holds each player's tanks and the modules bolted onto them — one row per
tank, columns `chassis_id / engine_id / fuel_id / radio_id / turret_id / gun_id`.

**The site does not read or write this table.** Nothing here needs changing today. This note
exists because the moment anyone builds a garage, inventory or profile-hangar page, the
obvious reading of `vehicle_inv_id` is wrong.

> [!important] `vehicle_inv_id` carries two different encodings
> The column mixes ids minted by two different eras, distinguished only by magnitude:
>
> | Range | Meaning | Who wrote it |
> |---|---|---|
> | `1 … 125` | legacy 0.6.5 id, decoded against per-nation bases `{ussr:1, germany:46, usa:90}` | the original emulator |
> | `≥ 256` | `(nation_id + 1) << 8 \| type_id` | the 0.8.2 server, since 2026-07-19 |
>
> They cannot collide: the legacy space is bounded well below 256 (measured across the whole
> table — 2444 rows, 283 accounts, max **125**), and the new one starts at 256 by construction.

## Why a second encoding exists

The legacy scheme only ever had bases for three nations. Germany's range runs into the USA's,
and **china / france / uk have no base at all** — so those nations simply cannot be expressed
in it. That was survivable while the table only recorded tanks a player had bought in 0.6.5.

It stopped being survivable when the 0.8.2 server let players change modules on **any** tank
in the game: a British tank's loadout has nowhere to go in the legacy scheme. Rather than
renumber 2444 live rows belonging to the other emulator, the 0.8.2 server writes a new,
unambiguous id in a range the old one never reaches, and reads both.

## The write rules the 0.8.2 server follows

- One row per tank the player **actually changed** — never a bulk fill. A player with all 253
  tanks in their hangar still has only the handful of rows they touched.
- A tank that already has a legacy row is updated **in place**, keeping its old id, so rows
  the 0.6.5 emulator understands stay understandable.
- A tank with no row gets `≥ 256`.
- `INSERT … ON DUPLICATE KEY UPDATE` on the existing `(account_id, vehicle_inv_id)` primary
  key, so repeated changes collapse into one row.

## If you ever surface this on the site

Decode by range first, then by nation base — and expect both kinds in the same player's
result set. Note also that a row's absence means "stock configuration", not "no tank": the
0.8.2 server gives every player the full catalog and only persists deviations.

The vehicle **names** for display come from `_vehicles.json`, not from this table, and its
`inv_id` (a plain index into that file, assigned by `load_vehicle_catalog()`) is unrelated to
`vehicle_inv_id`. Don't join on it → [[Vehicle access]].

Related: [[Database schema]], [[Vehicle access]]
