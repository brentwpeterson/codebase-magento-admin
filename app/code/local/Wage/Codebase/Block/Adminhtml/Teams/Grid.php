<?php
class Wage_Codebase_Block_Adminhtml_Teams_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('teamsGrid');
      $this->setDefaultSort('team_name');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('codebase/teams')->getCollection();

      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {


      $this->addColumn('team_name', array(
          'header'    => Mage::helper('codebase')->__('Team Name'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'team_name',
      ));

      $this->addColumn('team_id', array(
          'header'    => Mage::helper('codebase')->__('Team Members'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'team_id',
          'renderer' => 'Wage_Codebase_Block_Adminhtml_Teams_Edit_Tab_Grid_Renderer_Members',
      ));
      $this->addColumn('action',
          array(
              'header'    => Mage::helper('codebase')->__('Action'),
              'width'     => '50px',
              'type'      => 'action',
              'getter'     => 'getId',
              'actions'   => array(
                  array(
                      'caption' => Mage::helper('catalog')->__('Edit'),
                      'url'     => array(
                          'base'=>'*/*/edit',
                          'params'=>array('id'=>$this->getRequest()->getParam('id'))
                      ),
                      'field'   => 'id'
                  )
              ),
              'filter'    => false,
              'sortable'  => false,
              'index'     => 'id',
          ));
		$this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
