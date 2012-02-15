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
    //TODO: these could move into the php-client
    const JIRAFE_ACTION_ORDER_CREATE    = 'orderCreate';
    const JIRAFE_ACTION_ORDER_UPDATE    = 'orderUpdate';
    const JIRAFE_ACTION_INVOICE_CREATE  = 'invoiceCreate';
    const JIRAFE_ACTION_INVOICE_UPDATE  = 'invoiceUpdate';
    const JIRAFE_ACTION_SHIPMENT_CREATE = 'shipmentCreate';
    const JIRAFE_ACTION_REFUND_CREATE   = 'refundCreate';

    const JIRAFE_ORDER_STATUS_NEW               = 'new';
    const JIRAFE_ORDER_STATUS_PAYMENT_PENDING   = 'pendingPayment';
    const JIRAFE_ORDER_STATUS_PROCESSING        = 'processing';
    const JIRAFE_ORDER_STATUS_COMPLETE          = 'complete';
    const JIRAFE_ORDER_STATUS_CLOSED            = 'closed';
    const JIRAFE_ORDER_STATUS_CANCELLED         = 'canceled';
    const JIRAFE_ORDER_STATUS_HELD              = 'holded';
    const JIRAFE_ORDER_STATUS_PAYMENT_REVIEW    = 'paymentReview';

    protected $_eventPrefix = 'foomanjirafe_event';
    protected $_eventObject = 'jirafeevent';

    protected function _construct ()
    {
        $this->_init('foomanjirafe/event');
    }

    protected function _beforeSave()
    {
        $lastEventNumberForSite = $this->_getLastVersionNumber($this->getSiteId());
        $this->setVersion($lastEventNumberForSite + 1);
        $this->setGeneratedByJirafeVersion((string) Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version);
        parent::_beforeSave();
    }

    /**
     * there is no afterCommitCallback on earlier
     * versions, use the closest alternative
     * @see afterCommitCallback
     */
    protected function _afterSave()
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->afterCommitCallback();
        }
    }

    public function afterCommitCallback()
    {
        //ping Jirafe
        return parent::afterCommitCallback();
    }

    protected function _getLastVersionNumber($siteId)
    {
        return Mage::getResourceModel('foomanjirafe/event')->getLastVersionNumber($siteId);
    }

    public function orderCreateOrUpdate($order)
    {
        if ($order->getJirafeIsNew()) {
            $this->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_ORDER_CREATE);
            $eventData = array (
                'orderId'           => $order->getIncrementId(),
                'status'            => $this->_getOrderStatus($order),
                'customerId'        => md5(strtolower(trim($order->getCustomerEmail()))),
                'visitorId'         => $order->getJirafeVisitorId(),
                'time'              => strtotime($order->getCreatedAt()),
                'grandTotal'        => $order->getBaseGrandTotal(),
                'subTotal'          => $order->getBaseSubtotal(),
                'taxAmount'         => $order->getBaseTaxAmount(),
                'shippingAmount'    => $order->getBaseShippingAmount(),
                'discountAmount'    => $order->getBaseDiscountAmount(),
                'isBackendOrder'    => !$order->getJirafePlacedFromFrontend(),
                'currency'          => $order->getBaseCurrencyCode(),
                'items'             => $this->_getItems($order)
            );
            $order->unsJirafeIsNew();
        } else {
            //TODO: work out why we have order_create AND order_update for a new order
            //and if we can simply filter out by $order->getState() != Mage_Sales_Model_Order::STATE_NEW
            $this->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_ORDER_UPDATE);
            $eventData = array (
                'orderId'   =>$order->getIncrementId(),
                'status'    =>$this->_getOrderStatus($order)
            );
        }
        $this->setSiteId(Mage::helper('foomanjirafe')->getStoreConfig('site_id', $order->getStoreId()));
        $this->setEventData(json_encode($eventData));
        $this->save();
    }

    protected function _getItems($salesObject)
    {
        $returnArray = array();
        $isOrder = ($salesObject instanceof Mage_Sales_Model_Order);
        foreach ($salesObject->getAllItems() as $item)
        {
            if (!$item->getParentItemId()) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                $returnArray[] = array(
                    'sku' => $product->getSku(),
                    'name' => $item->getName(),
                    'category' => Mage::helper('foomanjirafe')->getCategory($product),
                    'price' => $item->getBasePrice(),
                    'qty' => $isOrder ? $item->getQtyOrdered() : $item->getQty()
                );
            }
        }
        return $returnArray;
    }

    protected function _getOrderStatus($order)
    {
        switch ($order->getState()) {
            case Mage_Sales_Model_Order::STATE_NEW:
                return self::JIRAFE_ORDER_STATUS_NEW;
                break;
            case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
                return self::JIRAFE_ORDER_STATUS_PAYMENT_PENDING;
                break;
            case Mage_Sales_Model_Order::STATE_PROCESSING:
                return self::JIRAFE_ORDER_STATUS_PROCESSING;
                break;
            case Mage_Sales_Model_Order::STATE_COMPLETE:
                return self::JIRAFE_ORDER_STATUS_COMPLETE;
                break;
            case Mage_Sales_Model_Order::STATE_CLOSED:
                return self::JIRAFE_ORDER_STATUS_CLOSED;
                break;
            case Mage_Sales_Model_Order::STATE_CANCELED:
                return self::JIRAFE_ORDER_STATUS_CANCELLED;
                break;
            case Mage_Sales_Model_Order::STATE_HOLDED:
                return self::JIRAFE_ORDER_STATUS_HELD;
                break;
            case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
                return self::JIRAFE_ORDER_STATUS_PAYMENT_REVIEW;
                break;
        }
    }

}