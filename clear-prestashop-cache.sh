#!/bin/bash

echo "🧹 Nettoyage complet des caches PrestaShop..."

# Accès au container web
ddev ssh <<'EOF'

echo "🗑️ Suppression var/cache/*..."
rm -rf var/cache/dev/*
rm -rf var/cache/prod/*

echo "🗑️ Suppression Smarty templates compilés..."
rm -rf var/cache/dev/smarty/compile/*
rm -rf var/cache/prod/smarty/compile/*

echo "🗑️ Suppression Smarty cache..."
rm -rf var/cache/dev/smarty/cache/*
rm -rf var/cache/prod/smarty/cache/*

# PrestaShop console si elle existe
if [ -f bin/console ]; then
  echo "🧽 Nettoyage via PrestaShop Console (cache:clear)..."
  php bin/console cache:clear --no-warmup
fi

echo "✅ Nettoyage terminé."
EOF
