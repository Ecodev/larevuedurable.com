larevuedurable.com
==================

Install
-------

Clone repository:
```
git clone git@github.com:Ecodev/larevuedurable.com.git larevuedurable.lan
cd larevuedurable.lan
```

Import PrestaShop core into our project:
```
git clone --branch 1.5.4.1 --depth 1 https://github.com/PrestaShop/PrestaShop.git /tmp/prestashop
cp -r -v /tmp/prestashop/* htdocs/
mv htdocs/admin-dev htdocs/admin[xxx] where [xxx] is a custom number for security
rm -rf htdocs/install-dev # remove installation only files
git checkout -- . # be sure that we did not overwrite anything from our project
``` 

Install PHP dependencies
```
composer install
```

Import DB and execute SQL:
```
UPDATE `ps_configuration` SET `value` = 'larevuedurable.lan' WHERE `ps_configuration`.`name` IN ('PS_SHOP_DOMAIN', 'PS_SHOP_DOMAIN_SSL');
UPDATE `ps_shop_url` SET `domain` = 'larevuedurable.lan', `domain_ssl` = 'larevuedurable.lan', `physical_uri` = '/';
```

Configure `htdocs/config/settings.inc.php` by renaming `htdocs/config/settings.empty.inc.php`

Give write permissions to webserver : ```sudo chmod -R a+rw .```
