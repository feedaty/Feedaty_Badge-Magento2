##### CHANGELOG FEEDATY FOR MAGENTO 2


#### RELASE NOTES

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
----------------------------------------------------------------------------------------------------------------------------
### V2.2.8 - Cosimo

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

