<?php

namespace App\Services\Merchant;

use App\Libraries\Http;
use App\Models\AdminUser;
use App\Models\Article;
use App\Models\MerchantsCategoryTemporarydate;
use App\Models\MerchantsDtFile;
use App\Models\MerchantsShopInformation;
use App\Models\MerchantsStepsFields;
use App\Models\MerchantsStepsProcess;
use App\Models\SellerDomain;
use App\Models\SellerShopinfo;
use App\Models\TouchPageView;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

class MerchantCommonService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获取会员申请入驻商家信息
     *
     * @param int $user_id
     * @return array
     */
    public function getMerchantsShopInformation($user_id = 0)
    {
        $res = MerchantsShopInformation::where('user_id', $user_id);
        $res = BaseRepository::getToArrayFirst($res);

        return $res;
    }

    /**
     * 获得店铺入驻流程扩展信息
     *
     * @access  public
     * @param int $seller_id
     * @return  array
     */
    public function getMerchantsStepsFields($seller_id = 0)
    {
        $row = MerchantsStepsFields::where('user_id', $seller_id);
        $row = BaseRepository::getToArrayFirst($row);
        if (!empty($row)) {
            $row['license_fileImg'] = isset($row['license_fileImg']) ? $this->dscRepository->getImagePath($row['license_fileImg']) : '';
            $row['legal_person_fileImg'] = isset($row['legal_person_fileImg']) ? $this->dscRepository->getImagePath($row['legal_person_fileImg']) : '';
            $row['id_card_img_one_fileImg'] = isset($row['id_card_img_one_fileImg']) ? $this->dscRepository->getImagePath($row['id_card_img_one_fileImg']) : '';
            $row['id_card_img_two_fileImg'] = isset($row['id_card_img_two_fileImg']) ? $this->dscRepository->getImagePath($row['id_card_img_two_fileImg']) : '';
            $row['id_card_img_three_fileImg'] = isset($row['id_card_img_three_fileImg']) ? $this->dscRepository->getImagePath($row['id_card_img_three_fileImg']) : '';
            $row['commitment_fileImg'] = isset($row['commitment_fileImg']) ? $this->dscRepository->getImagePath($row['commitment_fileImg']) : '';
        }

        return $row;
    }

    /**
     * 调取店铺名称
     *
     * @param int $ru_id
     * @param int $type
     * @return array|bool|\Illuminate\Cache\CacheManager|mixed|string
     * @throws \Exception
     */
    public function getShopName($ru_id = 0, $type = 0)
    {
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id, $type);
        return $merchantList[$ru_id] ?? [];
    }

    /**
     * 商家ULR地址
     *
     * @param int $ru_id
     * @param array $build_uri
     * @return mixed
     * @throws \Exception
     */
    public function getSellerDomainUrl($ru_id = 0, $build_uri = [])
    {
        $build_uri['cid'] = isset($build_uri['cid']) ? $build_uri['cid'] : 0;
        $build_uri['urid'] = isset($build_uri['urid']) ? $build_uri['urid'] : $ru_id;
        unset($build_uri['append']);

        $other = [];
        if ($build_uri['cid'] > 0) {
            $other = [
                'cid' => $build_uri['cid']
            ];
        }

        $res = [];
        $res['domain_name'] = $this->dscRepository->sellerUrl($build_uri['urid'], $other);

        return $res;
    }

    /**
     * 处理店铺二级域名
     *
     * @param int $ru_id
     * @return mixed
     */
    public function getSellerDomainInfo($ru_id = 0)
    {
        $row = SellerDomain::where('ru_id', $ru_id);
        $row = BaseRepository::getToArrayFirst($row);

        if (!$row) {
            $row['domain_name'] = '';
            $row['is_enable'] = '';
            $row['validity_time'] = '';
        }

        return $row;
    }

    /**
     * 入驻须知
     * @param int $process_steps
     * @return mixed
     */
    public function getMerchantsStepsProcess($process_steps = 0)
    {
        if (empty($process_steps)) {
            return [];
        }

        $model = MerchantsStepsProcess::where('process_steps', $process_steps);

        $result = BaseRepository::getToArrayFirst($model);

        if ($result['process_article'] > 0) {
            $article = Article::where('article_id', $result['process_article']);
            $article = BaseRepository::getToArrayFirst($article);

            if ($article) {
                if ($article['content']) {
                    $article['content'] = html_out($article['content']);
                    // 过滤样式 手机自适应
                    $article['content'] = $this->dscRepository->contentStyleReplace($article['content']);
                    // 显示文章详情图片 （本地或OSS）
                    $article['content'] = $this->dscRepository->getContentImgReplace($article['content']);
                }
            }

            $result['article_content'] = $article['content'] ?? [];
        }

        return $result;
    }


    /**
     * 更新申请进度
     * @param int $fid
     * @param int $user_id
     * @param array $data
     * @return bool
     */
    public function updateMerchantsStepsFields($fid = 0, $user_id = 0, $data = [])
    {
        if (empty($fid) || empty($user_id) || empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'merchants_steps_fields');

        return MerchantsStepsFields::where('fid', $fid)->where('user_id', $user_id)->update($data);
    }

    /**
     * 新增申请进度
     * @param array $data
     * @return bool
     */
    public function createMerchantsStepsFields($data = [])
    {
        if (empty($data)) {
            return false;
        }

        $count = MerchantsStepsFields::where('user_id', $data['user_id'])->count();

        if (empty($count)) {
            $data = BaseRepository::getArrayfilterTable($data, 'merchants_steps_fields');
            $data = BaseRepository::recursiveNullVal($data);

            return MerchantsStepsFields::insert($data);
        }

        return false;
    }

    /**
     * 删除商家入驻流程填写分类临时信息
     * @param int $user_id
     * @return mixed
     */
    public function deleleMerchantsCategoryTemporarydate($user_id = 0)
    {
        if (empty($user_id)) {
            return false;
        }

        return MerchantsCategoryTemporarydate::where('user_id', $user_id)->where('is_add', 0)->delete();
    }

    /**
     * 删除商家入驻流程填写分类临时信息
     * @param int $user_id
     * @return mixed
     */
    public function deleleMerchantsCategoryTemporarydateAll($user_id = 0)
    {
        if (empty($user_id)) {
            return false;
        }

        return MerchantsCategoryTemporarydate::where('user_id', $user_id)->delete();
    }

    /**
     * 更新商家入驻流程填写分类临时信息
     * @param int $user_id
     * @return mixed
     */
    public function updateMerchantsCategoryTemporarydate($user_id = 0)
    {
        if (empty($user_id)) {
            return false;
        }

        return MerchantsCategoryTemporarydate::where('user_id', $user_id)->where('is_add', 0)->update(['is_add' => 1]);
    }

    /**
     * 新增入驻商家信息
     * @param array $data
     * @return bool
     */
    public function createMerchantsShopInformation($data = [])
    {
        if (empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'merchants_shop_information');

        // 待审核
        $data['steps_audit'] = 1;
        $data['merchants_audit'] = 0;

        $count = MerchantsShopInformation::where('user_id', $data['user_id'])->count();

        if (empty($count)) {
            // 添加
            $data['add_time'] = TimeRepository::getGmTime();

            return MerchantsShopInformation::insert($data);
        } else {
            // 更新时间
            $data['update_time'] = TimeRepository::getGmTime();

            return MerchantsShopInformation::where('user_id', $data['user_id'])->update($data);
        }
    }

    /**
     * 删除商家入驻流程分类资质信息
     * @param int $parent_id
     * @return bool
     */
    public function deleteMerchantsDtFile($parent_id = 0)
    {
        if (empty($parent_id)) {
            return false;
        }

        return MerchantsDtFile::where('cat_id', $parent_id)->delete();
    }

    /**
     * 删除商家入驻流程填写分类临时信息
     * @param int $ct_id
     * @return bool
     */
    public function deleleMerchantsCategoryTemporarydateByCtid($ct_id = 0)
    {
        if (empty($ct_id)) {
            return false;
        }

        return MerchantsCategoryTemporarydate::where('ct_id', $ct_id)->where('is_add', 0)->delete();
    }

    /**
     * 删除商家入驻流程填写分类临时表 主分类下子分类
     *
     * @param int $cat_id
     * @param int $user_id
     * @return bool
     */
    public function deleleMerchantsCategoryTemporarydateByCateid($cat_id = 0, $user_id = 0)
    {
        if (empty($cat_id)) {
            return false;
        }

        return MerchantsCategoryTemporarydate::where('parent_id', $cat_id)->where('user_id', $user_id)->delete();
    }

    /**
     * 检查店铺名是否使用
     * @param int $user_id
     * @param string $rz_shop_name
     * @return int
     */
    public function checkMerchantsShopName($user_id = 0, $rz_shop_name = '')
    {
        if (empty($user_id) || empty($rz_shop_name)) {
            return 0;
        }

        $res = MerchantsShopInformation::where('rz_shop_name', $rz_shop_name)->where('user_id', '<>', $user_id)->value('user_id');

        return $res;
    }

    /**
     * 检查店铺名是否使用
     * @param int $user_id
     * @param string $hopeLoginName
     * @return int
     */
    public function checkMerchantsHopeLoginName($user_id = 0, $hopeLoginName = '')
    {
        if (empty($user_id) || empty($hopeLoginName)) {
            return 0;
        }

        $res = AdminUser::where('user_name', $hopeLoginName)
            ->where('ru_id', '<>', $user_id)
            ->value('user_id');

        return $res;
    }

    /**
     * 获取商家域名
     *
     * @param string $shop
     * @return mixed
     */
    public function getSellerDomain($shop = '')
    {
        $res = [];
        if (!empty($shop)) {
            $nowTime = TimeRepository::currentTimestamp();
            $res = SellerDomain::query()->where('domain_name', $shop)
                ->where('is_enable', 1)
                ->whereRaw("IF(validity_time > 0, validity_time > '$nowTime', 1)"); // 关闭二级域名

            $res = BaseRepository::getToArrayFirst($res);
        }

        return $res;
    }

    /**
     * 入驻会员填写店铺信息
     *
     * @param array $seller_id
     * @return array
     */
    public function getMerchantsShopInformationData($seller_id = [])
    {
        if (empty($seller_id)) {
            return [];
        }

        $seller_id = array_unique($seller_id);

        foreach ($seller_id as $k => $v) {
            $v = intval($v);
            if (empty($v)) {
                unset($seller_id[$k]);
            }
        }

        $arr = [];
        if ($seller_id) {
            $res = MerchantsShopInformation::whereIn('user_id', $seller_id);
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $val) {
                    $arr[$val['user_id']] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 获取自营标识店铺ID
     *
     * @return mixed
     */
    public function selfRunList()
    {
        $res = MerchantsShopInformation::where('self_run', 1);
        $res = CommonRepository::constantMaxId($res, 'user_id');
        $res = $res->pluck('user_id');
        $user_id = BaseRepository::getToArray($res);
        $user_id = BaseRepository::getArrayUnique($user_id);

        return $user_id;
    }

    /**
     * 获取商家logo地址
     * @param $qrcode_thumb
     * @param $ru_id
     * @return string
     */
    public function getLogoPath($qrcode_thumb)
    {
        if (empty($qrcode_thumb)) {
            return '';
        }
        $water_logo_url = $this->dscRepository->getImagePath($qrcode_thumb);

        // 远程图片（非本站）
        if ($this->dscRepository->remoteLinkExists($water_logo_url)) {
            // 商家logo目录
            $logo_file = storage_public('images/seller_imgs/seller_qrcode/qrcode_thumb/' . basename($water_logo_url));
            //下载文件到本地
            $water_logo = Http::doGet($water_logo_url);

            if (empty($water_logo)) {
                $water_logo = file_get_contents($water_logo_url);
            }

            file_put_contents($logo_file, $water_logo);
            $water_logo = $logo_file;

            return $water_logo;
        }

        return '';
    }

    /**
     * 导入移动端首页模板
     * @param $ru_id
     * @return string
     */
    public function importMobileTemplate($ru_id)
    {
        $shop_default = storage_path('app/diy/shop_default.php'); // H5店铺默认数据
        $shop_app_default = storage_path('app/diy/shop_app_default.php'); // app店铺首页默认数据
        $shop_wxapp_default = storage_path('app/diy/shop_wxapp_default.php'); // 小程序店铺首页默认数据

        if (file_exists($shop_default)) {
            $other = ['type' => 'store', 'title' => $GLOBALS['_LANG']['merchants_index'], 'device' => 'h5'];
            $this->insertDefaultData($shop_default, $ru_id, $other);
        }

        if (file_exists($shop_app_default)) {
            $other = ['type' => 'store', 'title' => $GLOBALS['_LANG']['merchants_index'], 'device' => 'app'];
            $this->insertDefaultData($shop_app_default, $ru_id, $other);
        }

        if (file_exists($shop_wxapp_default)) {
            $other = ['type' => 'store', 'title' => $GLOBALS['_LANG']['merchants_index'], 'device' => 'wxapp'];
            $this->insertDefaultData($shop_wxapp_default, $ru_id, $other);
        }
    }

    /**
     * 插入数据
     * @param $default
     * @param $ru_id
     * @param $other
     * @return string
     */
    private function insertDefaultData($default, $ru_id, $other)
    {
        $fp = fopen($default, "r");
        $content = fread($fp, filesize($default));
        $content = str_replace('<?php exit("no access");', '', $content);  //

        if ($content) {
            $data = [
                'ru_id' => $ru_id,
                'type' => $other['type'],
                'page_id' => '',
                'title' => $other['title'],
                'data' => '[]', // $content 暂时为空 待后期有装修好的默认模板再填充数据
                'pic' => '',
                'thumb_pic' => '',
                'create_at' => '',
                'update_at' => TimeRepository::getGmTime(),
                'default' => 1,
                'review_status' => 3,
                'is_show' => 1,
                'device' => $other['device'],
            ];

            $count = TouchPageView::query()->where('ru_id', $ru_id)->where('device', $other['device'])->count();

            if ($count == 0) {
                TouchPageView::query()->insertGetId($data);
            }
        }
        fclose($fp);
    }
}
