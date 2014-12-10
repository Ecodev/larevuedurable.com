#!/bin/bash

cd htdocs

rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/upload/ upload
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/img/ img
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/download/ download
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/download_tmp/ download_tmp
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/modules/homeslider/images/ modules/homeslider/images
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/modules/prestablog/themes/default/up-img/ modules/prestablog/themes/default/up-img