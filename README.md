URB-IT SHIPPING WOOCOMMERCE

Facts
Version: 1.1.10
extension key: Woocommerce-Urbit-Delivery
extension on GitHub

##Prerequisites##
You have an account with Urb-it and have received the integration details for your Urb-it account :

X-API-Key
Bearer token

The module is installed on your Woocommerce shop.
Installation

##Step-by-step installation##
STEP 1 : In your Back-office, go in the extensions tab
STEP 2 : Click on “Add” Upload the zip of the module
STEP 3 : Click on the Activate button to actually install the module

##Manual installation##
If receiving the module in a compressed file.Unpack the files into the module folder of your Woocommerce project via FTP access on your server.

##Settings##

Undeliverable Product : This is the switch to enable a bloc to warn the consumer when a product can’t be delivered via Urb-it
Exceeded Cart Limit : This is the switch to enable a bloc to warn the consumer when the cart exceeds the service limits (weight or volume)
Undeliverable Order : This is the switch to enable a bloc to finally explain why the order can not be delivered by Urb-it.
Postal code validation : Add a bloc to validate eligible postal codes on the product page.
Order confirmation : Add a confirmation dialog box to validate order processing
Enable test mode : You need to enable test mode before you go against the API test environment
Enable logs : Enable logs for Errors only or everything.
Now order auto-validation time : This field is a value, in minutes, creating an additional delay for « Now orders » and « first possible » Scheduled orders.
Order status trigger for confirmation : When an order is placed, an order status must be defined to trig the order confirmation on Urb-it’s end. After this event (can be automatic, changed manually in the back-office) the order will be created in the Urb-it system and Urbers will be able to claim the order to do the pickup and the delivery
Urb-it API Key : This is your retailer key. The token and the API-Key will be provided by your local sales team.

Bearer JWT Token
This token allows Urb-it to identify your web shop when creating orders The token forand the API-Key will be provided by your local sales team.

##Troubleshooting##
If shipping option is not visible at all in the checkout.
 
Uninstallation
##Woocommerce uninstallation##

STEP 1 : In your Back-office, go in the extension tab
STEP 2 : Search for Urb-it
STEP 3 : Click on Delete the module

##Woocommerce uninstallation##
Delete your urbit folder in the “extensions” directory of your Woocommerce module project via FTP access on your server.

####Support##
If you have any issues with this extension, contact us at support@urbit.com

##Contribution##
Any contribution is highly appreciated. The best way to contribute code is to open a pull request on GitHub.

License
GPL v3

Copyright
(c) 2018 Urb-it
