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

    public function afterCommitCallback()
    {
        //ping Jirafe
        return parent::afterCommitCallback();
    }

    protected function _afterSave()
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            //ping Jirafe
        }
        parent::_afterSaveCommit();
    }

    public function orderCreateOrUpdate($order)
    {
        if ($order->getJirafeIsNew()) {
            $this->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_ORDER_CREATE);
            $eventData = array (
                'order_id'          => $order->getIncrementId(),
                'status'            => $order->getState(),
                'customer_id'       => md5(strtolower(trim($order->getCustomerEmail()))),
                'visitor_id'        => $order->getJirafeVisitorId(),
                'attribution_data'  => $order->getJirafeAttributionData(),
                'order_time'        => strtotime($order->getCreatedAt()),
                'grand_total'       => $order->getBaseGrandTotal(),
                'subtotal'          => $order->getBaseSubtotal(),
                'tax_amount'        => $order->getBaseTaxAmount(),
                'shipping_amount'   => $order->getBaseShippingAmount(),
                'discount_amount'   => $order->getBaseDiscountAmount(),
                'is_backend_order'  => !$order->getJirafePlacedFromFrontend(),
                'currency'          => $order->getBaseCurrencyCode(),
                'items'             => $this->_getItems($order)
            );
            $order->unsJirafeIsNew();
        } else {
            //TODO: work out why we have order_create AND order_update for a new order
            //and if we can simply filter out by $order->getState() != Mage_Sales_Model_Order::STATE_NEW
            $this->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_ORDER_UPDATE);
            $eventData = array (
                'order_id'=>$order->getIncrementId(),
                'new_status'=>$order->getState()
            );
        }
        $this->setEventData(json_encode($eventData));
        $this->save();
    }

    protected function _getItems($salesObject)
    {
        $returnArray = array();
        $isOrder = ($salesObject instanceof Mage_Sales_Model_Order);
        foreach ($salesObject->getItems() as $item)
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

}