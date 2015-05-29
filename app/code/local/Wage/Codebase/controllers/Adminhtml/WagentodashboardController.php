<?php
class Wage_Codebase_Adminhtml_WagentodashboardController extends Mage_Adminhtml_Controller_Action {
    public function indexAction()
    {

        $this->loadLayout();        
		$this->_addContent($this->getLayout()->createBlock('codebase/adminhtml_dashboard'));
		$this->_setActiveMenu('codebase');
        $this->renderLayout();
    }

    public function syncAction()
    {
        try{
            Mage::getModel('codebase/codebase')->getTickets();
            //Mage::getModel('codebase/codebase')->getTimetracking();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('codebase')->__('Data refreshed successfully'));
        }
        catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }
}
