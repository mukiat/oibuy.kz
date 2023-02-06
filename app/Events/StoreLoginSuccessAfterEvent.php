<?php


namespace App\Events;


class StoreLoginSuccessAfterEvent
{

    public $store_model;

    /**
     * StoreLoginSuccessAfterEvent constructor.
     * @param $store_model
     */
    public function __construct($store_model)
    {
        $this->store_model = $store_model;
    }

}
