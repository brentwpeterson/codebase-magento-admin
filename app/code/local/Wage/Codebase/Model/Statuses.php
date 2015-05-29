<?php
class Wage_Codebase_Model_Statuses extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('codebase/statuses');
    }

    /**
     * find row by project id and status id
     */
    public function findStatus($projectId, $statusId) {
        $find = $this->getCollection()
            ->addFieldToFilter('status_id',$statusId)
            ->addFieldToFilter('project_id',$projectId)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }

        return $this;
    }

    /**
     * find status id by project and label
     */
    public function findStatusbyLabel($projectId, $label) {
        $find = $this->getCollection()
            ->addFieldToFilter('name', array('regexp' => strtolower($label)))
            ->addFieldToFilter('project_id',$projectId)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }

        return $this;
    }
}
