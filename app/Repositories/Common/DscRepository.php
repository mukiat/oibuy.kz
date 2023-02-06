<?php

namespace App\Repositories\Common;

use App\Kernel\Repositories\Common\DscRepository as Base;
use App\Models\RegionWarehouse;
use App\Models\SellerShopinfo;
use App\Repositories\User\UsersIdRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class DscRepository
 * @method getImagePath($image = '') 重新获得商品图片与商品相册的地址
 * @method getContentImgReplace($content) 正则批量替换详情内图片为 绝对路径
 * @method getDdownloadTemplate($path = '') 处理指定目录文件数据调取
 * @method objectToArray($obj) 对象转数组
 * @method getReturnMobile($url = '') 跳转H5方法
 * @method pageArray($page_size = 1, $page = 1, $array = [], $order = 0, $filter_arr = []) 数组分页函数 核心函数 array_slice
 * @method getPatch() 升级补丁SQL
 * @method readModules($directory = '.') 获得所有模块的名称以及链接地址
 * @method objectArray($array = null) 对象转数组
 * @method getInciseDirectory($list = []) 切割目录文件
 * @method mysqlLikeQuote($str) 对 MYSQL LIKE 的内容进行转义
 * @method stringToStar($string = '', $num = 3, $start_len = '') 将字符串以 * 号格式显示 配合msubstr_ect函数使用
 * @method msubstrEct($str = '', $start = 0, $length = 1, $charset = "utf-8", $suffix = '***', $position = 1) 字符串截取，支持中文和其他编码
 * @method dscIp() 获取用户IP ：可能会出现误差
 * @method contentStyleReplace($content) 正则过滤内容样式 style = '' width = '' height = ''
 * @method helpersLang($files = [], $module = '', $langPath = 0) 组合语言包信息
 * @method readStaticCache($cache_path = '', $cache_name = '', $storage_path = 'common_cache/', $prefix = "php") 读结果缓存文件
 * @method writeStaticCache($cache_path = '', $cache_name = '', $caches = '', $storage_path = 'common_cache/', $prefix = "php") 写结果缓存文件
 * @method getHttpBasename($url = '', $path = '', $goods_lib = '') 下载远程图片
 * @method remoteLinkExists($url) 判断远程链接|判断本地链接 -- 是否存在
 * @method pluginsLang($plugin, $dir) 调取插件语言包[插件名称(Alipay/), __DIR__]
 * @method subStr($str, $length = 0, $append = true) 截取UTF-8编码下字符串的函数
 * @method trimRight($str) 去除字符串右侧可能出现的乱码
 * @method strLen($str = '') 计算字符串的长度（汉字按照两个字符计算）
 * @method delStrComma($str = '', $delstr = ',') 去除字符串中首尾逗号[去除字符串中出现两个连续逗号]
 * @method getBucketInfo()【云存储】获取存储信息
 * @method getOssAddFile($file = [])【云存储】上传文件
 * @method getOssDelFile($file = [])【云存储】删除文件
 * @method getDelBatch($checkboxs = '', $val_id = '', $select = '', $id = '', $query, $del = 0, $fileDir = '')【云存储】单个或批量删除图片
 * @method getDelVisualTemplates($ip = [], $suffix = '', $act = 'del_hometemplates', $seller_id = 0)【云存储】删除可视化模板OSS标识文件
 * @method getOssListFile($file = [])【云存储】下载文件
 * @method dscEmpower($AppKey, $activate_time) 生成授权证书
 * @method checkEmpower() 校验授权
 * @method collateOrderGoodsBonus($bonus_list = [], $orderBonus = 0, $goods_bonus = 0) 核对均摊红包商品金额是否大于订单红包金额
 * @method collateOrderGoodsCoupons($coupons_list = [], $orderCoupons = 0, $goods_coupons = 0) 核对均摊优惠券商品金额是否大于订单红包金额
 * @method dscConfig($str = '') $str默认值空，多个示例:xx, xx, xx字符串组成
 * @method dscUrl($str = '') 获取网站地址[域名]
 * @method turnPluckFlattenOne($goods_list = [], $key = 'goods_list') 提取数组数据
 * @method chatQq($basic_info) 处理系统设置[QQ客服/旺旺客服]
 * @method shippingFee($shipping_code = '', $shipping_config = '', $goods_weight = 0, $goods_amount = 0, $goods_number = 0) 计算运费
 * @method valueOfIntegral($integral = 0) 计算积分的价值（能抵多少钱）
 * @method integralOfValue($value = 0) 计算指定的金额需要多少积分
 * @method changeFloat($float = 0) 转浮点值，保存两位
 * @method dscHttp($server = '') 获取http|https
 * @method isJsonp($back_act = '', $exp = '|', $strpos = 'is_jsonp') 获取店铺二级域名跨域关键值
 * @method hostDomain($url = '') 获取主域名
 * @method getUrlHtml($list = ['index', 'user']) 返回html链接： http://www.xxx.com/,http://www.xxx.com/user.html
 * @method filterFilePhp() 过滤上传含有php文件
 * @method filterAccountChangeOrder($order_sn = '', $user_id = 0, $user_money = 0, $pay_points = 0, $is_go = true) 防止会员订单退款金额重复操作
 * @method getLinkImgList($list = []) 返回过滤数组中不存在的链接远程图片地址信息
 * @method getDirectoryFileList($dir = '', $ext = ['png', 'jpg', 'jpeg', 'gif'], $disk = '') 获取目录下的文件
 * @method getGoodsConsumptionPrice($consumption_list = [], $goods_amount = 0) 返回商品优惠后最终金额
 * @method foreverDownFile($file_name = '') 下载生成缓存文件【路径 storage\framework\cache\forever】
 * @method foreverUpFile($file_name = '') 上传生成缓存文件到OSS等云存储【路径 framework\cache\forever】
 * @method getDownOssTemplate($list = '') 下载云存储上面模板文件
 * @package App\Repositories\Common
 */
class DscRepository extends Base
{

