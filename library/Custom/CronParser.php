<?php
/**
 * Class to manage UNIX cron-tab format
 *
 * $Rev$
 * $LastChangedDate$
 * $LastChangedBy$
 *
 * @category   Application
 * @package    Tools
 * @copyright  Copyright (c) 2012 Alexandru Rusu
 * @author     Alexandru Rusu <arusu@streamwide.ro>
 * @version    $Id$
 */

/**
 * Crontab-format schedule parser. It returns the last date a cron job should
 * have been executed. Class supports the '/' operator, such STAR/3 for minutes
 * to specify executing the script every 3 minutes. This can also be specified
 * as 0,3,6,[..],57 to get the same result.
 *                                                                         <br/>
 * Here is the crontab schedule format:                                    <br/>
 *   [ *  *  *  *  * ]                                                     <br/>
 *     |  |  |  |  |    /--------------+-------------------------\         <br/>
 *     |  |  |  |  |    | Symbol       | [INTERVAL]              |         <br/>
 *     |  |  |  |  |    |--------------+-------------------------|         <br/>
 *     |  |  |  |  +--- | day of week  | (Sunday 0 - 6 Saturday) |         <br/>
 *     |  |  |  +------ | month        | (1 - 12)                |         <br/>
 *     |  |  +--------- | day of month | (1 - 31)                |         <br/>
 *     |  +------------ | hour         | (0 - 23)                |         <br/>
 *     +--------------- | minute       | (0 - 59)                |         <br/>
 *                      \--------------+-------------------------/         <br/>
 *
 * Multiple values can be specified with comma. For example:
 *   [* 12 * * 1,5] - Executes every Monday and Friday, each minute of 12th hour
 *   [10 * * 5,8 3] - Executes every Wednesday at xx:10 in May and August
 *
 * <code>
 * $cron = new Custom_CronParser();
 * $cron->calcLastRan('10 * * 5,8 3');
 * $lastExecTimestamp = $cron->getLastRanUnix();
 * echo date('r', $lastExecTimestamp);
 * </code>
 */
class Custom_CronParser
{
    /**
     * exploded String (like 0 1 * * *)
     * @var array
     */
    protected $_bits = Array();

    /**
     * Array of cron-style entries for current time
     * @var array
     */
    protected $_now = Array();

    /**
     * Timestamp of last execution time.
     * @var int
     */
    protected $_lastRan;

    /**
     * @var int
     */
    protected $_year;

    /**
     * @var int
     */
    protected $_month;

    /**
     * @var int
     */
    protected $_day;

    /**
     * @var int
     */
    protected $_hour;

    /**
     * @var int
     */
    protected $_minute;

    /**
     * Minutes array based on cron string
     *
     * @var array
     */
    protected $_minutes_arr = array();

    /**
     * Hours array based on cron string
     *
     * @var array
     */
    protected $_hours_arr = array();

    /**
     * Months array based on cron string
     *
     * @var array
     */
    protected $_months_arr = array();

    /**
     * Return the last execution time as an array
     * @return array
     */
    public function getLastRan()
    {
        return explode(',', strftime('%M,%H,%d,%m,%w,%Y', $this->_lastRan));
    }

    /**
     * Return the last execution time as timestamp
     * @return int
     */
    public function getLastRanUnix()
    {
        return $this->_lastRan;
    }

    /**
     * Calculate the last due time before this moment
     *
     * @param string $string Crontab schedule string
     * @return void
     */
    public function calcLastRan($string, $currentTime = 0)
    {
        if ($currentTime == 0) {
            $currentTime = time();
        }

        $tstart = $currentTime;
        $this->_lastRan = 0;
        $this->_year = NULL;
        $this->_month = NULL;
        $this->_day = NULL;
        $this->_hour = NULL;
        $this->_minute = NULL;
        $this->_hours_arr = array();
        $this->_minutes_arr = array();
        $this->_months_arr = array();

        $string = preg_replace('/[\s]{2,}/', ' ', $string);

        if (preg_match('/[^-,*\/ \\d]/', $string) !== 0) {
            return false;
        }
        $this->_bits = @explode(' ', $string);
        if (count($this->_bits) != 5) {
            return false;
        }
        $t = strftime('%M,%H,%d,%m,%w,%Y', $currentTime);
        $this->_now = explode(',', $t);

        $this->_preprocess_bits();

        $this->_year = $this->_now[5];
        $arMonths = $this->_getMonthsArray();
        do {
            $this->_month = array_pop($arMonths);
        } while ($this->_month > $this->_now[3]);

        if ($this->_month === NULL) {
            $this->_year = $this->_year - 1;
            $arMonths = $this->_getMonthsArray();
            $this->_prevMonth($arMonths);
        } elseif ($this->_month == $this->_now[3]) {
            $arDays = $this->_getDaysArray($this->_month, $this->_year);
            do {
                $this->_day = array_pop($arDays);
            } while ($this->_day > $this->_now[2]);

            if ($this->_day === NULL) {
                $this->_prevMonth($arMonths);
            } elseif ($this->_day == $this->_now[2]) {
                $arHours = $this->_getHoursArray();

                do {
                    $this->_hour = array_pop($arHours);
                } while ($this->_hour > $this->_now[1]);

                if ($this->_hour === NULL) {
                    $this->_prevDay($arDays, $arMonths);
                } elseif ($this->_hour < $this->_now[1]) {
                    $this->_minute = $this->_getLastMinute();
                } else {
                    $arMinutes = $this->_getMinutesArray();
                    do {
                        $this->_minute = array_pop($arMinutes);
                    } while ($this->_minute > $this->_now[0]);

                    if ($this->_minute === NULL) {
                        $this->_prevHour($arHours, $arDays, $arMonths);
                    } else {
                        // Due this very minute or some earlier minutes
                        // before this moment within this hour
                    }
                }
            } else {
                $this->_hour = $this->_getLastHour();
                $this->_minute = $this->_getLastMinute();
            }
        } else {
            $this->_day = $this->_getLastDay($this->_month, $this->_year);
            if ($this->_day === NULL) {
                $this->_prevMonth($arMonths);
            } else {
                $this->_hour = $this->_getLastHour();
                $this->_minute = $this->_getLastMinute();
            }
        }
        if ($this->_minute === NULL) {
            return false;
        } else {
            $this->_lastRan = mktime($this->_hour, $this->_minute, 0, $this->_month, $this->_day, $this->_year);
            return true;
        }
    }

