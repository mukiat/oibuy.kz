<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Ads\AdsCommonService;
use App\Services\Article\ArticleManageService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class IndexController
 * @package App\Api\Controllers
 */
class IndexController extends Controller
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * App 首页数据
     * @param Request $request
     * @return Repository
     * @throws Exception
     */
    public function index(Request $request)
    {
        $id = $request->get('ru_id', 0);
        $type = $request->get('type', 'index');
        $device = $request->get('device', ''); // 设备 h5 app wxapp

        return cache()->remember('app_visual_data_' . $id . '_' . $device, config('shop.cache_time'), function () use ($id, $type, $device) {
            $defaultResp = $this->client(route('api.visual.default'), ['ru_id' => $id, 'type' => $type, 'device' => $device]);

            // 没有可视化数据，默认提供H5
            if (is_null($defaultResp['data'])) {
                $defaultResp = $this->client(route('api.visual.default'), ['ru_id' => $id, 'type' => $type, 'device' => 'h5']);
            }

            $viewResp = $this->client(route('api.visual.view'), ['id' => $defaultResp['data'], 'type' => $type, 'device' => $device]);

            return json_decode($viewResp['data']['data'], true);
        });
    }

    /**
     * App首页
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function home(Request $request)
    {
        $data = $this->index($request);

        return $this->succeed($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function parseArticle($data)
    {
        $data = [
            'cat_id' => $data['allValue']['optionCascaderVal'] ?? 0,
            'num' => $data['allValue']['number'] ?? 0
        ];

        $res = $this->client(route('api.visual.article'), $data);

        return $res['data'];
    }

    /**
     * @param $data
     * @return mixed
     */
    public function parseSeckill($data)
    {
        $data = [
            'num' => $data['allValue']['number'] ?? 0
        ];

        $res = $this->client(route('api.visual.seckill'), $data);

        return $res['data'];
    }

    /**
     * @param $data
     * @return mixed
     */
    public function parseShop($data)
    {
        $data = [
            'number' => $data['allValue']['number'] ?? 0
        ];

        $res = $this->client(route('api.visual.store'), $data);

        return $res['data'];
    }

    /**
     * @param $data
     * @return mixed
     */
    public function parseGoods($data)
    {
        $data = [
            'number' => $data['allValue']['number'] ?? 0,
            'goods_id' => isset($data['allValue']['selectGoodsId']) && !empty($data['allValue']['selectGoodsId']) ? implode(',', $data['allValue']['selectGoodsId']) : ''
        ];

        $res = $this->client(route('api.visual.checked'), $data);

        return $res['data'];
    }

    /**
     * 处理Http请求
     * @param $url
     * @param $data
     * @return mixed
     */
    protected function client($url, $data)
    {
        $config['verify'] = false;
        $client = new Client($config);

        $response = $client->post($url, ['form_params' => $data]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * 获取商店配置
     * @param AdsCommonService $adsCommonService
     * @param ArticleManageService $articleManageService
     * @return JsonResponse
     * @throws Exception
     */
    public function shopConfig(AdsCommonService $adsCommonService, ArticleManageService $articleManageService)
    {
        $config = config('shop');

        $allow = [
            'shop_name',
            'shop_title',
            'shop_desc',
            'shop_keywords',
            'shop_logo',
            'wap_logo',
            'buyer_cash',
            'buyer_recharge',
            'register_article_id',
            'search_keywords',
            'stats_code',
            'shop_reg_closed',
            'currency_format',
            'price_format',
            'lang',
            'privacy',
            'show_order_type',
            'wap_index_pro',
            'h5_index_pro_image',
            'h5_index_pro_title',
            'h5_index_pro_small_title',
            'is_show_currency_format',
            'currency_format',
            'price_format',
            'price_style',
            'copyright_text',
            'copyright_text_mobile',
            'copyright_img',
            'wxapp_top_img',
            'wxapp_top_url',
            'app_top_img',
            'app_top_url',
            'favourable_use_open',
            'use_bonus'
        ];

        if (file_exists(WXAPP_MEDIA)) {
            $allow = BaseRepository::getArrayPush($allow, 'wxapp_shop_status');
            $allow = BaseRepository::getArrayPush($allow, 'wxapp_media_id');
        }

        if (file_exists(MOBILE_WXAPP)) {
            $allow = BaseRepository::getArrayPush($allow, 'wxapp_chat');
        }

        $config['custom_jump_logo'] = isset($config['custom_jump_logo']) && !empty($config['custom_jump_logo']) ? $this->dscRepository->getImagePath('assets/' . str_replace('../', '', $config['custom_jump_logo'])) : asset('/static/dist/img/01-wykd.png');
        $config['kefu_logo'] = isset($config['kefu_logo']) && !empty($config['kefu_logo']) ? $this->dscRepository->getImagePath('assets/' . str_replace('../', '', $config['kefu_logo'])) : asset('/static/dist/img/02-zxzx.png');
        $config['consult_share_img'] = isset($config['consult_share_img']) && !empty($config['consult_share_img']) ? $this->dscRepository->getImagePath('assets/' . str_replace('../', '', $config['consult_share_img'])) : asset('/static/dist/img/img-share.png');

        $consult = [
            'consult_set_state' => $config['consult_set_state'],
            'custom_jump_logo' => $config['custom_jump_logo'],
            'custom_jump_url' => html_out($config['custom_jump_url']),
            'consult_kefu_url' => html_out($config['consult_kefu_url']),
            'consult_share_img' => $config['consult_share_img'],
            'kefu_logo' => $config['kefu_logo'],
            'consult_kefu_type' => $config['consult_kefu_type']
        ];

        foreach ($config as $key => $item) {
            if (!in_array($key, $allow)) {
                unset($config[$key]);
            }
        }

        $config['shop_logo'] = isset($config['shop_logo']) ? $this->dscRepository->getImagePath(str_replace('../', '', $config['shop_logo'])) : '';
        $config['wap_logo'] = isset($config['wap_logo']) ? $this->dscRepository->getImagePath(str_replace('../', '', $config['wap_logo'])) : '';

        $config['h5_index_pro_image'] = isset($config['h5_index_pro_image']) && !empty($config['h5_index_pro_image']) ? $this->dscRepository->getImagePath('assets/' . str_replace('../', '', $config['h5_index_pro_image'])) : asset('/static/dist/img/more_icon.png');

        $config['copyright_img'] = $this->dscRepository->getImagePath($config['copyright_img']);

        $config['bonus_ad'] = $adsCommonService->getPopupAds(); // 获取手机首页弹框红包

        // 是否显示分销，控制前端页面是否显示分销模块
        $config['is_show_drp'] = 0;
        if (file_exists(MOBILE_DRP)) {
            $isdrp = \App\Modules\Drp\Services\Drp\DrpConfigService::drpConfig('isdrp');
            $config['is_show_drp'] = $isdrp['value'] ?? 0;
        }

        if (file_exists(MOBILE_KEFU)) {
            $config['mobile_kefu'] = true;
        } else {
            $config['mobile_kefu'] = false;
        }

        /*小程序审核模式下的版本号*/
        $config['weapp_in_review'] = '';
        if (file_exists(MOBILE_WXAPP)) {
            $wxappConfig = app(\App\Modules\Wxapp\Services\WxappConfigService::class)->get_config();
            $config['weapp_in_review'] = $wxappConfig['weapp_in_review'];
            // 小程序首页顶部图片 url
            $config['wxapp_top_img'] = isset($config['wxapp_top_img']) ? $this->dscRepository->getImagePath($config['wxapp_top_img']) : '';
            $config['wxapp_top_url'] = isset($config['wxapp_top_url']) ? html_out($config['wxapp_top_url']) : '';
        }

        /*iOS APP审核模式下的版本号*/
        $config['app_in_review'] = '';
        if (file_exists(MOBILE_APP)) {
            $config['app_in_review'] = config('app.app_in_review');
            // app头部图片 url
            $config['app_top_img'] = isset($config['app_top_img']) ? $this->dscRepository->getImagePath($config['app_top_img']) : '';
            $config['app_top_url'] = isset($config['app_top_url']) ? html_out($config['app_top_url']) : '';
        }

        $config['privacy'] = [
            'article_id' => $config['privacy'],
            'version_code' => $articleManageService->getAtricleVersionCode($config['privacy'])
        ];

        $config['consult'] = $consult;

        // 收钱吧电子发票
        $config['is_shouqianba'] = 0;
        if (file_exists(MODULES_SHOUQIANBA)) {
            $config['is_shouqianba'] = 1;
        }

        //默认视频号信息
        $config['wxapp_shop_status'] = $config['wxapp_shop_status'] ?? 0; //视频号开关 0 是关闭 1是开启
        $config['wxapp_media_id'] = $config['wxapp_media_id'] ?? ''; // 视频号ID

        if ($config['wxapp_shop_status'] == 0) {
            $config['wxapp_media_id'] = '';
        }

        $config['wxapp_chat'] = $config['wxapp_chat'] ?? 0; // 小程序端客服 0 是IM客服 1是小程序客服

        return $this->succeed($config);
    }

    /**
     * 发现页功能菜单
     * @param Request $request
     * @return JsonResponse
     */
    public function pageNav(Request $request)
    {
        $device = $request->get('device', 'h5'); // 客户端 h5、wxapp、app
        $page = $request->get('page', 'discover');

        $cache_id = md5(serialize($request->all()));
        $data = Cache::remember('touch_page_nav' . $cache_id, config('shop.cache_time', 3600), function () use ($device, $page) {
            $modules = DB::table('touch_page_nav')
                ->where('ru_id', 0)
                ->where('device', $device)
                ->where('page_name', $page)
                ->where('display', 1)
                ->orderBy('sort')
                ->orderBy('id')
                ->get();

            return collect($modules)->toArray();
        });

        return $this->succeed($data);
    }

    /**
     * 获取后台语言包api
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function shopLang(Request $request)
    {
        static $_LANG = [];

        $replace = $request->get('replace', []);
        $locale = $request->get('lang', 'zh-CN');

        // 默认加载语言包文件
        $defaultFiles = [
            'common',
            'user',
            'flow'
        ];
        // 接受自定义加载语言包文件
        $file = $request->get('file', ['goods']); // 支持数组 ['user','sms']
        if (!is_array($file)) {
            $file = [$file];
        }
        $files = array_merge($defaultFiles, $file);

        foreach ($files as $k => $vo) {
            $_LANG[$vo] = lang($vo, $replace, $locale);
        }

        return $this->succeed($_LANG);
    }

    /**
     * 初始化seeder
     * @return JsonResponse
     */
    public function install()
    {
        // 自动更新数据库
        if (!Storage::disk('local')->exists('seeder/patch.lock.php')) {
            Artisan::call('db:seed', ['--force' => true]);
            Artisan::call('db:seed', [
                '--class' => 'UpdateVersionSeeder',
                '--force' => true
            ]);
            Storage::disk('local')->put('seeder/patch.lock.php', RELEASE);
            return $this->succeed('ok');
        }

        return $this->failed('import seeder fail');
    }
}
