<?php
class Wage_Codebase_Helper_Data extends Mage_Core_Helper_Abstract {
    public function convertToHoursMins($time, $format = '%d:%d') {
        settype($time, 'integer');
        if ($time < 1) {
            return ;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }

    public function convertHoursToMinutes($hours)
    {
        if (strstr($hours, ':'))
        {
            # Split hours and minutes.
            $separatedData = split(':', $hours);

            $minutesInHours    = $separatedData[0] * 60;
            $minutesInDecimals = $separatedData[1];

            $totalMinutes = $minutesInHours + $minutesInDecimals;
        }
        else
        {
            $totalMinutes = $hours * 60;
        }

        return $totalMinutes;
    }

    public function getActiveProjects()
    {
        $projects = Mage::getModel('codebase/projects')->getCollection()
                    ->addFieldToFilter('status','active');

        return $projects;
    }

    public function getActiveDevelopers()
    {
        $developers = Mage::getModel('codebase/users')->getCollection()
                    ->addFieldToFilter('enabled','1')
                    ->addFieldToFilter('user_name', array('nlike' => '%backlog%'))
                    ->addFieldToFilter('company','wagento');

        return $developers;
    }

    public function getProductOwners()
    {
        $roles = Mage::getModel('admin/roles')->getCollection();
        $roles->addFieldToFilter('role_name', 'Product Owner');    
        foreach($roles as $role){
            $adminroles = Mage::getModel('admin/roles')->load($role->getRoleId())->getRoleUsers();
            foreach($adminroles as $adminuserid){
                $adminuserModel = Mage::getModel('admin/user')->load($adminuserid);
                $productOwner[] = $adminuserModel->getApiUser();
            }   
        }

        $productOwnerCollection = Mage::getModel("codebase/users")
          ->getCollection()
          ->addFieldToFilter('user_name', array('in' => $productOwner));
        ;
        foreach($productOwnerCollection as $productOwnerValues){
            $productOwnerNames = $productOwnerValues->getFirstName().' '.$productOwnerValues->getLastName();
            $productOwnerArray[$productOwnerValues->getUserId()] = $productOwnerNames;
        }

        return $productOwnerArray;
    }

    public function getAllUserType()
    {
        $roles = Mage::getModel('admin/roles')->getCollection(); 
        foreach($roles as $role){
            $adminroles = Mage::getModel('admin/roles')->load($role->getRoleId())->getRoleUsers();
            foreach($adminroles as $adminuserid){
                $adminuserModel = Mage::getModel('admin/user')->load($adminuserid);
                $roleUser[$adminuserModel->getApiUser()] = $role->getRoleName();
            }   
        }

        
        $codebaseUsersCollection = Mage::getModel("codebase/users")->getCollection();

        foreach($codebaseUsersCollection as $codebaseUsersValues){
            if(isset($roleUser[$codebaseUsersValues->getUserName()]))
            {
                $userType[$codebaseUsersValues->getUserId()]['name'] = $codebaseUsersValues->getFirstName().' '.$codebaseUsersValues->getLastName();
                $userType[$codebaseUsersValues->getUserId()]['type'] = $roleUser[$codebaseUsersValues->getUserName()];
            }
        }

        return $userType;
    }
    
}
