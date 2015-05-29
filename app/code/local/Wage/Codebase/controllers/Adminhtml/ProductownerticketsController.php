<?php

class Wage_Codebase_Adminhtml_ProductownerticketsController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout();
		return $this;
	}   

	public function indexAction() {
        $this->loadLayout();
        $this->_initAction();
        $this->renderLayout();
    }


    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('codebase/adminhtml_productownertickets_grid')->toHtml()
        );
    }

	
}