    /**
     * Assumes that value is not *, and creates an array of valid numbers
     * that the string represents.
     *
     * @param string $str
     * @return array
     */
    protected function _expand_ranges($str)
    {
        if (strstr($str, ',')) {
            $arParts = explode(',', $str);
            foreach ($arParts AS $part) {
                if (strstr($part, '-')) {
                    $arRange = explode('-', $part);
                    for ($i = $arRange[0]; $i <= $arRange[1]; ++$i) {
                        $ret[] = $i;
                    }
                } else {
                    $ret[] = $part;
                }
            }
        } elseif (strstr($str, '-')) {
            $arRange = explode('-', $str);
            for ($i = $arRange[0]; $i <= $arRange[1]; ++$i) {
                $ret[] = $i;
            }
        } else {
            $ret[] = $str;
        }
        $ret = array_unique($ret);
        sort($ret);
        return $ret;
    }

    /**
     * Goto previous month
     *
     * @param array $arMonths
     * @return void
     */
    protected function _prevMonth($arMonths)
    {
        $this->_month = array_pop($arMonths);
        if ($this->_month === NULL) {
            $this->_year = $this->_year - 1;

            // Cannot go before 1970
            if ($this->_year > 1970) {
                $arMonths = $this->_getMonthsArray();
                $this->_prevMonth($arMonths);
            }
        } else {
            $this->_day = $this->_getLastDay($this->_month, $this->_year);

            if ($this->_day === NULL) {
                $this->_prevMonth($arMonths);
            } else {
                $this->_hour = $this->_getLastHour();
                $this->_minute = $this->_getLastMinute();
            }
        }
    }

    /**
     * Goto previous day
     * 
     * @param array $arDays
     * @param array $arMonths
     * @return void
     */
    protected function _prevDay($arDays, $arMonths)
    {
        $this->_day = array_pop($arDays);
        if ($this->_day === NULL) {
            $this->_prevMonth($arMonths);
        } else {
            $this->_hour = $this->_getLastHour();
            $this->_minute = $this->_getLastMinute();
        }
    }

    /**
     * Goto previous hour
     *
     * @param array $arHours
     * @param array $arDays
     * @param array $arMonths
     * @return void
     */
    protected function _prevHour($arHours, $arDays, $arMonths)
    {
        $this->_hour = array_pop($arHours);
        if ($this->_hour === NULL) {
            $this->_prevDay($arDays, $arMonths);
        } else {
            $this->_minute = $this->_getLastMinute();
        }
    }

    /**
     * Get last month
     * 
     * @return int
     */
    protected function _getLastMonth()
    {
        $months = $this->_getMonthsArray();
        $month = array_pop($months);
        return $month;
    }

    /**
     * Get last day
     *
     * @return int
     */
    protected function _getLastDay($month, $year)
    {
        $days = $this->_getDaysArray($month, $year);
        $day = array_pop($days);
        return $day;
    }

    /**
     * Get last hour
     *
     * @return int
     */
    protected function _getLastHour()
    {
        $hours = $this->_getHoursArray();
        $hour = array_pop($hours);
        return $hour;
    }

    /**
     * Get last minute
     *
     * @return int
     */
    protected function _getLastMinute()
    {
        $minutes = $this->_getMinutesArray();
        $minute = array_pop($minutes);
        return $minute;
    }

