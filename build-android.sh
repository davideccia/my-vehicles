#!/usr/bin/env bash
set -euo pipefail

echo "→ Loading environment..."
set -a
# shellcheck source=.env
source .env
# shellcheck source=.env.android
source .env.android
set +a

echo "→ Building frontend assets..."
npm run build

echo "→ Packaging Android APK..."
php artisan native:package android --output=./releases

TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
mv ./releases/app-release.apk "./releases/${TIMESTAMP}_build.apk"

echo ""
echo "✓ APK: ./releases/${TIMESTAMP}_build.apk"
