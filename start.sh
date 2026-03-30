#!/bin/bash

# ==============================================================================
# Local Docker Setup Script for Hi.Events (FrankenPHP Edition)
# Optimized for macOS (Intel & Apple Silicon)
# ==============================================================================

set -e

echo "🐳 Initiating Local Docker Environment for Hi.Events..."

# 1. Environment Configuration
if [ ! -f "backend/.env" ]; then
    echo "↳ Creating backend/.env from example..."
    cp backend/.env.example backend/.env
fi

if [ ! -f "frontend/.env" ]; then
    echo "↳ Creating frontend/.env from example..."
    cp frontend/.env.example frontend/.env
fi

# 2. Dependency Pre-flight (Optional but recommended)
# We usually let Docker handle this, but for speed, we can check local presence.
echo "↳ Verifying local requirements..."

# 3. Docker Compose Execution
# We use the development compose file which maps local volumes for HMR (Hot Module Replacement)
echo "🚀 Spinning up containers via Docker Compose..."

# Note: Using 'docker-compose' or 'docker compose' based on your version.
# This assumes we have updated the docker-compose.dev.yml to use FrankenPHP.
docker compose -f docker/development/docker-compose.dev.yml up -d --build

echo ""
echo "✅ Containers are warming up!"
echo "=============================================================================="
echo "Backend API:    http://localhost:8000"
echo "Frontend:       http://localhost:3000"
echo "Database:       localhost:5432 (PostgreSQL)"
echo "=============================================================================="
echo "To monitor logs, run: docker compose -f docker/development/docker-compose.dev.yml logs -f"