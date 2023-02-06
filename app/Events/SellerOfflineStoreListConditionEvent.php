<?php


namespace App\Events;


class SellerOfflineStoreListConditionEvent
{

    public $where;

    /**
     * SellerOfflineStoreListConditionEvent constructor.
     * @param $where
     */
    public function __construct($where)
    {
        $this->where = $where;
    }

}
