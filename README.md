# URB-IT SHIPPING WOOCOMMERCE

[![][UrbitLogo]][UrbitLink]

## About

- Version: 1.1.10
- extension key: Woocommerce-Urbit-Delivery
- extension on GitHub

## Prerequisites

You have an account with Urb-it and have received the integration details for your Urb-it account :

- X-API-Key
- Bearer token

The module is installed on your Woocommerce shop.

## Installation

### Getting a plugin

The plugin is not published in any marketplace yet so the only way to install it right now is to upload a **.zip** file from the Urb-it’s github repository. 

1. Click **Clone or download** (green button on the right side).
2. Push a **Download ZIP** button and the archive will be downloaded to your local machine.
3. Go to your Wordpress store back office page (admin panel).
4. Navigate **Plugins** section on the left menu bar.
5. Click **Add new**.
6. On the top of the page a **Upload Plugin** button will be shown. Please click it.
7. Then choose a .zip file you just downloaded from github and upload it to your Wordpress store.
8. Plugin will be extracted from .zip archive and installed on your store. Please wait a little and click the **Activate plugin** button when it will be showed after successful installation.

![Installation process][Img1-Get]

To check the plugin installed successfully please navigate it in the plugin list of the Wordpress back office (click on the “Plugins” button on the left menu bar).

### Setup for the first use

To setup a plugin we need to find Urb-it shipping plugin settings. To achieve it please follow these steps:

1. Go to your Wordpress store back office page (admin panel).
2. Navigate “WooCommerce” section on the left menu bar.
3. Hover it by mouse and select an “Urb-it”.

* If Urb-it settings in the WooCommerce section of menu does not exists it means the plugin was not installed.

#### Urb-it Plugin Settings

The plugin has a close interaction with Urb-it API so it is impossible to use it without environment credentials. The fields marked on the screenshot is required to proper working of the plugin.

1. Pick an environment appropriate for you purposes.
2. Insert **X-API-Key** to the according field.
3. Insert a **token** to the according field. *“Bearer”* word is necessary.
4. Set a custom preparation time in the **Now order auto validation time** field *(time represented in minutes)*.
5. Pick a WooCommerce order status option that will be act like a trigger for the immediate order shipping or when it will be prepared earlier then it is expected.
6. Click on the **Save changes** button to apply new configuration.

![Urb-it plugin settings][Img2-Setup]

**Production API URL**:  https://api.urb-it.com  
**Test/Sandbox API URL**:  https://sandbox.urb-it.com

Please follow this [settings reference] to get more details about each of the configuration section.

## Placing an order with Urb-it shipping

You have to be sure that plugin is installed, activated and configured. If something went wrong please follow previous steps.  

    Now order delivery time = Preparation time (defined in BO) or order confirmation (defined in BO) + 1h30min  

To make and order with Urb-it just follow the straight steps:

1. Go to the storefront.
2. Place some products to cart.
3. Go to the cart page.

### Cart

1. Choose a shipping option suitable for you (if store is closed at the time, urb-it now option may not appear).
2. Click “Proceed to checkout”.

![Placing an order (cart step)][Img3-Cart]

### Checkout

At the checkout step we need to fill all required billing/shipping information and choose a proper delivery time *(only for urb-it specific time)*. 

1. Fill the “Billing details” form with correct values. The error notification will be shown after pressing “Place order” button If provided values is not correct.
2. **(ONLY FOR URB-IT SPECIFIC TIME)**. Select a delivery day. All available delivery days and time took from the Urb-it API according to X-API-Key (individual for each customer).
3. **(ONLY FOR URB-IT SPECIFIC TIME)**. Select Hour and Minute from the dropdown list when order will be delivered.
4. Push “Place order” button at the bottom of the page.

![Placing an order (checkout step)][Img4-Checkout]

### Checking just received order

To see the details of the order that we just made, go back to the Wordpress admin panel.. 
1. In the left menu bar hover “WooCommerce” section and select “Orders”. 
2. Choose your order from the list (can be detected by the order number displayed at the “Order recieved” page).

Now you see the order details page. The order type and products are displayed in the “Item” section below, and the delivery time is on the right “Order notes” section. 

