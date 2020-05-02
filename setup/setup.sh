#!/bin/bash

sudo ln -s /srv/HomeBrain/setup/.aliases /etc/profile.d/00-homebrain_aliases.sh

sudo mv /var/www/html /var/www/html_old
sudo ln -s /srv/HomeBrain/www /var/www/html

sudo cp /srv/HomeBrain/setup/homebrain.service /etc/systemd/system
sudo systemctl daemon-reload
sudo systemctl enable homebrain.service
sudo systemctl start homebrain.service
