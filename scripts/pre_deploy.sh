#!/usr/bin/env bash
# Pull latest code, check .env and vendor directory

FRESH_INSTALLATION=false

echo "📥 Pulling latest code..."
git pull

if [ ! -f .env ]; then
    echo "⚙️ Copying .env.example to .env..."
    cp .env.example .env
    echo "✍️ Please edit your .env file and rerun the script."
    exit 1
fi

if [ ! -d "vendor" ]; then
    FRESH_INSTALLATION=true
fi

export FRESH_INSTALLATION
echo "🆕 Fresh installation: $FRESH_INSTALLATION"
