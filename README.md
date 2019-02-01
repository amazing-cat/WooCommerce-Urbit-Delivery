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

The plugin is not published in any marketplace yet. Thus, the only way to install it is to upload a **.zip** file from the Urb-it’s github repository. 

1. Click **Clone or download** (green button on the right side).
2. Press the **Download ZIP** button and the archive will be downloaded to your local machine.
3. Go to your Wordpress store back office page (admin panel).
4. Navigate to the **Plugins** section in the left-hand menu bar.
5. Click **Add new**.
6. At the top of the page, an **Upload Plugin** button will be shown. Please click it.
7. Choose the .zip file you just downloaded from Github and upload it to your Wordpress store.
8. Please wait while the plugin is installning. Click the **Activate plugin** button when it appears after the installation is completed.

![Installation process][Img1-Get]

To check if the plugin was installed successfully, please navigate to the plugins list in the Wordpress back office (click the “Plugins”-button in the left-hand menu bar).

### Setup for the first use

To set up the plugin, we need to locate the Urb-it shipping plugin settings. Follow these steps in order to locate the settings:

1. Go to your Wordpress store back office page (admin panel).
2. Locate the “WooCommerce” section in the left-hand menu bar.
3. Hover your mouse over the menu item and select “Urb-it”.

* If you can't locate the Urb-it settings in the WooCommerce section of the menu, it means the plugin was not installed successfully. Please go back to "Installation process" and make sure the plugin is installed successfully.

#### Urb-it Plugin Settings

The plugin communicates with [Urb-it Delivery API](https://developer.urb-it.com/) so you need to have the proper [credentials](https://developer.urb-it.com/#tag/Authentication) in order to use the plugin. You may get these by [contacting Urb-it](mailto:support@urbit.com). The fields marked in the screenshot are required in order for the plugin to work properly.

1. Pick an environment appropriate for you purposes.
2. FIll in your **X-API-Key** in the corresponding field.
3. Fill in your **token** in the corresponding field. `“Bearer”` word is necessary. For example: `Bearer 123-abc`.
4. Set a custom preparation time in the **Now order auto validation time** field *(time represented in minutes)*. This is the time it takes for you as a retailer to prepare the order in the store before the order will be placed at Urb-it.
5. Pick a WooCommerce order status option that will act as a trigger for when the order will be sent to Urb-it.
6. Click on the **Save changes** button to apply the new configuration.

![Urb-it plugin settings][Img2-Setup]

**Production API URL**:  https://api.urb-it.com  
**Test/Sandbox API URL**:  https://sandbox.urb-it.com

Please follow this [settings reference] to get more details about each of the configuration section.

## Placing an order with Urb-it shipping

When you're sure that the plugin is installed, activated and configured correctly, you're now ready to start placing orders with Urb-it. If something isn't working, please follow the previous steps in this guide.


To place an order with Urb-it, just follow these steps:

1. Go to the storefront.
2. Put some products in the cart.
3. Go to the cart page.

### Cart

1. Choose a shipping option suitable for you (if store is closed at the time, the "urb-it now"-option may not appear).
2. Click “Proceed to checkout”.

![Placing an order (cart step)][Img3-Cart]

### Checkout

At the checkout step we need to fill all required billing/shipping information and choose a proper delivery time *(only for urb-it specific time)*. 

1. Fill the "Billing details" form with correct values. The error notification will be shown after pressing the "Place order"-button if the provided values are not correct.
2. **(ONLY FOR URB-IT SPECIFIC TIME)**. Select a delivery day.
3. **(ONLY FOR URB-IT SPECIFIC TIME)**. Select Hour and Minute from the dropdown list.
4. Push “Place order” button at the bottom of the page.

![Placing an order (checkout step)][Img4-Checkout]

### Checking just received order

To see the details of the order that we just made, go back to the Wordpress admin panel.
1. In the left menu bar, hover “WooCommerce” section and select “Orders”. 
2. Choose your order from the list (can be detected by the order number displayed at the “Order recieved” page).

Now you see the order details page. The order type and products are displayed in the “Item” section below, and the delivery time is on the right “Order notes” section. 

There you have two options:
1. Wait for the order to be sent to Urb-it by the time selected at checkout (scheduled by the cron task).
2. In case the order has been prepared earlier you can mark it as ready to ship by changing the order status to one specified in Urb-it configuration. Then order will be triggered (cron task will be dropped) and go to Urb-it (it will notify Urb-it that it is ready to be shipped).

To trigger the order, follow these steps:

1. Switch the order status to the “trigger” (see above) status.
2. Update the order by clicking the “Update” button on the right side.

![Checking and sending an order][Img5-Order]

## Settings reference

- **Undeliverable Product** : This is the switch to enable a bloc to warn the customer that a product can’t be delivered via Urb-it.
- **Exceeded Cart Limit** : This is the switch to enable a bloc to warn the customer when the cart exceeds the service limits defined by Urb-it (weight or volume).
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

**If the shipping option is not visible at all in the checkout**: check that the plugin is installed and activated.

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
