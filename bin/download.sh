#!/bin/bash

cd htdocs

rsync -av --include=*.txt --exclude=* larevuedurable.com:/sites/larevuedurable.com/logs/ ../logs
rsync -av larevuedurable.com:/sites/larevuedurable.com/logs/emails/ ../logs/emails
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/img/ img
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/upload/ upload
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/download/ download
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/download_tmp/ download_tmp
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/modules/homeslider/images/ modules/homeslider/images
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/modules/prestablog/themes/default/up-img/ modules/prestablog/themes/default/up-img
