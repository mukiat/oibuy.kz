<?php

namespace App\Services\Commission;

use App\Models\MerchantsServer;
use App\Models\OrderGoods;
use App\Models\OrderReturn;
use App\Models\SellerCommissionBill;
use App\Models\SellerNegativeBill;
use App\Models\SellerNegativeOrder;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Order\OrderDataHandleService;

class CommissionManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 账单
     * 当前月的周数列表
     * @param $month Number
     * @return array
     */
    public function getWeeksList($month)
    {
        $weekinfo = [];
        $end_date = TimeRepository::getLocalDate('d', TimeRepository::getLocalStrtoTime($month . ' +1 month -1 day'));
        for ($i = 1; $i < $end_date; $i = $i + 7) {
            $w = TimeRepository::getLocalDate('N', TimeRepository::getLocalStrtoTime($month . '-' . $i));

            /* 账单开始时间 */
            $startTime = TimeRepository::getLocalDate('Y-m-d', TimeRepository::getLocalStrtoTime($month . '-' . $i . ' -' . ($w - 1) . ' days'));

            /* 账单结束时间 */
            $endStrtoTime = TimeRepository::getLocalStrtoTime($month . '-' . $i . ' +' . (7 - $w) . ' days');
            $endTime = TimeRepository::getLocalDate('Y-m-d', $endStrtoTime);

            //当前时间
            $nowTime = TimeRepository::getGmTime();

            /* 当前时间要大于账单结束时间 */
            if ($nowTime > $endStrtoTime) {
                $weekinfo[] = [
                    $startTime,
                    $endTime
                ];
            }
        }

        return $weekinfo;
    }

    /**
     * 账单
     * 类型：每天
     * 生成账单列表
     *
     * @param int $seller_id
     * @param int $cycle
     * @param int $type
     * @return array
     */
    public function getBillPerDay($seller_id = 0, $cycle = 0, $type = 0)
    {
        $day_array = [];

        if ($type == 1) {
            $bill = $this->getNegativeMinmaxTime($seller_id);
        } else {
            $bill = $this->getBillMinmaxTime($seller_id, $cycle);
        }

        $mintime = 0;
        $maxtime = 0;

        if ($bill) {
            $mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
            $maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
        }

        if ($mintime && $maxtime) {
            $min_time = TimeRepository::getLocalDate("Y-m-d", $mintime);
            $max_time = TimeRepository::getLocalDate("Y-m-d", $maxtime);

            $min_time = explode("-", $min_time);
            $max_time = explode("-", $max_time);

            $min_year = intval($min_time[0]);
            $max_year = intval($max_time[0]);
            $min_month = intval($min_time[1]);
            $max_month = intval($max_time[1]);
            $min_day = intval($min_time[2]);
            $max_day = intval($max_time[2]);

            $day_number = 0;
            if ($min_year < $max_year) {
                //开始账单的时间年份比最大的账单结束时间年份要小
                $min_count = 12 - $min_month;
                if ($min_count > 0) {
                    for ($i = $min_month; $i <= 12; $i++) {

                        //获取当月天数
                        $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $min_year);
                        if (!($i == $min_month)) {
                            $day_number += $days;
                        } else {
                            if ($i == $min_month) {
                                $min_day = $days - $min_day;
                                $day_number += $min_day;
                            }
                        }
                    }
                } else {
                    $min_month_day = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $min_month, $min_year);
                    $min_day = $min_month_day - $min_day;
                    $day_number += $min_day;
                }

                for ($i = 1; $i <= $max_month; $i++) {

                    /* 获取当月天数 */
                    $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $max_year);
                    if (!($i == $max_month)) {
                        $day_number += $days;
                    }

                    if ($i == $max_month) {
                        $day_number += $max_day;
                    }
                }
            } else {
                if ($min_month < $max_month) {
                    //开始账单的时间月份比最大的账单结束时间月份要小
                    for ($i = $min_month; $i <= $max_month; $i++) {

                        /* 获取当月天数 */
                        $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $min_year);

                        if (!($i == $min_month || $i == $max_month)) {
                            $day_number += $days;
                        } else {
                            if ($i == $min_month) {
                                $min_day = $days - $min_day;
                                $day_number += $min_day;
                            }

                            if ($i == $max_month) {
                                $day_number += $max_day;
                            }
                        }
                    }
                } else {
                    if ($max_day > $min_day) {
                        $day_number = $max_day - $min_day - 1;
                    }
                }
            }

            if ($day_number > 0) {
                $idx = 0;
                for ($i = 1; $i <= $day_number; $i++) {
                    $bill_day = TimeRepository::getLocalDate("Y-m-d", $mintime + 24 * 60 * 60 * $i);
                    $bill_day_start = $bill_day . " 00:00:00";
                    $bill_day_end = $bill_day . " 23:59:59";

                    $day_start = TimeRepository::getLocalStrtoTime($bill_day_start);
                    $day_end = TimeRepository::getLocalStrtoTime($bill_day_end);

                    if ($type == 1) {
                        $bill_id = $this->getNegativeBillId($seller_id, $day_start, $day_end);
                    } else {
                        $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                    }

                    if (!$bill_id && ($mintime <= $day_start && $maxtime >= $day_end)) {
                        $day_array[$idx]['last_year_start'] = $bill_day_start;
                        $day_array[$idx]['last_year_end'] = $bill_day_end;
                    }

                    $idx++;
                }
            }
        }

        return $day_array;
    }

    /**
     * 账单
     * 类型：每周（七天）
     * 生成账单列表
     *
     * @param int $seller_id
     * @param int $cycle
     * @return array
     */
    public function getBillSevenDay($seller_id = 0, $cycle = 0)
    {
        $bill = $this->getBillMinmaxTime($seller_id, $cycle);

        $min_time = $bill['min_time'] ?? '';

        $day_array = [];
        if (!empty($min_time)) {
            $mintime = $min_time;
            $maxtime = TimeRepository::getGmTime();
        } else {
            $mintime = TimeRepository::getGmTime();
            $maxtime = TimeRepository::getGmTime();
        }

        $week_array = [];
        if ($mintime && $maxtime) {
            $min_time = TimeRepository::getLocalDate("Y-m-d", $mintime);
            $max_time = TimeRepository::getLocalDate("Y-m-d", $maxtime);

            $min_time = explode("-", $min_time);
            $max_time = explode("-", $max_time);

            $min_year = intval($min_time[0]);
            $max_year = intval($max_time[0]);
            $min_month = intval($min_time[1]);
            $max_month = intval($max_time[1]);

            $weeks = [];
            $min_weeks = [];
            $max_weeks = [];
            if ($min_year < $max_year) {
                $min_count = 12 - $min_month;
                for ($i = 0; $i <= $min_count; $i++) {
                    $minmonth = $min_month + $i;
                    $min_weeks[] = $this->getWeeksList($min_year . "-" . $minmonth);
                }

                for ($i = 1; $i <= $max_month; $i++) {
                    $max_weeks[] = $this->getWeeksList($max_year . "-" . $i);
                }

                if ($min_weeks && $max_weeks) {
                    $weeks = array_merge($min_weeks, $max_weeks);
                    $weeks = ArrRepository::getArrCollapse($weeks);
                } elseif ($min_weeks) {
                    $weeks = $min_weeks;
                } elseif ($max_weeks) {
                    $weeks = $max_weeks;
                }
            } else {
                if ($min_month < $max_month) {
                    $m_count = $max_month - $min_month;
                    for ($i = 0; $i <= $m_count; $i++) {
                        $month = $min_month + $i;
                        $weeks[] = $this->getWeeksList($max_year . "-" . $month);
                    }
                }

                $weeks = ArrRepository::getArrCollapse($weeks);

                /* 检测是否跨月日期 start */
                $LastWeeks = BaseRepository::getArrayLast($weeks);
                $lastTime = $LastWeeks[1] ?? '';
                $last_time = explode("-", $lastTime);
                $last_time_day = $last_time[2] ?? 0;
                $last_time_day = (int)$last_time_day;

                $nowTime = TimeRepository::getGmTime();
                $nowYear = (int)TimeRepository::getLocalDate("Y", $nowTime);
                $nowMonth = (int)TimeRepository::getLocalDate("m", $nowTime);
                $nowDay = (int)TimeRepository::getLocalDate("d", $nowTime);
                if (!empty($lastTime) && !empty($last_time_day) && $nowDay < $last_time_day) {

                    $lastTime = TimeRepository::getLocalStrtoTime($lastTime) + (7 * 24 * 3600);
                    $w = TimeRepository::getLocalDate('N', TimeRepository::getLocalStrtoTime($nowYear . '-' . $nowMonth));
                    $addTime = TimeRepository::getLocalStrtoTime($nowYear . '-' . $nowMonth . ' +' . (7 - $w) . ' days');

                    if ($lastTime == $addTime) {
                        $weekInfo[] = [
                            TimeRepository::getLocalDate('Y-m-d', TimeRepository::getLocalStrtoTime($nowYear . '-' . $nowMonth . ' -' . ($w - 1) . ' days')),
                            TimeRepository::getLocalDate('Y-m-d', $addTime)
                        ];

                        $weeks = BaseRepository::getArrayMerge($weeks, $weekInfo);
                    }
                }
                /* 检测是否跨月日期 end */
            }

            $max = $maxtime + 7 * 24 * 3600;
            $time = TimeRepository::getGmTime();

            $newWeeks = [];
            if (empty($weeks) && $time > $max) {
                $between_time = $time - $maxtime;
                $differential_time = ($between_time + 1) / (24 * 3600);
                $diff_num = $differential_time > 0 ? $differential_time / 7 : 0;
                $diff_num = (int)$diff_num;

                if ($diff_num) {
                    for ($i = 1; $i <= $diff_num; $i++) {
                        $key = $i - 1;
                        $newWeeks[$key] = ($maxtime + $i * (7 * 24 * 3600));
                    }
                }
            } else {
                if (empty($weeks) && $mintime && $maxtime) {
                    $between_time = $maxtime - $mintime;
                    $differential_time = ($between_time + 1) / (24 * 3600);
                    $diff_num = $differential_time > 0 ? $differential_time / 7 : 0;

                    if ($diff_num) {
                        for ($i = 1; $i < $diff_num; $i++) {
                            if ($i > 1) {
                                $key = $i - 1;
                                $newWeeks[$key] = ($mintime + $i * (7 * 24 * 3600)) - 24 * 3600;
                            }
                        }
                    }
                }
            }

            if (!empty($newWeeks)) {
                foreach ($newWeeks as $key => $val) {
                    $last_year_start = $val - 6 * 24 * 3600;
                    $last_year_end = $val;

                    $day_array[$key]['last_year_start'] = TimeRepository::getLocalDate("Y-m-d", $last_year_start) . " 00:00:00";
                    $day_array[$key]['last_year_end'] = TimeRepository::getLocalDate("Y-m-d", $last_year_end) . " 23:59:59";
                }

                $day_array = array_values($day_array);
            } else {
                if ($weeks) {
                    $start_mintime = $mintime;

                    foreach ($weeks as $key => $rows) {
                        $start_time = TimeRepository::getLocalStrtoTime($rows[0]);

                        if ($start_mintime <= $start_time) {
                            $week_array[] = $rows;
                        }
                    }
                }

                $idx = 0;
                if ($week_array) {
                    foreach ($week_array as $wkey => $wrow) {
                        $bill_day_start = $wrow[0] . " 00:00:00";
                        $bill_day_end = $wrow[1] . " 23:59:59";

                        $day_start = TimeRepository::getLocalStrtoTime($bill_day_start);
                        $day_end = TimeRepository::getLocalStrtoTime($bill_day_end);

                        $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                        if (!$bill_id) {
                            $day_array[$idx]['last_year_start'] = $bill_day_start;
                            $day_array[$idx]['last_year_end'] = $bill_day_end;
                        }

                        $idx++;
                    }
                }
            }
        }

        return $day_array;
    }

    /**
     * 账单
     * 类型：半个月（15天）
     * 生成账单列表
     *
     * @param int $seller_id
     * @param int $cycle
     * @return array
     */
    public function getBillHalfMonth($seller_id = 0, $cycle = 0)
    {
        $bill = $this->getBillMinmaxTime($seller_id, $cycle);

        $mintime = 0;
        $maxtime = 0;

        $day_array = [];
        if ($bill) {
            $mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
            $maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
        }

        if ($mintime && $maxtime) {
            $min_time = TimeRepository::getLocalDate("Y-m-d", $mintime);
            $max_time = TimeRepository::getLocalDate("Y-m-d", $maxtime);

            $min_time = explode("-", $min_time);
            $max_time = explode("-", $max_time);

            $min_year = intval($min_time[0]);
            $max_year = intval($max_time[0]);
            $min_month = intval($min_time[1]);
            $max_month = intval($max_time[1]);

            $min_month_array = [];
            $max_month_array = [];
            if ($min_year < $max_year) {

                //开始账单的时间年份比最大的账单结束时间年份要小
                $min_count = 12 - $min_month;
                if ($min_count > 0) {
                    for ($i = $min_month; $i <= 12; $i++) {

                        //获取当月天数
                        $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $min_year);
                        $halfMonth = intval($days / 2);

                        if ($i <= 9) {
                            $upper_start_time = $min_year . "-0" . $i . "-01" . " 00:00:00";
                            $upper_end_time = $min_year . "-0" . $i . "-" . $halfMonth . " 23:59:59";

                            $lower_start_time = $min_year . "-0" . $i . "-" . ($halfMonth + 1) . " 00:00:00";
                            $lower_end_time = $min_year . "-0" . $i . "-" . $days . " 23:59:59";
                        } else {
                            $upper_start_time = $min_year . "-" . $i . "-01" . " 00:00:00";
                            $upper_end_time = $min_year . "-" . $i . "-" . $halfMonth . " 23:59:59";

                            $lower_start_time = $min_year . "-" . $i . "-" . ($halfMonth + 1) . " 00:00:00";
                            $lower_end_time = $min_year . "-" . $i . "-" . $days . " 23:59:59";
                        }

                        $min_month_array[] = [
                            'upper' => [
                                'start_time' => $upper_start_time,
                                'end_time' => $upper_end_time
                            ],
                            'lower' => [
                                'start_time' => $lower_start_time,
                                'end_time' => $lower_end_time
                            ]
                        ];
                    }
                } else {
                    $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $min_month, $min_year);
                    $halfMonth = intval($days / 2);

                    $upper_start_time = $min_year . "-12-01" . " 00:00:00";
                    $upper_end_time = $min_year . "-12-" . $halfMonth . " 23:59:59";

                    $lower_start_time = $min_year . "-12-" . ($halfMonth + 1) . " 00:00:00";
                    $lower_end_time = $min_year . "-12-" . $days . " 23:59:59";

                    $min_month_array[] = [
                        'upper' => [
                            'start_time' => $upper_start_time,
                            'end_time' => $upper_end_time
                        ],
                        'lower' => [
                            'start_time' => $lower_start_time,
                            'end_time' => $lower_end_time
                        ]
                    ];
                }

                for ($i = 1; $i <= $max_month; $i++) {

                    /* 获取当月天数 */
                    $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $max_year);
                    $halfMonth = intval($days / 2);

                    if ($i <= 9) {
                        $upper_start_time = $max_year . "-0" . $i . "-01" . " 00:00:00";
                        $upper_end_time = $max_year . "-0" . $i . "-" . $halfMonth . " 23:59:59";

                        $lower_start_time = $max_year . "-0" . $i . "-" . ($halfMonth + 1) . " 00:00:00";
                        $lower_end_time = $max_year . "-0" . $i . "-" . $days . " 23:59:59";
                    } else {
                        $upper_start_time = $max_year . "-" . $i . "-01" . " 00:00:00";
                        $upper_end_time = $max_year . "-" . $i . "-" . $halfMonth . " 23:59:59";

                        $lower_start_time = $max_year . "-" . $i . "-" . ($halfMonth + 1) . " 00:00:00";
                        $lower_end_time = $max_year . "-" . $i . "-" . $days . " 23:59:59";
                    }

                    $max_month_array[] = [
                        'upper' => [
                            'start_time' => $upper_start_time,
                            'end_time' => $upper_end_time
                        ],
                        'lower' => [
                            'start_time' => $lower_start_time,
                            'end_time' => $lower_end_time
                        ]
                    ];
                }

                $month_list = [];
                if ($min_month_array && $max_month_array) {
                    $month_list = array_merge($min_month_array, $max_month_array);
                } elseif ($min_month_array) {
                    $month_list = $min_month_array;
                } elseif ($max_month_array) {
                    $month_list = $max_month_array;
                }

                if ($month_list) {
                    foreach ($month_list as $key => $row) {
                        $upper_day_start = TimeRepository::getLocalStrtoTime($row['upper']['start_time']);
                        $upper_day_end = TimeRepository::getLocalStrtoTime($row['upper']['end_time']);

                        $lower_day_start = TimeRepository::getLocalStrtoTime($row['lower']['start_time']);
                        $lower_day_end = TimeRepository::getLocalStrtoTime($row['lower']['end_time']);

                        $upper_id = $this->getBillId($seller_id, $cycle, $upper_day_start, $upper_day_end);
                        if (!$upper_id && ($mintime <= $upper_day_start && $maxtime >= $upper_day_end)) {
                            $upper_array['last_year_start'] = $row['upper']['start_time'];
                            $upper_array['last_year_end'] = $row['upper']['end_time'];

                            array_push($day_array, $upper_array);
                        }

                        $lower_id = $this->getBillId($seller_id, $cycle, $lower_day_start, $lower_day_end);
                        if (!$lower_id && ($mintime <= $lower_day_start && $maxtime >= $lower_day_end)) {
                            $lower_array['last_year_start'] = $row['lower']['start_time'];
                            $lower_array['last_year_end'] = $row['lower']['end_time'];

                            array_push($day_array, $lower_array);
                        }
                    }
                }
            } else {
                if ($min_month < $max_month) {
                    $month_array = [];

                    //开始账单的时间月份比最大的账单结束时间月份要小
                    for ($i = $min_month; $i <= $max_month; $i++) {

                        /* 获取当月天数 */
                        $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $min_year);
                        $halfMonth = intval($days / 2);

                        if ($i <= 9) {
                            $upper_start_time = $min_year . "-0" . $i . "-01" . " 00:00:00";
                            $upper_end_time = $min_year . "-0" . $i . "-" . $halfMonth . " 23:59:59";

                            $lower_start_time = $min_year . "-0" . $i . "-" . ($halfMonth + 1) . " 00:00:00";
                            $lower_end_time = $min_year . "-0" . $i . "-" . $days . " 23:59:59";
                        } else {
                            $upper_start_time = $min_year . "-" . $i . "-01" . " 00:00:00";
                            $upper_end_time = $min_year . "-" . $i . "-" . $halfMonth . " 23:59:59";

                            $lower_start_time = $min_year . "-" . $i . "-" . ($halfMonth + 1) . " 00:00:00";
                            $lower_end_time = $min_year . "-" . $i . "-" . $days . " 23:59:59";
                        }

                        $month_array[] = [
                            'upper' => [
                                'start_time' => $upper_start_time,
                                'end_time' => $upper_end_time
                            ],
                            'lower' => [
                                'start_time' => $lower_start_time,
                                'end_time' => $lower_end_time
                            ]
                        ];
                    }

                    if ($month_array) {
                        foreach ($month_array as $key => $row) {
                            $upper_day_start = TimeRepository::getLocalStrtoTime($row['upper']['start_time']);
                            $upper_day_end = TimeRepository::getLocalStrtoTime($row['upper']['end_time']);

                            $lower_day_start = TimeRepository::getLocalStrtoTime($row['lower']['start_time']);
                            $lower_day_end = TimeRepository::getLocalStrtoTime($row['lower']['end_time']);

                            $upper_id = $this->getBillId($seller_id, $cycle, $upper_day_start, $upper_day_end);
                            if (!$upper_id && ($mintime <= $upper_day_start && $maxtime >= $upper_day_end)) {
                                $upper_array['last_year_start'] = $row['upper']['start_time'];
                                $upper_array['last_year_end'] = $row['upper']['end_time'];

                                array_push($day_array, $upper_array);
                            }

                            $lower_id = $this->getBillId($seller_id, $cycle, $lower_day_start, $lower_day_end);
                            if (!$lower_id && ($mintime <= $lower_day_start && $maxtime >= $lower_day_end)) {
                                $lower_array['last_year_start'] = $row['lower']['start_time'];
                                $lower_array['last_year_end'] = $row['lower']['end_time'];

                                array_push($day_array, $lower_array);
                            }
                        }
                    }
                }
            }
        }

        return $day_array;
    }

    /**
     * 账单
     * 类型：按月
     * 生成账单列表
     *
     * @param int $seller_id
     * @param int $cycle
     * @return array
     */
    public function getBillOneMonth($seller_id = 0, $cycle = 0)
    {
        $bill = $this->getBillMinmaxTime($seller_id, $cycle);

        $mintime = 0;
        $maxtime = 0;

        $day_array = [];
        if ($bill) {
            $mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
            $maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
        }

        if ($mintime && $maxtime) {
            $min_time = TimeRepository::getLocalDate("Y-m-d", $mintime);
            $max_time = TimeRepository::getLocalDate("Y-m-d", $maxtime);

            $min_time = explode("-", $min_time);
            $max_time = explode("-", $max_time);

            $min_year = intval($min_time[0]);
            $max_year = intval($max_time[0]);
            $min_month = intval($min_time[1]);
            $max_month = intval($max_time[1]);

            if ($min_year < $max_year) {

                //开始账单的时间年份比最大的账单结束时间年份要小
                $iidx = 0;
                $min_array = [];
                $min_count = 12 - $min_month;
                if ($min_count > 0) {
                    for ($i = $min_month; $i <= 12; $i++) {

                        /* 获取当月天数 */
                        $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $min_year);

                        $nowMonth = $i;
                        if ($nowMonth <= 9) {
                            $nowMonth = "0" . $nowMonth;
                        }

                        $last_year_start = $min_year . "-" . $nowMonth . "-01 00:00:00"; //上一个月的第一天
                        $last_year_end = $min_year . "-" . $nowMonth . "-" . $days . " 23:59:59"; //上一个月的最后一天

                        $day_start = TimeRepository::getLocalStrtoTime($last_year_start);
                        $day_end = TimeRepository::getLocalStrtoTime($last_year_end);

                        $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                        if (!$bill_id && ($mintime <= $day_start && $maxtime >= $day_end)) {
                            $min_array[$iidx]['last_year_start'] = $last_year_start;
                            $min_array[$iidx]['last_year_end'] = $last_year_end;
                        }

                        $iidx++;
                    }
                } else {
                    $last_year_start = $min_year . "-12-01 00:00:00"; //上一个月的第一天
                    $last_year_end = $min_year . "-12-31 23:59:59"; //上一个月的最后一天
                    $day_start = TimeRepository::getLocalStrtoTime($last_year_start);
                    $day_end = TimeRepository::getLocalStrtoTime($last_year_end);

                    $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                    if (!$bill_id && ($mintime <= $day_start && $maxtime >= $day_end)) {
                        $min_array[$iidx]['last_year_start'] = $last_year_start;
                        $min_array[$iidx]['last_year_end'] = $last_year_end;
                    }
                }

                $aidx = 0;
                $max_array = [];
                for ($i = 1; $i <= $max_month; $i++) {

                    /* 获取当月天数 */
                    $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $max_year);

                    $nowMonth = $i;
                    if ($nowMonth <= 9) {
                        $nowMonth = "0" . $nowMonth;
                    }

                    $last_year_start = $max_year . "-" . $nowMonth . "-01 00:00:00"; //上一个月的第一天
                    $last_year_end = $max_year . "-" . $nowMonth . "-" . $days . " 23:59:59"; //上一个月的最后一天

                    $day_start = TimeRepository::getLocalStrtoTime($last_year_start);
                    $day_end = TimeRepository::getLocalStrtoTime($last_year_end);

                    $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                    if (!$bill_id && ($mintime <= $day_start && $maxtime >= $day_end)) {
                        $max_array[$aidx]['last_year_start'] = $last_year_start;
                        $max_array[$aidx]['last_year_end'] = $last_year_end;
                    }

                    $aidx++;
                }

                if ($min_array && $max_array) {
                    $day_array = array_merge($min_array, $max_array);
                } elseif ($min_array) {
                    $day_array = $min_array;
                } elseif ($max_array) {
                    $day_array = $max_array;
                }
            } else {
                if ($min_month < $max_month) {
                    $idx = 0;
                    //开始账单的时间月份比最大的账单结束时间月份要小
                    for ($i = $min_month; $i <= $max_month; $i++) {
                        /* 获取当月天数 */
                        $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $min_year);

                        $nowMonth = $i;
                        if ($nowMonth <= 9) {
                            $nowMonth = "0" . $nowMonth;
                        }

                        $last_year_start = $min_year . "-" . $nowMonth . "-01 00:00:00"; //上一个月的第一天
                        $last_year_end = $min_year . "-" . $nowMonth . "-" . $days . " 23:59:59"; //上一个月的最后一天

                        $day_start = TimeRepository::getLocalStrtoTime($last_year_start);
                        $day_end = TimeRepository::getLocalStrtoTime($last_year_end);

                        $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                        if (!$bill_id && ($mintime <= $day_start && $maxtime >= $day_end)) {
                            $day_array[$idx]['last_year_start'] = $last_year_start;
                            $day_array[$idx]['last_year_end'] = $last_year_end;
                        }

                        $idx++;
                    }
                }
            }
        }

        return $day_array;
    }

    /**
     * 账单
     * 类型：季度
     * 生成账单列表
     *
     * @param int $seller_id
     * @param int $cycle
     * @return array
     */
    public function getBillQuarter($seller_id = 0, $cycle = 0)
    {
        $bill = $this->getBillMinmaxTime($seller_id, $cycle);

        $mintime = 0;
        $maxtime = 0;

        $day_array = [];
        if ($bill) {
            $mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
            $maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
        }

        if ($mintime && $maxtime) {
            $min_time = TimeRepository::getLocalDate("Y-m-d", $mintime);
            $max_time = TimeRepository::getLocalDate("Y-m-d", $maxtime);

            $min_time = explode("-", $min_time);
            $max_time = explode("-", $max_time);

            $min_year = intval($min_time[0]);
            $max_year = intval($max_time[0]);
            $min_month = intval($min_time[1]);
            $max_month = intval($max_time[1]);

            if ($min_year < $max_year) {

                //开始账单的时间年份比最大的账单结束时间年份要小
                $iidx = 0;
                $min_array = [];
                $min_count = 12 - $min_month;
                if ($min_count > 0) {
                    for ($i = $min_month; $i <= 12; $i++) {
                        $nowMonth = $i;
                        $month_year = $this->getMonthYear($nowMonth, $min_year);
                        if ($month_year && $month_year['last_year_start'] && $month_year['last_year_end']) {
                            $day_start = TimeRepository::getLocalStrtoTime($month_year['last_year_start']);
                            $day_end = TimeRepository::getLocalStrtoTime($month_year['last_year_end']);

                            $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                            if (!$bill_id && ($mintime < $day_start && $maxtime > $day_end)) {
                                $min_array[$month_year['quarter']]['last_year_start'] = $month_year['last_year_start'];
                                $min_array[$month_year['quarter']]['last_year_end'] = $month_year['last_year_end'];
                            }
                        }

                        $iidx++;
                    }
                } else {
                    $month_year = $this->getMonthYear(12, $min_year);
                    if ($month_year && $month_year['last_year_start'] && $month_year['last_year_end']) {
                        $day_start = TimeRepository::getLocalStrtoTime($month_year['last_year_start']);
                        $day_end = TimeRepository::getLocalStrtoTime($month_year['last_year_end']);

                        $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                        if (!$bill_id && ($mintime < $day_start && $maxtime > $day_end)) {
                            $min_array[$month_year['quarter']]['last_year_start'] = $month_year['last_year_start'];
                            $min_array[$month_year['quarter']]['last_year_end'] = $month_year['last_year_end'];
                        }
                    }
                }

                $aidx = 0;
                $max_array = [];
                for ($i = 1; $i <= $max_month; $i++) {
                    $nowMonth = $i;
                    $month_year = $this->getMonthYear($nowMonth, $max_year);
                    if ($month_year && $month_year['last_year_start'] && $month_year['last_year_end']) {
                        $day_start = TimeRepository::getLocalStrtoTime($month_year['last_year_start']);
                        $day_end = TimeRepository::getLocalStrtoTime($month_year['last_year_end']);

                        $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                        if (!$bill_id && ($mintime < $day_start && $maxtime > $day_end)) {
                            $max_array[$month_year['quarter']]['last_year_start'] = $month_year['last_year_start'];
                            $max_array[$month_year['quarter']]['last_year_end'] = $month_year['last_year_end'];
                        }
                    }

                    $aidx++;
                }

                if ($min_array && $max_array) {
                    $day_array = array_merge($min_array, $max_array);
                } elseif ($min_array) {
                    $day_array = $min_array;
                } elseif ($max_array) {
                    $day_array = $max_array;
                }
            } else {
                if ($min_month < $max_month) {
                    $idx = 0;
                    //开始账单的时间月份比最大的账单结束时间月份要小
                    for ($i = $min_month; $i <= $max_month; $i++) {
                        $nowMonth = $i;
                        $month_year = $this->getMonthYear($nowMonth, $max_year);
                        if ($month_year && $month_year['last_year_start'] && $month_year['last_year_end']) {
                            $day_start = TimeRepository::getLocalStrtoTime($month_year['last_year_start']);
                            $day_end = TimeRepository::getLocalStrtoTime($month_year['last_year_end']);

                            $bill_id = $this->getBillId($seller_id, $cycle, $day_start, $day_end);
                            if (!$bill_id && ($mintime < $day_start && $maxtime > $day_end)) {
                                $day_array[$month_year['quarter']]['last_year_start'] = $month_year['last_year_start'];
                                $day_array[$month_year['quarter']]['last_year_end'] = $month_year['last_year_end'];
                            }
                        }

                        $idx++;
                    }
                }
            }
        }

        return $day_array;
    }

    /**
     * 获取季度月份范围
     *
     * @param int $nowMonth
     * @param int $nowYear
     * @return array
     */
    public function getMonthYear($nowMonth = 0, $nowYear = 0)
    {
        if ($nowMonth == 1 && $nowMonth <= 3) {
            /* 当前第一季度时间段 */
            $last_year_start = $nowYear . "-01-01 00:00:00"; //当前第一季度开始的第一天
            $last_year_end = $nowYear . "-03-31 23:59:59";   //当前第一季度结束的最后一天

            $quarter = 1;
        } elseif ($nowMonth > 3 && $nowMonth <= 6) {
            /* 当前第二季度时间段 */
            $last_year_start = $nowYear . "-04-01 00:00:00"; //当前第二季度开始的第一天
            $last_year_end = $nowYear . "-06-30 23:59:59";   //当前第二季度结束的最后一天

            $quarter = 2;
        } elseif ($nowMonth > 6 && $nowMonth <= 9) {
            /* 当前第三季度时间段 */
            $last_year_start = $nowYear . "-07-01 00:00:00"; //当前第三季度开始的第一天
            $last_year_end = $nowYear . "-09-30 23:59:59";   //当前第三季度结束的最后一天

            $quarter = 3;
        } elseif ($nowMonth > 9 && $nowMonth <= 12) {
            /* 当前第四季度时间段 */
            $last_year_start = $nowYear . "-10-01 00:00:00"; //当前第四季度开始的第一天
            $last_year_end = $nowYear . "-12-31 23:59:59";   //当前第四季度结束的最后一天

            $quarter = 4;
        }

        $arr = [
            'last_year_start' => $last_year_start,
            'last_year_end' => $last_year_end,
            'quarter' => $quarter
        ];

        return $arr;
    }

    /**
     * 账单
     * 类型：半年（6个月）
     * 生成账单列表
     *
     * @param int $seller_id
     * @param int $cycle
     * @return array
     */
    public function getBillHalfYear($seller_id = 0, $cycle = 0)
    {
        $bill = $this->getBillMinmaxTime($seller_id, $cycle);

        $mintime = 0;
        $maxtime = 0;

        $day_array = [];
        if ($bill) {
            $mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
            $maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
        }

        if ($mintime && $maxtime) {
            $min_time = TimeRepository::getLocalDate("Y-m-d", $mintime);
            $max_time = TimeRepository::getLocalDate("Y-m-d", $maxtime);

            $min_time = explode("-", $min_time);
            $max_time = explode("-", $max_time);

            $min_year = intval($min_time[0]);
            $max_year = intval($max_time[0]);

            $year_array = [];
            if ($min_year < $max_year) {
                $year_count = $max_year - $min_year;
                for ($i = 0; $i <= $year_count; $i++) {
                    $year = $min_year + $i;

                    $upper_year_start = $year . "-01-01 00:00:00";
                    $upper_year_end = $year . "-06-30 23:59:59";

                    $upper = [
                        'start_time' => $upper_year_start,
                        'end_time' => $upper_year_end
                    ];

                    $year_array[$i]['upper'] = $upper;

                    $lower_year_start = $year . "-07-01 00:00:00";
                    $lower_year_end = $year . "-12-31 23:59:59";

                    $lower = [
                        'start_time' => $lower_year_start,
                        'end_time' => $lower_year_end
                    ];

                    $year_array[$i]['lower'] = $lower;
                }
            }

            if ($year_array) {
                foreach ($year_array as $key => $row) {
                    $upper_day_start = TimeRepository::getLocalStrtoTime($row['upper']['start_time']);
                    $upper_day_end = TimeRepository::getLocalStrtoTime($row['upper']['end_time']);

                    $lower_day_start = TimeRepository::getLocalStrtoTime($row['lower']['start_time']);
                    $lower_day_end = TimeRepository::getLocalStrtoTime($row['lower']['end_time']);

                    $upper_id = $this->getBillId($seller_id, $cycle, $upper_day_start, $upper_day_end);
                    if (!$upper_id && ($mintime <= $upper_day_start && $maxtime >= $upper_day_end)) {
                        $upper_array['last_year_start'] = $row['upper']['start_time'];
                        $upper_array['last_year_end'] = $row['upper']['end_time'];

                        array_push($day_array, $upper_array);
                    }

                    $lower_id = $this->getBillId($seller_id, $cycle, $lower_day_start, $lower_day_end);
                    if (!$lower_id && ($mintime <= $lower_day_start && $maxtime >= $lower_day_end)) {
                        $lower_array['last_year_start'] = $row['lower']['start_time'];
                        $lower_array['last_year_end'] = $row['lower']['end_time'];

                        array_push($day_array, $lower_array);
                    }
                }
            }
        }

        return $day_array;
    }

    /**
     * 账单
     * 类型：按年
     * 生成账单列表
     *
     * @param int $seller_id
     * @param int $cycle
     * @return array
     */
    public function getBillOneYear($seller_id = 0, $cycle = 0)
    {
        $bill = $this->getBillMinmaxTime($seller_id, $cycle);

        $mintime = 0;
        $maxtime = 0;

        $day_array = [];
        if ($bill) {
            $mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
            $maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
        }

        if ($mintime && $maxtime) {
            $min_time = TimeRepository::getLocalDate("Y-m-d", $mintime);
            $max_time = TimeRepository::getLocalDate("Y-m-d", $maxtime);

            $min_time = explode("-", $min_time);
            $max_time = explode("-", $max_time);

            $min_year = intval($min_time[0]);
            $max_year = intval($max_time[0]);

            $year_array = [];
            if ($min_year < $max_year) {
                $year_count = $max_year - $min_year;
                for ($i = 0; $i <= $year_count; $i++) {
                    $year = $min_year + $i;

                    $year_start = $year . "-01-01 00:00:00";
                    $year_end = $year . "-12-31 23:59:59";

                    $year_array[$i]['last_year_start'] = $year_start;
                    $year_array[$i]['last_year_end'] = $year_end;
                }
            }

            if ($year_array) {
                foreach ($year_array as $key => $row) {
                    $year_start = TimeRepository::getLocalStrtoTime($row['last_year_start']);
                    $year_end = TimeRepository::getLocalStrtoTime($row['last_year_end']);

                    $bill_id = $this->getBillId($seller_id, $cycle, $year_start, $year_end);
                    if (!$bill_id && ($mintime < $year_start && $maxtime > $year_end)) {
                        $day_array[$key]['last_year_start'] = $row['last_year_start'];
                        $day_array[$key]['last_year_end'] = $row['last_year_end'];
                    }
                }
            }
        }

        return $day_array;
    }

    /**
     * 账单
     * 类型：按天数
     * 生成账单列表
     *
     * @param int $seller_id
     * @param int $cycle
     * @return array
     */
    public function getBillDaysNumber($seller_id = 0, $cycle = 0)
    {
        $bill = $this->getBillMinmaxTime($seller_id, $cycle);

        $mintime = 0;
        $maxtime = 0;

        $day_array = [];
        if ($bill) {
            $mintime = isset($bill['min_time']) & !empty($bill['min_time']) ? $bill['min_time'] : $mintime;
            $maxtime = isset($bill['max_time']) & !empty($bill['max_time']) ? $bill['max_time'] : $maxtime;
        }

        if ($mintime && $maxtime) {
            $min_time = TimeRepository::getLocalDate("Y-m-d", $mintime);
            $max_time = TimeRepository::getLocalDate("Y-m-d", $maxtime);

            $min_time = explode("-", $min_time);
            $max_time = explode("-", $max_time);

            $min_year = intval($min_time[0]);
            $max_year = intval($max_time[0]);
            $min_month = intval($min_time[1]);
            $max_month = intval($max_time[1]);
            $min_day = intval($min_time[2]);
            $max_day = intval($max_time[2]);

            $day_number = 0;
            $server_day_number = MerchantsServer::where('user_id', $seller_id)->value('day_number');

            $year_array = [];
            if ($min_year < $max_year) {

                //开始账单的时间年份比最大的账单结束时间年份要小
                $min_count = 12 - $min_month;
                if ($min_count > 0) {
                    for ($i = $min_month; $i <= 12; $i++) {

                        //获取当月天数
                        $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $min_year);
                        if (!($i == $min_month)) {
                            $day_number += $days;
                        } else {
                            if ($i == $min_month) {
                                $minDay = $days - $min_day;
                                $day_number += $minDay;
                            }
                        }
                    }
                } else {
                    $min_month_day = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $min_month, $min_year);
                    $minDay = $min_month_day - $min_day;
                    $day_number += $minDay;
                }

                for ($i = 1; $i <= $max_month; $i++) {

                    /* 获取当月天数 */
                    $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $i, $max_year);
                    if (!($i == $max_month)) {
                        $day_number += $days;
                    }

                    if ($i == $max_month) {
                        $maxday = $max_day;
                        $day_number += $maxday;
                    }
                }

                if ($day_number && $server_day_number && $day_number > $server_day_number) {
                    $number = round($day_number / $server_day_number);

                    for ($i = 0; $i <= $number; $i++) {
                        $year_start = TimeRepository::getLocalDate("Y-m-d", TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate("Y-m-d", $bill['min_time'])) + (($i + 1) * ($server_day_number - 1) - ($server_day_number) + 1) * 24 * 60 * 60);
                        $year_end = TimeRepository::getLocalDate("Y-m-d", TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate("Y-m-d", $bill['min_time'])) + ($i + 1) * ($server_day_number - 1) * 24 * 60 * 60);

                        $year_start = $year_start . " 00:00:00";
                        $year_end = $year_end . " 23:59:59";

                        $year_array[$i]['last_year_start'] = $year_start;
                        $year_array[$i]['last_year_end'] = $year_end;
                    }
                }
            } else {
                if ($min_month < $max_month) {
                    $m_count = $max_month - $min_month;
                    for ($i = 0; $i <= $m_count; $i++) {
                        $month = $min_month + $i;

                        /* 获取当月天数 */
                        $days = TimeRepository::getCalDaysInMonth(CAL_GREGORIAN, $month, $min_year);

                        if (!($month == $min_month || $month == $max_month)) {
                            $day_number += $days;
                        } else {
                            if ($month == $min_month) {
                                $minDay = $days - $min_day;
                                $day_number += $minDay;
                            }

                            if ($month == $max_month) {
                                $maxday = $max_day;
                                $day_number += $maxday;
                            }
                        }
                    }

                    if ($day_number && $server_day_number && $day_number > $server_day_number) {
                        $number = round($day_number / $server_day_number);

                        for ($i = 0; $i <= $number; $i++) {
                            $year_start = TimeRepository::getLocalDate("Y-m-d", TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate("Y-m-d", $bill['min_time'])) + (($i + 1) * ($server_day_number - 1) - ($server_day_number) + 1) * 24 * 60 * 60);
                            $year_end = TimeRepository::getLocalDate("Y-m-d", TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate("Y-m-d", $bill['min_time'])) + ($i + 1) * ($server_day_number - 1) * 24 * 60 * 60);

                            $year_start = $year_start . " 00:00:00";
                            $year_end = $year_end . " 23:59:59";

                            $year_array[$i]['last_year_start'] = $year_start;
                            $year_array[$i]['last_year_end'] = $year_end;
                        }
                    }
                }
            }

            if ($year_array) {
                foreach ($year_array as $key => $row) {
                    $year_start = TimeRepository::getLocalStrtoTime($row['last_year_start']);
                    $year_end = TimeRepository::getLocalStrtoTime($row['last_year_end']);

                    $bill_id = $this->getBillId($seller_id, $cycle, $year_start, $year_end);
                    if (!$bill_id && ($mintime < $year_start && $maxtime > $year_end)) {
                        $day_array[$key]['last_year_start'] = $row['last_year_start'];
                        $day_array[$key]['last_year_end'] = $row['last_year_end'];
                    }
                }
            }
        }

        return $day_array;
    }

    /**
     * 账单
     * 获取商家账单最小开始时间
     * 获取商家账单最大结束时间
     *
     * @param int $seller_id
     * @param int $cycle
     * @return mixed
     */
    public function getBillMinmaxTime($seller_id = 0, $cycle = 0)
    {
        $bill = SellerCommissionBill::selectRaw("MIN(start_time) AS min_time, MAX(end_time) AS max_time")
            ->where('seller_id', $seller_id)
            ->where('bill_cycle', $cycle);

        $bill = BaseRepository::getToArrayFirst($bill);

        if ($bill && is_null($bill['min_time']) && is_null($bill['max_time'])) {
            $bill = SellerCommissionBill::select('id', 'bill_sn', 'start_time AS min_time', 'end_time AS max_time')
                ->where('seller_id', $seller_id)
                ->orderBy('id', 'desc')
                ->limit(1);
            $bill = BaseRepository::getToArrayFirst($bill);
        }

        return $bill;
    }

    /**
     * 账单ID
     * 按类型，根据开始时间和结束时间
     *
     * @param $seller_id
     * @param $cycle
     * @param $day_start
     * @param $day_end
     * @return int
     */
    public function getBillId($seller_id, $cycle, $day_start, $day_end)
    {
        $id = SellerCommissionBill::where('start_time', $day_start)
            ->where('end_time', $day_end)
            ->where('bill_cycle', $cycle)
            ->where('seller_id', $seller_id)
            ->value('id');
        $id = $id ? $id : 0;

        return $id;
    }

    /**
     * 负账单ID
     *
     * @param int $seller_id
     * @param int $day_start
     * @param int $day_end
     * @return int
     */
    public function getNegativeBillId($seller_id = 0, $day_start = 0, $day_end = 0)
    {
        $id = SellerNegativeBill::where('start_time', $day_start)
            ->where('end_time', $day_end)
            ->where('seller_id', $seller_id)
            ->value('id');
        $id = $id ? $id : 0;

        return $id;
    }

    /**
     * 账单
     * 获取商家账单最小开始时间
     * 获取商家账单最大结束时间
     *
     * @param int $seller_id
     * @return mixed
     */
    public function getNegativeMinmaxTime($seller_id = 0)
    {
        $bill = SellerNegativeBill::selectRaw('MIN(start_time) AS min_time, MAX(end_time) AS max_time')->where('seller_id', $seller_id);
        $bill = BaseRepository::getToArrayFirst($bill);

        return $bill;
    }

    /**
     * 负账单
     *
     * @param int $seller_id
     * @param array $negativeOrder
     */
    public function negativeBill($seller_id = 0, $negativeOrder = [])
    {
        /* 每天出负账单 start */
        if (empty($negativeOrder)) {
            $day_array = $this->getBillPerDay($seller_id, 0, 1);
        } else {
            $day_array = [];
        }

        if (empty($day_array)) {
            $last_year_start = TimeRepository::getLocalDate("Y-m-d 00:00:00", TimeRepository::getLocalStrtoTime("-1 day"));
            $last_year_end = TimeRepository::getLocalDate("Y-m-d 23:59:59", TimeRepository::getLocalStrtoTime("-1 day"));
            if ($negativeOrder) {
                $time = $negativeOrder['add_time'];
                $min_time = TimeRepository::getLocalDate("Y-m-d", $time);
                $max_time = TimeRepository::getLocalDate("Y-m-d");

                $day_array = TimeRepository::generateEveryDay($min_time, $max_time);
            } else {
                $day_array[0]['last_year_start'] = $last_year_start;
                $day_array[0]['last_year_end'] = $last_year_end;
            }
        }

        if ($day_array) {
            foreach ($day_array as $keys => $rows) {

                $rows['divide_channel'] = 0;

                $divide = $rows;
                $divide['divide_channel'] = 1;

                $arr = [
                    $rows,
                    $divide
                ];

                foreach ($arr as $avalue) {
                    $last_year_start = TimeRepository::getLocalStrtoTime($avalue['last_year_start']); //时间戳
                    $last_year_end = TimeRepository::getLocalStrtoTime($avalue['last_year_end']); //时间戳

                    $billInfo = SellerNegativeBill::select('id', 'commission_bill_id', 'actual_deducted')->where('seller_id', $seller_id)
                        ->where('start_time', $last_year_start)
                        ->where('end_time', $last_year_end)
                        ->where('divide_channel', $avalue['divide_channel']);
                    $billInfo = BaseRepository::getToArrayFirst($billInfo);

                    if (empty($billInfo)) {
                        $bill_sn = $this->getBillOrderSn();

                        $other = [
                            'seller_id' => $seller_id,
                            'bill_sn' => $bill_sn,
                            'start_time' => $last_year_start,
                            'end_time' => $last_year_end,
                            'divide_channel' => $avalue['divide_channel'],
                        ];

                        $negative_id = SellerNegativeBill::insertGetId($other);
                    } else {
                        $negative_id = empty($billInfo['commission_bill_id']) && $billInfo['actual_deducted'] == 0 ? $billInfo['id'] : 0;
                    }

                    if ($negative_id > 0) {
                        $negative_order_list = OrderReturn::where('refound_status', 1)
                            ->whereIn('return_type', [1, 3])
                            ->where('divide_channel', $avalue['divide_channel'])
                            ->where('negative_id', 0);

                        $negative_order_list = $negative_order_list->where(function ($query) use ($seller_id, $avalue) {
                            $query = $query->where(function ($query) use ($seller_id, $avalue) {
                                $query->whereHasIn('orderInfo', function ($query) use ($seller_id, $avalue) {
                                    $query->where('ru_id', $seller_id)
                                        ->where('shipping_status', SS_RECEIVED)
                                        ->where('divide_channel', $avalue['divide_channel'])
                                        ->whereIn('pay_status', [PS_REFOUND_PART, PS_PAYED]);
                                });
                            });

                            $query->orWhere(function ($query) use ($seller_id, $avalue) {
                                $query->whereHasIn('getSellerBillOrder', function ($query) use ($seller_id, $avalue) {
                                    $query->where('seller_id', $seller_id)->where('bill_id', '>', 0);
                                });
                            });
                        });

                        $negative_order_list = $negative_order_list->doesntHaveIn('getSellerNegativeOrder');

                        $negative_order_list = $negative_order_list->doesntHaveIn('getSellerBillOrderReturn');

                        $negative_order_list = BaseRepository::getToArrayGet($negative_order_list);

                        if ($negative_order_list) {

                            $orderGoodsList = [];
                            if (file_exists(MOBILE_DRP)) {
                                $recIdList = BaseRepository::getKeyPluck($negative_order_list, 'rec_id');
                                $orderGoodsList = OrderDataHandleService::orderGoodsDataList($recIdList, ['rec_id', 'drp_money']);
                            }

                            foreach ($negative_order_list as $idx => $val) {
                                $negativeCount = SellerNegativeOrder::where('ret_id', $val['ret_id'])
                                    ->where('divide_channel', $avalue['divide_channel'])
                                    ->count();

                                if ($negativeCount == 0) {
                                    $return_amount = $val['actual_return'] - $val['return_shipping_fee'] - $val['return_rate_price'];

                                    $commission = $this->commissionNegativeOrderList($val['ret_id'], $return_amount);

                                    $other = array(
                                        'negative_id' => $negative_id,
                                        'order_id' => $val['order_id'],
                                        'order_sn' => $val['order_sn'],
                                        'ret_id' => $val['ret_id'],
                                        'return_sn' => $val['return_sn'],
                                        'seller_id' => $seller_id,
                                        'return_amount' => $return_amount,
                                        'return_shippingfee' => $val['return_shipping_fee'],
                                        'return_rate_price' => $val['return_rate_price'],
                                        'add_time' => $val['return_time'],
                                        'seller_proportion' => $commission['seller_proportion'],
                                        'cat_proportion' => $commission['cat_proportion'],
                                        'commission_rate' => $commission['commission_rate'],
                                        'gain_commission' => $commission['gain_commission'],
                                        'should_amount' => $commission['should_amount'],
                                        'divide_channel' => $val['divide_channel']
                                    );

                                    $orderGoods = $orderGoodsList[$val['rec_id']] ?? [];

                                    // 记录分销金额
                                    if (file_exists(MOBILE_DRP) && !empty($orderGoods)) {
                                        if ($commission['should_amount'] >= $orderGoods['drp_money']) {
                                            $drp_money = $orderGoods['drp_money'];
                                        } else {
                                            $drp_money = $commission['should_amount'];
                                        }

                                        $other['drp_money'] = $drp_money;
                                    }

                                    SellerNegativeOrder::insert($other);

                                    OrderReturn::where('ret_id', $val['ret_id'])
                                        ->where('divide_channel', $val['divide_channel'])
                                        ->update([
                                            'negative_id' => $negative_id
                                        ]);
                                }
                            }
                        }

                        SellerNegativeOrder::where('add_time', '<=', $last_year_end)
                            ->where('seller_id', $seller_id)
                            ->where('negative_id', 0)
                            ->whereIn('settle_accounts', [0, 2])
                            ->where('divide_channel', $avalue['divide_channel'])
                            ->where(function ($query) use ($seller_id, $avalue) {
                                $query = $query->where(function ($query) use ($seller_id, $avalue) {

                                    $query = $query->where('settle_accounts', 0);

                                    $query->whereHasIn('orderInfo', function ($query) use ($seller_id, $avalue) {
                                        $query->where('ru_id', $seller_id)
                                            ->where('shipping_status', SS_RECEIVED)
                                            ->where('divide_channel', $avalue['divide_channel'])
                                            ->whereIn('pay_status', [PS_REFOUND_PART, PS_PAYED]);
                                    });
                                });

                                $query->orWhere(function ($query) use ($seller_id, $avalue) {

                                    $query = $query->where('settle_accounts', 2);

                                    $query->where(function ($query) use ($seller_id, $avalue) {
                                        $query->whereHasIn('getSellerBillOrder', function ($query) use ($seller_id, $avalue) {
                                            $query->where('seller_id', $seller_id)->where('bill_id', '>', 0);
                                        });
                                    });
                                });
                            })
                            ->update([
                                'negative_id' => $negative_id
                            ]);

                        $field = '';
                        if (file_exists(MOBILE_DRP)) {
                            $field = ", SUM(drp_money) AS drp_money_total";
                        }

                        $negative_order = SellerNegativeOrder::selectRaw('SUM(return_amount) AS amount_total, SUM(return_shippingfee) AS shippingfee_total, SUM(return_rate_price) AS rate_total, SUM(should_amount) AS should_total, GROUP_CONCAT(ret_id) AS ret_id' . $field)
                            ->where('negative_id', $negative_id)
                            ->where('divide_channel', $avalue['divide_channel'])
                            ->where('seller_id', $seller_id);

                        $negative_order = BaseRepository::getToArrayFirst($negative_order);

                        if ($negative_order) {
                            $ret_id = BaseRepository::getExplode($negative_order['ret_id']);

                            OrderReturn::whereIn('ret_id', $ret_id)
                                ->where('negative_id', 0)
                                ->where('divide_channel', $avalue['divide_channel'])
                                ->update([
                                    'negative_id' => $negative_id
                                ]);

                            //分销金额
                            $drp_money = $negative_order['drp_money_total'] ?? 0;

                            $amount_total = $this->dscRepository->changeFloat($negative_order['amount_total'] ?? 0);
                            $shippingfee_total = $this->dscRepository->changeFloat($negative_order['shippingfee_total'] ?? 0);
                            $should_total = $this->dscRepository->changeFloat($negative_order['should_total'] ?? 0);
                            $actual_deducted = $shippingfee_total + $should_total;
                            $actual_deducted = $actual_deducted >= $drp_money ? $actual_deducted - $drp_money : 0;

                            /* 更新负账单金额 */
                            $other = [
                                'return_amount' => $amount_total,
                                'return_shippingfee' => $shippingfee_total,
                                'return_rate_price' => $should_total,
                                'actual_deducted' => $actual_deducted
                            ];

                            if (file_exists(MOBILE_DRP)) {
                                $other['drp_money'] = $drp_money;
                            }

                            SellerNegativeBill::where('id', $negative_id)
                                ->where('seller_id', $seller_id)
                                ->where('divide_channel', $avalue['divide_channel'])
                                ->update($other);
                        }
                    }
                }
            }
        }
        /* 每天出负账单 end */
    }

    /**
     * 负账单订单信息
     *
     * @param int $ret_id
     * @param int $refund_amount
     * @return array|\Illuminate\Database\Eloquent\Builder
     */
    public function commissionNegativeOrderList($ret_id = 0, $refund_amount = 0)
    {
        if ($ret_id > 0) {
            $order_list = OrderReturn::where('ret_id', $ret_id);

            $order_list = $order_list->with([
                'getSellerBillGoods' => function ($query) {
                    $query->select('rec_id', 'commission_rate', 'proportion');
                },
                'getSellerBillOrder' => function ($query) {
                    $query = $query->select('bill_id', 'order_id', 'seller_id');
                    $query->with([
                        'getSellerCommissionBill' => function ($query) {
                            $query->select('id', 'commission_model', 'proportion');
                        }
                    ]);
                },
                'orderInfo' => function ($query) {
                    $query->select('order_id', 'ru_id');
                }
            ]);

            $order = BaseRepository::getToArrayFirst($order_list);

            $bill_goods = $order['get_seller_bill_goods'] ?? [];
            $cat_proportion = $bill_goods['proportion'] ?? 0;
            $commission_rate = $bill_goods['commission_rate'] ?? 0;

            $order['seller_id'] = $order['order_info']['ru_id'] ?? 0;
            $order['return_amount'] = $refund_amount;

            $commission = $this->commissionNegative($order, $cat_proportion, $commission_rate);

            $other = [
                'seller_proportion' => $commission['seller_proportion'],
                'cat_proportion' => $cat_proportion,
                'commission_rate' => $commission_rate,
                'gain_commission' => $commission['gain_commission'],
                'should_amount' => $commission['should_amount']
            ];

            return $other;
        } else {
            $order_list = SellerNegativeOrder::query();

            $order_list = $order_list->with([
                'getOrderReturn' => function ($query) {
                    $query = $query->select('ret_id', 'rec_id');
                    $query->with([
                        'getSellerBillGoods' => function ($query) {
                            $query->select('rec_id', 'commission_rate', 'proportion');
                        }
                    ]);
                },
                'getSellerBillOrder' => function ($query) {
                    $query = $query->select('bill_id', 'order_id');
                    $query->with([
                        'getSellerCommissionBill' => function ($query) {
                            $query->select('id', 'commission_model', 'proportion');
                        }
                    ]);
                }
            ]);

            $order_list = BaseRepository::getToArrayGet($order_list);
            return $order_list;
        }
    }

    /**
     * 负账单应收和应结佣金信息
     *
     * @param array $order
     * @param int $cat_proportion
     * @param int $commission_rate
     * @return array
     */
    public function commissionNegative($order = [], $cat_proportion = 0, $commission_rate = 0)
    {
        $proportion = 1;
        if ($cat_proportion == 0 && $commission_rate == 0) {
            $commission_bill = $order['get_seller_bill_order']['get_seller_commission_bill'] ?? [];

            if (empty($commission_bill)) {
                $merchantsServer = MerchantsServer::where('user_id', $order['seller_id']);
                $merchantsServer = $merchantsServer->with([
                    'getMerchantsPercent'
                ]);

                $merchantsServer = $merchantsServer->first();
                $merchantsServer = $merchantsServer ? $merchantsServer->toArray() : [];

                $proportion = $merchantsServer['get_merchants_percent']['percent_value'] ?? 100;
            } else {
                $proportion = $commission_bill['proportion'] ?? 100;
            }

            /* 商品店铺佣金比例 */
            $proportion = $proportion / 100;
        }

        $seller_proportion = $proportion;

        if ($commission_rate > 0) {
            /* 商品佣金比例 */
            $should_amount = $order['return_amount'] * $commission_rate;
            $gain_commission = $order['return_amount'] - $should_amount;
        } else {
            if ($cat_proportion > 0) {
                /* 商品分类佣金比例 */
                $proportion = $cat_proportion;
            }

            $should_amount = $order['return_amount'] * $proportion;
            $gain_commission = $order['return_amount'] - $should_amount;
        }

        return [
            'seller_proportion' => $seller_proportion,
            'should_amount' => $should_amount,
            'gain_commission' => $gain_commission
        ];
    }

    /**
     * 得到新订单号
     *
     * @return string
     */
    public function getBillOrderSn()
    {
        $time = explode(" ", microtime());
        $time = $time[1] . ($time[0] * 1000);
        $time = explode(".", $time);
        $time = isset($time[1]) ? $time[1] : 0;
        $time = TimeRepository::getLocalDate('YmdHis') + $time;

        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);
        return $time . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}