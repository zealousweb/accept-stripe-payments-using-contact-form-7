=== Accept Stripe Payments Using Contact Form 7 ===

Contributors: zealopensource
Tags: donation, payment, payments, stripe, online payment, Contact Form 7
Donate link: http://www.zealousweb.net/payment/
Requires at least: 4.9
Tested up to: 6.6
Requires PHP: 5.6
License: GPLv3 or later License
CF7 requires at least: 3.0
CF7 tested up to: 5.8
Stable tag: 2.3
Version: 2.3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Contact Form 7 - Integrate Stripe payment gateway for making your payments through Contact Form 7. 

== Description ==

Introducing ZealousWeb's new way to get paid online: easily accept credit card payments on your website using Stripe and Contact Form 7. With our plugin, <strong>Accept Stripe Payments Using Contact Form 7</strong>, anyone can receive payments from customers hassle-free. Just add the plugin to your website, and any Contact Form 7 becomes a safe payment spot. Customers fill out the form, and Stripe takes care of the rest, making sure it's quick and secure.

No need for hard setups or extra tools. It's all about keeping things easy and safe for businesses big and small. Give our plugin a try today and watch your revenue grow without any payment headaches for your customers.

= Features of Accept Stripe Payments Using Contact Form 7 =

* You can get paid in 25 different currencies.
* You can make various payment forms using Contact Form 7.
* It can handle input from different types of fields like dropdowns, textboxes, radio buttons, etc.
* It can take values from the website like item description, price, email, quantity, and customer info.
* You can test payments before going live.
* With the free version, you can see up to 10 payment transactions in the admin area.
* You can easily export payment data to a CSV file.
* The admin can filter and search payment data easily.
* Admins can view or delete payment data easily.
* You can use a shortcode [stripe-details] to show transaction details like ID, amount, and status.
* Both the customer and admin get emails after payment.
* You can customize the content of these emails.
* Stripe payment tag added to email content will display stripe payment response in email.
* You can set ‘Success Return URL’ and ‘Cancel Return URL’ pages to redirect after the payment transaction.

<strong>[Get more Features check our Pro version here](https://store.zealousweb.com/accept-stripe-payments-using-contact-form-7-pro)</strong>

Check out the <strong>[Pro plugin documentation link](https://store.zealousweb.com/pub/media/catalog/product/file/Documentation_-_Accept_Stripe_Payments_Using_Contact_Form_7_-_ZealousWeb_5.pdf)</strong> !here.

== Plugin Requirement ==

PHP version : 5.4 and latest
WordPress version : [WordPress](http://wordpress.org) 3.0 and latest

= Getting Help With Plugin =

If you have any difficulties while using this Plugin, please feel free to contact us at <a href="mailto:support@zealousweb.com">support@zealousweb.com</a>

We also offer custom Wordpress extension development and Wordpress theme design services to fulfill your e-commerce objectives.

Our professional Wordpress experts provide customer-oriented development of your project within short timeframes.

Thank you for choosing a Plugin developed by <strong>[ZealousWeb](https://www.zealousweb.com)</strong>!

== Installation ==

Installing the plugin is easy. Just follow these steps:

1. From the dashboard of your site, navigate to Plugins --> Add New.
2. Select the Upload option and hit "Choose File."
3. When the popup appears, select the contact-form-7-stripe-addon.zip file from your desktop.
4. Follow the on-screen instructions and wait till the upload completes.
5. When it's finished, activate the plugin via the prompt. A message will display confirming activation was successful.

That's it! Just configure your settings as you see fit, and you're on your way to creating forms with Stripe in your style. 
Are you facing problems while installation? Need help getting things started? 

== Frequently Asked Questions ==

= How to get label and value separately in mail for dropdown field? =

For dropdown field while using value and label separately example:
[select menu-696 "t1|20" "t2|30" "t3|40" "t4|50" "t5|60"],
To get the value after pipe character, put the usual mail-tag corresponding to the form-tag ([menu-696] in the mail templates. 
To get value before pipe, you can use [_raw_{field name}] Example[_raw_menu-696].

= Can I set Test mode for Stripe for testing? =

Yes, You can set Test mode from admin for testing with Test API, and after the success, you can use your stripe payment with Live Mode.

= Can I show transaction details on another page? =

Yes, you can show transaction details on the page using shortcode -[stripe-details], but that shows one time when payment is done and redirected to Thank you Page.

= Can I show transaction details on email content? =

Yes, as same as using show another page using shortcode you can use same on email content - [stripe-details]

== Screenshots ==

1. Stripe Demo Form
2. Stripe Settings and Configuration
3. Stripe Amount Field Configuration
4. Stripe Card Amount Field Configuration
4. Stripe Payments List Page
5. Stripe Transaction Detail Page

== Changelog ==

= 2.3 =
* Bug Resolved

= 2.2 =
* Improved compatibility with WordPress VIP platform by refactoring code to adhere to VIP coding standards.

= 2.1 =
* The problem with the log file on the frontend has been resolved.

= 2.0 =
* Transactions failing bug fixed.

= 1.9 =
* Uploaded missing API library files(fixed fatal error on activation), Updated Payment intent script.

= 1.8 =
* Fixed fatal error of API Requestor library.

= 1.7 =
* Updated API requestor library.

= 1.6 =
* Fixed contact form 7 Form setting for saving sandbox value for stripe.

= 1.5 =
* Updated the Stripe Library : Made use of the Payment Intents API that can handle complex payment flows.

= 1.4 =
* Fix : solved issue of showing Message on Submit

= 1.3 =
* Fix : Testing with lates CF7 5.6 and WP 6.0.1 and fix issues.

= 1.2 =
* Fix minor bugs and improve functionality

= 1.1 =
* Compatibility with latest cf7 version
* Fix minor bugs and improve functionality

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.1 =
1.0 Compatibility with latest cf7 version.

= 1.0 =
1.0 is Initial Release.
