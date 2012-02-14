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

class Fooman_Jirafe_JirafeController extends Mage_Core_Controller_Front_Action
{

    public function eventsAction()
    {
        $token = $this->getRequest()->getParam('token');
        $version = $this->getRequest()->getParam('v');
        $siteId = $this->getRequest()->getParam('siteId');
        //TODO: check token against Jirafe

        //TODO: post events
        $jirafeEvents = Mage::getModel('foomanjirafe/event')
            ->getCollection()
            ->addFieldToFilter('site_id', $siteId)
            ->addFieldToFilter('version', array('gteq'=>$version));
        foreach($jirafeEvents as $event) {
            var_dump($event->getData());
        }

    }

}