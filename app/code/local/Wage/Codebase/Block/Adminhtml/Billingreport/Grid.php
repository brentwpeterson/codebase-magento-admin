<?php
class Wage_Codebase_Block_Adminhtml_Billingreport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('billingreportGrid');
      $this->setDefaultSort('project_name');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('codebase/reports')->getCollection();

      $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_projects', 'main_table.project_id ='.Mage::getConfig()->getTablePrefix().'codebase_projects.project_id',array('project_name'));

      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {


      $this->addColumn('project_name', array(
          'header'    => Mage::helper('codebase')->__('Project Id'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'project_name',
      ));

       $this->addColumn('from_date', array(
            'header'    => Mage::helper('codebase')->__('From'),
            'index'     => 'from_date',
            'type'      => 'datetime',
            'width'     => '70',
        ));

       $this->addColumn('to_date', array(
            'header'    => Mage::helper('codebase')->__('To'),
            'index'     => 'to_date',
            'type'      => 'datetime',
            'width'     => '70',
        ));
      $this->addColumn('Created At', array(
          'header'    => Mage::helper('codebase')->__('Created At'),
          'index'     => 'created_at',
          'type'      => 'datetime',
          'width'     => '70',
      ));
      $this->addColumn('Updated At', array(
          'header'    => Mage::helper('codebase')->__('Updated At'),
          'index'     => 'updated_at',
          'type'      => 'datetime',
          'width'     => '70',
      ));
      $this->addColumn('report', array(
          'header'    => Mage::helper('codebase')->__('View Report'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'id',
          'renderer' => 'Wage_Codebase_Block_Adminhtml_Billingreport_Edit_Tab_Grid_Renderer_Viewreport',
      ));      		
	  
      return parent::_prepareColumns();
  }

    

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
