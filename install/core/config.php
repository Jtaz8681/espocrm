<?php
/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

return [
    'apiPath' => '/api/v1',
    'rewriteRules' => [
        'APACHE1' => 'sudo a2enmod rewrite
sudo service apache2 restart',
        'APACHE2' => '&#60;Directory /PATH_TO_ESPO/&#62;
 AllowOverride <b>All</b>
&#60;/Directory&#62;',
        'APACHE2_PATH1' => '/etc/apache2/sites-available/ESPO_VIRTUAL_HOST.conf',
        'APACHE2_PATH2' => '/etc/apache2/apache2.conf',
        'APACHE2_PATH3' => '/etc/httpd/conf/httpd.conf',
        'APACHE3' => 'sudo service apache2 restart',
        'APACHE4' => '# RewriteBase /',
        'APACHE5' => 'RewriteBase {ESPO_PATH}{API_PATH}',
        'WINDOWS_APACHE1' => 'LoadModule rewrite_module modules/mod_rewrite.so',
        'NGINX_PATH' => '/etc/nginx/sites-available/YOUR_SITE',
        'NGINX' => 'server {
    # ...

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /api/v1/ {
        if (!-e $request_filename){
            rewrite ^/api/v1/(.*)$ /api/v1/index.php last; break;
        }
    }

    location /portal/ {
        try_files $uri $uri/ /portal/index.php?$query_string;
    }

    location /api/v1/portal-access {
        if (!-e $request_filename){
            rewrite ^/api/v1/(.*)$ /api/v1/portal-access/index.php last; break;
        }
    }

    location ~ /reset/?$ {
        try_files /reset.html =404;
    }

    location ^~ (api|client)/ {
        if (-e $request_filename){
            return 403;
        }
    }
    location ^~ /data/ {
        deny all;
    }
    location ^~ /application/ {
        deny all;
    }
    location ^~ /custom/ {
        deny all;
    }
    location ^~ /vendor/ {
        deny all;
    }
    location ~ /\.ht {
        deny all;
    }
}',
    'APACHE_LINK' => '#',
    'NGINX_LINK' => '#',
    ],
];
