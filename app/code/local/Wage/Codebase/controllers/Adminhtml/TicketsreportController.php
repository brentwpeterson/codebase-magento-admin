<?php
class Wage_Codebase_Adminhtml_TicketsreportController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction()

    {
        $this->loadLayout()
            ->_setActiveMenu('codebase/open_tickets');
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
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_tickets_grid')
            ->getCsv();
 
        $this->_sendUploadResponse($fileName, $content);
    }
 
    public function exportXmlAction()
    {
        $fileName   = 'tickets.xml';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_tickets_grid')
            ->getXml();
 
        $this->_sendUploadResponse($fileName, $content);
    }

    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('codebase/adminhtml_ticketsreport_grid')->toHtml()
        );
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
