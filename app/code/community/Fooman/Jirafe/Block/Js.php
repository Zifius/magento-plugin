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
    
    public $pageType = self::VISITOR_BROWSERS;

    /**
     * Set default template
     *
     */
    protected function _construct()
    {
        $this->setTemplate('fooman/jirafe/js.phtml');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

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
        if (!empty($type) && $type > $this->pageType) {
            $this->pageType = $type;
        }
    }
    
    public function getJirafePageType()
    {
        $type = $this->_getSession()->getJirafePageType();
        if (!empty($type) && $type > $this->pageType) {
            // Override page type with session data
            $this->pageType = $type;
            // Clear session variable
            $this->_getSession()->setJirafePageType(null);
        }
        
        return $this->pageType;
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
