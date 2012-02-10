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

class Fooman_Jirafe_Model_OrderObserver extends Fooman_Jirafe_Model_Observer
{

    /**
     * salesOrderSaveCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     *
     * @see salesOrderSaveCommitAfter
     * @param type $observer
     */
    public function salesOrderSaveAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->salesOrderSaveCommitAfter($observer);
        }
    }

    /**
     * Save this event to the Event Table for later synchronisation with Jirafe
     *
     * @param $observer
     */
    public function salesOrderSaveCommitAfter ($observer)
    {
        Mage::helper('foomanjirafe')->debug('salesOrderSaveCommitAfter');
        $order = $observer->getOrder();
        $event = Mage::getModel('foomanjirafe/event');
        if ($order->getJirafeIsNew()) {
            $event->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_ORDER_CREATE);
            $eventData = array (
                'order_id'          => $order->getIncrementId(),
                'status'            => $order->getState(),
                'customer_id'       => md5(strtolower(trim($order->getCustomerEmail()))),
                'visitor_id'        => $order->getJirafeVisitorId(),
                'attribution_data'  => $order->getJirafeAttributionData(),
                'date'              => $order->getCreatedAt(),
                'grand_total'       => $order->getBaseGrandTotal(),
                'subtotal'          => $order->getBaseSubtotal(),
                'tax_amount'        => $order->getBaseTaxAmount(),
                'shipping_amount'   => $order->getBaseShippingAmount(),
                'discount_amount'   => $order->getBaseDiscountAmount(),
                'is_backend_order'  => !$order->getJirafePlacedFromFrontend(),
                'currency'          => $order->getBaseCurrencyCode(),
                'items'             => $this->getItems($order)

            );
        } else {
            $event->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_ORDER_UPDATE);
            $eventData = array (
                'order_id'=>$order->getIncrementId(),
                'new_status'=>$order->getState()
            );
        }
        $event->setEventData(json_encode($eventData));
        $event->save();


        //track only orders that are just being converted from a quote
        /*if($order->getJirafeIsNew()) {
            $piwikTracker = $this->_initPiwikTracker($order->getStoreId());
            $piwikTracker->setCustomVariable(1, 'U', Fooman_Jirafe_Block_Js::VISITOR_CUSTOMER);
            $piwikTracker->setCustomVariable(5, 'orderId', $order->getIncrementId());
            $piwikTracker->setIp($order->getRemoteIp());

            // this observer can be potentially be triggered via a backend action
            // it is safer to set the visitor id from the order (if available)
            if ($order->getJirafeVisitorId()) {
                $piwikTracker->setVisitorId($order->getJirafeVisitorId());
            }

            if($piwikTracker->getVisitorId()){
                if ($order->getJirafeAttributionData()) {
                    $piwikTracker->setAttributionInfo($order->getJirafeAttributionData());
                }

                try {
                    Mage::helper('foomanjirafe')->debug($order->getIncrementId().': '.$order->getJirafeVisitorId().' '.$order->getBaseGrandTotal());
                    $checkoutGoalId = Mage::helper('foomanjirafe')->getStoreConfig('checkout_goal_id', $order->getStoreId());

                    $this->_addEcommerceItems($piwikTracker, Mage::getModel('sales/quote')->load($order->getQuoteId()));
                    $piwikTracker->doTrackEcommerceOrder(
                        $order->getIncrementId(),
                        $order->getBaseGrandTotal(),
                        $order->getBaseSubtotal(),
                        $order->getBaseTaxAmount(),
                        $order->getBaseShippingAmount(),
                        $order->getBaseDiscountAmount()
                    );
                    $order->unsJirafeIsNew();

                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }*/
    }

    protected function getItems($salesObject)
    {
        $returnArray = array();
        foreach ($salesObject->getAllVisibleItems() as $item)
        {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $returnArray[] = array(
                'sku'=> $product->getSku(),
                'name'=> $item->getName(),
                'category'=>$this->_getCategory($product),
                'price'=> $item->getBasePrice(),
                'qty'=> $item->getQty()
            );
        }
        return $returnArray;
    }
}
