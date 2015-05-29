<?php
class Wage_Codebase_Model_System_Config_Source_Companies
{
    public function toOptionArray($isMultiselect=false)
    {
        $options = array();
        /*$collection = Mage::getModel('codebase/users')->getCollection();

        foreach ($collection as $user) {
            if (empty($options[$user->getCompany()])) {
                $options[$user->getCompany()] = array('value' => preg_replace('/[^a-zA-Z0-9\-]/', '', strtolower($user->getCompany())), 'label' => $user->getCompany());
            }
        }*/

        $collection = Mage::getModel('codebase/projectindex')->getCollection(); 
        $collection->addFieldToFilter('company' , array('nlike' => 'Array'));
        $collection->addFieldToFilter('company' , array('nlike' => '%Wagento%'));

        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));

        $collection->getSelect()->group('company');
        $collection->load();

        foreach ($collection as $user) {
            if (empty($options[$user->getCompany()])) {
                $options[$user->getCompany()] = array('value' => preg_replace('/[^a-zA-Z0-9\-]/', '', strtolower($user->getCompany())), 'label' => $user->getCompany());
            }
        }

        return $options;
    }
}
