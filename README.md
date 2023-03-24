<h1> FEEDATY BADGE 2</h1>


Feedaty is a social commerce site dedicated to online stores for the professional management of customer feedback. 
The service is provided through a platform Saas (Software as a Service) and may be activated quickly and easily through a short integration process.


<h2> INSTALL FEEDATY MODULE </h2>


<h3> Install from composer </h3>

1) move to your magento root directory
 ```bash
 # cd /var/www/html/path/to/your/magento-root-dir
```

2)  login as the owner of your magento filesystem, for example:
```bash
 # su magentouser
```
3) require and install the package

```bash
 # composer require feedaty/module-badge2
```
 
<h3> Install Feedaty module manually </h3>

1) Download Feedaty Module from https://github.com/feedaty/Feedaty_Badge-Magento2
2) You have to manually create folders Feedaty/Badge/ at path/to/your/magento-root-dir/app/code/ and Move content of Feedaty_Badge-Magento2-master directory in path/to/your/magento-root-dir/app/code/Feedaty/Badge/ 
3) move to your magento root directory
```bash
# cd /var/www/html/path/to/your/magento-root-dir
```
5) login as the owner of your magento filesystem, for example:
```bash
 # su magentouser
```
5) run comand
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy [<languages>]
```

<h2> DISABLE FEEDATY MODULE </h2>

<h3> Disable feedaty module from Magento Console. </h3>

1st) login with the own of the magento installation, after you must enter below commands:
```bash

 # cd /var/www/path/to/your/magento-root-dir

 # bin/magento module:disable Feedaty_Badge

```

to clear static contents you can append "--clear-static-contents" in module:disable command
If you append "--clear-static-contents" don't forget to run
```bash

 # bin/magento setup:di:compile
 # bin/magento setup:static-content:deploy

```

<h2> SETUP/CONFIG FEEDATY WIDGETS </h2>


To setup Feedaty Widgets follow these steps;

1st) Click on "Stores" in the left-side bar menu.

2nd) Click on "Configurations".

3rd) Insert Feedaty merchant codes and Feedaty merchant secrets on relative stores .

4th) Select preferences aboute module design, and set on enable widgets and/or product reviews.


<h3>Import Feedaty Reviews</h2>

Reviews are imported by cron job. Make sure you have cron enabled on Magento.


<h2> INFOS AND CONTACTS </h2>

www.feedaty.com

<h2> LICENSE </h2>

AFL-3.0

