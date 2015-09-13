=== Aplazame ===
Contributors: calvin
Tags: aplazame,api,rest,woocommerce,ecommerce,payment,checkout,credit,aplazar,financiar,financiera,financiación,pago aplazado,método de pago
Requires at least: 4.0.1
Tested up to: 4.3
Stable tag: 4.3
License: GPLv3 or later License
URI: http://www.gnu.org/licenses/gpl-3.0.html
WC requires at least: 2.2
WC tested up to: 2.3


== Introduction ==

Aplazame is a consumer credit company, offers a payment system that can be used by online buyers to receive funding for their purchases.

The module allows the customer to defer the payment of his online purchases in ecommerces offering Aplazame as a payment method. Aplazame's mission is to increase sales of any online store eliminating any friction in the process.

Ease of implementation is the main objective we had in mind when developing Aplazame.

= l10n =

Development according to standards for i18n and l10n.

= Continuous Integration =

Automated syntax review and deployment using continuous integration tools like [drone.io](http://drone.aplazame.com/github.com/aplazame/woocommerce).

= Support =

For any support request please drop us an email at [soporte.woocommerce@aplazame.com](mailto:soporte.woocommerce@aplazame.com?subject=Help me). Our support team is available 24/7.

= Feedback =

We are open to changes in the API documentation and our services. For any suggestions please send us an email to  [dev@aplazame.com](mailto:dev@aplazame.com?subject=Hello).

*We hope you'll enjoy using Aplázame's WooCommerce plugin!*

== Install ==

= Requirements =

* WooCommerce 2.1 or higher

= Quick install =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Aplazame for WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type Aplazame for WooCommerce and click Search Plugins. Once you've found our plugin you can install it by simply clicking Install Now.

= Latest version =

1. *Download* the latest plugin from [here](https://s3.eu-central-1.amazonaws.com/aplazame/modules/woocommerce/aplazame.latest.zip) to local directory as `aplazame.latest.zip`.
2. Go to the Wordpress administration page, and then go to *Plugins* > *Add New*.
3. *Add new plugin* and select the `aplazame.latest.zip` file from your computer.

= Updating =

Automatic updates should work great for you.

= Usage =

* *Sandbox*: Determines if the module is on Sandbox mode.
* *Host*: Aplazame host `https://aplazame.com`
* *API Version*: The latest version is `v1.2`
* *Button*: The CSS Selector for Aplazame payment method. The default selector is `#payment ul li:has(input#payment_method_aplazame)`.
* *Secret API Key*: The Secret Key provided by Aplazame. You cannot share this key with anyone!!
* *Public API Key*: The Public Key provided by Aplazame.
* *Enable Analytics**: If you want to enable customer tracking for better interests.

> Be sure that on all fields you don't keep any whitespace. Otherwise the module can generate unexpected results.


== Screenshots ==

1. Configure
2. Signin
3. Pin Code
4. Summary
5. Discounts
6. oh, that's not mine!
7. Select payment method
8. Add payment method
9. Success


== Changelog ==

= [v0.0.2](https://github.com/aplazame/woocommerce/tree/v0.0.2) (2015-?-?) =

[Full Changelog v0.0.2-dev](https://github.com/aplazame/woocommerce/compare/v0.0.1...v0.0.2)

* Fix ?
* Full Readme.txt

= [v0.0.1](https://github.com/aplazame/woocommerce/tree/v0.0.1) (2015-09-12) =

* birth
