<?php
class Wage_Codebase_Model_System_Config_Source_Client {
    public function toOptionArray($isMultiselect=false)
    {
        $options = array();
        $collection = Mage::getModel('codebase/users')->getCollection();
        $collection->addFieldToFilter('company', array('neq' => 'Wagento'));
        $collection->addFieldToFilter('user_name', array('neq' => 'Array'));

        foreach ($collection as $user) {
            $options[] = array('value' => $user->getUserId(), 'label' => $user->getUserName());
        }
        
        return $options;
    }
}