    /**
     * 格式化商品价格[$change_price：false 默认保留两位，true 根据商店设置控制处理小数点位数|$format：false 不带价格符号，true 带价格符号]
     *
     * @param int $price
     * @param bool $change_price
     * @param bool $format
     * @param bool $goodsSelf
     * @return int|null|string|string[]
     */
    public function getPriceFormat($price = 0, $change_price = true, $format = true, $goodsSelf = false)
    {
        $price = parent::getPriceFormat($price, $change_price, $format);

        $current_path = UsersIdRepository::current_path();

        $currentExits = true;
        if (in_array($current_path, [ADMIN_PATH, SELLER_PATH, STORES_PATH, SUPPLLY_PATH])) {
            $currentExits = false;
        }

        //店铺商品需购买权益卡可显示价格功能
        $drp_show_price = config('shop.drp_show_price') ?? 0;
        if (file_exists(MOBILE_DRP) && $drp_show_price == 1 && $currentExits == true) {
            if ($goodsSelf == false) {
                $user_id = UsersIdRepository::getUsersId();

                $exits = false;
                if (!empty($user_id)) {

                    $ttl = Carbon::now()->addHours(24);
                    $drpUserAudit = Cache::remember('drp_user_audit_' . $user_id, $ttl, function () use ($user_id) {
                        $drpUser = app(\App\Modules\Drp\Services\Drp\DrpShopService::class)->get_drp_shop_by_user($user_id);
                        $drpUserAudit = $drpUser['audit'] ?? 0;

                        return $drpUserAudit;
                    });

                    $exits = $drpUserAudit ? true : false;
                }

                if ($exits == false) {
                    /* 正则匹配替换数字 */
                    $price = preg_replace("/[0-9]/", "?", $price);
                }
            }
        }

        return $price;
    }

    /**
     * 重写 URL 地址
     *
     * @param string $app 执行程序
     * @param array $params 参数数组
     * @param string $append 附加字串
     * @param int $page 页数
     * @param string $keywords 搜索关键词字符串
     * @return bool|\Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function buildUri($app = '', $params = [], $append = '', $page = 0, $keywords = '')
    {
        static $rewrite = null;

        if ($rewrite === null) {
            $rewrite = (int)config('shop.rewrite', 0);
        }

        /* 初始值 */
        $cid = 0;
        $chkw = '';
        $secid = 0;
        $tmr = '';

        $args = ['cid' => 0, 'gid' => 0, 'bid' => 0, 'acid' => 0, 'aid' => 0, 'mid' => 0, 'urid' => 0, 'ubrand' => 0, 'chkw' => '', 'is_ship' => '', 'hid' => 0, 'sid' => 0, 'gbid' => 0, 'auid' => 0, 'sort' => '', 'order' => '', 'status' => -1, 'secid' => 0, 'tmr' => 0];

        extract(array_merge($args, $params));

        $uri = '';
        switch ($app) {
            case 'history_list':
                if ($rewrite) {
                    $uri = 'history_list';

                    $uri .= '-' . (!empty($page) ? $page : 0);
                    $uri .= '-' . (!empty($ship) ? $ship : 0);
                    $uri .= '-' . (!empty($self) ? $self : 0);
                    $uri .= '-' . (!empty($have) ? $have : 0);

                } else {
                    $uri = 'history_list.php';

                    $uri .= !empty($page) ? '&amp;page=' . $page : '';
                    $uri .= !empty($ship) ? '&amp;ship=' . $ship : '';
                    $uri .= !empty($self) ? '&amp;self=' . $self : '';
                    $uri .= !empty($have) ? '&amp;have=' . $have : '';
                }

                break;

            case 'category':
                if (empty($cid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'category-' . $cid;

                        if (!empty($bid)  || !empty($ubrand) || !empty($price_min) || !empty($price_max) || !empty($filter_attr)) {
                            $uri .= '-b' . (!empty($bid) ? $bid : 0) ;
                            $uri .= '-ubrand' . (!empty($ubrand) ? $ubrand : 0);
                            $uri .= '-min' . (!empty($price_min) ? $price_min : 0);
                            $uri .= '-max' . (!empty($price_max) ? $price_max : 0);
                            $uri .= '-attr' . (!empty($filter_attr) ? $filter_attr : 0);
                        }

                        if (!empty($ship) || !empty($self) || !empty($have)) {
                            $uri .= '-ship' . (!empty($ship) ? $ship : 0) ;
                            $uri .= '-self' . (!empty($self) ? $self : 0);
                            $uri .= '-have' . (!empty($have) ? $have : 0);
                        }

                        $uri .= '-d' . (!empty($display) ? $display : 'list');
                        $uri .= '-' . (!empty($page) ? $page : 0);
                        $uri .= '-' . (!empty($sort) ? $sort : 'goods_id');
                        $uri .= '-' . (!empty($order) ? $order : 'DESC');


                    } else {
                        $uri = 'category.php?id=' . $cid;
                        if (!empty($bid)) {
                            $uri .= '&amp;brand=' . $bid;
                        }

                        if (!empty($ubrand)) {
                            $uri .= '&amp;ubrand=' . $ubrand;
                        }

                        if (isset($price_min) && !empty($price_min)) {
                            $uri .= '&amp;price_min=' . $price_min;
                        }
                        if (isset($price_max) && !empty($price_max)) {
                            $uri .= '&amp;price_max=' . $price_max;
                        }

                        if (isset($filter_attr) && !empty($filter_attr)) {
                            $uri .= '&amp;filter_attr=' . $filter_attr;
                        }

                        if (isset($ship) && !empty($ship)) {
                            $uri .= '&amp;ship=' . $ship;
                        }

                        if (isset($self) && !empty($self)) {
                            $uri .= '&amp;self=' . $self;
                        }

                        if (isset($have) && !empty($have)) {
                            $uri .= '&amp;have=' . $have;
                        }

                        if (isset($display) && !empty($display)) {
                            $uri .= '&amp;display=' . $display;
                        }

                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                    }
                }

                break;

            case 'wholesale':
                if (empty($cid) && empty($act)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'wholesale';
                        if (!empty($cid)) {
                            $uri .= '-' . $cid;
                        }

                        if (!empty($cid)) {
                            $uri .= '-c' . $cid;
                        }

                        if (isset($status) && $status != -1) {
                            $uri .= '-status' . $status;
                        }

                        if (!empty($act)) {
                            $uri .= '-' . $act;
                        }
                    } else {
                        $uri = 'wholesale.php?';
                        if (!empty($act)) {
                            $uri .= 'act=' . $act;
                        }
                        if (!empty($cid)) {
                            $uri .= '&amp;id=' . $cid;
                        }

                        if (isset($status) && $status != -1) {
                            $uri .= '&amp;status=' . $status;
                        }
                    }
                }

