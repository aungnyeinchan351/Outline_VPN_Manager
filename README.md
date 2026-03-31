# Installing outline server
First update your system and install docker.
```
sudo apt update
```
Install docker...
```
sudo apt install docker -y
```

Then, install outline server
```
sudo bash -c "$(wget -qO- https://raw.githubusercontent.com/Jigsaw-Code/outline-apps/master/server_manager/install_scripts/install_server.sh)"
```
After installation compllete, copy apiURL from command outputs like "http://13.2.56.98/bVGhut25k"

open **function.php** and replace "http://......" with the apiURL copied from command output using **nano** and save the file.

Install Nginx Web Server.....
```
sudo apt install nginx php-fpm php-curl -y
```

Set Up the Dashboard folder....
```
sudo mkdir -p /var/www/vpn-manager
```
```
sudo chown -R $USER:$USER /var/www/vpn-manager
```
Then move all files to **vpn-manager** folder.
```
sudo mv * /var/www/vpn-manager
```
Configure Ngix server
```
sudo nano /etc/nginx/sites-available/vpn-manager
```
Paste this text...
```
server {
    listen 80;
    server_name _;
    root /var/www/vpn-manager;
    index index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
    }
}
```

Enable website and restart
```
sudo ln -s /etc/nginx/sites-available/vpn-manager /etc/nginx/sites-enabled/
```
```
sudo rm /etc/nginx/sites-enabled/default
```
```
sudo systemctl restart nginx
```
Adjust Permissions for the History File
```
cd /var/www/vpn-manager
```
```
sudo chown www-data:www-data key_history.json
```
```
sudo chown www-data:www-data config.php
```
sudo chown www-data:www-data .
```

Then enable all **TCP** and **UDP** ports.

type your **Static IP address** in your browser and access to your web.
The default password is **456**.
