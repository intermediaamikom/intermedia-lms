#!/bin/bash
echo "Backing up .env and storage..."
cp /var/www/lms.intermediaamikom.org/.env /tmp/env_backup
cp -r /var/www/lms.intermediaamikom.org/storage /tmp/storage_backup