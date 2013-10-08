folder-manager
==============

Simple folder manager based on Nette Framework and Twitter Bootstrap

Usage
-----
1. download sources
2. make folders `/log` and `/temp` writtable
3. rename `/app/config/config.local.neon.example` to `/app/config/config.local.neon` and configure it
4. done :)

Example nginx configuration (with http auth protection)
-------------------------------------------------------
    location /manager/ {
      auth_basic "manager";
      auth_basic_user_file /path/to/.htpasswd;
      
      alias /path/to/www/folder/;
      try_files $uri $uri/ /manager/index.php?$args;
        
      location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
      }
    }


TODO
----
- rename/move
- user-friendly size
- .htaccess configuraiton
