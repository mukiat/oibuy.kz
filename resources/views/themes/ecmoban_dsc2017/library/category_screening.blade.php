

@if($brands['1'] || $price_grade['1'] || $filter_attr_list || $color_search || $get_bd || $g_price || $g_array || $c_array || $parray)

<div class="right-extra" rewrite={{ $rewrite }}>
    <div class="u_cloose">
        <dl>
            <dt>{{ $lang['Selected_condition'] }}：</dt>
            <dd>

@if(!$get_bd['bd'] && !$g_price && !$parray && !$c_array['attr_value'] && !$g_array)

                &nbsp;

@endif



@if($get_bd['bd'])

                <div class="get_item" title="{{ $get_bd['bd'] }}">
                    <b>{{ $lang['brand'] }}：</b>
                    <em>{{ $get_bd['bd'] }}</em>
                    <a href="{!! $get_bd['br_url'] !!}"></a>
                </div>

@endif



@if($g_price)


@foreach($g_price as $price)

                <div class="get_item" title="{!! $price['price_range'] !!}">
                    <b>{{ $lang['price'] }}：</b>
                    <em>{!! $price['price_range'] !!}</em>
                    <a href="{!! $price['url'] !!}"></a>
                </div>

@endforeach


@endif



@if($parray)

                <div class="get_item" title="{!! $price['min_max'] !!}">
                    <b>{{ $lang['price'] }}：</b>
                    <em>{!! $price['min_max'] !!}</em>
                    <a href="{!! $parray['purl'] !!}"></a>
                </div>

@endif



@if($c_array['attr_value'])

                <div class="get_item" title="{{ $c_array['attr_value'] }}">
                    <b>{{ $c_array['filter_attr_name'] }}：</b>
                    <em>{{ $c_array['attr_value'] }}</em>
                    <a href="{!! $c_array['url'] !!}"></a>
                </div>

@endif





@if($g_array)


@foreach($g_array as $garray)

                <div class="get_item" title="{{ $garray['g_name'] }}">
                    <span class="brand_t">{{ $garray['filter_attr_name'] }}：</span>
                    <em>{{ $garray['g_name'] }}</em>
                    <a href="{!! $garray['g_url'] !!}"></a>
                </div>

@endforeach


@endif

            </dd>
            <dd class="give_up_all"><a href="
@if($script_name == 'search')
search.php?
@if($cou_id)
&cou_id={{ $cou_id }}
@endif
&keywords={{ $pager['search']['keywords'] }}
@else
category.php?id={{ $category }}
@endif
" class="ftx-05">{{ $lang['All_undo'] }}</a></dd>
        </dl>
    </div>
	<div class="goods_list">
		<ul class="attr_father">


@if($brands)

            <li class="s-line">
                <div class="s-l-wrap brand_img attr_list">
                    <div class="s-l-tit brand_name_l">{{ $lang['brand'] }}：</div>
                    <div class="s-l-value brand_select_more">
                        <div class="all_a_z">
                            <ul class="a_z">
                                <li class="all_brand curr">{{ $lang['all_brand'] }}</li>

@foreach($letter as $key => $letter)

                                <li data-key="{{ $letter }}">{{ $letter }}</li>

@endforeach

                                <li class="other_brand">{{ $lang['Other'] }}</li>
                            </ul>
                        </div>
                        <div class="wrap_brand">
                            <div class="brand_div">

@foreach($brands as $brand)

                                <div class="brand_img_word" brand ="{{ $brand['brand_letters'] }}">

@if($brand['brand_logo'])

                                        <a href="{!! $brand['url'] !!}">
                                        	<img src="{{ $brand['brand_logo'] }}" alt="{{ $brand['brand_name'] }}" title="{{ $brand['brand_name'] }}">
                                        	<span>{{ $brand['brand_name'] }}</span>
                                        </a>

@else

                                        <a href="{!! $brand['url'] !!}"><b>{{ $brand['brand_name'] }}</b></a>

@endif

                                </div>

@endforeach

                            </div>
                        </div>
                        <div class="zimu_list">
                            <ul class="get_more" >

@foreach($brands as $brand)

                                <li class="is_no" brand ="{{ $brand['brand_letters'] }}" url_id="{{ $brand['brand_id'] }}"><span class="choose_ico"></span><a class="goods_brand_name" data-url="{!! $brand['url'] !!}">{{ $brand['brand_name'] }}</a></li>

