#!/bin/bash
# Fix User Management Migration Script for Production
# Run this script to clean up and re-run the user management migrations

echo "================================================"
echo "User Management Tables Migration Fix"
echo "================================================"
echo ""
echo "This script will:"
echo "1. Rollback user management migrations"
echo "2. Delete duplicate migration entries"
echo "3. Re-run migrations fresh"
echo ""
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Aborted."
    exit 1
fi

echo ""
echo "Step 1: Rolling back user management migrations..."
php artisan migrate:rollback --path=/database/migrations/2026_02_06_210745_create_roles_table.php
php artisan migrate:rollback --path=/database/migrations/2026_02_06_210746_create_permissions_table.php
php artisan migrate:rollback --path=/database/migrations/2026_02_06_210747_create_role_permission_table.php
php artisan migrate:rollback --path=/database/migrations/2026_02_06_210748_add_role_id_to_users_table.php

echo ""
echo "Step 2: Cleaning up failed migration entries from database..."
php artisan tinker --execute="DB::table('migrations')->where('migration', 'like', '%role%')->orWhere('migration', 'like', '%permission%')->delete();"

echo ""
echo "Step 3: Running migrations fresh..."
php artisan migrate

echo ""
echo "Step 4: Running seeders..."
php artisan db:seed --class=RolesAndPermissionsSeeder

echo ""
echo "================================================"
echo "Migration fix complete!"
echo "================================================"
