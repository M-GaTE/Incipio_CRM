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