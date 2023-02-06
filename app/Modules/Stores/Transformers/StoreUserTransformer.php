<?php

namespace App\Modules\Stores\Transformers;

use App\Api\Foundation\Transformer\Transformer;

class StoreUserTransformer extends Transformer
{
    public function transform($item = [])
    {
        return [
            'id' => $item['id'],
            'stores_user' => $item['stores_user'],
            'tel' => $item['tel'],
            'email' => $item['email'],
            'store_user_img' => $item['store_user_img'],
            'last_login' => $item['last_login'],
        ];
    }

}