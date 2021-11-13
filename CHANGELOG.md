##### CHANGELOG FEEDATY FOR MAGENTO 2

#### RELASE NOTES

### V2.7.0

- Add Feedaty Review Columns to review_detail table

### V2.6.7 05/09/2021
- Fix Culture Code - Reviews are now requested in the correct store view language

### V2.6.0 14/04/2021

- 	New Product Badge Widget
- 	New Store Badge Widget

### V2.5.7-dev- 14/04/2021 

 - 	Snippet prodotto

### V2.5.6-beta2 - 04/02/2020 

 - 	Fix undefined variable 

### V2.5.5-beta - 04/02/2020 

 - 	Patch Errore Store style
 -	Corretto Prametro Culture in web service feedaty

### V2.5.4-beta - 04/02/2020 

 - Gestione parametro Culture tramite API per invio mail survey

### V2.5.3-beta - 04/02/2020 

 - Gestione dello store e delle conf in base allo store dell'ordine 

### V2.5.2-beta - 11/01/2020 

 - Gestiti Prodotti Configurabili (viene presa immagine del children sku del padre e url del padre)
 - Gestito errore stato pending e gestito array prodotti vuoto

### V2.5.1-beta - 11/01/2020 


### V2.5.0-beta - 23/12/2020 

 - Sostituita classe OrderRepository con OrderInterface e caricato ordine tramite increment ID
 - Incremento di una minor per via della classe sostituita in teoria i cambiamenti sono incompatibili 
 - Con le vecchie versioni 
 
### V2.4.0-beta - 03/12/2020

UPDATES:

 - Refactory codice Observer utiizzando API INTERFACE ORDERS
 - Refactory Snippets prodotto

FIXES:

- Fix undefined $zoorate_env 
- Fix Intercept order (qaplà)

### V2.3.6-beta - Cosimo

UPDATES:

	- Fixato errore variabile $return non dichiarata in ProdVariants.php at line 84

### V2.3.5-beta - Cosimo

UPDATES:

	- Fixato sistema widget prodotto
	- Cambiato sistema configurazione widget (aggiunto JS per gestione preview in tempo reale senza salvataggio)
	- Fixato problema istanza varianti merch e varianti prodotto (KGC shop)

### V2.3.4-beta - Cosimo

UPDATES:

	- Fix bug nei microdati prodotto che appendeva una & che rompeva le cache nel File:
		./Model/Config/Source/WebService.php
		
	- Inserita gestione configurazione timeout
		(nel caso di timeout reached il plugin logga errore e rilascia processo)
	- Aumentata la cache a 24h


### V2.3.3-beta - Cosimo

UPDATES:

	- Fix bug get instance model product in foreach nel File ./Observer/InterceptOrder.php

#### RELASE NOTES

### V2.3.2-beta - Cosimo

- Fixato problema order id e store id nel'intrcept dell'ordine

### V2.3.1-stable - Cosimo

- Fixato problema con info curl protocollo

### V2.3.1-beta - Cosimo

- Fixato path di export csv (viene sportato nella cartella tmp)
- Ampliata Funzione di debug per rilevare errori CURL e info HEADER

### V2.3.0-beta - Cosimo

Fixata visualizzazione store badge
Fixata visualizzazione product badge

### V2.3.0 - Cosimo

- New Widgets V6

### V2.2.9 - Cosimo

- Fix all scope settings

### V2.2.8 - Cosimo
- Update feedaty event name and make it unique to avoid observer conflicts with other plugins

### V2.2.7 - Cosimo
- Update Intercept order Observer 
- Fix OB_CLEAN Error

### V2.2.6 - Cosimo

- Update configurations (allow sets up only on storeviews)
- Insert new debug function in csv export 
- Insert new debug function intercept order event observer
- Fix Store id in intercept order event observer when the context is AdminHtml
- Deleted deprecated D.I in intercept order event observer 

### V2.2.5 - Cosimo

- Fix conflict with trd-party plugin tempaltes for base.phtml overrides
- Fix Merchant and Product snippet observers 

### V2.2.4 - Cosimo

## Functional enchancements
- extend Debug functionality, to debug data sent to feedaty

### V2.2.3 - Cosimo

## Fixed issues
- Remove stage from getdata

### V2.2.2 - Cosimo

## Fixed issues
- Code Convenction enchancements
- Fix ObjectMagaer instance error

### V2.2.0 - Cosimo

## Fixed issues
- Fix Feedaty installation errors for Magento 2.2
- Works for multishop
- Code Convenction enchancements

## Functional enchancements
- Added Feedaty admin menu
- Multisite supported  
- Feedaty customers's credentials can be mapped in a special section

### V2.0.4 - Cosimo

## Fixed issues

- Avoid errors checking http response code from microdata server
- Feedaty now handle bundle and configurable products as one product
- Change to "Mage2" cURL req User-Agent 
- Removed discouraged echo construct from csv export 

## Functional enchancements

- Microdata configuration in Feedaty menu on backend
- Add debug function for orders and microdata response

### V2.0.3 - Cosimo

## Fixed issues

- Fix display errors on Feedaty Servers issues

## Functional enchancements

