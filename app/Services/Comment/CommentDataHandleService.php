<?php

namespace App\Services\Comment;

use App\Models\Comment;
use App\Models\CommentImg;
use App\Repositories\Common\BaseRepository;

class CommentDataHandleService
{
    /**
     * 会员列表信息
     *
     * @param array $goods_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getGoodsCommentDataList($goods_id = [], $data = [], $limit = 0)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = array_unique($goods_id);

        $data = empty($data) ? "*" : $data;

        $goods_id = array_unique($goods_id);

        $res = Comment::select($data)
            ->whereIn('id_value', $goods_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['comment_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 会员列表信息
     *
     * @param array $comment_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getCommentImgDataList($comment_id = [], $data = [], $limit = 0)
    {
        $comment_id = BaseRepository::getExplode($comment_id);

        if (empty($comment_id)) {
            return [];
        }

        $comment_id = array_unique($comment_id);

        $data = empty($data) ? "*" : $data;

        $comment_id = array_unique($comment_id);

        $res = CommentImg::select($data)
            ->whereIn('comment_id', $comment_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['comment_id']][] = $val;
            }
        }

        return $arr;
    }
}