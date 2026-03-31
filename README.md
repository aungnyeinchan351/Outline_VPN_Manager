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

open function.php and replace "http://......" with the apiURL copied from command output.

