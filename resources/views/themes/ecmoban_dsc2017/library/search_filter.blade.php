<div class="filter-wrap">
    <div class="filter-sort">
        <a href="search.php?keywords={{ $pager['search']['keywords'] }}&display={{ $pager['display'] }}&price_min={{ $pager['search']['price_min'] }}&price_max={{ $pager['search']['price_max'] }}&page={{ $pager['page'] }}&sort=goods_id&is_ship={{ $pager['search']['is_ship'] }}&order=
@if($pager['search']['sort'] == 'goods_id' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif

@if($cou_id)
&cou_id={{ $cou_id }}
@endif
" class="
@if($pager['search']['sort'] == 'goods_id')
curr
@endif
">{{ $lang['default'] }}<i class="iconfont
@if($pager['search']['sort'] == 'goods_id' && $pager['search']['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
        <a href="search.php?keywords={{ $pager['search']['keywords'] }}&display={{ $pager['display'] }}&price_min={{ $pager['search']['price_min'] }}&price_max={{ $pager['search']['price_max'] }}&page={{ $pager['page'] }}&sort=sales_volume&is_ship={{ $pager['search']['is_ship'] }}&order=
@if($pager['search']['sort'] == 'sales_volume' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif

@if($cou_id)
&cou_id={{ $cou_id }}
@endif
" class="
@if($pager['search']['sort'] == 'sales_volume')
curr
@endif
">{{ $lang['sales_volume'] }}<i class="iconfont
@if($pager['search']['sort'] == 'sales_volume' && $pager['search']['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
        <a href="search.php?keywords={{ $pager['search']['keywords'] }}&display={{ $pager['display'] }}&price_min={{ $pager['search']['price_min'] }}&price_max={{ $pager['search']['price_max'] }}&page={{ $pager['page'] }}&sort=last_update&is_ship={{ $pager['search']['is_ship'] }}&order=
@if($pager['search']['sort'] == 'last_update' && $pager['search']['order'] == 'DESC')
ASC
@else
DESC
@endif

@if($cou_id)
&cou_id={{ $cou_id }}
@endif
" class="
@if($pager['search']['sort'] == 'last_update')
curr
@endif
">{{ $lang['is_new'] }}<i class="iconfont
@if($pager['search']['sort'] == 'last_update' && $pager['search']['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
        <a href="search.php?keywords={{ $pager['search']['keywords'] }}&display={{ $pager['display'] }}&price_min={{ $pager['search']['price_min'] }}&price_max={{ $pager['search']['price_max'] }}&page={{ $pager['page'] }}&sort=comments_number&is_ship={{ $pager['search']['is_ship'] }}&order=
@if($pager['search']['sort'] == 'comments_number' && $pager['search']['order'] == 'ASC')
DESC
@else
ASC
@endif

@if($cou_id)
&cou_id={{ $cou_id }}
@endif
#goods_list" class="
@if($pager['search']['sort'] == 'comments_number')
curr
@endif
">{{ $lang['Comment_number'] }}<i class="iconfont
@if($pager['search']['sort'] == 'comments_number' && $pager['search']['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
        <a href="search.php?keywords={{ $pager['search']['keywords'] }}&display={{ $pager['display'] }}&price_min={{ $pager['search']['price_min'] }}&price_max={{ $pager['search']['price_max'] }}&page={{ $pager['page'] }}&sort=shop_price&is_ship={{ $pager['search']['is_ship'] }}&order=
@if($pager['search']['sort'] == 'shop_price' && $pager['search']['order'] == 'ASC')
DESC
@else
ASC
@endif

@if($cou_id)
&cou_id={{ $cou_id }}
@endif
" class="
@if($pager['search']['sort'] == 'shop_price')
curr
@endif
">{{ $lang['price'] }}<i class="iconfont
@if($pager['search']['sort'] == 'shop_price' && $pager['search']['order'] == 'DESC')
icon-arrow-down
@else
icon-arrow-up
@endif
"></i></a>
    </div>
    <div class="filter-range">
        <div class="fprice">
            <form method="GET" class="sort" name="listform" action="">
                <div class="fP-box">
                    <input type="text" name="price_min" class="f-text price-min" autocomplete="off" maxlength="6" id="price-min" placeholder="￥" value="
@if($price_min)
{{ $price_min }}
@endif
">
                    <span>&nbsp;~&nbsp;</span>
                    <input type="text" name="price_max" class="f-text price-max" autocomplete="off" maxlength="6" id="price-max" value="
@if($price_max)
{{ $price_max }}
@endif
" placeholder="￥">
                </div>
                <div class="fP-expand">
                    <a class="ui-btn-s ui-btn-clear" href="javascript:void(0);" id="clear_price">{{ $lang['clear'] }}</a>
                    <a href="javascript:void(0);" class="ui-btn-s ui-btn-s-primary ui-btn-submit">{{ $lang['assign'] }}</a>
                </div>
                <input type="hidden" name="keywords" value="{{ $pager['search']['keywords'] }}" />
                <input type="hidden" name="display" value="{{ $pager['display'] }}" id="display" />
                <input type="hidden" name="is_ship" value="{{ $pager['search']['is_ship'] }}" />
                <input type="hidden" name="sort" value="{{ $pager['search']['sort'] }}" />
                <input type="hidden" name="order" value="{{ $pager['search']['order'] }}" />
            @csrf </form>
        </div>

        <div class="fcheckbox">
                <div class="checkbox_items">
                <div class="checkbox_item
@if($pager['search']['is_ship'] == 'is_shipping')
checkbox-checked
@endif
">
                    <input type="checkbox" name="fk-type" class="ui-checkbox" value="" id="store-checkbox-011"
@if($pager['search']['is_ship'] == 'is_shipping')
checked="checked"
@endif
>
                    <label class="ui-label" for="store-checkbox-011">{{ $lang['Free_shipping'] }}</label>
                    <i id="input-i1" rev="search.php?keywords={{ $pager['search']['keywords'] }}
@if($cou_id)
&cou_id={{ $cou_id }}
@endif
&display={{ $pager['display'] }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort={{ $pager['search']['sort'] }}&is_ship=is_shipping
@if($pager['search']['self_support'] == 1)
&is_self=1
@endif

@if($pager['search']['have'] == 1)
&have=1
@endif
"></i>
                    <i id="input-i2" rev="search.php?keywords={{ $pager['search']['keywords'] }}
@if($cou_id)
&cou_id={{ $cou_id }}
@endif
&display={{ $pager['display'] }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort={{ $pager['search']['sort'] }}&order={{ $pager['search']['order'] }}
@if($pager['search']['self_support'] == 1)
&is_self=1
@endif

@if($pager['search']['have'] == 1)
&have=1
@endif
"></i>
                </div>
                <div class="checkbox_item
@if($pager['search']['self_support'] == 1)
checkbox-checked
@endif
">
                    <input type="checkbox" name="fk-type" class="ui-checkbox" value="" id="store-checkbox-012"
@if($pager['search']['self_support'] == 1)
checked="checked"
@endif
>
                    <label class="ui-label" for="store-checkbox-012">{{ $lang['Self_goods'] }}</label>
                    <i id="input-i1" rev="search.php?keywords={{ $pager['search']['keywords'] }}
@if($cou_id)
&cou_id={{ $cou_id }}
@endif
&display={{ $pager['display'] }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort={{ $pager['search']['sort'] }}&is_self=1
@if($pager['search']['is_ship'] == 'is_shipping')
&is_ship=is_shipping
@endif

@if($pager['search']['have'] == 1)
&have=1
@endif
"></i>
                    <i id="input-i2" rev="search.php?keywords={{ $pager['search']['keywords'] }}
@if($cou_id)
&cou_id={{ $cou_id }}
@endif
&display={{ $pager['display'] }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort={{ $pager['search']['sort'] }}&order={{ $pager['search']['order'] }}
@if($pager['search']['is_ship'] == 'is_shipping')
&is_ship=is_shipping
@endif

@if($pager['search']['have'] == 1)
&have=1
@endif
"></i>
                </div>
                <div class="checkbox_item
@if($pager['search']['have'] == 1)
checkbox-checked
@endif
">
                    <input type="checkbox" name="fk-type" class="ui-checkbox" value="" id="store-checkbox-012"
@if($pager['search']['have'] == 1)
checked="checked"
@endif
>
                    <label class="ui-label" for="store-checkbox-013">{{ $lang['Only_have_inventory'] }}</label>
                    <i id="input-i1" rev="search.php?keywords={{ $pager['search']['keywords'] }}
@if($cou_id)
&cou_id={{ $cou_id }}
@endif
&display={{ $pager['display'] }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort={{ $pager['search']['sort'] }}&have=1
@if($pager['search']['is_ship'] == 'is_shipping')
&is_ship=is_shipping
@endif

@if($pager['search']['self_support'] == 1)
&is_self=1
@endif
"></i>
                    <i id="input-i2" rev="search.php?keywords={{ $pager['search']['keywords'] }}
@if($cou_id)
&cou_id={{ $cou_id }}
@endif
&display={{ $pager['display'] }}&price_min={{ $price_min }}&price_max={{ $price_max }}&page={{ $pager['page'] }}&sort={{ $pager['search']['sort'] }}&order={{ $pager['search']['order'] }}
@if($pager['search']['is_ship'] == 'is_shipping')
&is_ship=is_shipping
@endif

@if($pager['search']['self_support'] == 1)
&is_self=1
@endif
"></i>
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
                <li class="item current" data-type="large"><a href="javascript:void(0)" title="{{ $lang['big_pic'] }}{{ $lang['pattern'] }}"><span class="iconfont icon-switch-grid"></span>{{ $lang['big_pic'] }}</a></li>
                <li class="item" data-type="samll"><a href="javascript:void(0)" title="{{ $lang['Small_pic'] }}{{ $lang['pattern'] }}"><span class="iconfont icon-switch-list"></span>{{ $lang['Small_pic'] }}</a></li>
            </ul>
        </div>

@endif

    </div>
</div>