@endforeach

                            </ul>
                        </div>
                        <div class="enter_yes_no">
                            <div class="ct_auto">
                                <span class="yes_bt botton disabled">{{ $lang['assign'] }}</span>
                                <span class="no_bt botton">{{ $lang['close'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="s-l-opt sl-ext">
                        <div class="choose_open s-l-more"><i class="iconfont icon-down"></i></div>
                        <div class="choose_more s-l-multiple"><i class="iconfont icon-plus"></i>{{ $lang['multi_select'] }}</div>
                    </div>
                </div>
            </li>

@endif




@if($price_grade['1'])

            <li class="s-line">
                <dl class="s-l-wrap">
                    <div class="s-l-tit filter_attr_name">{{ $lang['price'] }}：</div>
                    <div class="s-l-value attr_son">
                        <div class="price_auto fl">

@foreach($price_grade as $grade)


@if($grade['price_range'])

                            <div class="price_range"><a href="{!! $grade['url'] !!}">{!! $grade['price_range'] !!}</a></div>

@endif


@endforeach

                        </div>
                        <div class="price_auto price_bottom fl">
                            <input type="text" title="{{ $lang['Min_price'] }}" name="price_min" class="price_class price_min">
                            <span class="price_class span_price_class">-</span>
                            <input type="text" title="{{ $lang['Max_price'] }}" name="price_max" class="price_class price_max">
                            <button class="price_ok price_class">{{ $lang['assign'] }}</button>
                        </div>
                    </div>
                </dl>
            </li>

@endif




@if($color_search)

            <li class="s-line">
				<dl class="s-l-wrap attr_list">
                  	<div class="s-l-tit filter_attr_name">{{ $color_search['filter_attr_name'] }}：</div>
                    <div class="s-l-value attr_son attr_color">
                        <div class="item_list color_list_color">

@foreach($color_search['attr_list'] as $color_se)


@if($color_se['selected'])

                            <span class="u_get"></span>

@else

                            <div class="color_divdd">
                                <dd url_id="{{ $color_se['goods_attr_id'] }}">
                                    <a title="{{ $color_se['attr_value']['c_value'] }}" href="{!! $color_se['url'] !!}" data-url="{!! $color_se['url'] !!}">
                                        <span style="background:{{ $color_se['attr_value']['c_url'] }}"></span>
                                        <b></b>
                                    </a>
                                </dd>
                            </div>

@endif


@endforeach

                        </div>
                        <div class="tw_buttom">
                            <span class="sure sure_color disabled">{{ $lang['assign'] }}</span>
                            <span class="no_btn">{{ $lang['is_cancel'] }}</span>
                        </div>
                    </div>
                    <div class="s-l-opt sl-ext">
                  		<div class="choose_more s-l-multiple"><i class="iconfont icon-plus"></i>{{ $lang['multi_select'] }}</div>
					</div>
                </dl>
            </li>

@endif




@foreach($filter_attr_list as $filter_attr)

            <li class="s-line same_li">
            	<dl class="s-l-wrap attr_list">
                    <div class="s-l-tit filter_attr_name">{{ $filter_attr['filter_attr_name'] }}：</div>
                    <div class="s-l-value attr_son">
                        <div class="item_list">

@foreach($filter_attr['attr_list'] as $attr)


@if($attr['selected'])

                            <span class="u_get">{{ $attr['attr_value'] }}</span>

@else

                            <dd url_id="{{ $attr['goods_attr_id'] }}"><a href="{!! $attr['url'] !!}" data-url="{!! $attr['url'] !!}"><span></span><strong>{{ $attr['attr_value'] }}</strong></a></dd>

@endif


@endforeach

                        </div>
                        <div class="tw_buttom">
                            <span class="sure sure_I disabled">{{ $lang['assign'] }}</span>
                            <span class="no_btn">{{ $lang['is_cancel'] }}</span>
                        </div>
                    </div>
                    <div class="s-l-opt sl-ext">
                        <div class="choose_open s-l-more"><i class="iconfont icon-down"></i></div>
                        <div class="choose_more s-l-multiple"><i class="iconfont icon-plus"></i>{{ $lang['multi_select'] }}</div>
                    </div>
				</dl>
            </li>

@endforeach

		</ul>
    </div>
	<div class="click_more s-more"><span class="sm-wrap"><strong>{{ $lang['More_options'] }}</strong><i class="iconfont icon-down"></i></span></div>
</div>

@endif

