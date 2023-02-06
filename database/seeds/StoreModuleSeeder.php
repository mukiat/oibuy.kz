<?php

use App\Repositories\Common\TimeRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 门店模块数据填充
 * Class DrpModuleSeeder
 */
class StoreModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->adminAction();

        $this->config();

        // 门店隐私协议
        $this->store_privacy_agreement();
    }


    private function adminAction()
    {
    }

    private function config()
    {
    }

    /**
     * 门店隐私保护协议
     */
    private function store_privacy_agreement()
    {
        // 门店隐私保护协议
        $result = DB::table('article')->where('cat_id', '-3')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'cat_id' => '-3',
                    'title' => '门店隐私保护协议',
                    'content' => '<p>以此声明对本站用户隐私保护的许诺。随着本站服务范围的扩大，会随时更新隐私声明。我们欢迎您随时查看隐私声明。详细隐私政策，您可参考《隐私声明》。</p>
<p>本网站非常重视对用户隐私权的保护，用户的邮件及手机号等个人资料为用户重要隐私，本站承诺不会将个人资料用作它途；承诺不会在未获得用户许可的情况下擅自将用户的个人资料信息出租或出售给任何第三方，但以下情况除外：</p>
<p>A、用户同意让第三方共享资料；</p>
<p>B、用户为享受产品和服务同意公开其个人资料；</p>
<p>C、本站发现用户违反了本站服务条款或本站其它使用规定。</p>
<h5>使用说明</h5> 
<p>用户可以通过设定的密码来保护账户和资料安全。用户应当对其密码的保密负全部责任。请不要和他人分享此信息。如果您使用的是公共电脑，请在离开电脑时退出本网站、以保证您的信息不被后来的使用者获取。</p>
<h5>服务条款说明</h5>   
<p>接受本网站的用户同时受本站用户协议的约束。</p>',
                    'add_time' => TimeRepository::getGmTime(),
                    'article_type' => 3,
                    'is_open' => 1,
                ]
            ];
            DB::table('article')->insert($rows);
        }

    }

}
