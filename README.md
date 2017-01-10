<h1> FEEDATY BADGE 2</h1>
---------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------
Feedaty is a social commerce site dedicated to online stores for the professional management of customer feedback. 
The service is provided through a platform Saas (Software as a Service) and may be activated quickly and easily through a short integration process.

<h3>  REQUIRED MAGENTO MODULES </h3>

magento/framework: 					100.0.* | <br/>
magento/module-store: 				100.0.* | <br/>
magento/module-catalog: 			100.0.* | <br/>
magento/module-customer: 			100.0.* | <br/>
magento/module-backend: 			100.0.* | <br/>
magento/module-rewrite: 			100.0.* | <br/>
magento/module-bundle				100.0.* | <br/>
magento/module-catalog-url-rewrite	100.0.* | <br/>
magento/module-url-rewrite 			100.0.* | <br/>
magento/module-cron					100.0.* | <br/>
magento/module-sales				100.0.* | <br/>
magento/module-translation			100.0.* | <br/>
magento/module-widget				100.0.* | <br/>

<h3>  SUGGESTED MAGENTO MODULES </h3>

magento/module-theme: 				100.0.* | <br/>
magento/module-ui: 					100.0.* | <br/>
magento/module-cache-invalidate		100.0.* | <br/>

<h3> REQUIRED SERVER CONFIGURATIONS </h3>

- php >=5.5.27 | php 5.6.* | php 7.0.0
- php-curl 


---------------------------------------------------------------------------------------------------------------------
<h2> INSTALL FEEDATY MODULE </h2>
---------------------------------------------------------------------------------------------------------------------
<h3> Install from composer </h3>

1st) move to your magento root directory
 ```bash
 # cd /var/www/html/path/to/your/magento-root-dir

```

2nd) add http://packagist.org/ repository in your composer.json file

3rd) login as the owner of your magento filsystem, for example:
```bash
 # su magentouser
```
4th) require and install the package

```bash
 # composer require feedaty/module-badge2

```
 
<h3> Install Feedaty module manually in app/code </h3>

1st) Move Feedaty_Badge-2.0.1 directory in path/to/your/magento-root-dir/app/code/ and rename it Feedaty


---------------------------------------------------------------------------------------------------------------------
<h2> ENABLE FEEDATY MODULE </h2>
---------------------------------------------------------------------------------------------------------------------
<h3> Enable Feedaty module from backend </h3>

1st) Move Feedaty directory in path/to/your/magento-root-dir/app/code/

2nd) Clink on "System Config" in the left sidebar menu.

3rd) Click on "Web Setup Config".

4th) Click on "Component Manager".

5th) Enable "feedaty/module-badge2".

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

<h3> enable module from Magento consolle </h3>
1st) login with the own of the magento installation for example:

```bash
 # su magentouser
```

now enter below commands:

```bash

 # cd /var/www/path/to/your/magento-root-dir

 # bin/magento module:enable Feedaty_Badge

```
 consolle ask to run setup:di:compile command, some times you can avoid enter this command
 if you enter "setup:di:compile" command, don't forget to check permissions to 
 "path/to/your/magento-root-dir/var" directory, otherwise a zend exeption rise
 because magento can't write cache in var directory.

-----------------------------------------------------------------------------------------------------------------------
<h2> DISABLE FEEDATY MODULE </h2>
-----------------------------------------------------------------------------------------------------------------------
<h3> Disable feedaty module from backend</h3>

same procedure like enabling, when you arrive in "component manager", select "Disable" and save your configuration.

<h3> Disable feedaty module from Magento Consolle. </h3>

1st) login with the own of the magento installation, after you must enter below commands:
```bash

 # cd /var/www/path/to/your/magento-root-dir

 # bin/magento module:disable Feedaty_Badge

```

to clear static contents you can append "--clear-static-contents" in module:disable command
If you append "--clear-static-contents" don't forget to run

 # bin/magento setup:di:compile
 # bin/magento setup:static-content:deploy
-----------------------------------------------------------------------------------------------------------------------
<h2> SETUP/CONFIG FEEDATY WIDGETS </h2>
-----------------------------------------------------------------------------------------------------------------------
To setup Feedaty Widgets follow these steps;

1st) Click on "Stores" in the left-side bar menu.

2nd) Click on "Configurations".

3rd) Insert Feedaty merchant codes and Feedaty merchant secrets on relative stores .

4th) Select preferences aboute module design, and set on enable widgets and/or product reviews.

-----------------------------------------------------------------------------------------------------------------------
<h2> INFOS AND CONTACTS </h2>
-----------------------------------------------------------------------------------------------------------------------
www.zoorate.com
www.feedaty.com

<h2> LICENSE </h2>


