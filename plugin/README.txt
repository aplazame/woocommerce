=== Aplazame ===
Contributors: aplazame,calvin
Donate link: https://aplazame.com?action=show-me-the-money
Tags: aplazame,api,rest,woocommerce,ecommerce,payment,checkout,credit,aplazar,financiar,financiera,financiación,pago aplazado,método de pago
Requires at least: 4.0.1
Tested up to: 4.7.3
Stable tag: 0.6.1
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

For any support request please drop us an email at [soporte@aplazame.com](mailto:soporte@aplazame.com?subject=Help-me). Our support team is available 24/7.

= Feedback =

We are open to changes in the API documentation and our services. For any suggestions please send us an email to  [dev@aplazame.com](mailto:dev@aplazame.com?subject=Hello).

*We hope you'll enjoy using Aplazame's WooCommerce plugin!*


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
* **Private API Key**: The Secret Key provided by Aplazame. You cannot share this key with anyone!!
* **Public API Key**: The Public Key provided by Aplazame.
* **Button**: The CSS Selector for Aplazame payment method. The default selector is `#payment ul li:has(input#payment_method_aplazame)`.
* **Product quantity CSS selector**: The CSS Selector for retrieve the product quantity in the product page. The default value is empty.
* **Product price CSS selector**: The CSS Selector for retrieve the product price in the product page. The default value is empty.
* **Variable product price CSS selector**: The CSS Selector for retrieve the variable product calculated price in the product page. The default selector is `#main [itemtype="http://schema.org/Product"] .single_variation_wrap .amount`.

> Be sure that on all fields you don't keep any whitespace. Otherwise the module can generate unexpected results.


== Frequently Asked Questions ==

= How Can I Get an API Keys? =
Just signup for a vendor account at [https://vendors.aplazame.com/u/signup](https://vendors.aplazame.com/u/signup)

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

#### [v0.6.1] (2017-07-06)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.6.1)

* Fix error on checkout payload

#### [v0.6.0] (2017-04-10)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.6.0)

* Fix refunds.
* Add compatibility with WooCommerce v3

#### [v0.5.2] (2017-02-23)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.5.2)

* Many fixes and improvements.

#### [v0.5.1] (2017-02-22)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.5.1)

* Many fixes and improvements.

#### [v0.5.0] (2017-02-22)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.5.0)

* Many fixes and improvements.

#### [v0.4.5] (2016-11-24)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.4.5)

* [fixed] When return to shop current order is cancelled.

#### [v0.4.4] (2016-10-31)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.4.4)

* Fix confirmation_url generation

#### [v0.4.3] (2016-09-16)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.4.3)

* Fix aplazame-redirect for WP < 4.4

#### [v0.4.2] (2016-09-14)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.4.2)

* Fix compatibility with WooCommerce < 2.4
* Tested up to WordPress 4.6


#### [v0.4.1] (2016-08-18)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.4.1)

* Fix third party plugins conflict with aplazame redirect.


#### [v0.4.0] (2016-08-16)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.4.0)

* Aplazame Campaigns.


#### [v0.3.0] (2016-07-27)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.3.0)

* Many performance improvements.


#### [v0.2.0] (2016-06-20)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.2.0)

* Add new settings for customize product price and product quantity CSS selectors.
* Remove `host` and `version` settings.
* Fix API error when refund.
* Fix product amount for products with fixed prices.


#### [v0.1.0] (2016-06-13)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.1.0)

* Fix many warnings and other kind of improvements.
* Improvements for WooCommerce "Variable product"


#### [v0.0.8] (2016-03-16)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.8)

* Widget data view


#### [v0.0.7] (2016-02-17)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.7)

* Cart Widget

#### [v0.0.6] (2015-12-15)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.6)

* Fix order get_total_shipping method

#### [v0.0.5] (2015-12-15)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.5)

* Fix PHP 5.3 compatibility
* Fix shipping division by zero
* Minor improvements

#### [v0.0.4] (2015-09-25)

[view on Github](https://github.com/aplazame/woocommerce/tree/v0.0.4)

* Allow empty shipping
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

= v0.6.1 =

The latest release has passed all quality checks.
