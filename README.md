Product Review Captcha
=====================

"Product Review Captcha" extension add in easy way the captcha to "Product Reviews" form and will protect this form from unwanted spambots.

This extension uses reCaptcha library (http://www.google.com/recaptcha).

## EXTENSION on MagentoConnect
https://www.magentocommerce.com/magento-connect/product-review-captcha-1.html

## INSTRUCTION
* Sign up for a reCAPTCHA account on http://www.google.com/recaptcha
* Open configuration page of "Product Review Captcha": [Top menu of Magento Store Admin Panel] System -> Configuration -> [select tab] Catalog -> [expand section] Product Reviews
* Enable extension: "Enable Captcha" set to "Yes"
* Enter the public and private API keys from reCAPTCHA in "Public Key"/"Private Key" fields
* [Optional] Select name of theme of reCAPTCHA widget (theme "Red" is default)
* [Optional] Select language which use for reCAPTCHA widget (language "English" is default)
* Save Config

## CUSTOM DESIGN
* If you have a custom design, you will need to update the corresponding "Product Review Captcha" file:
    app/design/frontend/base/default/template/productreviewcaptcha/form.phtml
