<?php
/**
 * Product Review Captcha index controller
 *
 * @category    OlegKoval
 * @package     OlegKoval_ProductReviewCaptcha
 * @copyright   Copyright (c) 2012 - 2016 Oleg Koval
 * @author      Oleg Koval <oleh.koval@gmail.com>
 */
//include controller to override it
require_once 'Mage/Review/controllers/ProductController.php';

class OlegKoval_ProductReviewCaptcha_ProductController extends Mage_Review_ProductController {
    const XML_PATH_PRC_ENABLED     = 'catalog/review/prc_enabled';
    const XML_PATH_PRC_PRIVATE_KEY = 'catalog/review/prc_private_key';

    /**
     * @see parent::preDispatch
     */
    public function preDispatch() {
        parent::preDispatch();

        return $this;
    }

    /**
     * Submit new review action
     */
    public function postAction() {
        if (Mage::getStoreConfigFlag(self::XML_PATH_PRC_ENABLED)) {
            try {
                $post = $this->getRequest()->getPost();
                if ($post) {
                    //validate captcha
                    if (!isset($post['g-recaptcha-response']) || !$this->isCaptchaValid($post['g-recaptcha-response'])) {
                        throw new Exception($this->__("The reCAPTCHA wasn't entered correctly."), 1);
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

    /**
     * Check if captcha is valid
     * @param  string $captchaResponse
     * @return boolean
     */
    private function isCaptchaValid($captchaResponse) {
        $result = false;

        $params = array(
            'secret' => Mage::getStoreConfig(self::XML_PATH_PRC_PRIVATE_KEY),
            'response' => $captchaResponse
        );

        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $requestResult = trim(curl_exec($ch));
        curl_close($ch);

        if (is_array(json_decode($requestResult, true))) {
            $response = json_decode($requestResult, true);

            if (isset($response['success']) && $response['success'] === true) {
                $result = true;
            }
        }

        return $result;
    }
}
