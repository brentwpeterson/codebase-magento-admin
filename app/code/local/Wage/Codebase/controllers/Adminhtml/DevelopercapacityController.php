<?php
class Wage_Codebase_Adminhtml_DevelopercapacityController extends Mage_Adminhtml_Controller_Action {
    public function indexAction()
    {
        $this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('codebase/adminhtml_developercapacity'));
		$this->_setActiveMenu('codebase');
        $this->renderLayout();
    }
}
