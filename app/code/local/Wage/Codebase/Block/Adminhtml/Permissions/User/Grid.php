<?php

class Wage_Codebase_Block_Adminhtml_Permissions_User_Grid extends Mage_Adminhtml_Block_Permissions_User_Grid
{

    protected function _prepareColumns()
    {
        $this->addColumn('user_id', array(
            'header'    => Mage::helper('adminhtml')->__('ID'),
            'width'     => 5,
            'align'     => 'right',
            'sortable'  => true,
            'index'     => 'user_id'
        ));

        $this->addColumn('username', array(
            'header'    => Mage::helper('adminhtml')->__('User Name'),
            'index'     => 'username'
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('adminhtml')->__('First Name'),
            'index'     => 'firstname'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('adminhtml')->__('Last Name'),
            'index'     => 'lastname'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('adminhtml')->__('Email'),
            'width'     => 40,
            'align'     => 'left',
            'index'     => 'email'
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('adminhtml')->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array('1' => Mage::helper('adminhtml')->__('Active'), '0' => Mage::helper('adminhtml')->__('Inactive')),
        ));

        $this->addColumn('wagento_staff', array(
            'header'    => Mage::helper('adminhtml')->__('Wagento Staff'),
            'index'     => 'wagento_staff',
            'type'      => 'options',
            'options'   => array('1' => Mage::helper('adminhtml')->__('Yes'), '0' => Mage::helper('adminhtml')->__('No')),
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //$this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('is_active', array(
            'label'=> Mage::helper('codebase')->__('Change Status'),
            'url'  => $this->getUrl('*/*/updateStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'is_active',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('catalog')->__('Select Status'),
                    'values' => array('0' => 'No','1' => 'Yes')
                )
            )
        ));

        $this->getMassactionBlock()->addItem('wagento_staff', array(
            'label'=> Mage::helper('codebase')->__('Change Wagento Staff'),
            'url'  => $this->getUrl('*/*/updateWagentoStaff', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'wagento_staff',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('catalog')->__('Select Wagento Staff'),
                    'values' => array('0' => 'No','1' => 'Yes')
                )
            )
        ));
        return $this;
    }

}
