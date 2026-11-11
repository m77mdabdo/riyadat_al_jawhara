#!/usr/bin/env bash
# Runs on the Hostinger server for every deploy (called by .github/workflows/deploy.yml).
# Unpacks a release next to the previous ones, links the shared .env and storage, installs PHP dependencies,
# migrates, warms the caches and only then switches `current`, so visitors never see a half-deployed site.
# The last five releases are kept for a quick rollback: ln -sfn releases/<older> current
set -euo pipefail

APP="${APP_DIR:-$HOME/apps/riyadat}"
ARCHIVE="$1"
SHA="${2:-manual}"
RELEASE="$APP/releases/$(date +%Y%m%d%H%M%S)-${SHA:0:12}"

mkdir -p "$RELEASE"
tar -xzf "$ARCHIVE" -C "$RELEASE"
rm -f "$ARCHIVE"

# .env, uploads (storage/app/public), logs, caches and generated PDFs live outside the releases.
ln -sfn "$APP/shared/.env" "$RELEASE/.env"
rm -rf "$RELEASE/storage" && ln -sfn "$APP/shared/storage" "$RELEASE/storage"
rm -f "$RELEASE/public/storage" && ln -sfn "$APP/shared/storage/app/public" "$RELEASE/public/storage"

cd "$RELEASE"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
php artisan migrate --force
php artisan optimize
php artisan filament:optimize || true

# Atomic switch: the web server follows the symlink to the new release.
ln -sfn "$RELEASE" "$APP/current.next" && mv -Tf "$APP/current.next" "$APP/current"
# Pages, menus and PDFs are cached; start clean on the new code.
php "$APP/current/artisan" cache:clear

cd "$APP/releases" && ls -1dt */ | tail -n +6 | xargs -r rm -rf
echo "Deployed $RELEASE"
