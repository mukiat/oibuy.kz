<ul>

@foreach($cat_store_list as $cat)

    <li>
        <div class="jOneLevelarea user_temp_one">
            <div class="jTwoLevel">
                <span class="square_box"></span>
                 <a href="{!! $cat['url'] !!}" target="_blank">{{ $cat['cat_name'] }}</a>
            </div>

@if($cat['cat_list'])

            <div class="s_b">

@foreach($cat['cat_list'] as $tree)

                <a href="{!! $tree['url'] !!}" target="_blank">{{ $tree['cat_name'] }}</a>

@endforeach

            </div>

@endif

        </div>
    </li>

@endforeach

</ul>
