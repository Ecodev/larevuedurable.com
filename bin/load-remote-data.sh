#!/bin/bash

if [ $1 ]
then
    domain=$1
else
    domain='larevuedurable.com'
fi

php tasks/index.php LoadRemoteDump $domain