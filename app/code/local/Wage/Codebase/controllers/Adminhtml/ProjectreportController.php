<?php
class Wage_Codebase_Adminhtml_ProjectreportController extends Mage_Adminhtml_Controller_Action
{

    public function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('codebase/projectreport');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(
                Mage::helper('codebase')->__('Project Report'),
                Mage::helper('codebase')->__('Project Report')
            )
            ->renderLayout();
    }

    public function exportProjectreportCsvAction()
    {
        $fileName   = 'project_report.csv';
        $content    = $this->getLayout()
            ->createBlock('codebase/adminhtml_projectreport_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportProjectreportExcelAction()
    {
        $fileName   = 'project_report.xml';
        $content    = $this->getLayout()
            ->createBlock('codebase/adminhtml_projectreport_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
}
