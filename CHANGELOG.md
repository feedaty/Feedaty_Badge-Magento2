
#### CHANGELOG FEEDATY FOR MAGENTO 2



---------------------------------------------------------------------------------------------------------------------------------------------------------
### RELASE NOTES
---------------------------------------------------------------------------------------------------------------------------------------------------------
### V2.0.2

## Fixed issues

- Fix cache merchant's and product's rich snippets to minimize the site loading

### V2.0.1

## Fixed issues

- Bug in product reviews tab ( a bug in product reviews tab was able to display the tab if reviews are equals to 0 and some times display wrong reviews).


## Functional enhancements

- New Feedaty API with OAuth authentication for sending Orders
- New google rich snippets
- Survey email is sent in Customer language


## Security enhancements

- Updated validation in Feedaty code and Feedaty secret

## Known Issues

- Feedaty plugin is not caching merchant's and product's rich snippets ( we'll add this function in next relase )




---------------------------------------------------------------------------------------------------------------------------------------------------------
### TECHNICAL DETAILS
---------------------------------------------------------------------------------------------------------------------------------------------------------
### V2.0.2

## UPDATES IN Feedaty\Badge\Model\Config\Source\WebService Class

- UPDATED METHOD getProductRichSnippet()
- UPDATED METHOD getMerchantRichSnippet()


### V2.0.1

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

