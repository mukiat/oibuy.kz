<?php


namespace App\Events;


class StoreTopMenuExtendEvent
{

    public $store_top_menu_extend;

    /**
     * StoreSessionAssignEvent constructor.
     * @param $store_top_menu_extend
     */
    public function __construct($store_top_menu_extend = null)
    {
    	$this->store_top_menu_extend = $store_top_menu_extend;
    }

}
