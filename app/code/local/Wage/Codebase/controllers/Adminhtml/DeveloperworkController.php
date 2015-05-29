<?php
class Wage_Codebase_Adminhtml_DeveloperworkController extends Mage_Adminhtml_Controller_Action {
    public function indexAction()
    {
        $this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('codebase/adminhtml_developerwork'));
		$this->_setActiveMenu('codebase');
        $this->renderLayout();
    }
}
