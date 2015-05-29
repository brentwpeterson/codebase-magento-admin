<?php
class Wage_Codebase_Block_Adminhtml_Notes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('notesGrid');
      $this->setDefaultSort('project_id');
      $this->setDefaultDir('ASC');
      $this->setUseAjax(true);
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('codebase/notes')->getCollection();
                    
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

        $this->addColumn('project_id', array(
            'header' => Mage::helper('codebase')->__('Project ID'),
            'index' => 'project_id',

        ));
        $this->addColumn('user_id', array(
            'header'    =>Mage::helper('codebase')->__('User Id'),
            'index'     =>'user_id',
            'type'      =>'number'
        ));
        $this->addColumn('title', array(
            'header'    =>Mage::helper('codebase')->__('Title'),
            'index'     =>'title',
        ));
        $this->addColumn('subject', array(
            'header'    =>Mage::helper('codebase')->__('Subject'),
            'index'     =>'subject',
        ));
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('codebase')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => '70',
        ));        
        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('codebase')->__('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
            'width'     => '70',
        ));
        //$this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
        return parent::_prepareColumns();
	  

  }



  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
