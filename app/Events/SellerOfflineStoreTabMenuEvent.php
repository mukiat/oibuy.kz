<?php


namespace App\Events;


class SellerOfflineStoreTabMenuEvent
{

    public $tab_menu;

    /**
     * SellerOfflineStoreTabMenuEvent constructor.
     * @param $tab_menu
     */
    public function __construct($tab_menu)
    {
        $this->tab_menu = $tab_menu;
    }

}
