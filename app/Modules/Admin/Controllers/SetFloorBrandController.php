<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\FloorContent;
use App\Models\Template;
use App\Repositories\Common\BaseRepository;

class SetFloorBrandController extends InitController
{
    public function index()
    {
        load_helper('template', 'admin');
        load_helper('goods', 'admin');

        $act = empty($_REQUEST['act']) ? 'list' : trim($_REQUEST['act']);

        /*------------------------------------------------------ */
        //-- 模版列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            admin_priv('template_select');

            $filename = empty($_REQUEST['filename']) ? 'index' : trim($_REQUEST['filename']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['floor_content_list']);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['floor_content_add'], 'href' => "set_floor_brand.php?act=add&filename=" . $filename]);

            // 获得当前的模版的信息
            $curr_template = $GLOBALS['_CFG']['template'];
            $floor_content = $this->get_floors($curr_template, $filename);
            $this->smarty->assign('floor_content', $floor_content);

            $this->smarty->assign('full_page', 1);
            return $this->smarty->display('floor_content_list.dwt');
        } elseif ($act == 'add') {
            admin_priv('template_select');

            $filename = empty($_REQUEST['filename']) ? 'index' : trim($_REQUEST['filename']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['floor_content_list'], 'href' => "set_floor_brand.php?act=list&filename=" . $filename]);
            /* 获得当前的模版的信息 */
            $curr_template = $GLOBALS['_CFG']['template'];
            $template = $this->get_template($curr_template, $filename, $GLOBALS['_LANG']['home_floor']);

            $this->smarty->assign('filename', $filename);
            $this->smarty->assign('template', $template);

            set_default_filter(); //设置默认筛选

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['set_floor']);
            return $this->smarty->display('floor_content_add.dwt');
        } elseif ($act == 'edit') {
            admin_priv('template_select');
            /* 获得当前的模版的信息 */

            $filename = !empty($_GET['filename']) ? trim($_GET['filename']) : '';
            $theme = !empty($_GET['theme']) ? trim($_GET['theme']) : '';
            $region = !empty($_GET['region']) ? trim($_GET['region']) : '';
            $cat_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['floor_content_list'], 'href' => "set_floor_brand.php?act=list"]);

            $floor_content = $this->get_floor_content($theme, $filename, $cat_id, $region);
            $template = $this->get_template($theme, $filename, $GLOBALS['_LANG']['home_floor']);

            $this->smarty->assign('filename', $filename);
            $this->smarty->assign('template', $template);
            $this->smarty->assign('floor_content', $floor_content);

            $this->smarty->assign('cat_id', $cat_id);

            set_default_filter(0, $cat_id); //设置默认筛选

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['set_floor']);
            return $this->smarty->display('floor_content_add.dwt');
        } elseif ($act == 'remove') {
            $filename = !empty($_GET['filename']) ? trim($_GET['filename']) : 0;
            $theme = !empty($_GET['theme']) ? trim($_GET['theme']) : 0;
            $region = !empty($_GET['region']) ? trim($_GET['region']) : '';
            $cat_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;

            FloorContent::where('filename', $filename)
                ->where('theme', $theme)
                ->where('id', $cat_id)
                ->where('region', $region)
                ->delete();

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'set_floor_brand.php?filename=index'];
            return sys_msg($GLOBALS['_LANG']['remove_success'], 0, $link);
        }
    }

    private function get_floor_content($curr_template, $filename, $id = 0, $region = '')
    {
        $row = FloorContent::where('filename', $filename)->where('theme', $curr_template);

        if (!empty($id)) {
            $row = $row->where('id', $id);
        }

        if (!empty($region)) {
            $row = $row->where('region', $region);
        }

        $row = BaseRepository::getToArrayGet($row);

        return $row;
    }

    private function get_floors($curr_template, $filename)
    {
        $row = FloorContent::where('filename', $filename)
            ->where('theme', $curr_template);

        $row = $row->groupBy('filename,theme,id');

        $row = BaseRepository::getToArrayGet($row);

        foreach ($row as $key => $val) {
            $brand_list = Brand::whereHasIn('getFloorContent', function ($query) use ($val) {
                $query->where('filename', $val['filename'])
                    ->where('theme', $val['theme'])
                    ->where('id', $val['id'])
                    ->where('region', $val['region']);
            });

            $brand_list = BaseRepository::getToArrayGet($brand_list);

            $row[$key]['brand_list'] = $brand_list;

            $cat_name = Category::where('cat_id', $val['id'])->value('cat_name');
            $cat_name = $cat_name ? $cat_name : '';
            $row[$key]['cat_name'] = $cat_name;
        }

        return $row;
    }

    private function get_template($curr_template, $filename, $region)
    {
        $res = Template::where('filename', $filename)->where('theme', $curr_template)->where('region', $region);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        foreach ($res as $key => $row) {
            $arr[$key] = $row;
            $arr[$key]['filename'] = $filename;

            $cat_name = Category::where('cat_id', $row['id'])->value('cat_name');
            $cat_name = $cat_name ? $cat_name : '';
            $arr[$key]['cat_name'] = $cat_name;
        }

        return $arr;
    }
}
