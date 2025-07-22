Changelog for Stripe Connector
===

3.4.2
---

* _ADDED_ User Guide inside the module
* _ADDED_ Refactor content
* _FIXED_ PrestaShop hooks for older version
* _FIXED_ Duplicate address saving
* _FIXED_ Order confirmation by webhook


3.4.1
---

* _ADDED_ Checkbox in configuration for Save payment method at customer level
* _ADDED_ Translations text
* _FIXED_ Currency conversion amount when Adaptive prices is enabled
* _FIXED_ Out of stock when attribute or quantity changed
* _FIXED_ Tax calculation for express checkout for shipping and product

3.4.0
---

* _ADDED_ Create order options for legacy and new flow
* _ADDED_ Save payment methods for later use
* _FIXED_ Wrong amount on express checkout when using promo codes
* _FIXED_ Express checkout buttons when product is out of stock
* _FIXED_ Get symbol bug on PrestaShop 1.7.4

3.3.3
---

* _ADDED_ Amount Refunded on the Order page when status is Partial Refund
* _FIXED_ Fix no shipping rates provided for express checkout element initially(no carriers available/active)
* _FIXED_ Fix shipping rate amount float value e.g. (490.0000006)
* _FIXED_ Fix total amount parsing when the decimal separator is a ","
* _FIXED_ Fix total amount on initial express checkout modal when there is a paid carrier as first option
* _FIXED_ Amount for products that have multiple attributes
* _FIXED_ Partial refund

3.3.2
---

* _FIXED_ integrated payment form it's not loaded
* _FIXED_ webhook endpoint when is used language or locale

3.3.1
---

* _ADDED_ Order ID in description
* _ADDED_ Transaction ID in orders page
* _FIXED_ error when configurations are saved
* _FIXED_ error when having over 10 carriers
* _FIXED_ error when using Express Checkout and choosing a different carrier instead of than default one
* _REMOVED_ redundant payment date from the order detail page when using separate authentication and capture with Express Checkout

3.3.0
---

* _ADDED_ Express Checkout functionality

  3.2.4
---

* _FIXED_ Redirect to payment methods when using Link and 3DS
* _FIXED_ Add translations checks on the templates 

3.2.3
---

* _FIXED_ Create custom status: Awaiting for payment confirmation
* _FIXED_ Error on capture triggered by multiple order statuses

3.2.2
---

* _FIXED_ Compatibility with other PrestaShop payment methods

3.2.1
---

* _FIXED_ Unable to upgrade to 3.2.0 because of SQL

3.2.0
---

* _ADDED_ Implement new translation system
* _ADDED_ Use PrestaShop native table for logs
* _ADDED_ Implement new UI
* _CHANGED_ Order creation and webhook handler rewrite
* _CHANGED_ Use one webhook for multistore

3.1.4
---

* _ADDED_ - Pin API Version
* _FIXED_ - Remove Sofort references
* _FIXED_ - Fixed Link payment with 3DS
* _FIXED_ - Fixed publishableKey check
* _CHANGED_ Format logs and exceptions

3.1.3
---

* _ADDED_ - Remove redundant error messages
* _FIXED_ - Initialize Stripe library on selecting payment method
* _CHANGED_ Text changes

3.1.2
---

* _CHANGED_ Text changes
* _FIXED_ - Error on confirming payment intents

3.1.1
---

* _FIXED_ - Idempotency error on customer creation
* _FIXED_ - Moment of order creation
* _FIXED_ - Synchronization for statuses for some payment methods: sepa_debit and sofort
* _ADDED_ - Support for new status PENDING
* _FIXED_ - Charge object retrieval for new Stripe api version

3.1.0
---

* _FIXED_ - Duplicate payments
* _FIXED_ - Order creation and update
* _ADDED_ - Streamlined payment flows
* _FIXED_ - Reduced JS dependencies
* _FIXED_ - Multishop with one Stripe account
* _FIXED_ - Multishop with multiple Stripe accounts
* _FIXED_ - Idempotent requests
* _ADDED_ - Pass default values to payment elements
* _FIXED_ - Webhook response codes

