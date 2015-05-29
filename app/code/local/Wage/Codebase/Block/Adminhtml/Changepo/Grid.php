<?php
class Wage_Codebase_Block_Adminhtml_Changepo_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('changepoGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('codebase/changepo')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rule_title', array(
            'header'    => Mage::helper('codebase')->__('Rule Title'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'rule_title',
        ));

        $this->addColumn('current_user_id', array(
            'header'    => Mage::helper('codebase')->__('Current User'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'current_user_id',
            'frame_callback' => array($this, 'callbackCurrentUser'),
            'filter' => false,
        ));

        $this->addColumn('new_user_id', array(
            'header'    => Mage::helper('codebase')->__('New User'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'new_user_id',
            'frame_callback' => array($this, 'callbackNewUser'),
            'filter' => false,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('codebase')->__('Status'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'status',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * add massAction column
     * @author atheotsky
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //$this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('delete_product_owner', array(
            'label'=> Mage::helper('codebase')->__('Delete User Rule'),
            'url'  => $this->getUrl('*/*/delete'),
        ));
        return $this;
    }

    public function callbackCurrentUser($value, $row, $column, $isExport)
    {
        $currentUserId = $row->getCurrentUserId();
        $users = Mage::getModel("codebase/users")
            ->getCollection()
            ->addFieldToFilter('user_id', $currentUserId)
        ;

        if ($users->getFirstItem()->getFirstName()) {
            return $users->getFirstItem()->getFirstName().' '.$users->getFirstItem()->getLastName();
        } else {
            return 'User Name Not Exist';
        }
    }

    public function callbackNewUser($value, $row, $column, $isExport)
    {
        $newUserId = $row->getNewUserId();
        $users = Mage::getModel("codebase/users")
            ->getCollection()
            ->addFieldToFilter('user_id', $newUserId)
        ;

        if ($users->getFirstItem()->getFirstName()) {
            return $users->getFirstItem()->getFirstName().' '.$users->getFirstItem()->getLastName();
        } else {
            return 'User Name Not Exist';
        }
    }
}