- Increments to 6h Snippets cache
- Improve performance by reducing calls to Feedaty API

### V2.0.2 - Cosimo

## Fixed issues

- Fix cache merchant's and product's rich snippets to minimize the site loading

### V2.0.1 - Cosimo

## Fixed issues

- Bug in product reviews tab ( a bug in product reviews tab was able to display the tab if reviews are equals to 0 and some times display wrong reviews).


## Functional enchancements

- New Feedaty API with OAuth authentication for sending Orders
- New google rich snippets
- Survey email is sent in Customer language


## Security enchancements

- Updated validation in Feedaty code and Feedaty secret

## Known Issues

- Feedaty plugin is not caching merchant's and product's rich snippets ( we'll add this function in next relase )




----------------------------------------------------------------------------------------------------------------------------
### METHOD/CLASS CHANGES
------------------------------------------------------------------------------------------------------

### V2.2.9 - 25/11/2019 (Cosimo)

- UPDATED adminhtml\system.xml
- UPDATED adminhtml\menu.xml

### V2.2.8 - 25/11/2019 (Cosimo)

- UPDATE FILE /etc/events.xml

### V2.2.7 - Cosimo

## UPDATES IN Feedaty\Badge\Observer\InterceptOrder Class
- UPDATED METHOD execute()

## UPDATES IN Feedaty\Badge\Controller\Adminhtml\Index\Index
- UPDATED METHOD downloadCsv()

### V2.2.6 - Cosimo

## UPDATES IN Feedaty\Badge\Model\Config\Source\WebService Class
- DELETED METHOD feedatyDebug()

## UPDATES IN Feedaty\Badge\Observer\InterceptOrder Class
- UPDATED METHOD execute()

## UPDATES IN Feedaty\Badge\Helper\Data Class
- ADDED METHOD feedatyDebug()

## UPDATES IN Feedaty\Badge\etc\adminhtml\system.xml
- Show Feedaty configurations only if scope is in store view

### V2.0.4 - Cosimo

## UPDATES IN Feedaty\Badge\Model\Config\Source\WebService Class
- UPDATED METHOD getProductRichSnippet()
- UPDATED METHOD getMerchantRichSnippet()
- UPDATED METHOD getReqToken()
- UPDATED METHOD send_order()

## UPDATES IN Feedaty\Badge\Observer\StoreBadge Class
- UPDATED METHOD execute()

## UPDATES IN Feedaty\Badge\Observer\ProductBadge Class
- UPDATED METHOD execute()

## UPDATED OBSERVERS
- ADDED Feedaty\Badge\Observer\ProductSnippet Class
- ADDED Feedaty\Badge\Observer\StoreSnippet Class
- UPDATED Feedaty\Badge\Observer\ProductBadge Class
- UPDATED Feedaty\Badge\Observer\StoreBadge Class

## UPDATES IN Feedaty\Badge\Controller\Adminhtml\Index\Index
- UPDATED METHOD execute()
- ADDED METHOD downloadCsv()

## UPDATED Feedaty\Badge\etc\
- UPDATED adminhtml\system.xml
- UPDATED events.xml

### V2.0.3 - Cosimo

## UPDATES IN Feedaty\Badge\Model\Config\Source\WebService Class

- UPDATED METHOD getProductRichSnippet()
- UPDATED METHOD getMerchantRichSnippet()
- UPDATED METHOD _get_FeedatyData()

## UPDATES IN Feedaty\Badge\Observer\StoreBadge Class

- UPDATED METHOD execute()

### V2.0.2 - Cosimo

## UPDATES IN Feedaty\Badge\Model\Config\Source\WebService Class

- UPDATED METHOD getProductRichSnippet()
- UPDATED METHOD getMerchantRichSnippet()


### V2.0.1 - Cosimo

## UPDATES IN Feedaty\Badge\Model\Config\Source\WebService Class

- ADDED NEW METHOD getReqToken()
- ADDED NEW METHOD serializeData()
- ADDED NEW METHOD getAccessToken()
- ADDED NEW METHOD encryptToken()
- ADDED NEW METHOD getProductRichSnippet()
- ADDED NEW METHOD getMerchantRichSnippet()
- UPDATED METHOD send_order()
- UPDATED METHOD _get_FeedatyData()
- UPDATED METHOD send_notification()
 
## UPDATED TEMPLATE Feedaty\Badge\view\frontend\templates\product_reviews.phtml
	
	- FIXED BUG wich display empty tab

## UPDATED Feedaty\Badge\Observer\InterceptOrder Class

	- UPDATED array structure sent to new feedaty API
	- ADDED functional enchancement for send survey email in Customer's language

## UPDATED Feedaty\Badge\Observer\ProductBadge Class

	- ADDED google rich snippet for product

## UPDATED Feedaty\Badge\Observer\StoreBadge Class

	- ADDED google rich snippet for merchant
	- UPDATED call to sendnotification() method ()

## UPDATED Feedaty\Badge\Observer\ProductReviews Class

    - FIXED BUG wich allow to display empty tab

## UPDATED Feedaty\Badge\etc\adminhtml\system.xml Class

	- ADDED new field with id 'feedaty_secret'