There you have two options:
1. Wait for the order will be sent to Urb-it by the time picked at checkout (scheduled by the cron task).
2. In case when order prepared earlier you can mark it as ready to ship by changing the order status to one specified in Urb-it configuration. Then order will be triggered (cron task will be dropped) and order will go to Urb-it (it will notify Urb-it that it is ready to be shipped).

To trigger the order please follow the next steps:

1. Switch the order status to the “trigger” (see above) status.
2. Update order by clicking “Update” button on the right side.

![Checking and sending an order][Img5-Order]

## Settings reference

- **Undeliverable Product** : This is the switch to enable a bloc to warn the consumer when a product can’t be delivered via Urb-it
- **Exceeded Cart Limit** : This is the switch to enable a bloc to warn the consumer when the cart exceeds the service limits (weight or volume)
- **Undeliverable Order** : This is the switch to enable a bloc to finally explain why the order can not be delivered by Urb-it.
- **Postal code validation** : Add a bloc to validate eligible postal codes on the product page.
- **Order confirmation** : Add a confirmation dialog box to validate order processing
- **Enable test mode** : You need to enable test mode before you go against the API test environment
- **Enable logs** : Enable logs for Errors only or everything.
- **Now order auto-validation time** : This field is a value, in minutes, creating an additional delay for « Now orders » and « first possible » Scheduled orders.
- **Order status trigger for confirmation** : When an order is placed, an order status must be defined to trig the order confirmation on Urb-it’s end. After this \* event (can be automatic, changed manually in the back-office) the order will be created in the Urb-it system and Urbers will be able to claim the order to do the pickup and the delivery
- **Urb-it API Key** : This is your retailer key. The token and the API-Key will be provided by your local sales team.
- **Bearer JWT Token** : This token allows Urb-it to identify your web shop when creating orders The token forand the API-Key will be provided by your local sales team.

## Troubleshooting

**If shipping option is not visible at all in the checkout**: check the plugin is installed and activated.

## Woocommerce uninstallation

1. In your Back-office, go in the **extension** tab
2. Search for **Urb-it**
3. Click on **Delete** the module

## Support

If you have any issues with this extension, contact us at support@urbit.com

## Contribution

Any contribution is highly appreciated. The best way to contribute code is to open a pull request on GitHub.

License
GPL v3

Copyright
(c) 2018 Urb-it

[UrbitLogo]: https://s3.eu-central-1.amazonaws.com/urbit-connect/logo_g.png
[UrbitLink]: https://urb-it.com/
[Img1-Get]: https://lh6.googleusercontent.com/go0QLgP3Z073lMKzLc0qvVmDOaEjkNcnSMi1NKfS2KkTniFaoR1LjpX-ZVzsCzrYvgffWQPZEPnJLTzhErBRC9goPKPCkpLgWb2pi4ZKSyRhcT0LwZWLhJ-zmKWZNOfIsA
[Img2-Setup]: https://lh5.googleusercontent.com/aX38pj2ZdZKn8O4tMTwFotv03rrCS8LQPu408zo7RS6Rbv13djGSm1_KEgvzhyDpuTwIDDHzl0W0OfxDJ5cs-bMQR56akfO_lp574SA4rwYBMQcBkcjrc11x-LIDwHhBKA
[Img3-Cart]: https://lh5.googleusercontent.com/rVirapx9WR32SRMxArejpJZunC1zDmQwfV5FhJGwZdzBFR_eVH1rcZf50zO1pJOJMAvRbMN83hUIRTGuMT2fmycQSt5jsQnDSoXFDO74ZO-Er_4dhYZToRPpTerbXTa8CA
[Img4-Checkout]: https://lh6.googleusercontent.com/mqRsiNDVBsdcuokxJxf6bLiCUPE51ZvDilTJy9Hx2ryukZ7zD-Z90ZwNjrAx0sGjzMvFk3Mr-nqtUb---B-1DaQERp95oZM0IK5Cu5ySTW642X__UNgi6psykqU9-AqJ3Q
[Img5-Order]: https://lh5.googleusercontent.com/vXL7Memtn3emxmCX3bQDXYdndkDkNRIp_4qMeXZIFDHRNkPXA1rHmeyZ-pMsLMN_MrEDiXLvhl-Ep1Xw4n-fj-12tADPKQuLS0jru4f3qUgqha118u8cVDXZdYSXO0mDdA
[settings reference]: https://github.com/urbitassociates/WooCommerce-Urbit-Delivery#settings
