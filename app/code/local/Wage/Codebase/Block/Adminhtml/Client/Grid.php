<?php
class Wage_Codebase_Block_Adminhtml_Client_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function getPeriod() {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['period'])) {
            return $filter['period'];
        }

        return 'day';
    }

    public function getFrom() {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['from'])) {
            return $filter['from'];
        }

        return null;
    }

    public function getTo() {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['to'])) {
            return $filter['to'];
        }

        return null;
    }

    public function getProject() {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['project'])) {
            return $filter['project'];
        }

        return null;
    }

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('codebase/client.phtml');
        $this->setId('reportsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $excludeTypes = explode(',',Mage::getStoreConfig('codebase/report/exclude_category'));
        if(count($excludeTypes) == 1){
            $excludeTypes = array($excludeTypes);
        }
        $collection = Mage::getModel('codebase/timetracking')->getClientHoursByPeriod();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('period', array(
            'header' => Mage::helper('codebase')->__('Period'),
            'index' => 'period',
            'filter' => false

        ));

        $this->addColumn('gross', array(
            'header' => Mage::helper('codebase')->__('Gross'),
            'index' => 'gross',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addColumn('billable', array(
            'header' => Mage::helper('codebase')->__('Billable'),
            'index' => 'billable',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addColumn('bug_fixes', array(
            'header' => Mage::helper('codebase')->__('Bug fixes'),
            'index' => 'bug_fixes',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addColumn('uncategorized', array(
            'header' => Mage::helper('codebase')->__('Yet uncategorized'),
            'index' => 'uncategorized',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addColumn('tickets_completed', array(
            'header' => Mage::helper('codebase')->__('Tickets Completed'),
            'index' => 'tickets_completed',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addColumn('tickets_created', array(
            'header' => Mage::helper('codebase')->__('New Tickets Created'),
            'index' => 'tickets_created',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addColumn('completed_estimate', array(
            'header' => Mage::helper('codebase')->__('Completed Estimate'),
            'index' => 'completed_estimate',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addColumn('over_estimate', array(
            'header' => Mage::helper('codebase')->__('Over Estimate'),
            'index' => 'over_estimate',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addColumn('new_estimate', array(
            'header' => Mage::helper('codebase')->__('New Estimated Hours'),
            'index' => 'new_estimate',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Client_Grid_Renderer_Hours',
            'filter' => false

        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _filterConditionCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == NULL) {
            return $this;
        }
        else {
            $this->getCollection()
                ->addFieldToFilter('estimated_time',$value);
        }

        return $this;
    }


}
