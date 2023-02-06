@if($pager['page_count'] > 1)

<div class="tc">
    <form name="selectPageForm" action="{{ request()->server('PHP_SELF') }}" method="get">

@if($pager['styleid'] == 0 )

        <div class="pages w1390" id="pager">
  {{ $lang['pager_1'] }}{{ $pager['record_count'] }}{{ $lang['pager_2'] }}{{ $lang['pager_3'] }}{{ $pager['page_count'] }}{{ $lang['pager_4'] }} <span> <a href="{!! $pager['page_first'] !!}">{{ $lang['page_first'] }}</a> <a href="{!! $pager['page_prev'] !!}">{{ $lang['page_prev'] }}</a> <a href="{!! $pager['page_next'] !!}">{{ $lang['page_next'] }}</a> <a href="{!! $pager['page_last'] !!}">{{ $lang['page_last'] }}</a> </span>

@foreach($pager['search'] as $key => $item)


@if($key == 'keywords')

          <input type="hidden" name="{{ $key }}" value="{{ $item }}" />

@else

          <input type="hidden" name="{{ $key }}" value="{{ $item }}" />

@endif


@endforeach

    <select name="page" id="page" onchange="selectPage(this)">
        @foreach($pager['page_number'] as $page => $item)
            <option value="{{ $page }}"
            @if($page == $pager['page'])
                selected
            @endif
            >{{ $page }}</option>
        @endforeach
    </select>
        </div>



@else

            <div class="pages" id="pager">
                <ul>

@if($pager['page_kbd'])


@foreach($pager['search'] as $key => $item)


@if($key == 'keywords')

                    <input type="hidden" name="{{ $key }}" value="{{ $item }}" />

@else

                    <input type="hidden" name="{{ $key }}" value="{{ $item }}" />

@endif


@endforeach


@endif



@if($pager['page_first'])
<div class="item prev" style="display:none;"><a href="{!! $pager['page_first'] !!}"><span>{{ $lang['home'] }}</span></a></div>
@endif


                    <div class="item prev"><a href="
@if($pager['page_prev'])
{!! $pager['page_prev'] !!}
@else
#none
@endif
"><i class="iconfont icon-left"></i></a></div>


@if($pager['page_count'] != 1)


@foreach($pager['page_number'] as $key => $item)


@if($pager['page'] == $key)

                    <div class="item cur"><a href="#none">{{ $key }}</a></div>

@else

                    <div class="item"><a href="{!! $item !!}">{{ $key }}</a></div>

@endif


@endforeach


@endif

                    <div class="item next"><a href="
@if($pager['page_next'])
{!! $pager['page_next'] !!}
@else
#none
@endif
"><i class="iconfont icon-right"></i></a></div>

@if($pager['page_last'])
<div class="item next" style="display:none"><a href="{!! $pager['page_last'] !!}"><span>{{ $lang['page_last_new'] }}</span></a></div>
@endif

                </ul>
            </div>

@endif

    @csrf </form>
</div>

@endif


<script type="text/javascript">
<!--

function selectPage(sel)
{
  sel.form.submit();
}

//-->
</script>
