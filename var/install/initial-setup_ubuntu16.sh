#!/usr/bin/env bash

sudo apt-get update
sudo apt-get -y install curl linux-image-extra-$(uname -r) linux-image-extra-virtual
sudo apt-get -y install apt-transport-https ca-certificates
curl -fsSL https://yum.dockerproject.org/gpg | sudo apt-key add -
sudo add-apt-repository "deb https://apt.dockerproject.org/repo/  ubuntu-$(lsb_release -cs)  main"
sudo apt-get update
sudo apt-get -y install docker-engine
docker --version

#install docker-compose
curl -L "https://github.com/docker/compose/releases/download/1.10.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
docker-compose --version

# Prepare docker-compose.yml
echo "What is the domain name that points on that server (crm.n7consulting.fr) :"
read subdomain
echo "What is your contact email adress (contact@n7consulting.fr) :"
read email
cp docker-compose.yml.dist docker-compose.yml

sed -i "s/REPLACE_WITH_YOUR_HOST/$subdomain/g" docker-compose.yml
sed -i "s/REPLACE_WITH_YOUR_EMAIL/$email/g" docker-compose.yml

docker-compose build
docker-compose up -d

#load database schema & fixtures
docker-compose exec web composer install:first
echo "Installation is now complete. You can now log in with credentials admin/admin. Don't forget to change that password."
