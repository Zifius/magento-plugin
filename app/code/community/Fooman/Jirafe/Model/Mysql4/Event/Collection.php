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

<<<<<<< HEAD
// Modify tables with the new DB schema
Mage::helper('foomanjirafe/setup')->runDbSchemaUpgrade($installer, $version);

//Run sync when finished with install/update
Mage::getModel('foomanjirafe/jirafe')->initialSync($version);
=======
class Fooman_Jirafe_Model_Mysql4_Event_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('foomanjirafe/event');
    }
}
>>>>>>> parent of 2c01def... prepare 0.4.0 release with enable question
