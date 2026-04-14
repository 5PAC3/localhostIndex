#!/bin/bash

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
HTTP_ROOT="${HTTP_ROOT:-/srv/http}"

echo "Installing localhostIndex to $HTTP_ROOT..."

if [ ! -d "$HTTP_ROOT" ]; then
    echo "Error: $HTTP_ROOT does not exist"
    exit 1
fi

if [ ! -w "$HTTP_ROOT" ]; then
    echo "Error: $HTTP_ROOT is not writable. Run as root or fix permissions."
    exit 1
fi

if [ -L "$HTTP_ROOT/index.php" ]; then
    echo "Removing existing symlink..."
    rm "$HTTP_ROOT/index.php"
fi

echo "Creating index.php symlink..."
ln -s "$SCRIPT_DIR/index.php" "$HTTP_ROOT/index.php"

echo "Installing EXTRA_PATHS apps..."

EXTRA_PATHS=("/usr/share/webapps")
for extraPath in "${EXTRA_PATHS[@]}"; do
    if [ -d "$extraPath" ]; then
        for dir in "$extraPath"/*; do
            [ -d "$dir" ] || continue
            name=$(basename "$dir")
            linkPath="$HTTP_ROOT/$name"
            if [ ! -e "$linkPath" ]; then
                echo "  Creating symlink: $name -> $dir"
                ln -s "$dir" "$linkPath"
            else
                echo "  Skipping $name (already exists)"
            fi
        done
    fi
done

echo "Done!"