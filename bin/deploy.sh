#!/bin/bash

platform=`uname`
www_group='nobody'
if [[ $platform == 'Linux' ]]; then
  www_group='www-data'
elif [[ $platform == 'Darwin' ]]; then
  www_group='_www'
fi

cd ../htdocs

# Set POSIX permissions for PHP writable directories
chgrp -R $www_group .
chmod -R g+w ./config
chmod -R g+w ./cache
chmod -R g+w ./log
chmod -R g+w ../logs
chmod -R g+w ./img
chmod -R g+w ./mails
chmod -R g+w ./modules
chmod -R g+w ./modules/*/fr
chmod -R g+w ./themes/larevuedurable/
chmod -R g+w ./translations
chmod -R g+w ./upload
chmod -R g+w ./download
chmod -R g+w ./download_tmp
chmod g+w ./sitemap.xml
chmod -R g+w ./administrator/backups

if [[ $platform == 'Linux' ]]; then
  setfacl -R -m g:larevuedurablecom:rwx -m d:g:larevuedurablecom:rwx ../
  setfacl -R -m u:www-data:r-x -m d:u:www-data:r-x ../
  setfacl -R -m u:www-data:rwx -m d:u:www-data:rwx config cache log ../logs ./img ./mails ./modules ./themes/larevuedurable ./translations ./upload ./download ./download_tmp ./sitemap.xml ./administrator/backups
  # Set PHP write permissions in french translations of modules
  # find ./modules/ -type d -name fr -exec setfacl -R -m u:www-data:rwx -m d:u:www-data:rwx \{\} \;
elif [[ $platform == 'Darwin' ]]; then
chmod -R +ai "user:_www allow list,add_file,search,delete,add_subdirectory,delete_child,readattr,readsecurity,file_inherit,directory_inherit" config cache log ../logs ./img ./mails ./modules ./themes/larevuedurable ./translations ./upload ./download ./download_tmp ./sitemap.xml ./administrator/backups
fi
