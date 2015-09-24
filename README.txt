=== Aplazame ===
Contributors: calvin
Donate link: https://aplazame.com?action=show-me-the-money
Tags: aplazame,api,rest,woocommerce,ecommerce,payment,checkout,credit,aplazar,financiar,financiera,financiación,pago aplazado,método de pago
Requires at least: 4.0.1
Tested up to: 4.3
Stable tag: 4.3
License: GPLv3 or later License
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Aplazame para WooCommerce, compra ahora y paga después


== Description ==

= Introduction =

[Aplazame](https://aplazame.com) is a consumer credit company, offers a payment system that can be used by online buyers to receive funding for their purchases.

The module allows the customer to defer the payment of his online purchases in ecommerces offering Aplazame as a payment method. Aplazame's mission is to increase sales of any online store eliminating any friction in the process.

Ease of implementation is the main objective we had in mind when developing Aplazame.

= Continuous Integration =

Automated syntax review and deployment using continuous integration tools like [drone.io](http://drone.aplazame.com/github.com/aplazame/woocommerce).

= Coding Standards =
Aplazame maintain a consistent style so the code can become clean and easy to read at a glance.

In continuous integration processes we deploy a release using the Wordpress package [WordPress-Coding-Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) to validate code developed for WordPress.

= l10n =

Development according to standards for i18n and l10n.
All of our Spanish translators are professionally qualified, native speakers.

= Support =

For any support request please drop us an email at [soporte.woo@aplazame.com](mailto:soporte.woo@aplazame.com?subject=Help-me). Our support team is available 24/7.

= Feedback =

We are open to changes in the API documentation and our services. For any suggestions please send us an email to  [dev@aplazame.com](mailto:dev@aplazame.com?subject=Hello).

*We hope you'll enjoy using Aplázame's WooCommerce plugin!*


== Installation ==

= Requirements =

*WooCommerce 2.1 or higher*

= Quick install =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Aplazame for WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type Aplazame for WooCommerce and click Search Plugins. Once you've found our plugin you can install it by simply clicking Install Now.

= Latest version =

1. **Download** the latest plugin from [here](https://s3.eu-central-1.amazonaws.com/aplazame/modules/woocommerce/aplazame.latest.zip) to local directory as `aplazame.latest.zip`.
2. Go to the Wordpress administration page, and then go to **Plugins** > **Add New**.
3. **Add new plugin** and select the `aplazame.latest.zip` file from your computer.

= Updating =

Automatic updates should work great for you.

= Usage =

* **Sandbox**: Determines if the module is on Sandbox mode.
* **Host**: Aplazame host `https://aplazame.com`
* **API Version**: The latest version is `v1.2`
* **Button**: The CSS Selector for Aplazame payment method. The default selector is `#payment ul li:has(input#payment_method_aplazame)`.
* **Secret API Key**: The Secret Key provided by Aplazame. You cannot share this key with anyone!!
* **Public API Key**: The Public Key provided by Aplazame.
* **Enable Analytics**: If you want to enable customer tracking for better interests.

> Be sure that on all fields you don't keep any whitespace. Otherwise the module can generate unexpected results.


== Frequently Asked Questions ==

= How Can I Get an API Keys? =
We will have a signup page soon, for now you can [contact us](mailto:soporte.woo@aplazame.com?subject=i-need-a-token) for the API Keys.

= Can I cancel an order with Aplazame? =
Yes, simply replace the status to *"cancelled"* or *"refunded"*.

= Can I refund an order with Aplazame? =
Of course, simply select an *"Aplazame refund"* in the product detail.


== Screenshots ==

1. Configure
2. Cart
3. Signin
4. Pin Code
5. Discounts
6. Connect
7. Select payment method
8. Add payment method
9. Success


== Changelog ==

#### [v0.0.4] (2015-09-25)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.4)

* Fix security, order session key
* Fix customer serializer

#### v0.0.3 (2015-09-15)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.3)

* Admin notices checks
* l10n review
* update composer.json

= [v0.0.2] (2015-09-14) =

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.2)

* l10n fix and review
* API exception handler
* process_refund on gateway class
* Full Readme.txt
* make options

= [v0.0.1] (2015-09-12) =

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.1)

* birth


== Upgrade Notice ==

= v0.0.4 =

The latest release has passed all quality checks.
