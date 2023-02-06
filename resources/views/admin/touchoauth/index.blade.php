@include('admin.base.header')

<div class="warpper">
    <div class="title">{{ $lang['touch_list'] }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                @if(isset($lang['touch_list_tips']) && !empty($lang['touch_list_tips']))

                    @foreach($lang['touch_list_tips'] as $v)
                        <li>{!! $v !!}</li>
                    @endforeach

                @endif
            </ul>
        </div>

        <div class="flexilist">
        <div class="switch_info">
            <div class="wrapper-content oauth_list" style="margin-top:20px;">
                <ul class="items-box">

                @foreach($modules as $key => $vo)

                    <li class="item_wrap">
                        <div class="plugin_item" style="clear:both">
                            <div class="plugin_icon">
                                <i class="img-rounded icon iconfont icon-{{ $vo['type'] }} bg-{{ $vo['type'] }}"></i>
                                {{--<img class="img-rounded" src="{{ asset('assets/mobile/img/oauth/sns_' . $vo['type'] . '.png') }}" alt="">--}}
                            </div>
                            <div class="plugin_status">
                        	<span class="status_txt">
	                        	<div class="list-div">
	                        		<div class="handle">
	                        			<div class="tDiv">

                                            @if(isset($vo['install']) && $vo['install'] == 1)

                                                <a href="{{ route('admin/touch_oauth/edit', array('type'=>$vo['type'])) }}" class="btn_edit"><i class="fa fa-edit"></i>{{ $lang['edit'] }}</a>
                                                <a href="{{ route('admin/touch_oauth/uninstall', array('type'=>$vo['type'])) }}" class="btn_trash"><i class="fa fa-trash-o"></i>{{ $lang['uninstall'] }}</a>

                                            @else

                                                <a href="{{ route('admin/touch_oauth/install', array('type'=>$vo['type'])) }}" class="btn_inst"><i class="sc_icon sc_icon_inst"></i>{{ $lang['install'] }}</a>

                                            @endif

	                        			</div>
	                        		</div>
	                        	</div>
                        	</span>
                            </div>
                            <div class="plugin_content">
                                <h3 class="title">{{ $vo['name'] }}</h3>
                                <p class="desc">{{ $vo['desc'] ?? '' }}</p>
                            </div>
                        </div>
                    </li>

                @endforeach

                </ul>
            </div>
        </div>
        </div>
    </div>
</div>
<script>
    $(document).on("mouseenter", ".list-div tbody td", function () {
        $(this).parents("tr").addClass("tr_bg_blue");
    });

    $(document).on("mouseleave", ".list-div tbody td", function () {
        $(this).parents("tr").removeClass("tr_bg_blue");
    });

</script>
@include('admin.base.footer')