#!/usr/bin/env bash
# ============================================================
#   Project Orion 0.6.5 - Setup hosts entries (Linux)
# ============================================================
SERVER_HOST="YOUR_SERVER_IP"
SERVER_IP=""

MARK="# Project Orion Emulator"
OLD_MARK="# WoT Emulator"

if [ "$(id -u)" -ne 0 ]; then
    echo "[INFO] Need root rights to edit hosts file."
    echo "[INFO] Restarting with sudo..."
    exec sudo bash "$0" "$@"
    exit $?
fi

HOSTS="/etc/hosts"
HOSTS_BAK="${HOSTS}.orion.bak"
HOSTS_TMP="${HOSTS}.orion.tmp"

if echo "$SERVER_HOST" | grep -qE '^([0-9]{1,3}\.){3}[0-9]{1,3}$'; then
    SERVER_IP="$SERVER_HOST"
fi

if [ -z "$SERVER_IP" ]; then
    SERVER_IP=$(getent hosts "$SERVER_HOST" 2>/dev/null | awk '{print $1; exit}')
fi

echo ""
echo "===== Project Orion 0.6.5 - Setup ====="
echo ""
echo "Server: $SERVER_HOST"
echo "IP:     $SERVER_IP"
echo "Hosts:  $HOSTS"
echo ""

if [ -z "$SERVER_IP" ]; then
    echo "[ERROR] Unable to resolve $SERVER_HOST."
    echo "[ERROR] Check internet connection and try again."
    echo ""
    read -r -p "Press Enter to exit..."
    exit 1
fi

cp -f "$HOSTS" "$HOSTS_BAK"

if grep -qE '^\s*#\s*(Project Orion Emulator|WoT Emulator)' "$HOSTS" 2>/dev/null; then
    echo "[*] Previous patch found - removing old entries..."
    awk '
    /^[[:space:]]*#[[:space:]]*(Project Orion Emulator|WoT Emulator)/ { skip=2; next }
    skip > 0 { skip--; next }
    { print }
    ' "$HOSTS" > "$HOSTS_TMP"
    if [ -f "$HOSTS_TMP" ]; then
        mv -f "$HOSTS_TMP" "$HOSTS"
    fi
fi

echo "[+] Adding entries to hosts..."
{
    echo ""
    echo "$MARK (do not edit manually)"
    echo "$SERVER_IP login-master.worldoftanks.com"
    echo "$SERVER_IP game.worldoftanks.com"
} >> "$HOSTS"

if command -v systemd-resolve &>/dev/null; then
    systemd-resolve --flush-caches 2>/dev/null
elif command -v resolvectl &>/dev/null; then
    resolvectl flush-caches 2>/dev/null
elif command -v nscd &>/dev/null; then
    nscd -i hosts 2>/dev/null
fi

echo ""
echo "===== DONE. Patch installed. ====="
echo "[OK] Hosts backup: $HOSTS_BAK"
echo "[OK] Run uninstall.sh to remove."
echo ""
read -r -p "Press Enter to exit..."
exit 0
