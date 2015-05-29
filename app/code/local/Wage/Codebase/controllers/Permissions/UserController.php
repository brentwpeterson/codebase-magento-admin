<?php

require_once 'Mage/Adminhtml/controllers/Permissions/UserController.php';

class Wage_Codebase_Permissions_UserController extends Mage_Adminhtml_Permissions_UserController 
{

    public function updateStatusAction()
    {
        $userIds = $this->getRequest()->getParam('ids');
        $is_active   = (int)$this->getRequest()->getParam('is_active');


        try {
	        foreach ($userIds as $ids) {
	        	$admin = Mage::getModel('admin/user')->load($ids);
			    $admin->setIsActive($is_active);
				$admin->save();

			}
		} catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The user details has been updated'));
       	$this->_redirect('*/*/');
        return;
    }


    public function updateWagentoStaffAction()
    {
        $userIds = $this->getRequest()->getParam('ids');
        $wagento_staff = (int)$this->getRequest()->getParam('wagento_staff');

		try {
	        foreach ($userIds as $ids) {
	        	$admin = Mage::getModel('admin/user')->load($ids);
			    $admin->setWagentoStaff($wagento_staff);
				$admin->save();
			}
		} catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The user details has been updated'));
        $this->_redirect('*/*/');
        return;
    }

}
