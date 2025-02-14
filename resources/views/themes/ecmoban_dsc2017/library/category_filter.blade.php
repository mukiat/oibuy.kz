<div class="filter-wrap">
    <div class="filter-sort">
        <a href="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=goods_id&order=
@if($pager['sort'] == 'goods_id' && $pager['order'] == 'DESC')
ASC
@else
DESC
@endif
#goods_list" class="
@if($pager['sort'] == 'goods_id')
curr
@endif
">{{ $lang['default'] }}<i class="iconfont
@if($pager['sort'] == 'goods_id' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
        <a href="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=sales_volume&order=
@if($pager['sort'] == 'sales_volume' && $pager['order'] == 'DESC')
ASC
@else
DESC
@endif
#goods_list" class="
@if($pager['sort'] == 'sales_volume')
curr
@endif
">{{ $lang['sales_volume'] }}<i class="iconfont
@if($pager['sort'] == 'sales_volume' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
        <a href="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=last_update&order=
@if($pager['sort'] == 'last_update' && $pager['order'] == 'DESC')
ASC
@else
DESC
@endif
#goods_list" class="
@if($pager['sort'] == 'last_update')
curr
@endif
">{{ $lang['is_new'] }}<i class="iconfont
@if($pager['sort'] == 'last_update' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
        <a href="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=comments_number&order=
@if($pager['sort'] == 'comments_number' && $pager['order'] == 'ASC')
DESC
@else
ASC
@endif
#goods_list" class="
@if($pager['sort'] == 'comments_number')
curr
@endif
">{{ $lang['Comment_number'] }}<i class="iconfont
@if($pager['sort'] == 'comments_number' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
        <a href="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&page={{ $pager['page'] }}&sort=shop_price&order=
@if($pager['sort'] == 'shop_price' && $pager['order'] == 'ASC')
DESC
@else
ASC
@endif
#goods_list" class="
@if($pager['sort'] == 'shop_price')
curr
@endif
">{{ $lang['price'] }}<i class="iconfont
@if($pager['sort'] == 'shop_price' && $pager['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
    </div>
    <div class="filter-range">
        <div class="fprice">
        	<form method="POST" action="
@if($script_name == 'brand')
brand.php
@else
category.php
@endif
" class="sort" name="listform">

@if($filename != "history_list")

                <div class="fP-box">
                    <input type="text" name="price_min" class="f-text price-min" autocomplete="off" maxlength="6" placeholder="￥" id="price-min" value="
@if($price_min)
{{ $price_min }}
@endif
">
                    <span>&nbsp;~&nbsp;</span>
                    <input type="text" name="price_max" class="f-text price-max" autocomplete="off" maxlength="6" id="price-max"value="
@if($price_max)
{{ $price_max }}
@endif
" placeholder="￥">
                </div>
                <div class="fP-expand">
                	<a class="ui-btn-s ui-btn-clear" href="javascript:void(0);" id="clear_price">{{ $lang['clear'] }}</a>
					<a href="javascript:void(0);" class="ui-btn-s ui-btn-s-primary ui-btn-submit">{{ $lang['assign'] }}</a>
                </div>

@endif

                <input type="hidden" name="category" value="{{ $category }}" />
                <input type="hidden" name="display" value="{{ $pager['display'] }}" id="display" />
                <input type="hidden" name="brand" value="{{ $brand_id }}" />
                <input type="hidden" name="ubrand" value="{{ $ubrand }}" />
                <input type="hidden" name="filter_attr" value="{{ $filter_attr }}" />
                <input type="hidden" name="sort" value="{{ $pager['sort'] }}" />
                <input type="hidden" name="order" value="{{ $pager['order'] }}" />
                <input type="hidden" name="script_name" value="category" />
            @csrf </form>
        </div>
        <div class="fcheckbox">
                <div class="checkbox_items">
                <div class="checkbox_item
@if($pager['ship'])
 checkbox-checked
@endif
">
                    <input type="checkbox" name="fk-type" class="ui-checkbox" value="" id="store-checkbox-011"
@if($pager['ship'])
checked="checked"
@endif
>
                    <label class="ui-label" for="store-checkbox-011">{{ $lang['Free_shipping'] }}</label>
                    <i id="input-i1" rev="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&ship=1&self={{ $pager['self'] }}&have={{ $pager['have'] }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}#goods_list"></i>
                    <i id="input-i2" rev="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&ship=0&self={{ $pager['self'] }}&have={{ $pager['have'] }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}#goods_list"></i>
                </div>
                <div class="checkbox_item
@if($pager['self'])
 checkbox-checked
@endif
">
                    <input type="checkbox" name="fk-type" class="ui-checkbox" value="" id="store-checkbox-012"
@if($pager['self'])
checked="checked"
@endif
>
                    <label class="ui-label" for="store-checkbox-012">{{ $lang['Self_goods'] }}</label>
                    <i id="input-i1" rev="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&ship={{ $pager['ship'] }}&self=1&have={{ $pager['have'] }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}#goods_list"></i>
                    <i id="input-i2" rev="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&ship={{ $pager['ship'] }}&self=0&have={{ $pager['have'] }}&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}#goods_list"></i>
                </div>
                <div class="checkbox_item
@if($pager['have'])
 checkbox-checked
@endif
">
                    <input type="checkbox" name="fk-type" class="ui-checkbox" value="" id="store-checkbox-013"
@if($pager['have'])
checked="checked"
@endif
>
                    <label class="ui-label" for="store-checkbox-013">{{ $lang['Only_have_inventory'] }}</label>
                    <i id="input-i1" rev="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&ship={{ $pager['ship'] }}&self={{ $pager['self'] }}&have=1&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}#goods_list"></i>
                    <i id="input-i2" rev="{{ $script_name }}.php?category={{ $category }}&display={{ $pager['display'] }}&brand={{ $brand_id }}&ubrand={{ $ubrand }}&price_min={{ $price_min }}&price_max={{ $price_max }}&filter_attr={{ $filter_attr }}&ship={{ $pager['ship'] }}&self={{ $pager['self'] }}&have=0&sort={{ $pager['sort'] }}&order={{ $pager['order'] }}#goods_list"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="filter-right">

@if(!$category_load_type)

        <div class="button-page">
            <span class="pageState"><span>{{ $pager['page'] }}</span>/{{ $pager['page_count'] }}</span>
            <a href="
@if($pager['page_prev'])
{!! $pager['page_prev'] !!}
@else
javascript:void(0);
@endif
"
@if($pager['page_prev'])
 class="page page_prev"
@endif
 title="{{ $lang['page_prev'] }}"><i class="iconfont icon-left"></i></a>
            <a href="
@if($pager['page_next'])
{!! $pager['page_next'] !!}
@else
javascript:void(0);
@endif
"
@if($pager['page_next'])
 class="page page_next"
@endif
 title="{{ $lang['page_next'] }}"><i class="iconfont icon-right"></i></a>
        </div>

@endif


@if($dwt_filename != 'history_list')

        <div class="styles">
            <ul class="items" ectype="fsortTab">
                <li class="item
@if($dsc_display == 'list')
current
@endif
" data-type="large"><a href="{!! $pager['left_display'] !!}" title="{{ $lang['big_pic'] }}{{ $lang['pattern'] }}"><span class="iconfont icon-switch-grid"></span>{{ $lang['big_pic'] }}</a></li>
                <li class="item
@if($dsc_display == 'grid')
current
@endif
" data-type="samll"><a href="{!! $pager['right_display'] !!}" title="{{ $lang['Small_pic'] }}{{ $lang['pattern'] }}"><span class="iconfont icon-switch-list"></span>{{ $lang['Small_pic'] }}</a></li>
            </ul>
        </div>

@endif

    </div>
</div>
