<?php
class Wage_Codebase_Adminhtml_ClientController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('codebase/weekly_client_hours');
        return $this;
    }

    /**
     * main report function
     */
    public function indexAction() {
        /*prepare filter*/
        if ($filter = Mage::app()->getRequest()->getPost()) {
            $session = Mage::getSingleton("admin/session");
            $session->setFilter(
                array(
                    'period' => Mage::app()->getRequest()->getPost('period'),
                    'from' => Mage::app()->getRequest()->getPost('from'),
                    'to' => Mage::app()->getRequest()->getPost('to'),
                    'project' => Mage::app()->getRequest()->getPost('project'),
                )
            );
        }

        $this->loadLayout();
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * export the report to csv format
     */
    public function exportCsvAction()
    {
        $fileName   = 'weekly_client_hours.csv';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_client_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * export the report to Excel format
     */
    public function exportXmlAction()
    {
        $fileName   = 'weekly_client_hours.xml';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_client_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('codebase/adminhtml_client_grid')->toHtml()
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
