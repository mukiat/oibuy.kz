
### 数据迁移 与 数据填充

- 数据迁移
```
php artisan migrate
```

- 数据填充

```
php artisan vendor:publish --provider="App\Modules\Wxapp\WxappServiceProvider" --tag=seeds --force
composer dump-autoload
php artisan db:seed --class=WeappModuleSeeder
```

   