<?php
/**
 * Product Review Captcha form Block
 * Overrided this block to set custom review form (form with reCAPTCHA widget)
 *
 * @category    OlegKoval
 * @package     OlegKoval_ProductReviewCaptcha
 * @copyright   Copyright (c) 2012 Oleg Koval
 * @author      Oleg Koval <oleh.koval@gmail.com>
 */

class OlegKoval_ProductReviewCaptcha_Block_Form extends Mage_Review_Block_Form {
    const XML_PATH_PRC_ENABLED     = 'catalog/review/prc_enabled';
    const XML_PATH_PRC_PUBLIC_KEY  = 'catalog/review/prc_public_key';
    const XML_PATH_PRC_PRIVATE_KEY = 'catalog/review/prc_private_key';
    const XML_PATH_PRC_THEME       = 'catalog/review/prc_theme';
    const XML_PATH_PRC_LANG        = 'catalog/review/prc_lang';
    const XML_PATH_PRC_SSL         = 'catalog/review/prc_ssl';

    /**
     * Constructor of this class which set template of review form
     */
    public function __construct() {
        $customerSession = Mage::getSingleton('customer/session');

        Mage_Core_Block_Template::__construct();

        $data =  Mage::getSingleton('review/session')->getFormData(true);

        //maybe we do not have form data - so we try another session
        if ($data == null) {
            $data = Mage::getSingleton('core/session')->getFormData(true);
        }
        
        $data = new Varien_Object($data);

        // add logged in customer name as nickname
        if (!$data->getNickname()) {
            $customer = $customerSession->getCustomer();
            if ($customer && $customer->getId()) {
                $data->setNickname($customer->getFirstname());
            }
        }

        $this->setAllowWriteReviewFlag($customerSession->isLoggedIn() || Mage::helper('review')->getIsGuestAllowToWrite());
        if (!$this->getAllowWriteReviewFlag) {
            $this->setLoginLink(
                Mage::getUrl('customer/account/login/', array(
                    Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME => Mage::helper('core')->urlEncode(
                        Mage::getUrl('*/*/*', array('_current' => true)) .
                        '#review-form')
                    )
                )
            );
        }

        //if "Product Review Captcha" module is enabled - then we display template with reCAPTCHA
        if (Mage::getStoreConfigFlag(self::XML_PATH_PRC_ENABLED)) {
            //include reCaptcha library
            require_once(Mage::getModuleDir('', 'OlegKoval_ProductReviewCaptcha') . DS .'Helper'. DS .'recaptchalib.php');
            
            //create captcha html-code
            $publickey = Mage::getStoreConfig(self::XML_PATH_PRC_PUBLIC_KEY);
            $ssl_option = Mage::getStoreConfig(self::XML_PATH_PRC_SSL);
            $captcha_code = '';
            if ($ssl_option == 1) {
            	$captcha_code = recaptcha_get_html($publickey, null, true);
            }
            else {
            	$captcha_code = recaptcha_get_html($publickey, null, false);
            }

            //get reCaptcha theme name
            $theme = Mage::getStoreConfig(self::XML_PATH_PRC_THEME);
            if (strlen($theme) == 0 || !in_array($theme, array('red', 'white', 'blackglass', 'clean'))) {
                $theme = 'red';
            }

            //get reCaptcha lang name
            $lang = Mage::getStoreConfig(self::XML_PATH_PRC_LANG);
            if (strlen($lang) == 0 || !in_array($lang, array('en', 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr'))) {
                $lang = 'en';
            }

            //small hack for language feature - because it's not working as described in documentation
            $captcha_code = str_replace('?k=', '?hl='. $lang .'&amp;k=', $captcha_code);

            $this->setTemplate('productreviewcaptcha/form.phtml')
                ->assign('data', $data)
                ->assign('messages', Mage::getSingleton('review/session')->getMessages(true))
                ->setCaptchaCode($captcha_code)
                ->setCaptchaTheme($theme)
                ->setCaptchaLang($lang);
        }
        //otherwise use standard form
        else {
            $this->setTemplate('review/form.phtml')
                ->assign('data', $data)
                ->assign('messages', Mage::getSingleton('review/session')->getMessages(true));
        }
    }
}