    /**
     * Get days when cron should execute (month or week)
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    protected function _getDaysArray($month, $year = 0)
    {
        if ($year == 0) {
            $year = $this->_year;
        }
        $days = array();
        if ($this->_bits[2] == '*' AND $this->_bits[4] == '*') {
            $days = $this->_getDays($month, $year);
        } else {
            if ($this->_bits[4] == '*') {
                for ($i = 0; $i <= 6; ++$i) {
                    $arWeekdays[] = $i;
                }
            } else {
                $arWeekdays = $this->_expand_ranges($this->_bits[4]);
                $arWeekdays = $this->_sanitize($arWeekdays, 0, 7);

                if (in_array(7, $arWeekdays)) {
                    if (in_array(0, $arWeekdays)) {
                        array_pop($arWeekdays);
                    } else {
                        $tmp[] = 0;
                        array_pop($arWeekdays);
                        $arWeekdays = array_merge($tmp, $arWeekdays);
                    }
                }
            }
            if ($this->_bits[2] == '*') {
                $daysmonth = $this->_getDays($month, $year);
            } else {
                $daysmonth = $this->_expand_ranges($this->_bits[2]);
                $daysinmonth = $this->_daysinmonth($month, $year);
                $daysmonth = $this->_sanitize($daysmonth, 1, $daysinmonth);
            }
            foreach ($daysmonth AS $day) {
                $wkday = date('w', mktime(0, 0, 0, $month, $day, $year));
                if (in_array($wkday, $arWeekdays)) {
                    $days[] = $day;
                }
            }
        }
        return $days;
    }

    /**
     * Get days in month when cron should execute
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    protected function _getDays($month, $year)
    {
        $daysinmonth = $this->_daysinmonth($month, $year);
        $days = array();
        for ($i = 1; $i <= $daysinmonth; ++$i) {
            $days[] = $i;
        }
        return $days;
    }

    /**
     * Get hours when cron should execute
     *
     * @return array
     */
    protected function _getHoursArray()
    {
        if (empty($this->_hours_arr)) {
            $hours = array();
            if ($this->_bits[1] == '*') {
                for ($i = 0; $i <= 23; ++$i) {
                    $hours[] = $i;
                }
            } else {
                $hours = $this->_expand_ranges($this->_bits[1]);
                $hours = $this->_sanitize($hours, 0, 23);
            }
            $this->_hours_arr = $hours;
        }
        return $this->_hours_arr;
    }

    /**
     * Get minutes when cron should execute
     *
     * @return array
     */
    protected function _getMinutesArray()
    {
        if (empty($this->_minutes_arr)) {
            $minutes = array();
            if ($this->_bits[0] == '*') {
                for ($i = 0; $i <= 60; ++$i) {
                    $minutes[] = $i;
                }
            } else {
                $minutes = $this->_expand_ranges($this->_bits[0]);
                $minutes = $this->_sanitize($minutes, 0, 59);
            }
            $this->_minutes_arr = $minutes;
        }
        return $this->_minutes_arr;
    }

    /**
     * Get months when cron should execute
     *
     * @return array
     */
    protected function _getMonthsArray()
    {
        if (empty($this->_months_arr)) {
            $months = array();
            if ($this->_bits[3] == '*') {
                for ($i = 1; $i <= 12; ++$i) {
                    $months[] = $i;
                }
            } else {
                $months = $this->_expand_ranges($this->_bits[3]);
                $months = $this->_sanitize($months, 1, 12);
            }
            $this->_months_arr = $months;
        }
        return $this->_months_arr;
    }

    /**
     * Get number of days in a month
     *
     * @param int $month
     * @param int $year
     * @return int Casted automatically by PHP from string
     */
    protected function _daysinmonth($month, $year)
    {
        return date('t', mktime(0, 0, 0, $month, 1, $year));
    }

    /**
     * Sanitize an array based on min and max values
     *
     * @param array $arr
     * @param int $low
     * @param int $high
     * @return array The clean array
     */
    protected function _sanitize($arr, $low, $high)
    {
        $count = count($arr);
        for ($i = 0; $i <= ($count - 1); ++$i) {
            if ($arr[$i] < $low) {
                unset($arr[$i]);
            } else {
                break;
            }
        }
        for ($i = ($count - 1); $i >= 0; --$i) {
            if ($arr[$i] > $high) {
                unset($arr[$i]);
            } else {
                break;
            }
        }
        sort($arr);
        return $arr;
    }

    /**
     * Preprocess bits - if items are stared fractions, replace by values
     * Ex.: for minutes, STAR/3 = 0,3,6,[...],57
     *
     * @return void
     */
    protected function _preprocess_bits()
    {
        foreach ($this->_bits as $index => $bit) {
            $to = ($index == 0 ? 60 :
                    ($index == 1 ? 24 :
                      ($index == 2 ? 31 :
                        ($index == 3 ? 12 :
                          ($index == 4 ? 7 : 60) ) ) ) );
            // Type-strong strpos result
            if (strpos($bit, '*/') === 0) {
                $actual = array();
                $slice = intval(substr($bit, 2));
                if ($slice > 0) {
                    for ($i = 0; $i <= $to; ++$i) {
                        if (($i % $slice) == 0) {
                            $actual[] = $i;
                        }
                    }
                }
                $this->_bits[$index] = implode(',', $actual);
            }
        }
    }
}

/* EOF */