<?php
/**
 * Product Review Captcha index controller
 *
 * @category    OlegKoval
 * @package     OlegKoval_ProductReviewCaptcha
 * @copyright   Copyright (c) 2012 Oleg Koval
 * @author      Oleg Koval <oleh.koval@gmail.com>
 */
//include controller to override it
require_once 'Mage/Review/controllers/ProductController.php';

class OlegKoval_ProductReviewCaptcha_ProductController extends Mage_Review_ProductController {
    const XML_PATH_PRC_ENABLED     = 'catalog/review/prc_enabled';
    const XML_PATH_PRC_PRIVATE_KEY = 'catalog/review/prc_private_key';

    /**
     * Call parent preDispatch() method
     * 
     */
    public function preDispatch() {
        parent::preDispatch();

        return $this;
    }

    /**
     * Submit new review action
     *
     */
    public function postAction() {
        if (Mage::getStoreConfigFlag(self::XML_PATH_PRC_ENABLED)) {
            try {
                $post = $this->getRequest()->getPost();
                if ($post) {
                    //include reCaptcha library
                    require_once(Mage::getModuleDir('', 'OlegKoval_ProductReviewCaptcha') . DS .'Helper'. DS .'recaptchalib.php');
                    
                    //validate captcha
                    $privatekey = Mage::getStoreConfig(self::XML_PATH_PRC_PRIVATE_KEY);
                    $remote_addr = $this->getRequest()->getServer('REMOTE_ADDR');
                    $captcha = recaptcha_check_answer($privatekey, $remote_addr, $post["recaptcha_challenge_field"], $post["recaptcha_response_field"]);


                    if (!$captcha->is_valid) {
                        throw new Exception("The reCAPTCHA wasn't entered correctly.", 1);
                    }
                }
                else {
                    throw new Exception('', 1);
                }
            }
            catch (Exception $e) {
                if (strlen($e->getMessage()) > 0) {
                    Mage::getSingleton('core/session')->addError($this->__($e->getMessage()));
                    Mage::getSingleton('core/session')->setFormData($post);
                }
                if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
                    $this->_redirectUrl($redirectUrl);
                    return;
                }
                $this->_redirectReferer();
                return;
            }
        }

        //everything is OK - call parent action
        parent::postAction();
    }

}
