<?php
class Wage_Codebase_Adminhtml_OwnerreportController extends Mage_Adminhtml_Controller_Action
{

    public function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('codebase/ownerreport');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(
                Mage::helper('codebase')->__('Owner Report'),
                Mage::helper('codebase')->__('Owner Report')
            )
            ->renderLayout();
    }


    public function activitiesAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function exportProjectreportCsvAction()
    {
        $fileName   = 'product_owner_report.csv';
        $content    = $this->getLayout()
            ->createBlock('codebase/adminhtml_ownerreport_grid')
            ->getCsv();
        $content = strip_tags($content);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportProjectreportExcelAction()
    {
        $fileName   = 'project_report.xml';
        $content    = $this->getLayout()
            ->createBlock('codebase/adminhtml_ownerreport_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
}