3.0.5
---

* _FIXED_ - Security audit issues


3.0.4
---

* _FIXED_ - Payment error when you change from payment element to checkout flow mid payment
* _ADDED_ - Missing translations for de, es, fr, it
* _FIXED_ - Translations for checkout flow
* _FIXED_ - JS errors on checkout page
* _FIXED_ - Saving api keys when switching between live and test mode
* _FIXED_ - Saving webhook configs when switching between live and test mode


3.0.3
---

* _FIXED_ Payment is not showing an order in Prestashop
* _FIXED_ Duplicate payments in Stripe Dashboard
* _FIXED_ Checkout Session back button error
* _FIXED_ Add Order ID to Description in Stripe Payments
* _FIXED_ Make disabled Place Order button after one click
* _CHANGED_ Version

3.0.2
---

* _FIXED_ amount not calculated correctly on checkout
* _FIXED_ javascript interferrence on older PS versions
* _FIXED_ json decoding information for payment intent
* _FIXED_ payment element initialization on older PS versions
* _FIXED_ logger errors on Stripe Customer
* _FIXED_ incompatibility issues with PS version newer than 8.0.0
* _FIXED_ specific compatibility issues for PS version 8.0.3
* _FIXED_ user not able to save live keys
* _CHANGED_ Version

3.0.1
---

* _FIXED_ Plugin interacting and breaking css on checkout
* _FIXED_ Plugin interacting and breaking other plugins/payment methods
* _CHANGED_ Version

3.0.0
---

* _FIXED_ Remove beta flags
* _CHANGED_ Version

3.0.0-RC2
---

* _FIXED_ Billing Details are not used to render Payment Elements
* _FIXED_ When using Stripe Checkout, the total amount does not include VAT
* _FIXED_ Send billing details when rendering payment elements
* _FIXED_ "Do not collect zip code" is working now
* _FIXED_ Checkout and Payment Element are displayed in the wrong locale
* _FIXED_ Refund page problem, where we got a blank page instead of an error
* _FIXED_ Full/Partial Refund status syncing
* _FIXED_ Missing images for payment methods
* _FIXED_ Caching issue
* _FIXED_ Rename the "Payment form settings" option
* _FIXED_ SEPA doesn't capture automatically
* _FIXED_ Text and asterisk corrections on Prestashop backoffice
* _FIXED_ Cancel Payment Sync between Stripe dashboard and Prestashop backoffice
* _FIXED_ Checkout error on successful transactions
* _FIXED_ Payment Error status on Prestashop backoffice
* _FIXED_ Remove shipping and product details and only send total amount with checkout
* _FIXED_ Do not filter payment methods when separate auth and capture is activated
* _FIXED_ 3D Secure 'Cancel' button redirect to correct page
* _ADDED_ Style Payment Element from back office

3.0.0-RC1
---

* _ADDED_ Billing and delivery details sent when creating a checkout session
* _ADDED_ Decouple Payment Intent creation and Payment Element rendering
* _ADDED_ Fraud and payment data with OMS sync
* _FIXED_ Capture option in backoffice			
* _FIXED_ Remove 'Save customer cards' option	
* _ADDED_ Radio button for both Payment Elements and Stripe Checkout		
* _FIXED_ Remove "Collect client name" option in connector options	
* _FIXED_ Customers are not created in stripe dashboard	
* _FIXED_ Remove the option for Google Pay and Apple Pay from the backoffice
* _FIXED_ Fix the current refund implementation	
* _FIXED_ Removing support from PrestaShop 1.6		
* _ADDED_ Payments with Payment Element	
* _ADDED_ Payments with Stripe Checkout	
* _ADDED_ Simultaneous auth and capture with OMS sync		
* _ADDED_ Separate auth and capture with OMS sync
