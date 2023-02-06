<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Exceptions\HttpException;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\ValueCard\ValueCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class ValueCardController
 * @package App\Api\Controllers
 */
class ValueCardController extends Controller
{
    protected $valueCardService;

    public function __construct(
        ValueCardService $valueCardService
    )
    {
        $this->valueCardService = $valueCardService;
    }

    /**
     * 储值卡列表
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'page' => 'required|integer',
            'size' => 'required|integer',
        ]);

        $rec_id = $request->input('rec_id', ''); // 购物车商品
        $rec_id = BaseRepository::getExplode($rec_id);
        $rec_id = DscEncryptRepository::filterValInt($rec_id);

        $use_type = $request->input('use_type', 1); // 1 可用卡 0 不可用卡
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        $user_id = $this->authorization();
        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $card = $this->valueCardService->getUserValueCardList($user_id, $page, $size, $rec_id);

        $time = TimeRepository::getGmTime();
        $sql = [
            'whereIn' => [
                [
                    'name' => 'use_status',
                    'value' => [1, 3]
                ],
            ],
            'where' => [
                [
                    'name' => 'use_card_money',
                    'value' => 0,
                    'condition' => '>' //条件查询
                ],
                [
                    'name' => 'end_time',
                    'value' => $time,
                    'condition' => '>' //条件查询
                ]
            ]
        ];
        $use_card_list = BaseRepository::getArraySqlGet($card['card_list'], $sql);

        $sql = [
            'where' => [
                [
                    'name' => 'is_rec',
                    'value' => 1
                ],
                [
                    'name' => 'end_time',
                    'value' => $time,
                    'condition' => '>' //条件查询
                ]
            ]
        ];
        $use_card_rec_list = BaseRepository::getArraySqlGet($card['card_list'], $sql);
        $card_list = BaseRepository::getArrayMerge($use_card_list, $use_card_rec_list);
        $card_list = BaseRepository::getArrayUnique($card_list, 'vid');

        /* 购物车 */
        if (!empty($rec_id)) {
            $sql = [
                'where' => [
                    [
                        'name' => 'use_card_money',
                        'value' => 0,
                        'condition' => '>' //条件查询
                    ]
                ]
            ];
            $card_list = BaseRepository::getArraySqlGet($card_list, $sql);
        }

        if ($use_type != 1) {
            $notVid = BaseRepository::getKeyPluck($card_list, 'vid');

            $sql = [
                'whereNotIn' => [
                    [
                        'name' => 'vid',
                        'value' => $notVid
                    ],
                ]
            ];
            $card_list = BaseRepository::getArraySqlGet($card['card_list'], $sql);
        }

        /* 分页 */
        $list = BaseRepository::getPaginate($card_list, $size);
        $card_list = $list['data'] ? array_values($list['data']) : [];

        $data = [
            'card_list' => $card_list, //可用卡列表
            'use_card_count' => $card['use_card_count'], //可用卡总数
            'not_use_card_count' => $card['not_use_card_count'], //不可用卡总数
            'card_total' => StrRepository::priceFormat($card['card_total']), //可用卡总金额
        ];

        return $this->succeed($data);
    }

    /**
     * 绑定储值卡
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function addvaluecard(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'vc_num' => 'required',
            'vc_password' => 'required',
        ]);

        $vc_num = $request->post('vc_num', '');
        $vc_password = $request->post('vc_password', '');

        $user_id = $this->authorization();
        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->valueCardService->addCard($user_id, $vc_num, $vc_password);

        return $this->succeed($data);
    }

    /**
     * 储值使用详情
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function detail(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'vc_id' => 'required|integer',
        ]);

        $vc_id = $request->get('vc_id', 1);

        $user_id = $this->authorization();
        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $value_card = DB::table('value_card')->where('vid', $vc_id)->select('user_id')->first();
        if ($value_card->user_id != $user_id) {
            return $this->setErrorCode(422)->failed(trans('user.unauthorized_access'));
        }

        $data = $this->valueCardService->cardDetail($user_id, $vc_id);

        return $this->succeed($data);
    }

    /**
     * 充值卡绑定储值卡
     *
     * @param Request $request
     * @return array|JsonResponse
     * @throws ValidationException
     */
    public function deposit(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'vc_id' => 'required|integer',
            'vc_num' => 'required',
            'vc_password' => 'required',
        ]);
        $vc_num = $request->post('vc_num', '');
        $vc_password = $request->post('vc_password', '');
        $vc_id = $request->post('vc_id', 0);


        $user_id = $this->authorization();
        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        try {
            $data = $this->valueCardService->deposit($user_id, $vc_id, $vc_num, $vc_password);
        } catch (HttpException $httpException) {
            return ['error' => 1, 'msg' => $httpException->getMessage()];
        }

        return $this->succeed($data);
    }
}
