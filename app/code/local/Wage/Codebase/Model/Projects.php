<?php
class Wage_Codebase_Model_Projects extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('codebase/projects');
    }

    /**
     * get active project ids
     * @author atheotsky
     */
    public function getActiveIds() {
        $ids = array();
        $collection = $this->getCollection()->addFieldToFilter('status', 'active');
        foreach ($collection as $item) {
            $ids[] = $item->getProjectId();
        }

        return $ids;
    }

    public function loadProjectByProjectId($projectId) {
        $find = $this->getCollection()
            ->addFieldToFilter('project_id',$projectId)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }
        return $this;
    }

    public function loadProjectByPermalink($permalink) {
        $find = $this->getCollection()
            ->addFieldToFilter('permalink',$permalink)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }
        return $this;
    }
}
