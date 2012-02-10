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
     * @copyright   Copyright (c) 2012 Jirafe Inc (http://www.jirafe.com)
     * @copyright   Copyright (c) 2012 Fooman Limited (http://www.fooman.co.nz)
     * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     */

class Fooman_Jirafe_Model_Event extends Mage_Core_Model_Abstract
{
    const JIRAFE_ACTION_ORDER_CREATE    = 'order_create';
    const JIRAFE_ACTION_ORDER_UPDATE    = 'order_update';
    const JIRAFE_ACTION_INVOICE_CREATE  = 'invoice_create';
    const JIRAFE_ACTION_INVOICE_UPDATE  = 'invoice_update';
    const JIRAFE_ACTION_SHIPMENT_CREATE = 'shipment_create';
    const JIRAFE_ACTION_REFUND_CREATE   = 'refund_create';


    protected function _construct ()
    {
        $this->_init('foomanjirafe/event');
    }

    protected function _beforeSave()
    {
        $this->setGeneratedByJirafeVersion((string) Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version);
        parent::_beforeSave();
    }

}