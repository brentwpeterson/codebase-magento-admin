<?php
class Wage_Codebase_Model_Businessdayscalculator extends Mage_Core_Model_Abstract
{
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;
    const SUNDAY    = 7;

    public $_date;
    public $_holidays;
    public $_nonBusinessDays;

    public function addBusinessDays($startDate, array $holidays, array $nonBusinessDays, $howManyDays)
    {
        $this->_holidays = $holidays;
        $this->_nonBusinessDays = $nonBusinessDays;

        $i = 0;
        while ($i < $howManyDays) {
            $startDate = date("Y-m-d", strtotime($startDate.' +1 day'));
            if ($this->isBusinessDay($startDate)) {
                $i++;
            }
        }
        return $startDate;
    }

    public function isBusinessDay($date)
    {
        $checkDay = date("N", strtotime($date));
        if (in_array((int)$checkDay, $this->_nonBusinessDays)) {
            return false; //Date is a nonBusinessDay.
        }
        $date = date("Y-m-d", strtotime($date));
        foreach ($this->_holidays as $day) {
            $holiday = date("Y-m-d", strtotime($day));
            if ($date == $holiday) {
                return false; //Date is a holiday.
            }
        }
        return true; //Date is a business day.
    }

    public function workingdays($startDate, $endDate, array $holidays, array $nonBusinessDays)
    {
        $this->_holidays = $holidays;
        $this->_nonBusinessDays = $nonBusinessDays;

        $i = 0;
        while (strtotime($startDate) < strtotime($endDate)) {
            if ($this->isBusinessDay($startDate)) {
                $i++;
            }
            $startDate = date("Y-m-d", strtotime($startDate.' +1 day'));
        }
        return $i;
    }


}
