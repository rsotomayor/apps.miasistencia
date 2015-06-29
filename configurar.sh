#!/bin/bash

mkdir tmp
mkdir upload_backup
mkdir upload_log
mkdir upload_svt
mkdir upload_solem
mkdir outbox_solem
ln -s /u/savtec/public_html/rso.cl/data/ data
ln -s /u/Respaldos/download/ download
sudo chgrp -R www-data *
sudo chmod -R g+w tmp
sudo chmod -R g+w upload_*
sudo chmod -R g+w outbox_*

