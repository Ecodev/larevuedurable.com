#!/bin/bash

cd htdocs

rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/upload/ upload
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/img/ img
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/download/ download
rsync -av larevuedurable.com:/sites/larevuedurable.com/htdocs/download_tmp/ download_tmp