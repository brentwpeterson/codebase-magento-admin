<?php
class Wage_Codebase_Model_Users extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('codebase/users');
    }

    /**
     * find user by codebase user id
     */
    public function findUser($user_id) {
        $find = $this->getCollection()
            ->addFieldToFilter('user_id',$user_id)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }

        return $this;
    }

    /**
     * get all active users
     * return collection
     */
    public function getActiveUsers()
    {
        $collection = $this->getCollection()->addFieldToFilter('enabled', 1);
        $collection->getSelect()->order('first_name ASC');
        return $collection;
    }
}
