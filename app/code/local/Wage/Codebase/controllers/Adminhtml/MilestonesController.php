<?php
class Wage_Codebase_Adminhtml_MilestonesController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction()

    {
        $this->loadLayout()
            ->_setActiveMenu('codebase/milestones');
        return $this;
    }
    public function indexAction() {
        $this->loadLayout();
        $this->_initAction();
        $this->renderLayout();
    }
    public function exportCsvAction()
    {
        $fileName   = 'milestones.csv';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_milestones_grid')
            ->getCsv();
 
        $this->_sendUploadResponse($fileName, $content);
    }
 
    public function exportXmlAction()
    {
        $fileName   = 'milestones.xml';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_milestones_grid')
            ->getXml();
 
        $this->_sendUploadResponse($fileName, $content);
    }

   

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('codebase/adminhtml_milestones_grid')->toHtml()
        );
    }

   public function refreshAction()
    {
        //$tickets = Mage::getModel('codebase/codebase')->getStatuses();
	try{
		$milestones = Mage::getModel('codebase/codebase')->getMilestones();
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('codebase')->__('Milestones has been refreshed successfully'));
		$this->_redirect('*/*/');
	} catch (Exception $e) {
		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		$this->_redirect('*/*/');
	}
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}
