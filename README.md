<h1 style="color:red">### FEEDATY BADGE</h1>
---------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------
Feedaty ti consente la gestione professionale
di feedback e opinioni certificate dei tuoi clienti,
migliorando il tuo posizionamento,
la tua reputazione e le tue vendite online.

### REQUIRED MAGENTO MODULES

magento/framework: 					100.0.* .

magento/module-store: 				100.0.* .

magento/module-catalog: 			100.0.* .

magento/module-customer: 			100.0.* .

magento/module-eav: 				100.0.* .

magento/module-theme: 				100.0.* .

magento/module-backend: 			100.0.* .

magento/module-ui: 					100.0.* .

magento/module-rewrite: 			100.0.* .

magento/module-bundle				100.0.* .

magento/module-cache-invalidate		100.0.* .

magento/module-catalog-url-rewrite	100.0.* .

magento/module-url-rewrite 			100.0.* .

magento/module-cron					100.0.* .

magento/module-sales				100.0.* .

magento/module-translation			100.0.* .

magento/module-widget				100.0.* .

### REQUIRED SERVER CONFIGURATIONS

- php >=5.5.38 | ~php 5.6.* | php ~7.0.0
- php-curl 

---------------------------------------------------------------------------------------------------------------------
### INSTALL FEEDATY MODULE
---------------------------------------------------------------------------------------------------------------------

## Download and Install with Composer

1) Insert https://packagist.org in your project repositories

2) type 
```bash
 # cd /var/www/path/to/your/magento-root-dir
```

3) now type
 ```bash
 # composer require feedaty/module-badge2
```
##Dowload and Install from Mageconnect



---------------------------------------------------------------------------------------------------------------------
### ENABLE FEEDATY MODULE
---------------------------------------------------------------------------------------------------------------------

## Enable Feedaty module from backend

1st) Move Feedaty directory in path/to/your/magento-root-dir/app/code/

2nd) Clink on "System Config" in the left sidebar menu.

3rd) Click on "Web Setup Config".

4th) Click on "Component Manager".

5th) Enable "feedaty/module-badge".

If a blank page returned, maybe you haven't a right configuration in your
Magento installation.
Check for permission in /var/www/path/to/your/magento-root-dir/var directory.
If the apache error log report a permission denied on var 
directory, run command:
```bash

 # chown -R apache:apache 

```
if your server run on Centos

or if your server run on Debian/Ubuntu:

```bash

 # chown -R www-data:www-data on "path/to/your/magento-root-dir/var"

```

 to let apache get the permission to write cache.

## Enable Feedaty module from Magento consolle

1st) Move Feedaty directory in path/to/your/magento-root-dir/app/code/

2nd) login with the own of the magento installation, after you must enter below commands:

```bash

 # cd /var/www/path/to/your/magento-root-dir

 # bin/magento module:enable Feedaty_Badge

```
 consolle ask to run setup:di:compile command, some times you can avoid enter this command
 if you enter "setup:di:compile" command, don't forget to check permissions to 
 "path/to/your/magento-root-dir/var" directory, otherwise a zend exeption rise
 because magento can't write cache in var directory.

-----------------------------------------------------------------------------------------------------------------------
### DISABLE FEEDATY MODULE
-----------------------------------------------------------------------------------------------------------------------
## Disable feedaty module from backend

same procedure like enabling, when you arrive in "component manager", select "Disable" and save your configuration.

## Disable feedaty module from Magento Consolle.

1st) login with the own of the magento installation, after you must enter below commands:
```bash

 # cd /var/www/path/to/your/magento-root-dir

 # bin/magento module:disable Feedaty_Badge

```
to clear static contents you can append "--clear-static-contents" in module:disable command
If you append "--clear-static-contents" don't forget to run

 # bin/magento setup:di:compile
 # bin/magento setup:static-content:deploy

### SETUP/CONFIG FEEDATY WIDGETS

To setup Feedaty Widgets follow these steps;

1st) Click on "Stores" in the left-side bar menu.

2nd) Click on "Configurations".

3rd) Select the default scope, and insert Feedaty Merchant Code provided.

4th) Select preferences aboute module design, and set on enable widgets and/or product reviews.

5th) check in other scopes, the configurations must works on the store scope.

### INFOS AND CONTACTS

Websites:
www.zoorate.com
www.feedaty.com

E-Mail:
info@feedaty.com

### LICENSE


