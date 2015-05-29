<?php
class Wage_Codebase_Block_Adminhtml_Charts extends Mage_Adminhtml_Block_Template {
    public function getData() {
        return Mage::getModel('codebase/timetracking')->getTimetrackingByPeriod($this->getPeriod(), $this->getFrom(), $this->getTo(), $this->getProjects(), $this->getUser());
    }

    public function getPeriod() {
        if (Mage::app()->getRequest()->getPost('period'))
            return Mage::app()->getRequest()->getPost('period');

        return 'day';
    }

    public function getFrom() {
        if (Mage::app()->getRequest()->getPost('from'))
            return Mage::app()->getRequest()->getPost('from');

        return null;
    }

    public function getTo() {
        if (Mage::app()->getRequest()->getPost('to'))
            return Mage::app()->getRequest()->getPost('to');

        return null;
    }

    public function getProjects() {
        if (Mage::app()->getRequest()->getPost('projects'))
            return Mage::app()->getRequest()->getPost('projects');

        return array();
    }

    public function getUser()
    {
        if (Mage::app()->getRequest()->getPost('user'))
            return Mage::app()->getRequest()->getPost('user');

        return null;
    }
}
