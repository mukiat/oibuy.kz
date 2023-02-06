<div class="categorys-items" id="cata-nav">

@if($categories_pro)


@foreach($categories_pro as $cat)


@if($loop->index < $nav_cat_num)

    <div class="categorys-item
@if($nav_cat_model)
 nav_cat_model
@endif
" ectype="cateItem" data-id="{{ $cat['id'] }}" data-eveval="0">
        <div class="item item-content">

@if($cat['style_icon'] == 'other')


@if($cat['cat_icon'])
<div class="icon-other"><img src="{{ $cat['cat_icon'] }}" alt="分类图标"></div>
@endif


@else

            <i class="iconfont icon-{{ $cat['style_icon'] }}"></i>

@endif

            <div class="categorys-title">
                <strong>

@if($cat['category_link'] == 1)

                {!! $cat['name'] !!}

@else

                <a href="{!! $cat['url'] !!}" target="_blank">{{ $cat['name'] }}</a>

@endif

                </strong>

@if(!$nav_cat_model)

                <span>

@foreach($cat['cat_list'] as $child_two)


@if($loop->index < 2)

                    <a href="{{ $child_two['url'] }}" target="_blank">{{ $child_two['cat_name'] }}</a>

@endif


@endforeach

                </span>

@endif

            </div>
        </div>
        <div class="categorys-items-layer" ectype='cateLayer'>
            <div class="cate-layer-con clearfix" ectype='cateLayerCon_{{ $cat['id'] }}'></div>
        </div>
        <div class="clear"></div>
    </div>

@endif


@endforeach


@endif

</div>
