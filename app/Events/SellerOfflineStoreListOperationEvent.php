<?php


namespace App\Events;


class SellerOfflineStoreListOperationEvent
{
	public $store_info;
    public $operation;

    /**
     * SellerOfflineStoreListOperationEvent constructor.
     * @param array $store_info
     */
    public function __construct($store_info = [])
    {
        $this->store_info = $store_info;
        $this->operation = null;
    }

}
