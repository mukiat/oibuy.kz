<div class="discuss-list" style="display:block;">

@forelse($discuss_list['list'] as $list)

    <div class="discuss-item">
        <div class="u-ico"><img src="
@if($list['user_picture'])
{{ $list['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif
"></div>
        <div class="ud-right d-i-info">
            <div class="d-i-name">

@if($list['dis_type'] == 1)

                <i class="icon icon-tie icon-tao"></i>

@elseif ($list['dis_type'] == 2)

                <i class="icon icon-tie icon-wen"></i>

@elseif ($list['dis_type'] == 3)

                <i class="icon icon-tie icon-quan"></i>

@elseif ($list['dis_type'] == 4)

                <i class="icon icon-tie icon-shai"></i>

@endif


@if($list['dis_type'] == 4)

                <a href="single_sun.php?act=discuss_show&did={{ $list['dis_id'] }}&dis_type=4" target="_blank">{{ $list['dis_title'] }}</a>

@else

                <a href="single_sun.php?act=discuss_show&did={{ $list['dis_id'] }}" target="_blank">{{ $list['dis_title'] }}</a>

@endif

            </div>
            <div class="d-i-lie">
                <div class="fl">
                    <div class="user-name">{{ $list['user_name'] }}</div>
                    <div class="time">{{ $list['add_time'] }}</div>
                </div>
                <div class="fr">
                    <span class="browse"><i class="iconfont icon-eye-open"></i> {{ $list['dis_browse_num'] }}</span>
                    <span class="comment"><i class="iconfont icon-comment"></i> {{ $list['reply_num'] }}</span>
                </div>
            </div>
        </div>
    </div>

@empty

    <div class="no_records">
        <i class="no_icon_two"></i>
        <div class="no_info">
            <h3>{{ $lang['information_null'] }}</h3>
        </div>
    </div>

@endforelse

    <div class="pages26">
        <div class="pages">
            <div class="pages-it">
                {!! $discuss_list['pager'] !!}
            </div>
        </div>
    </div>
</div>
