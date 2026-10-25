#!/bin/bash

# FaraTech Backup Script
# This script creates backups of the database and files

# Configuration
BACKUP_DIR="/var/backups/faratech"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="FaraTech"
DB_USER="faratech_user"
FILES_DIR="/var/www/faratech"
SITE_URL="https://faratechlab.ir"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Database backup
echo "Creating database backup..."
mysqldump -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_DIR/database_$DATE.sql"

# Compress database backup
gzip "$BACKUP_DIR/database_$DATE.sql"

# Files backup
echo "Creating files backup..."
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" "$FILES_DIR"

# Remove backups older than 30 days
echo "Cleaning old backups..."
find "$BACKUP_DIR" -name "database_*.sql.gz" -mtime +30 -delete
find "$BACKUP_DIR" -name "files_*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
echo "Database: $BACKUP_DIR/database_$DATE.sql.gz"
echo "Files: $BACKUP_DIR/files_$DATE.tar.gz"
