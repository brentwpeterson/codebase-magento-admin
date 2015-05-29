<?php
class Wage_Codebase_Adminhtml_ReportsController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction()

    {
        $this->loadLayout()
            ->_setActiveMenu('codebase/non_estimated_tickets');
        return $this;
    }
    public function indexAction() {
        $this->loadLayout();
        $this->_initAction();
        $this->renderLayout();
    }
    public function exportCsvAction()
    {
        $fileName   = 'tickets.csv';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_reports_grid')
            ->getCsv();
 
        $this->_sendUploadResponse($fileName, $content);
    }
 
    public function exportXmlAction()
    {
        $fileName   = 'tickets.xml';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_reports_grid')
            ->getXml();
 
        $this->_sendUploadResponse($fileName, $content);
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('codebase/adminhtml_reports_grid')->toHtml()
        );
    }
   
    public function estimateNeedAction()
    {
        $success = array(); 
        $failed = array();

        $mticketIds = $this->getRequest()->getParam('ids');

        $estimateNeed   = (int)$this->getRequest()->getParam('estimate_need');

       foreach ($mticketIds as $id) {

            $ticket = Mage::getModel('codebase/tickets')->load($id);

            $ticket->setEstimateNeed($estimateNeed)->save();

            if($ticket->getId())
            {
                $success[] = $ticket->getPermalink() . ' #' . $ticket->getTicketId();
            }
            
        }

        Mage::getSingleton('adminhtml/session')->addSuccess('The Following tickets Estimate need have been changed : '. implode(', ', $success) );        
        $this->_redirect('*/*/');
    }

    public function needTimeEstimateAction()
    {
        $success = array(); 
        $failed = array();

        $mticketIds = $this->getRequest()->getParam('ids');

        foreach ($mticketIds as $id) {

            $ticket = Mage::getModel('codebase/tickets')->load($id);

            $url = '/'.$ticket->getPermalink().'/tickets/'.$ticket->getTicketId().'/notes';

            $status_id = '4121618';

            $codebase = Mage::getModel('codebase/codebase')->ticketChangeStatus($url, $status_id);

            $ticket->setStatusName('Need Time Estimate');
            $ticket->save();

            if($ticket->getId()) {
                $success[] = $ticket->getPermalink() . ' #' . $ticket->getTicketId();
            }
            
        }

        Mage::getSingleton('adminhtml/session')->addSuccess('The Following tickets Estimate need have been changed : '. implode(', ', $success) );        
        $this->_redirect('*/*/');
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
