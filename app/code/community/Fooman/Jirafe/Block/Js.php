<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_Block_Js extends Mage_Core_Block_Template
{

    const VISITOR_ALL       = 'A';
    const VISITOR_BROWSERS  = 'B';
    const VISITOR_ENGAGED   = 'C';
    const VISITOR_READY2BUY = 'D';
    const VISITOR_CUSTOMER  = 'E';
    
    /**
     * Set default template
     *
     */
    protected function _construct ()
    {
        $this->setTemplate('fooman/jirafe/js.phtml');
    }

    function getTrackingCode()
    {
        if (Mage::helper('foomanjirafe')->isConfigured()) {
            $jfUrl = $this->getPiwikBaseURL(false);
            $jfUrlSsl = $this->getPiwikBaseURL(true);
            $siteId = Mage::helper('foomanjirafe')->getStoreConfig('site_id', Mage::app()->getStore()->getId());

            $pageType = $this->_getSession()->getJirafePageType();
            switch ($pageType) {
                case 'E' : $additionalJs = $this->_getAdditionalJsE(); break;
                case 'D' : $additionalJs = $this->_getAdditionalJsD(); break;
                case 'C' : $additionalJs = $this->_getAdditionalJsC(); break;
                default  : $additionalJs = '';
            }

            $js = "
<!-- Jirafe -->
<script type='text/javascript'>
var _paq = _paq || [];
(function(){
    var u=(('https:' == document.location.protocol) ? '{$jfUrl}' : '{$jfUrlSsl}');
    var s='visit';
    _paq.push(['setSiteId', {$siteId}]);
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['enableLinkTracking']);
    _paq.push([ function() {
        var jf_v = '{$pageType}';
        var jf_u = this.getCustomVariable(1, s);
        alert(jf_u);
        jf_u = jf_u ? jf_u[1] : 'A';
        var jf_n = this.getCustomVariable(5, s);
        alert(jf_n);
        jf_n = jf_n ? (parseInt(jf_n[1]) + 1) : 1;
        if (jf_n > 1) {
            jf_u = jf_v > jf_u ? jf_v : jf_u;
        } else {
            jf_u = 'A';
        }
        alert ('jf_v=' + jf_v + ', jf_u=' + jf_u + ', jf_n=' + jf_n);
        this.setCustomVariable(1,'U',jf_u,s);
        this.setCustomVariable(5,'N',String(jf_n), s);{$additionalJs}
    }]);
    _paq.push(['trackPageView']);
    
    var d=document,
        g=d.createElement('script'),
        s=d.getElementsByTagName('script')[0];
        g.type='text/javascript';
        g.defer=true;
        g.async=true;
        g.src=u+'piwik.js';
        s.parentNode.insertBefore(g,s);
})();
</script>
<!-- End Jirafe Code -->";
        }
        
        return $js;
    }
    
    function _getAdditionalJsC()
    {
        $js = '';
        $product = Mage::registry('product');
        if ($product) {
            $sku = addslashes($product->getSku());
            $name = addslashes($product->getName());
            $category = $product->getCategoryIds();
            $price = $product->getPrice();
            $js .= "
        this.setEcommerceView('{$sku}','{$name}', '');";  // Should add category, and price when it is supported by Piwik
        }
        
        return $js;
    }
    
    function _getAdditionalJsD()
    {
        return '';
    }
    
    function _getAdditionalJsE()
    {
        $js = '';
        $quote = $this->_getLastQuote();
        if ($quote) {
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $sku = $quoteItem->getSku();
                $name = $quoteItem->getName();
                $category = $quoteItem->getCategoryIds();
                $price = $quoteItem->getBasePrice();
                $quantity = $quoteItem->getQuantity();
                $js .= "
        this.addEcommerceItem('{$sku}', '{$name}', '{$category}', '{$price}', '{$quantity}');";
            }
        }
        
        return $js;
    }

    public function setJirafePageType ($type=null)
    {
        $this->_getSession()->setJirafePageType($type);
    }



    protected $_isCheckoutSuccess = false;

    protected function _getSession ()
    {
        return Mage::getSingleton('customer/session');
    }

    public function setIsCheckoutSuccess ($flag)
    {
        $this->_isCheckoutSuccess = $flag;
    }

    public function getIsCheckoutSuccess ()
    {
        return $this->_isCheckoutSuccess;
    }

    public function getPiwikVisitorType ()
    {
        $currentType = $this->_getSession()->getPiwikVisitorType();
        $this->setPiwikVisitorType(self::VISITOR_ALL);
        return $this->_getSession()->getPiwikVisitorType();
    }

    public function getTrackingInfo ()
    {
        $js = "";
        $js .= $this->_getPageTrackingInfo() . "\n";
        //$js .= $this->_getCartTrackingInfo()."\n";
        $js .= $this->_getPurchaseTrackingInfo() . "\n";
        Mage::helper('foomanjirafe')->debug($js);
        return $js;
    }

    /**
     * load the quote belonging to the last successful order
     * 
     * @return Mage_Sales_Model_Quote|bool
     */
    public function _getLastQuote()
    {
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        if ($orderIncrementId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            if ($order->getId()) {
                $quoteId = $order->getQuoteId();
            }
        } else {
            $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
        }

        if ($quoteId) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote->getId()) {
                return $quote;
            }
        }
        return false;
    }

    /**
     * load the quote belonging to the last successful order
     *
     * @return string|bool
     */
    public function _getLastOrderIncrementId ()
    {
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        if ($orderIncrementId) {
            return $orderIncrementId;
        } else {
            $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
        }

        if ($quoteId) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote->getId()) {
                return $quote->getReservedOrderId();
            }
        }
        return false;
    }

    public function getSiteId ()
    {
        return Mage::helper('foomanjirafe')->getStoreConfig('site_id', Mage::app()->getStore()->getId());
    }

    public function getPiwikBaseURL ($secure = false)
    {
        $protocol = $secure ? "https://" : "http://";
        return $protocol . Mage::getModel('foomanjirafe/jirafe')->getPiwikBaseUrl();
    }

}
