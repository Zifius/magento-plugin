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

class Fooman_Jirafe_EventsController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $token = $this->getRequest()->getParam('confirmToken');
        $hash = $this->getRequest()->getParam('hash');
        $version = $this->getRequest()->getParam('version');
        $siteId = $this->getRequest()->getParam('siteId');

	$response = $this->getResponse();

        if($token && $hash && $siteId) {
            $jirafe = Mage::getModel('foomanjirafe/jirafe');
            if($jirafe->checkEventsToken($token, $hash)) {
                $jirafe->postEvents($token, $siteId, $version+1);
                $response->setBody('OK');
            } else {
                $response->setHttpResponseCode(400);
                $response->setBody('KO');
            }
        } else {
            $response->setHttpResponseCode(400);
            $response->setBody('KO');
        }
    }

}
