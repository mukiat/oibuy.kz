<?php

namespace App\Api\Transformers;

use App\Api\Foundation\Transformer\Transformer;

class UserTransformer extends Transformer
{
    public function transform($item)
    {
        return [
            'id' => $item['user_id'],
            'name' => $item['nick_name'],
            'username' => $item['user_name'],
            'mobile' => $item['mobile_phone'],
            'email' => $item['email'],
            'avatar' => $item['user_picture'],
            'question' => $item['question'],
            'answer' => $item['answer'],
            'sex' => $item['sex'],
            'birthday' => ($item['birthday'] == '1000-01-01') ? '2000-01-01' : $item['birthday'],
            'user_money' => $item['user_money'],
            'bonus' => isset($item['get_user_bonus_list']) ? count($item['get_user_bonus_list']) : 0,
            'coupons_num' => isset($item['get_coupons_user_list']) ? count($item['get_coupons_user_list']) : 0,
            'value_card_num' => isset($item['value_card_num']) ? count($item['value_card_num']) : 0,
            'frozen_money' => $item['frozen_money'],
            'pay_points' => $item['pay_points'],
            'rank_points' => $item['rank_points'],
            'address_id' => $item['address_id'],
            'user_rank' => $item['user_rank'],
            'drp_parent_id' => $item['drp_parent_id'],
            'parent_id' => $item['parent_id'],
            'qq' => $item['qq'],
            'office_phone' => $item['office_phone'],
            'telephone' => $item['home_phone'],
            'is_validated' => $item['is_validated'],
            'credit_line' => $item['credit_line'],
            'pay_count' => $item['nopay'] ?? 0,
            'team_num' => $item['team_num'] ?? 0,
            'confirmed_count' => $item['nogoods'] ?? 0,
            'not_comment' => $item['not_comment'] ?? 0,
            'return_count' => $item['return_count'] ?? 0,
        ];
    }

    public function transformInfo($item = [])
    {
        return [
            'id' => $item['user_id'],
            'name' => $item['nick_name'],
            'user_name' => $item['user_name'],
            'avatar' => $item['user_picture'],
            'user_money' => $item['user_money'],
            'user_rank' => $item['user_rank'],
        ];
    }
}
