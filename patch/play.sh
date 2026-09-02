#!/usr/bin/env bash
# ============================================================
#   Project Orion 0.6.5 - Launch via Wine/Proton (Linux)
# ============================================================

GAME_EXE=""
CANDIDATES=(
    "$PWD/WorldOfTanks.exe"
    "$HOME/Games/World_of_Tanks/WorldOfTanks.exe"
    "$HOME/Games/world_of_tanks/WorldOfTanks.exe"
    "$HOME/.wine/drive_c/Games/World_of_Tanks/WorldOfTanks.exe"
    "$HOME/.wine/drive_c/World_of_Tanks/WorldOfTanks.exe"
    "$HOME/.wine/drive_c/Program Files/World_of_Tanks/WorldOfTanks.exe"
    "$HOME/.wine/drive_c/Program Files (x86)/World_of_Tanks/WorldOfTanks.exe"
)

for c in "${CANDIDATES[@]}"; do
    if [ -f "$c" ]; then
        GAME_EXE="$c"
        break
    fi
done

if [ -z "$GAME_EXE" ]; then
    echo "[ERROR] WorldOfTanks.exe not found."
    echo "[ERROR] Place this script next to WorldOfTanks.exe or set WOT_GAME_EXE."
    echo ""
    if [ -n "$WOT_GAME_EXE" ] && [ -f "$WOT_GAME_EXE" ]; then
        GAME_EXE="$WOT_GAME_EXE"
    else
        read -r -p "Press Enter to exit..."
        exit 1
    fi
fi

WINE_BIN="${WINE:-wine}"
if ! command -v "$WINE_BIN" &>/dev/null; then
    echo "[ERROR] $WINE_BIN not found in PATH."
    echo "[ERROR] Install Wine (>= 8) or set WINE=/path/to/proton."
    read -r -p "Press Enter to exit..."
    exit 1
fi

export WINEPREFIX="${WINEPREFIX:-$HOME/.wine}"

GAME_DIR="$(dirname "$GAME_EXE")"
echo "[*] Launching Project Orion 0.6.5..."
echo "    exe:    $GAME_EXE"
echo "    wine:   $WINE_BIN"
echo "    prefix: $WINEPREFIX"

cd "$GAME_DIR" || exit 1
exec "$WINE_BIN" "$GAME_EXE" "$@"
