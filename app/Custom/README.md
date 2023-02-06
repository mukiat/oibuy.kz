### 二次开发基本规范 （202106版本）

一、 当前 app\Custom 目录为二开模块，所有编程规范基于 Laravel 框架核心, 新增独立服务提供者 ServiceProvider 实现与其他模块解藕，更加优雅灵活的自定义编码体验

开发前请熟悉laravel文档：[laravel中文文档](https://learnku.com/docs/laravel/6.x)

> Guestbook 模块为开发demo 可作为参考，不实现任何功能。



- 新建模块目录，目录名自定义且不得与其他模块重名,  新建服务提供者 GuestbookServiceProvider

参考: [Laravel服务提供者](https://learnku.com/docs/laravel/6.x/providers/5132)

 exp: Guestbook、GuestbookServiceProvider



二、 开发模块目录基本结构
```
Guestbook
└───Commands 命令行
└───Config 配置文件
└───Controllers 控制器
└───database 数据迁移与数据填充
└───Events 事件类
└───Extensions 扩展类
└───Lang 后台菜单与权限语言包
└───Listeners 事件监听类
└───Models 数据模型类
└───public 前端静态资源
└───Repositories 模型仓库类
└───Resources
│   └───lang 主语言包
│   └───views 视图文件
└───Routes 路由文件
│   │   api.php  api接口路由
│   │   web.php  PC,后台路由
└───Services 服务类
└───Support 常用函数类
│   README.md
│   GuestbookServiceProvider.php 服务提供者
│   menu.php 平台后台菜单项
│   menu_seller.php 商家后台菜单项
│   priv.php 平台后台菜单权限控制项
│   priv_seller.php 商家后台菜单权限控制项
```

以上即开发模块基本目录结构与说明，当然你可以按需建其他目录。

### 常用代码
```php
// 获取开发模块配置信息
config('guestbook.name');
// 获取开发模块语言包
trans('guestbook::admin/guestbook.test');


```

```html
// 加载开发模块 公共视图
@extends('guestbook::layout')

// 引入组件
@include('guestbook::components.nav', ['arr'])

{{--加载css js--}}
@push('scripts')

@endpush
```

### 常用命令行
```
// 执行开发模块下 数据迁移
php artisan migrate

// 执行开发模块下 数据填充
php artisan vendor:publish --provider="App\Custom\Guestbook\GuestbookServiceProvider" --tag=seeds --force
composer dump
php artisan db:seed --class=GuestbookSeeder

// 发布资源文件JavaScript、CSS 和图片等文件
php artisan vendor:publish --provider="App\Custom\Guestbook\GuestbookServiceProvider" --tag=public --force

// 执行开发模块下 任务调试
php artisan custom:guestbook

```

### 开发场景指导

##### 后台开发
1. 开发场景：基于现有后台，每个开发模块需要增加菜单与权限。
    - 1.1 菜单分为平台后台菜单，商家后台菜单，按所需功能添加
    - 1.2 权限的关系：最高管理员登录平台后台后展示所有且可控制分配子管理员或商家所需的权限

exp: 新建 menu.php、 priv.php、 priv_seller.php 文件，文件名固定

对应关系
```
menu.php 菜单项 -> Lang/zh-CN/menu.php 菜单项语言包
priv.php 平台权限项 -> Lang/zh-CN/priv_action.php 权限项语言包
priv_seller.php 商家权限项 -> Lang/zh-CN/priv_action.php 权限项语言包
```


- 菜单项文件 menu.php

 ```
 /**
  * 顶级菜单 example
  */
 // $menu_top['custom_top'] = '26_custom';
 
 /**
  * 子菜单 example
  */
 $modules['26_custom']['01_custom'] = 'custom/index'; // url
 
 // 基于原有左侧菜单
 $menu_top['custom_top'] = '26_custom';
 ```

- 菜单语言包文件  Lang/zh-CN/menu.php (平台与商家可共用) (多语言目录名 en、zh-CN、zh-TW)
 ```
 // $_LANG['custom_top'] = '开发顶级菜单';
 $_LANG['26_custom'] = '开发菜单管理';
 $_LANG['01_custom'] = '开发子菜单';
 ```
- 平台菜单权限文件 priv.php
 ```
 $purview['01_custom'] = 'custom_code';
 ```

- 商家菜单权限文件 priv_seller.php
 ```
 $purview['01_custom'] = 'custom_code';
 
 ```

- 菜单权限语言包文件 Lang/zh-CN/priv_action.php (多语言目录名 en、zh-CN、zh-TW)

```php
$_LANG['custom_code'] = '开发菜单权限语言包';
```


- 使用数据填充 增加开发菜单权限控制 example: database/seeds/GuestbookSeeder.php

```php

    // 开发菜单权限 code 
    $count = DB::table('admin_action')->where('action_code', 'custom_code')->count();
    if (empty($count)) {
        // 父级菜单id
        $parent_id = DB::table('admin_action')->where('action_code', 'custom_top')->value('action_id');
        $parent_id = $parent_id ? $parent_id : 0;

        DB::table('admin_action')->insert([
            'parent_id' => $parent_id,
            'action_code' => 'custom_code',
            'seller_show' => 0, // 是否控制商家分配权限 0 否 1 是
        ]);
    }
        
```



>  前端样式 css、js、image 文件等  可以在 public 目录下 新建 guestbook/assets 目录，分别按模块建 对应的
css、js、image 目录 即可， 或使用命令行发布
```
php artisan vendor:publish --provider="App\Custom\Guestbook\GuestbookServiceProvider" --tag=public --force
```

exp:  blade模板中使用

```blade
{{ asset('guestbook/assets/css/style.css')  }}
{{ asset('guestbook/assets/js/index.js')  }}
{{ asset('guestbook/assets/image/image.png')  }}
```


2. 继承原功能开发 即在不修改原路由地址情况下，仅修改部分返回数据或增加部分扩展功能。

举例说明：
   原功能 admin/goods.php 返回商品列表，需要 新增 商品其他数据返回，或增加其他流程

   - 2.1 先找到原路由，如 商品列表 原完整路由为

```php
Route::prefix(ADMIN_PATH)->group(function () {
    Route::any('goods.php', 'GoodsController@index');
});
```
 复制此路由组至 当前模块 web.php 文件中(注意命令空间)
```php
Route::namespace('Admin')->prefix(ADMIN_PATH . '/')->group(function () {
    Route::any('goods.php', 'GoodsController@index')->name('admin/goods');
});
```

  - 2.2 在Admin目录下新建 GoodsController 继承 原 GoodsController 即可：

```php
<?php

namespace App\Custom\Guestbook\Controllers\Admin;

use App\Modules\Admin\Controllers\GoodsController as BaseController;

class GoodsController extends BaseController
{
     public function index() {
        return $this->smarty->display('goods_list.dwt'); // 对应smarty模板路径： app/Modules/Admin/Views/goods_list.dwt
    }
}
```

  - 2.3 关于继承开发 原smarty模板文件的解决方案(blade模板忽略)，比如默认 商品列表页模板 

对应代码
    
```php
$this->smarty->display('goods_list.dwt');
```

 可复制一份 goods_list.dwt 在同目录下新建目录 guestbook, 然后放进目录，调用

```php
$this->smarty->display('guestbook/goods_list.dwt');
```

3. 新建路由独立开发，完成与laravel开发一致

- 3.1 开发前台功能, 继承 App\Custom\Controller

```php
<?php

namespace App\Custom\Guestbook\Controllers;

use App\Custom\Controller as FrontController;

class IndexController extends FrontController
{
    public function index() {
        return $this->display();// 对应blade模板路径： app/Custom/Guestbook/Resource/views/index/index.blade.php
    }
}
```

- 3.2 开发后台功能，继承 App\Custom\BaseAdminController（平台）、App\Custom\BaseSellerController（商家）

```php
<?php

namespace App\Custom\Guestbook\Controllers\Admin;

use App\Custom\BaseAdminController as BaseController;

class NewAdminController extends BaseController
{
    public function index() {
        return $this->display();// 对应blade模板路径： app/Custom/Guestbook/Resource/views/new_admin/index.blade.php
    }
}
```


##### 接口开发

1. 在控制器目录下 新建 Api 目录，新建 ApiController.php 继承 App\Api\Foundation\Controllers\Controller

```php
<?php

namespace App\Custom\Guestbook\Api\Controllers;

use App\Api\Foundation\Controllers\Controller as FrontController;
use Illuminate\Http\Request;

class ApiController extends FrontController
{
    public function index(Request $request)
    {
        //
        return $this->succeed('api');
    }
}
```

2. 新增路由

```php
<?php

use Illuminate\Support\Facades\Route;

// api
Route::namespace('Api')->prefix('api/guestbook')->group(function () {

    Route::get('/', 'ApiController@index')->name('api.guestbook.index');

});
```

3. 测试
get api/guestbook
