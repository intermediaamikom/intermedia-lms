#!/bin/bash
echo "Restarting PHP and Nginx gracefully..."
systemctl reload php8.3-fpm
systemctl reload nginx