                break;

            case 'wholesale_cat':
                if (empty($cid) && empty($act)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'wholesale_cat';
                        if (!empty($cid)) {
                            $uri .= '-' . $cid;
                        }

                        if (isset($status) && $status != -1) {
                            $uri .= '-status' . $status;
                        }

                        if (!empty($act)) {
                            $uri .= '-' . $act;
                        }
                    } else {
                        $uri = 'wholesale_cat.php?';

                        if (!empty($cid)) {
                            $uri .= 'id=' . $cid;
                        }
                        if (isset($status) && $status != -1) {
                            $uri .= '&amp;status=' . $status;
                        }

                        if (!empty($act)) {
                            $uri .= '&amp;act=' . $act;
                        }

                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                    }
                }

                break;

            case 'wholesale_suppliers':
                if (empty($sid) && empty($act)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'wholesale_suppliers';
                        if (!empty($sid)) {
                            $uri .= '-' . $sid;
                        }

                        if (isset($status) && $status != -1) {
                            $uri .= '-status' . $status;
                        }

                        if (!empty($act)) {
                            $uri .= '-' . $act;
                        }
                    } else {
                        $uri = 'wholesale_suppliers.php?';

                        if (!empty($sid)) {
                            $uri .= 'suppliers_id=' . $sid;
                        }

                        if (!empty($act)) {
                            $uri .= '&amp;act=' . $act;
                        }

                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                    }
                }

                break;

            case 'wholesale_goods':
                if (empty($aid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'wholesale_goods-' . $aid : 'wholesale_goods.php?id=' . $aid;
                }

                break;

            case 'wholesale_purchase':
                if (empty($gid) && empty($act)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'wholesale_purchase';
                        if (!empty($gid)) {
                            $uri .= '-' . $gid;
                        }

                        if (!empty($act)) {
                            $uri .= '-' . $act;
                        }
                    } else {
                        $uri = 'wholesale_purchase.php?';

                        if (!empty($gid)) {
                            $uri .= 'id=' . $gid;
                        }

                        if (!empty($act)) {
                            $uri .= '&amp;act=' . $act;
                        }
                    }
                }

                break;

            case 'goods':
                if (empty($gid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'goods-' . $gid : 'goods.php?id=' . $gid;
                }

                break;
            case 'presale':
                if (empty($presaleid) && empty($act)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'presale';
                        if (!empty($presaleid)) {
                            $uri .= '-' . $presaleid;
                        }

                        if (!empty($cid)) {
                            $uri .= '-c' . $cid;
                        }

                        if (isset($status) && $status != -1) {
                            $uri .= '-status' . $status;
                        }

                        if (!empty($act)) {
                            $uri .= '-' . $act;
                        }
                    } else {
                        $uri = 'presale.php?';
                        if (!empty($presaleid)) {
                            $uri .= 'id=' . $presaleid;
                        }

                        if (!empty($cid)) {
                            $uri .= 'cat_id=' . $cid;
                        }

                        if (isset($status) && $status != -1) {
                            $uri .= '&amp;status=' . $status;
                        }

                        if (!empty($act)) {
                            $uri .= '&amp;act=' . $act;
                        }
                    }
                }

                break;
            case 'categoryall':
                if (empty($urid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'categoryall';
                        if (!empty($urid)) {
                            $uri .= '-' . $urid;
                        }
                    } else {
                        $uri = 'categoryall.php';
                        if (!empty($urid)) {
                            $uri .= '?id=' . $urid;
                        }
                    }
                }

                break;
            case 'brand':
                if (empty($bid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'brand-' . $bid;

                        if (!empty($mbid)) {
                            $uri .= '-mbid' . $mbid;
                        }

                        if (!empty($cid)) {
                            $uri .= '-c' . $cid;
                        }
                        //by wang start
                        if (isset($price_min) && !empty($price_min)) {
                            $uri .= '-min' . $price_min;
                        }
                        if (isset($price_max) && !empty($price_max)) {
                            $uri .= '-max' . $price_max;
                        }
                        if (isset($ship) && !empty($ship)) {
                            $uri .= '-ship' . $ship;
                        }
                        if (isset($self) && !empty($self)) {
                            $uri .= '-self' . $self;
                        }
                        //by wang end
                        if (isset($display) && !empty($display)) {
                            $uri .= '-d' . $display;
                        }
                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                    } else {
                        $uri = 'brand.php?id=' . $bid;

                        if (!empty($mbid)) {
                            $uri .= '&amp;mbid=' . $mbid;
                        }

                        if (!empty($cid)) {
                            $uri .= '&amp;cat_id=' . $cid;
                        }
                        //by wang start
                        if (isset($price_min)) {
                            $uri .= '&amp;price_min=' . $price_min;
                        }
                        if (isset($price_max)) {
                            $uri .= '&amp;price_max=' . $price_max;
                        }
                        if (isset($ship) && !empty($ship)) {
                            $uri .= '&amp;ship=' . $ship;
                        }
                        if (isset($self) && !empty($self)) {
                            $uri .= '&amp;self=' . $self;
                        }
                        if (isset($display) && !empty($display)) {
                            $uri .= '&amp;display=' . $display;
                        }
                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        //by wang end
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                    }
                }

                break;
            case 'brandn':
                if (empty($bid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'brandn-' . $bid;
                        if (isset($cid) && !empty($cid)) {
                            $uri .= '-c' . $cid;
                        }
                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }

                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                        if (!empty($act)) {
                            $uri .= '-' . $act;
                        }
                    } else {
                        $uri = 'brandn.php?id=' . $bid;
                        if (!empty($cid)) {
                            $uri .= '&amp;cat_id=' . $cid;
                        }
                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (isset($price_min)) {
                            $uri .= '&amp;price_min=' . $price_min;
                        }
                        if (isset($price_max)) {
                            $uri .= '&amp;price_max=' . $price_max;
                        }
                        if (isset($is_ship) && !empty($is_ship)) {
                            $uri .= '&amp;is_ship=' . $is_ship;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                        if (!empty($act)) {
                            $uri .= '&amp;act=' . $act;
                        }
                    }
                }

                break;
            case 'article_cat':
                if (empty($acid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'article_cat-' . $acid;
                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                        if (!empty($keywords)) {
                            $uri .= '-' . $keywords;
                        }
                    } else {
                        $uri = 'article_cat.php?id=' . $acid;
                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                        if (!empty($keywords)) {
                            $uri .= '&amp;keywords=' . $keywords;
                        }
                    }
                }

                break;
            case 'article':
                if (empty($aid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'article-' . $aid : 'article.php?id=' . $aid;
                }

                break;
            case 'merchants':
                if (empty($mid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'merchants-' . $mid : 'merchants.php?id=' . $mid;
                }

                break;
            case 'merchants_index':
                if (empty($urid) && empty($merchant_id)) {
                    return false;
                } else {
                    if ($urid) {
                        if ($rewrite) {
                            $uri = '';
                            $uri .= 'merchants_index-' . $urid;
                        } else {
                            $uri = 'merchants_index.php?merchant_id=' . $urid;
                        }
                    }

                    if ($merchant_id) {
                        if ($rewrite) {
                            $uri = '';
                            $uri .= 'merchants_index-' . $merchant_id;
                        } else {
                            $uri = 'merchants_index.php?merchant_id=' . $merchant_id;
                        }
                    }
                }

                break;
            case 'merchants_store':
                if (empty($urid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $storeUrl = 'merchants_store-' . $urid;
                    } else {
                        $storeUrl = 'merchants_store.php?merchant_id=' . $urid;
                    }

                    $uri .= $this->merchantsStoreUrl($storeUrl, $cid ?? 0, $bid ?? 0, $keyword ?? '', $price_min ?? 0, $price_max ?? 0, $filter_attr ?? '', $page ?? 0, $sort ?? '', $order ?? '');
                }
                break;

            case 'merchants_store_shop':
                if (empty($urid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri .= 'merchants_store_shop-' . $urid;

                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                    } else {
                        $uri = 'merchants_store_shop.php?id=' . $urid;

                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                    }
                }
                break;
            case 'group_buy':
                if (empty($gbid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'group_buy-' . $gbid : 'group_buy.php?act=view&amp;id=' . $gbid;
                }

                break;
            case 'auction':
                if (empty($auid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'auction-' . $auid : 'auction.php?act=view&amp;id=' . $auid;
                }

                break;
            case 'snatch':
                if (empty($sid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'snatch-' . $sid : 'snatch.php?id=' . $sid;
                }

                break;
            case 'search':
                $uri = 'search.php?keywords=' . $chkw;

                if (!empty($bid)) {
                    $uri .= '&amp;brand=' . $bid;
                }
                if (isset($price_min)) {
                    $uri .= '&amp;price_min=' . $price_min;
                }
                if (isset($price_max)) {
                    $uri .= '&amp;price_max=' . $price_max;
                }
                if (!empty($filter_attr)) {
                    $uri .= '&amp;filter_attr=' . $filter_attr;
                }
                if (!empty($cou_id)) {
                    $uri .= '&amp;cou_id=' . $cou_id;
                }
                break;
            case 'user':
                if (empty($act)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'user';
                        if (!empty($act)) {
                            $uri .= '-' . $act;
                        }
                    } else {
                        $uri = 'user.php?';
                        if (!empty($act)) {
                            $uri .= 'act=' . $act;
                        }
                    }
                }

                break;
            case 'exchange':
                if (empty($cid)) {
                    if (!empty($page)) {
                        $uri = 'exchange-' . $cid;
                        if ($rewrite) {
                            $uri .= '-' . $page;
                        } else {
                            $uri = 'exchange.php?';
                            $uri .= 'page=' . $page;
                        }
                    } else {
                        return false;
                    }
                } else {
                    if ($rewrite) {
                        $uri = 'exchange-' . $cid;
                        if (isset($price_min)) {
                            $uri .= '-min' . $price_min;
                        }
                        if (isset($price_max)) {
                            $uri .= '-max' . $price_max;
                        }
                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                    } else {
                        $uri = 'exchange.php?cat_id=' . $cid;
                        if (isset($price_min)) {
                            $uri .= '&amp;integral_min=' . $price_min;
                        }
                        if (isset($price_max)) {
                            $uri .= '&amp;integral_max=' . $price_max;
                        }

                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                    }
                }

                break;
            case 'exchange_goods':
                if (empty($gid)) {
                    return false;
                } else {
                    $uri = $rewrite ? 'exchange-id' . $gid : 'exchange.php?id=' . $gid . '&amp;act=view';
                }
                break;
            case 'gift_gard':
                if (empty($cid)) {
                    return false;
                } else {
                    if ($rewrite) {
                        $uri = 'gift_gard-' . $cid;
                        if (!empty($page)) {
                            $uri .= '-' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '-' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '-' . $order;
                        }
                    } else {
                        $uri = 'gift_gard.php?cat_id=' . $cid;
                        if (!empty($page)) {
                            $uri .= '&amp;page=' . $page;
                        }
                        if (!empty($sort)) {
                            $uri .= '&amp;sort=' . $sort;
                        }
                        if (!empty($order)) {
                            $uri .= '&amp;order=' . $order;
                        }
                    }
                }
                break;
            case 'seckill':
                if (empty($act)) {
                    if (!empty($cid)) {
                        $uri = $rewrite ? 'seckill-' . $cid : 'seckill.php?cat_id=' . $cid;
                    } else {
                        return false;
                    }
                } else {
                    if ($rewrite) {
                        $uri = 'seckill-' . $secid;

                        if (!empty($act)) {
                            $uri .= '-' . $act;
                        }
                    } else {
                        $uri = 'seckill.php?id=' . $secid;

                        if ($act == 'view') {
                            $uri .= "&act=view";
                        }
                        if ($tmr) {
                            $uri .= "&tmr=1";
                        }
                    }
                }

                break;
            default:
                return false;
                break;
        }

        if ($rewrite) {
            if ($rewrite == 2 && !empty($append)) {
                $uri .= '-' . urlencode(preg_replace('/[\.|\/|\?|&|\+|\\\|\'|"|,]+/', '', $append));
            }

            if (!in_array($app, ['search'])) {
                $uri .= '.html';
            }
        }
        if (($rewrite == 2) && (strpos(strtolower(EC_CHARSET), 'utf') !== 0)) {
            $uri = urlencode($uri);
        }

        return $this->dscUrl($uri, config('app.url'));
    }

    /**
     * 处理编辑器内容图片
     *
     * @param string $text_desc 编辑文本
     * @param string $str_file 模板调用变量
     * @param int $is_mobile_div 0|不过滤div，1|过滤div
     * @return array
     * @throws \Exception
     */
    public function descImagesPreg($text_desc = '', $str_file = 'goods_desc', $is_mobile_div = 0)
    {
        if ($str_file === 'desc_mobile' && $is_mobile_div == 1) {
            $text_desc = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\/div>(.*?)<\/div>/is', '', $text_desc);
        }

        if ($this->config['open_oss'] == 1) {
            $bucket_info = $this->getBucketInfo();
            $endpoint = $bucket_info['endpoint'];
        } else {
            $endpoint = $this->dscUrl();
        }

        $endpoint = rtrim($endpoint, '/') . '/';
        $image_dir = $this->imageDir();
        $data_dir = $this->dataDir();

        $pathImage = [
            $this->dscUrl('storage/' . $image_dir . '/'),
            $this->dscUrl($image_dir . '/')
        ];

        $pathData = [
            $this->dscUrl('storage/' . $data_dir . '/'),
            $this->dscUrl($data_dir . '/')
        ];

        $uploads = $this->dscUrl('storage/uploads/');

        if ($text_desc) {
            $text_desc = stripcslashes($text_desc);
            $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\'].*?>/i';
            preg_match_all($preg, $text_desc, $desc_img);
        } else {
            $desc_img = '';
        }

        $arr = [];
        if ($desc_img) {
            $img_list = isset($desc_img[1]) && $desc_img[1] ? array_unique($desc_img[1]) : [];//剔除重复值，防止重复添加域名

            if ($img_list && $endpoint) {
                foreach ($img_list as $key => $row) {
                    $row = trim($row);
                    if ($this->config['open_oss'] == 1) {
                        if (strpos($row, $this->dscUrl('storage/' . $image_dir)) !== false || strpos($row, $this->dscUrl($image_dir)) !== false) {
                            $row = str_replace($pathImage, '', $row);
                            $arr[] = 'storage/' . $image_dir . '/' . $row;

                            $text_desc = str_replace($pathImage, $endpoint . $image_dir . '/', $text_desc);
                        } elseif (strpos($row, $this->dscUrl('storage/' . $data_dir)) !== false || strpos($row, $this->dscUrl($data_dir)) !== false) {
                            $row = str_replace($pathData, '', $row);
                            $arr[] = 'storage/' . $data_dir . '/' . $row;

                            $text_desc = str_replace($pathData, $endpoint . $data_dir . '/', $text_desc);
                        } elseif (strpos($row, $uploads) !== false) {
                            $arr[] = 'storage/uploads/' . $row;
                        }
                    } else {
                        if (strpos($row, 'http://') !== false || strpos($row, 'https://') !== false) {
                            if (strpos($row, 'storage/' . $image_dir) !== false || strpos($row, $image_dir) !== false) {
                                $row = str_replace($pathImage, '', $row);
                                $arr[] = 'storage/' . $image_dir . '/' . $row;

                                $text_desc = str_replace($pathImage, $this->dscUrl('storage/' . $image_dir . '/'), $text_desc);
                            } elseif (strpos($row, 'storage/' . $data_dir) !== false || strpos($row, $data_dir) !== false) {
                                $row = str_replace($pathData, '', $row);
                                $arr[] = 'storage/' . $data_dir . '/' . $row;

                                $text_desc = str_replace($pathData, $this->dscUrl('storage/' . $data_dir . '/'), $text_desc);
                            } elseif (strpos($row, $uploads) !== false) {
                                $arr[] = 'storage/uploads/' . $row;
                            }
                        } else {
                            if (strpos($row, 'storage') !== false) {
                                $arr[] = $row;
                                $text_desc = str_replace($row, $this->dscUrl($row), $text_desc);
                            } else {
                                $arr[] = 'storage/' . $row;
                                $text_desc = str_replace($row, $this->dscUrl('storage/' . $row), $text_desc);
                            }
                        }
                    }
                }
            }
        }

        $res = [
            'images_list' => $arr,
            $str_file => $text_desc
        ];
        return $res;
    }

    /**
     * 获得图片的目录路径
     *
     * @param int $sid
     *
     * @return string 路径
     */
    public function imageDir($sid = 0)
    {
        if (empty($sid)) {
            $s = 'images';
        } else {
            $s = 'user_files/';
            $s .= ceil($sid / 3000) . '/';
            $s .= ($sid % 3000) . '/';
            $s .= 'images';
        }
        return $s;
    }

    /**
     * 获得数据目录的路径
     *
     * @param int $sid
     *
     * @return string 路径
     */
    public function dataDir($sid = 0)
    {
        if (empty($sid)) {
            $s = 'data';
        } else {
            $s = 'user_files/';
            $s .= ceil($sid / 3000) . '/';
            $s .= $sid % 3000;
        }
        return $s;
    }

    /**
     * 获取分类数组进行转换
     *
     * @param array $list
     * @param string $str
     * @return array
     */
    public function getCatVal($list = [], $str = 'cat_id')
    {
        $arr = [];
        if ($list) {
            foreach ($list as $key => $val) {
                $arr[$key][$str] = $val[$str];
                $arr[$key]['cat_list'] = $this->getCatTree($val['cat_list'], $str);
            }
        }

        return $arr;
    }

    /**
     * 获取分类数组进行转换
     *
     * @param array $list
     * @param string $str
     * @return array
     */
    public function getCatTree($list = [], $str = 'cat_id')
    {
        $arr = [];
        if ($list) {
            foreach ($list as $key => $val) {
                $arr[$key][$str] = $val[$str];
                $arr[$key]['cat_list'] = $this->getCatTree($val['cat_list']);
            }
        }

        return $arr;
    }

    /**
     * 计算积分的价值（能抵多少钱）
     *
     * @param int $integral 积分
     * @return float|int 积分价值
     */
    public function getValueOfIntegral($integral = 0)
    {
        $scale = floatval($this->config['integral_scale']);

        return $scale > 0 ? round(($integral / 100) * $scale, 2) : 0;
    }

    /**
     * 计算指定的金额需要多少积分
     *
     * @param int $value 金额
     * @return float|int
     */
    public function getIntegralOfValue($value = 0)
    {
        $scale = floatval($this->config['integral_scale']);

        return $scale > 0 ? round($value / $scale * 100) : 0;
    }

    /**
     * 关联地区查询商品
     *
     * @param $res 对象
     * @param int $area_id 省份/直辖市
     * @param int $city_id 市/县
     * @return mixed
     */
    public function getAreaLinkGoods($res, $area_id = 0, $city_id = 0)
    {
        if ($this->config['open_area_goods'] == 1 && $area_id) {

            $area_id = $area_id > 0 ? RegionWarehouse::where('region_id', $area_id)->value('regionId') : 0;
            $area_id = $area_id ?? 0;

            $prefix = config('database.connections.mysql.prefix');

            $where = '';
            /*if ($this->config['area_pricetype'] == 1 && $city_id) {
                $where = " AND (FIND_IN_SET('" . $city_id . "', `{$prefix}link_area_goods`.city_id))";
            }*/

            $res = $res->whereRaw("IF(`{$prefix}goods`.area_link > 0, exists(select goods_id from `{$prefix}link_area_goods` where `{$prefix}link_area_goods`.goods_id = `{$prefix}goods`.goods_id and `{$prefix}link_area_goods`.region_id = '$area_id'" . $where . "), 1)");
        }

        return $res;
    }

    /**
     * 处理oss远程图片路径
     *
     * @param array $file_arr
     * @return array
     * @throws \Exception
     */
    public function transformOssFile($file_arr = [])
    {
        if (empty($file_arr)) {
            return [];
        }

        // oss图片处理
        $oss_http = '';
        if (config('shop.open_oss', 0) == 1) {
            $bucket_info = $this->getBucketInfo();
            $bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
            $oss_http = rtrim($bucket_info['endpoint'], '/') . '/';
        }

        foreach ($file_arr as $k => $file) {
            // oss远程图片
            if (!empty($oss_http)) {
                $file = str_replace($oss_http, '', $file);
            }

            // 本地远程图片
            if (stripos(substr($file, 0, 4), 'http') !== false) {
                $file = str_replace(url('/'), '', $file);
            }
            $file = str_replace('storage/', '', ltrim($file, '/'));
            $file_arr[$k] = $file;
        }

        return $file_arr;
    }

    /**
     * 处理编辑素材时上传保存图片
     * 配合 get_wechat_image_path 方法使用 ,将网站本地图片绝对路径地址 转换为 相对路径
     * 保存到数据库的值 为相对路径 data/attached/..... or oss完整路径
     * @param string $url
     * @return mixed|string
     */
    public function editUploadImage($url = '')
    {
        if (!empty($url)) {
            $prex_patch = rtrim($this->dscUrl(), '/') . '/';
            $url = str_replace([$prex_patch, 'storage/'], '', $url);
            $url = ltrim($url, '/');
        }

        return $url;
    }

    /**
     * 验证图片格式
     * @param string $url
     * @return bool
     */
    public function checkImageUrl($url = '')
    {
        // 验证商品图片外链格式
        $ext = strtolower(strrchr($url, '.'));
        if (substr($url, 0, 4) !== 'http' || !in_array($ext, ['.jpg', '.png', '.gif', '.jpeg'])) {
            return false;
        }

        return true;
    }

    /**
     * 设置伪静态链接
     *
     * @param string $initUrl 传入链接
     * @param string $params
     * @param string $append
     * @param int $page
     * @param string $keywords
     * @param int $size
     * @return bool|\Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function setRewriteUrl($initUrl = '', $params = '', $append = '', $page = 0, $keywords = '', $size = 0)
    {
        $url = false;
        $rewrite = intval(config('shop.rewrite'));
        $baseUrl = basename($initUrl);
        $urlArr = explode('?', $baseUrl);

        if ($rewrite && !empty($urlArr[0]) && strpos($urlArr[0], '.php')) {
            //程序名
            $app = str_replace('.php', '', $urlArr[0]);

            //取id值
            @parse_str($urlArr[1], $queryArr);
            if (isset($queryArr['id'])) {
                $id = intval($queryArr['id']);
            }

            //链接中包含id
            if (!empty($id)) {
                //判断id类型
                switch ($app) {
                    case 'history_list':
                        $idType = ['cid' => $id];
                        break;
                    case 'category':
                        $idType = ['cid' => $id];
                        break;
                    case 'goods':
                        $idType = ['gid' => $id];
                        break;
                    case 'presale':
                        $idType = ['presaleid' => $id];
                        break;
                    case 'brand':
                        $idType = ['bid' => $id];
                        break;
                    case 'brandn':
                        $idType = ['bid' => $id];
                        break;
                    case 'article_cat':
                        $idType = ['acid' => $id];
                        break;
                    case 'article':
                        $idType = ['aid' => $id];
                        break;
                    case 'merchants':
                        $idType = ['mid' => $id];
                        break;
                    case 'merchants_index':
                        $idType = ['urid' => $id];
                        break;
                    case 'group_buy':
                        $idType = ['gbid' => $id];
                        break;
                    case 'seckill':
                        $idType = ['secid' => $id];
                        break;
                    case 'auction':
                        $idType = ['gbid' => $id];
                        break;
                    case 'snatch':
                        $idType = ['sid' => $id];
                        break;
                    case 'exchange':
                        $idType = ['cid' => $id];
                        break;
                    case 'exchange_goods':
                        $idType = ['gid' => $id];
                        break;
                    case 'gift_gard':
                        $idType = ['cid' => $id];
                        break;
                    default:
                        $idType = ['id' => ''];
                        break;
                }
            } //链接中不含id
            else {
                switch ($app) {
                    case 'index':
                        $idType = null;
                        break;
                    case 'brand':
                        $idType = null;
                        break;
                    case 'brandn':
                        $idType = null;
                        break;
                    case 'seckill':
                        $idType = null;
                        break;
                    case 'auction':
                        $idType = null;
                        break;
                    case 'package':
                        $idType = null;
                        break;
                    case 'activity':
                        $idType = null;
                        break;
                    case 'snatch':
                        $idType = null;
                        break;
                    case 'exchange':
                        $idType = null;
                        break;
                    case 'store_street':
                        $idType = null;
                        break;
                    case 'presale':
                        $idType = null;
                        break;
                    case 'categoryall':
                        $idType = null;
                        break;
                    case 'merchants':
                        $idType = null;
                        break;
                    case 'merchants_index':
                        $idType = null;
                        break;
                    case 'message':
                        $idType = null;
                        break;
                    case 'wholesale':
                        $idType = null;
                        break;
                    case 'gift_gard':
                        $idType = null;
                        break;
                    case 'history_list':
                        $idType = null;
                        break;
                    case 'merchants_steps':
                        $idType = null;
                        break;
                    case 'merchants_steps_site':
                        $idType = null;
                        break;
                    default:
                        $idType = ['id' => ''];
                        break;
                }
            }

            //rewrite
            if ($rewrite && $idType == null) {
                $url = $this->dscUrl($app . '.html', config('app.url'));
            } else {
                if (strpos($initUrl, 'keywords=') !== false) {
                    $url = $initUrl;
                } else {
                    $params = empty($params) ? $idType : $params;
                    $url = $this->buildUri($app, $params, $append, $page, $keywords, $size);
                }
            }
        }

        if ($url) {
            return $url;
        } else {
            if ((strpos($initUrl, 'http://') === false && strpos($initUrl, 'https://') === false)) {
                return $this->dscUrl($initUrl, config('app.url'));
            } else {
                return $initUrl;
            }
        }
    }

    /**
     * 转化对象数组
     *
     * @param $order
     * @return array|bool|\mix|string
     */
    public function getStrArray1($order)
    {
        $arr = [];
        if ($order) {
            foreach ($order as $key => $row) {
                $row = explode("@", $row);
                $arr[$row[0]] = $row[1];
            }

            $arr = json_encode($arr, JSON_UNESCAPED_UNICODE);
            $arr = dsc_decode($arr);
        } else {
            $arr = (object)$arr;
        }

        return $arr;
    }

    /**
     * 转化数组
     *
     * @param $id
     * @return array
     */
    public function getStrArray2($id)
    {
        $arr = [];
        if ($id) {
            foreach ($id as $key => $row) {
                if ($row) {
                    $row = explode("-", $row);
                    $arr[$row[0]] = $row[1];
                }
            }
        }

        return $arr;
    }

    /**
     * 店铺地址
     *
     * @param string $url
     * @param int $cid
     * @param int $bid
     * @param string $keyword
     * @param int $price_min
     * @param int $price_max
     * @param string $filter_attr
     * @param int $page
     * @param string $sort
     * @param string $order
     * @param int $is_domain
     * @return string
     */
    private function merchantsStoreUrl($url = '', $cid = 0, $bid = 0, $keyword = '', $price_min = 0, $price_max = 0, $filter_attr = '', $page = 0, $sort = '', $order = '', $is_domain = 0)
    {
        $rewrite = $this->config['rewrite'] ?? 0;
        $rewrite = intval($rewrite);

        $uri = '';
        if ($rewrite) {
            if (!empty($cid)) {
                $uri .= '-c' . $cid;
            }
            if (!empty($bid)) {
                $uri .= '-b' . $bid;
            }
            if (!empty($keyword)) {
                $uri .= '-keyword' . $keyword;
            }
            if ($price_min > 0) {
                $uri .= '-min' . $price_min;
            }
            if ($price_max > 0) {
                $uri .= '-max' . $price_max;
            }
            if (!empty($filter_attr)) {
                $uri .= '-attr' . $filter_attr;
            }
            if (!empty($page)) {
                $uri .= '-' . $page;
            }
            if (!empty($sort)) {
                $uri .= '-' . $sort;
            }
            if (!empty($order)) {
                $uri .= '-' . $order;
            }

            if ($is_domain == 1) {
                $uri = ltrim($uri, '-');
            }
        } else {
            if (!empty($cid)) {
                $uri .= '&amp;id=' . $cid;
            }

            if (!empty($bid)) {
                $uri .= '&amp;brand=' . $bid;
            }
            if (!empty($keyword)) {
                $uri .= '&amp;keyword=' . $keyword;
            }

            if ($price_min > 0) {
                $uri .= '&amp;price_min=' . $price_min;
            }

            if ($price_max > 0) {
                $uri .= '&amp;price_max=' . $price_max;
            }

            if (!empty($filter_attr)) {
                $uri .= '&amp;filter_attr=' . $filter_attr;
            }

            if (!empty($page)) {
                $uri .= '&amp;page=' . $page;
            }
            if (!empty($sort)) {
                $uri .= '&amp;sort=' . $sort;
            }
            if (!empty($order)) {
                $uri .= '&amp;order=' . $order;
            }

            if ($is_domain == 1) {
                $uri = ltrim($uri, '&amp;');
            }
        }

        return $url . $uri;
    }

    /**
     * 店铺二级域名
     *
     * @param int $seller_id
     * @param array $param
     * @return bool|\Illuminate\Contracts\Routing\UrlGenerator|string
     * @throws \Exception
     */
    public function sellerUrl($seller_id = 0, $param = [])
    {
        $url = parent::sellerDomain($seller_id);

        if (empty($url)) {
            $param['urid'] = $seller_id;
            $url = $this->buildUri('merchants_store', $param);
        } else {
            $rewrite = $this->config['rewrite'] ?? 0;
            $rewrite = intval($rewrite);

            $url = rtrim($url, '/') . '/';

            if (!empty($param)) {
                if ($rewrite == 0) {
                    $url .= config('app.store_param') . "?";
                }
            }

            $cid = $param['cid'] ?? 0;
            $bid = $param['bid'] ?? 0;
            $keyword = $param['keyword'] ?? '';
            $price_min = $param['price_min'] ?? 0;
            $price_max = $param['price_max'] ?? 0;
            $filter_attr = $param['filter_attr'] ?? '';
            $page = $param['page'] ?? 0;
            $sort = $param['sort'] ?? '';
            $order = $param['order'] ?? '';

            $url = $this->merchantsStoreUrl($url, $cid, $bid, $keyword, $price_min, $price_max, $filter_attr, $page, $sort, $order, 1);

            if (!empty($param)) {
                if ($rewrite > 0) {
                    $url .= ".html";
                }
            }
        }

        return $url;
    }

    /**
     * 字符串或者数组编码格式转换gbk->utf-8
     * @param $data array|string
     * @return array|string
     */
    public function gbkToUtf8($data = [])
    {
        if (!empty($data)) {
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        $data[$k] = $this->gbkToUtf8($v);
                    } else {
                        $data[$k] = $this->toUtf8Iconv($v);
                    }
                }
                return $data;
            } else {
                $data = $this->toUtf8Iconv($data);
                return $data;
            }
        }

        return [];
    }

    /**
     * 循环转码成utf8内容
     *
     * @param string $str
     * @return string
     */
    public function toUtf8Iconv($str)
    {
        if (EC_CHARSET != 'utf-8') {
            if (is_string($str)) {
                return dsc_iconv(EC_CHARSET, 'utf-8', $str);
            } elseif (is_array($str)) {
                foreach ($str as $key => $value) {
                    $str[$key] = $this->toUtf8Iconv($value);
                }
                return $str;
            } elseif (is_object($str)) {
                foreach ($str as $key => $value) {
                    $str->$key = $this->toUtf8Iconv($value);
                }
                return $str;
            } else {
                return $str;
            }
        }
        return $str;
    }

    /**
     * 获取数组key值
     *
     * @param array $list
     * @return array|\Illuminate\Support\Collection
     */
    public function arrayKeys($list = [])
    {
        if ($list) {
            $list = collect($list)->keys();
            $list = $list->all();
        }

        return $list;
    }

    /**
     * 定义cookie
     *
     * @param string $name
     * @param int $val
     */
    public function dscCookieStorage($name = '', $val = 0, $num = 0)
    {
        $cookie_name = request()->cookie($name);

        if ($cookie_name) {
            $history = explode(',', $cookie_name);

            array_unshift($history, $val);
            $history = array_unique($history);

            $num = !empty($num) ? $num : $this->config['history_number'];

            while (count($history) > $num) {
                array_pop($history);
            }

            cookie()->queue($name, implode(',', $history), 60 * 24 * 30);
        } else {
            cookie()->queue($name, $val, 60 * 24 * 30);
        }
    }

    /**
     * 重新获得品牌图片的地址
     *
     * @param string $image
     * @return string
     */
    public function brandImagePath($image = '')
    {
        $url = empty($image) ? $this->config['no_brand'] : $image;

        return $url;
    }

    /**
     * 存储过滤条件
     * @param array $filter 过滤条件
     * @param string $param_str 参数字符串，由list函数的参数组成
     */
    public function setSessionFilter($filter, $param_str = '')
    {
        $action = request()->route()->getAction();
        $prefix = !empty($action['prefix']) ? $action['prefix'] : 'dsc';
        $filterfile = $prefix . '.lastfilter.' . basename(PHP_SELF, '.php');

        if ($param_str) {
            $filterfile .= $param_str;
        }

        $lastfilter = urlencode(json_encode($filter));
        session()->put($filterfile, $lastfilter);
    }

    /**
     * 获取上一次过滤条件
     *
     * @param string $param_str 参数字符串，由list函数的参数组成
     * @return array|bool 如果有，返回array('filter' => $filter)；否则返回false
     */
    public function getSessionFilter($param_str = '')
    {
        $action = request()->route()->getAction();
        $prefix = !empty($action['prefix']) ? $action['prefix'] : 'dsc';
        $filterfile = $prefix . '.lastfilter.' . basename(PHP_SELF, '.php');

        if ($param_str) {
            $filterfile .= $param_str;
        }

        if (session()->has($filterfile) && request()->get('uselastfilter')) {
            $lastfilter = session($filterfile);
            session()->forget($prefix); // 读取之后忘记

            return json_decode(urldecode($lastfilter), true);
        } else {
            return false;
        }
    }

    /**
     * 分页的信息加入条件的数组
     *
     * @param $filter
     * @return mixed
     */
    public function pageAndSize($filter)
    {
        /* 每页显示 */
        $page_size = request()->cookie('dsccp_page_size');
        if (request()->has('page_size') && intval(request()->input('page_size')) > 0) {
            $filter['page_size'] = intval(request()->input('page_size'));
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        // 第几页
        $page = request()->input('page') ?? 0;

        $filter['page'] = $page <= 0 ? 1 : intval($page);

        /* page 总数 */
        $filter['page_count'] = (!empty($filter['record_count']) && $filter['record_count'] > 0) ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 边界处理 */
        if ($filter['page'] > $filter['page_count']) {
            $filter['page'] = $filter['page_count'];
        }

        $filter['start'] = ($filter['page'] - 1) * $filter['page_size'];

        return $filter;
    }

    /**
     * 版权信息处理
     * @return \Illuminate\Config\Repository|mixed|string
     */
    public static function copyright()
    {
        $copyright = '';
        if (!empty(config('shop.show_copyright')) && config('shop.show_copyright') == 1) {
            $copyright_text = config('shop.copyright_text') ?? '';
            $copyright_link = config('shop.copyright_link') ?? '';
            $copyright = !empty($copyright_link) ? "<a href='" . html_out($copyright_link) . "' target='_blank' >" . $copyright_text . "</a>" : $copyright_text;
        }
        return $copyright;
    }

    /**
     * 自定义客服链接
     *
     * @param int $ru_id
     * @return string
     */
    public static function getServiceUrl($ru_id = 0)
    {
        if (empty($ru_id)) {
            $service_url = config('shop.service_url');
        } else {
            $service_url = SellerShopinfo::where('ru_id', $ru_id)->value('service_url');
        }
        return $service_url ? html_out($service_url) : '';
    }
}
