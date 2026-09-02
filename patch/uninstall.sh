#!/usr/bin/env bash
# ============================================================
#   Project Orion 0.6.5 - Remove hosts entries (Linux)
# ============================================================

if [ "$(id -u)" -ne 0 ]; then
    echo "[INFO] Need root rights to edit hosts file."
    echo "[INFO] Restarting with sudo..."
    exec sudo bash "$0" "$@"
    exit $?
fi

HOSTS="/etc/hosts"
HOSTS_BAK="${HOSTS}.orion.bak"
HOSTS_TMP="${HOSTS}.orion.tmp"

echo ""
echo "===== Project Orion 0.6.5 - Uninstall ====="
echo ""

if ! grep -qE '^\s*#\s*(Project Orion Emulator|WoT Emulator)' "$HOSTS" 2>/dev/null; then
    echo "[=] No Project Orion entries found in hosts."
    echo ""
    read -r -p "Press Enter to exit..."
    exit 0
fi

cp -f "$HOSTS" "$HOSTS_BAK"

awk '
/^[[:space:]]*#[[:space:]]*(Project Orion Emulator|WoT Emulator)/ { skip=2; next }
skip > 0 { skip--; next }
{ print }
' "$HOSTS" > "$HOSTS_TMP"

if [ ! -f "$HOSTS_TMP" ]; then
    echo "[ERROR] Failed to process hosts file. No changes made."
    echo "[ERROR] A backup is at: $HOSTS_BAK"
    read -r -p "Press Enter to exit..."
    exit 1
fi

mv -f "$HOSTS_TMP" "$HOSTS"

if command -v systemd-resolve &>/dev/null; then
    systemd-resolve --flush-caches 2>/dev/null
elif command -v resolvectl &>/dev/null; then
    resolvectl flush-caches 2>/dev/null
elif command -v nscd &>/dev/null; then
    nscd -i hosts 2>/dev/null
fi

echo "[OK] Project Orion entries removed from hosts."
echo "[OK] Backup saved to: $HOSTS_BAK"
echo ""
read -r -p "Press Enter to exit..."
exit 0
