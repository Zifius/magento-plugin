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
    
    static public $pageType = self::VISITOR_BROWSERS;

    /**
     * Set default template
     *
     */
    protected function _construct ()
    {
        $this->setTemplate('fooman/jirafe/js.phtml');
    }

    protected function _getSession ()
    {
        return Mage::getSingleton('customer/session');
    }

//    protected $_isCheckoutSuccess = false;
// 
//    public function setIsCheckoutSuccess ($flag)
//    {
//        $this->_isCheckoutSuccess = $flag;
//    }
//
//    public function getIsCheckoutSuccess ()
//    {
//        return $this->_isCheckoutSuccess;
//    }
//
//    public function setPiwikVisitorType ($type=null)
//    {
//        $currentType = $this->_getSession()->getPiwikVisitorType();
//        if (empty($currentType)) {
//            $this->_getSession()->setPiwikVisitorType(self::VISITOR_ALL);
//            Mage::register('piwik_visitor_type_set', true);
//        } elseif ($type > $currentType) {
//            $this->_getSession()->setPiwikVisitorType($type);
//        } elseif ($currentType == self::VISITOR_ALL && $type == self::VISITOR_ALL) {
//            //upgrade to browser on second page view
//            if (!Mage::registry('piwik_visitor_type_set')) {
//                $this->_getSession()->setPiwikVisitorType(self::VISITOR_BROWSERS);
//            }
//        }
//    }
//
//    public function getPiwikVisitorType ()
//    {
//        $currentType = $this->_getSession()->getPiwikVisitorType();
//        $this->setPiwikVisitorType(self::VISITOR_ALL);
//        return $this->_getSession()->getPiwikVisitorType();
//    }
//
//    public function getTrackingInfo ()
//    {
//        $js = "";
//        $js .= $this->_getPageTrackingInfo() . "\n";
//        //$js .= $this->_getCartTrackingInfo()."\n";
//        $js .= $this->_getPurchaseTrackingInfo() . "\n";
//        Mage::helper('foomanjirafe')->debug($js);
//        return $js;
//    }
//
//    
//    public function getAdditionalTrackingInfo ()
//    {
//        return "";
//    }
//
//    public function _getPageTrackingInfo ()
//    {
//        $js = "";
//        if ($this->getIsCheckoutSuccess()) {
//            //$js .= "_paq.push(['setCustomVariable',5, 'orderId','".$this->_getLastOrderIncrementId()."']);";
//        }
//        $js .= "_paq.push(['setCustomVariable','1','U','" . $this->getPiwikVisitorType() . "']);
//        _paq.push(['trackPageView']);";
//        return $js;
//    }
//
//    public function _getCartTrackingInfo ()
//    {
//        return "";
//    }
//
//    public function _getPurchaseTrackingInfo ()
//    {
//        $js = "";
//        if ($this->getIsCheckoutSuccess()) {
//            $items = array();
//            $quote = $this->_getLastQuote();
//            if ($quote) {
//                foreach ($quote->getAllVisibleItems() as $quoteItem) {
//                    $items[] = array('sku' => $quoteItem->getSku(), 'price' => $quoteItem->getBasePrice());
//                }
//                //$js .= "_paq.push(['trackGoal',".Mage::helper('foomanjirafe')->getStoreConfig('checkoutGoalId', Mage::app()->getStore()->getId()).",'".$quote->getBaseGrandTotal()."']);";
//            }
//        }
//        return $js;
//    }
//    
//    /**
//     * load the quote belonging to the last successful order
//     * 
//     * @return Mage_Sales_Model_Quote|bool
//     */
//    public function _getLastQuote ()
//    {
//        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
//        if ($orderIncrementId) {
//            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
//            if ($order->getId()) {
//                $quoteId = $order->getQuoteId();
//            }
//        } else {
//            $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
//        }
//
//        if ($quoteId) {
//            $quote = Mage::getModel('sales/quote')->load($quoteId);
//            if ($quote->getId()) {
//                return $quote;
//            }
//        }
//        return false;
//    }
//
//    /**
//     * load the quote belonging to the last successful order
//     *
//     * @return string|bool
//     */
//    public function _getLastOrderIncrementId ()
//    {
//        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
//        if ($orderIncrementId) {
//            return $orderIncrementId;
//        } else {
//            $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
//        }
//
//        if ($quoteId) {
//            $quote = Mage::getModel('sales/quote')->load($quoteId);
//            if ($quote->getId()) {
//                return $quote->getReservedOrderId();
//            }
//        }
//        return false;
//    }

    public function getSiteId()
    {
        return Mage::helper('foomanjirafe')->getStoreConfig('site_id', Mage::app()->getStore()->getId());
    }

    public function getBaseURL()
    {
        return Mage::getModel('foomanjirafe/jirafe')->getPiwikBaseUrl();
    }
    
    public function setJirafePageType($type)
    {
        if (strlen($type) > 1) {
            // Maybe type is a class constant name?
            $type = constant(__CLASS__.'::'.$type);
        }
        if (!empty($type) && $type > self::$pageType) {
            self::$pageType = $type;
        }
    }
    
    public function getJirafePageType()
    {
        $type = $this->_getSession()->getJirafePageType();
        if (!empty($type) && $type > self::$pageType) {
            // Override page type with session data
            self::$pageType = $type;
            // Clear session variable
            $this->_getSession()->setJirafePageType(null);
        }
        
        return self::$pageType;
    }
    
    public function getTrackingCode()
    {
        $jirafeJson = json_encode(array(
            'siteId'   => $this->getSiteId(),
            'pageType' => $this->getJirafePageType(),
            'baseUrl'  => $this->getBaseURL(),
        ));
    
        return <<<EOF
<!-- Jirafe:START -->
<script type="text/javascript">
(function(j){
    jirafe = j;
    var d = document,
        g = d.createElement('script'),
        s = d.getElementsByTagName('script')[0];
    g.type = 'text/javascript';
    g.defer = g.async = true;
    g.src = d.location.protocol + '//' + j.baseUrl + 'jirafe.js';
    s.parentNode.insertBefore(g, s);
})({$jirafeJson});
</script>
<!-- Jirafe:END -->

EOF;
    }
    
}
