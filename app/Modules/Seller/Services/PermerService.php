<?php

namespace App\Modules\Seller\Services;

use App\Models\MerchantsStepsFields;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

/**
 * 商家入驻 个人会员
 * Class PermerService
 * @package App\Modules\Seller\Services
 */
class PermerService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * @param int $user_id
     * @return mixed
     */
    public function getPersonal($user_id = 0)
    {
        return MerchantsStepsFields::where('user_id', $user_id)->value('is_personal');
    }

    //获取个人会员申请商家入驻信息 start -- zhuo
    public function getStepsUserShopInfoPersonal($user_id = 0)
    {
        $personal = MerchantsStepsFields::where('user_id', $user_id);
        $personal = BaseRepository::getToArrayFirst($personal);

        $arr = [
            'sp_id' => 0,
            'process_title' => '个人信息提交',
            'steps_title' =>
                [
                    [
                        'tid' => 0,
                        'fields_titles' => '个人入驻资料',
                        'steps_style' => 0,
                        'titles_annotation' => '',
                        'cententFields' => [],
                        'parentType' => []
                    ]
                ]
        ];

        $arr['steps_title'][0]['cententFields'] = [
            [
                'id' => 0,
                'textFields' => 'id_card_img_one_fileImg',
                'fieldsDateType' => 'VARCHAR',
                'fieldsLength' => '255',
                'fieldsNotnull' => 'NOT NULL',
                'fieldsFormName' => '身份证正面',
                'fieldsCoding' => 'UTF8',
                'fields_sort' => '1',
                'will_choose' => '1',
                'titles_centents' => $this->dscRepository->getImagePath($personal['id_card_img_one_fileImg']),
                'chooseForm' => 'other',
                'formSpecial' => ' ',
                'otherForm' => 'dateFile',
            ],
            [
                'id' => 0,
                'textFields' => 'id_card_img_two_fileImg',
                'fieldsDateType' => 'VARCHAR',
                'fieldsLength' => '255',
                'fieldsNotnull' => 'NOT NULL',
                'fieldsFormName' => '身份证反面',
                'fieldsCoding' => 'UTF8',
                'fields_sort' => '2',
                'will_choose' => '1',
                'titles_centents' => $this->dscRepository->getImagePath($personal['id_card_img_two_fileImg']),
                'chooseForm' => 'other',
                'formSpecial' => ' ',
                'otherForm' => 'dateFile',
            ],
            [
                'id' => 0,
                'textFields' => 'name',
                'fieldsDateType' => 'VARCHAR',
                'fieldsLength' => '255',
                'fieldsNotnull' => 'NOT NULL',
                'fieldsFormName' => '姓名',
                'fieldsCoding' => 'UTF8',
                'fields_sort' => '3',
                'will_choose' => '1',
                'titles_centents' => $personal['name'],
                'chooseForm' => 'input',
                'formSpecial' => ' ',
                'inputForm' => '10',
            ],
            [
                'id' => 0,
                'textFields' => 'id_card',
                'fieldsDateType' => 'VARCHAR',
                'fieldsLength' => '255',
                'fieldsNotnull' => 'NOT NULL',
                'fieldsFormName' => '身份证号码',
                'fieldsCoding' => 'UTF8',
                'fields_sort' => '4',
                'will_choose' => '1',
                'titles_centents' => $personal['id_card'],
                'chooseForm' => 'input',
                'formSpecial' => ' ',
                'inputForm' => '20',
            ],
            [
                'id' => 0,
                'textFields' => 'id_card_img_three_fileImg',
                'fieldsDateType' => 'VARCHAR',
                'fieldsLength' => '255',
                'fieldsNotnull' => 'NOT NULL',
                'fieldsFormName' => '手持身份证上半身照',
                'fieldsCoding' => 'UTF8',
                'fields_sort' => '5',
                'will_choose' => '1',
                'titles_centents' => $this->dscRepository->getImagePath($personal['id_card_img_three_fileImg']),
                'chooseForm' => 'other',
                'formSpecial' => ' ',
                'otherForm' => 'dateFile',
            ],
            [
                'id' => 0,
                'textFields' => 'business_address',
                'fieldsDateType' => 'VARCHAR',
                'fieldsLength' => '255',
                'fieldsNotnull' => 'NOT NULL',
                'fieldsFormName' => '经营地址',
                'fieldsCoding' => 'UTF8',
                'fields_sort' => '7',
                'will_choose' => '1',
                'titles_centents' => $personal['business_address'],
                'chooseForm' => 'input',
                'formSpecial' => ' ',
                'inputForm' => '200',
            ],
            [
                'id' => 0,
                'textFields' => 'business_category',
                'fieldsDateType' => 'VARCHAR',
                'fieldsLength' => '255',
                'fieldsNotnull' => 'NOT NULL',
                'fieldsFormName' => '经营类目',
                'fieldsCoding' => 'UTF8',
                'fields_sort' => '8',
                'will_choose' => '1',
                'titles_centents' => $personal['business_category'],
                'chooseForm' => 'input',
                'formSpecial' => ' ',
                'inputForm' => '200',
            ],
            [
                'id' => 0,
                'textFields' => 'mobile',
                'fieldsDateType' => 'VARCHAR',
                'fieldsLength' => '11',
                'fieldsNotnull' => 'NOT NULL',
                'fieldsFormName' => '联系电话',
                'fieldsCoding' => 'UTF8',
                'fields_sort' => '9',
                'will_choose' => '',
                'titles_centents' => $personal['mobile'],
                'chooseForm' => 'input',
                'formSpecial' => ' ',
                'inputForm' => '11',
            ]
        ];

        return $arr;
    }

}
