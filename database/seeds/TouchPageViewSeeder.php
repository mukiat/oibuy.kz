<?php

use App\Models\TouchPageView;
use Illuminate\Database\Seeder;

class TouchPageViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->touch_page_view();
        // 更新设备
        $this->update_device();
        // 添加新设备 app wxapp
        $this->add_device();
    }

    public function touch_page_view()
    {
        $res = TouchPageView::query()->select('id', 'data')->where('ru_id', 0)->where('type', 'index')->first();

        if (isset($res->data) && !empty($res->data)) {
            if (stripos($res->data, '"isShow":true') === false) {
                $data = str_replace(',"data":{', ',"isShow":true,"data":{', $res->data);

                TouchPageView::where('id', $res->id)->update([
                    'data' => $data
                ]);
            }
        }
    }

    public function update_device()
    {
        // 更新首页 设备 h5
        $result = TouchPageView::where('ru_id', 0)->where('type', 'index')->where('device', '')->count();
        if ($result > 0) {
            TouchPageView::where('ru_id', 0)->where('type', 'index')->where('device', '')->update([
                'device' => 'h5'
            ]);
        }

        // 更新专题 设备 h5
        $result = TouchPageView::where('ru_id', 0)->where('type', 'topic')->where('device', '')->count();
        if ($result > 0) {
            TouchPageView::where('ru_id', 0)->where('type', 'topic')->where('device', '')->update([
                'device' => 'h5'
            ]);
        }

        // 更新店铺 设备 h5
        $result = TouchPageView::where('ru_id', '>', 0)->where('type', 'store')->where('device', '')->count();
        if ($result > 0) {
            TouchPageView::where('ru_id', '>', 0)->where('type', 'store')->where('device', '')->update([
                'device' => 'h5'
            ]);
        }
    }

    // 添加新设备 app wxapp
    public function add_device()
    {
        // 首页
        $result = TouchPageView::where('ru_id', 0)->where('type', 'index')->where('device', 'wxapp')->count();
        $result1 = TouchPageView::where('ru_id', 0)->where('type', 'index')->where('device', 'app')->count();
        if(empty($result) && empty($result1)){
            $result = TouchPageView::where('ru_id', 0)->where('type', 'index')->where('device', 'h5')->first();
            if($result){
                if(file_exists(MOBILE_WXAPP)){
                    $rows = [
                        [
                            'ru_id' => $result->ru_id,
                            'type' => $result->type,
                            'page_id' => $result->page_id,
                            'title' => $result->title,
                            'keywords' => $result->keywords,
                            'description' => $result->description,
                            'data' => $result->data,
                            'pic' => $result->pic,
                            'thumb_pic' => $result->thumb_pic,
                            'create_at' => $result->create_at,
                            'update_at' => $result->update_at,
                            'default' => $result->default,
                            'review_status' => $result->review_status,
                            'is_show' => $result->is_show,
                            'device' => 'wxapp'
                        ]
                    ];

                    TouchPageView::insert($rows);
                }

                if(file_exists(MOBILE_APP)){
                    $rows = [
                        [
                            'ru_id' => $result->ru_id,
                            'type' => $result->type,
                            'page_id' => $result->page_id,
                            'title' => $result->title,
                            'keywords' => $result->keywords,
                            'description' => $result->description,
                            'data' => $result->data,
                            'pic' => $result->pic,
                            'thumb_pic' => $result->thumb_pic,
                            'create_at' => $result->create_at,
                            'update_at' => $result->update_at,
                            'default' => $result->default,
                            'review_status' => $result->review_status,
                            'is_show' => $result->is_show,
                            'device' => 'app'
                        ]
                    ];

                    TouchPageView::insert($rows);
                }

            }
        }

        // 专题
        $result = TouchPageView::where('ru_id', 0)->where('type', 'topic')->where('device', 'wxapp')->count();
        $result1 = TouchPageView::where('ru_id', 0)->where('type', 'topic')->where('device', 'app')->count();
        if(empty($result) && empty($result1)){
            $result = TouchPageView::where('ru_id', 0)->where('type', 'topic')->where('device', 'h5')->get();
            $result = $result ? $result->toArray() : [];
            if($result){
                if(file_exists(MOBILE_WXAPP)){
                    foreach ($result as $key => $val) {
                        $rows = [
                            [
                                'ru_id' => $val['ru_id'],
                                'type' => $val['type'],
                                'page_id' => $val['page_id'],
                                'title' => $val['title'],
                                'keywords' => $val['keywords'],
                                'description' => $val['description'],
                                'data' => $val['data'],
                                'pic' => $val['pic'],
                                'thumb_pic' => $val['thumb_pic'],
                                'create_at' => $val['create_at'],
                                'update_at' => $val['update_at'],
                                'default' => $val['default'],
                                'review_status' => $val['review_status'],
                                'is_show' => $val['is_show'],
                                'device' => 'wxapp'
                            ]
                        ];

                        TouchPageView::insert($rows);
                    }
                }

                if(file_exists(MOBILE_APP)){
                    foreach ($result as $key => $val) {
                        $rows = [
                            [
                                'ru_id' => $val['ru_id'],
                                'type' => $val['type'],
                                'page_id' => $val['page_id'],
                                'title' => $val['title'],
                                'keywords' => $val['keywords'],
                                'description' => $val['description'],
                                'data' => $val['data'],
                                'pic' => $val['pic'],
                                'thumb_pic' => $val['thumb_pic'],
                                'create_at' => $val['create_at'],
                                'update_at' => $val['update_at'],
                                'default' => $val['default'],
                                'review_status' => $val['review_status'],
                                'is_show' => $val['is_show'],
                                'device' => 'app'
                            ]
                        ];

                        TouchPageView::insert($rows);
                    }
                }

            }
        }


    }



}
