#!/bin/bash

echo "🔍 DIAGNOSTIC DOCKER - SYSTÈME DE FILTRAGE CV"
echo "=============================================="

echo ""
echo "📊 1. ÉTAT DES CONTENEURS"
echo "-------------------------"
docker-compose ps

echo ""
echo "🌐 2. TEST DE CONNECTIVITÉ"
echo "--------------------------"

# Test Laravel
echo "🔸 Laravel App:"
curl -s -o /dev/null -w "Status: %{http_code}\n" http://localhost:8000 || echo "❌ Laravel inaccessible"

# Test PostgreSQL
echo "🔸 PostgreSQL:"
docker-compose exec -T postgres pg_isready -U laravel_user -d laravel_db || echo "❌ PostgreSQL inaccessible"

# Test Redis
echo "🔸 Redis:"
docker-compose exec -T redis redis-cli ping || echo "❌ Redis inaccessible"

echo ""
echo "📋 3. LOGS RÉCENTS (5 dernières lignes)"
echo "---------------------------------------"

echo "🔸 Laravel App:"
docker-compose logs app --tail=3

echo "🔸 Nginx:"
docker-compose logs nginx --tail=3

echo "🔸 PostgreSQL:"
docker-compose logs postgres --tail=3

echo ""
echo "💾 4. UTILISATION DES RESSOURCES"
echo "--------------------------------"
docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}"

echo ""
echo "🔗 5. RÉSEAU DOCKER"
echo "------------------"
docker network ls | grep laravel

echo ""
echo "✅ DIAGNOSTIC TERMINÉ"
echo "===================="
echo "🌐 Application: http://localhost:8000"
echo "🗄️  PostgreSQL: localhost:5432"
echo "🔴 Redis: localhost:6379"
echo "⚡ Vite: http://localhost:5173"
