<?php

namespace App\Services\Goods;

use App\Models\Comment;
use App\Repositories\Common\BaseRepository;

class GoodsCommentService
{
    /**
     * 获取评价买家对商品印象词的个数
     *
     * @param int $goods_id
     * @param string $txt
     * @return int
     */
    public function commentGoodsTagNum($goods_id = 0, $txt = '')
    {
        $txt = !empty($txt) ? trim($txt) : '';

        $res = Comment::where('id_value', $goods_id);
        $res = BaseRepository::getToArrayGet($res);
        $str = BaseRepository::getKeyPluck($res, 'goods_tag');
        $str = BaseRepository::getImplode($str);

        if ($str && $txt) {
            $num = substr_count($str, $txt);
        } else {
            $num = 0;
        }

        return $num;
    }
}
