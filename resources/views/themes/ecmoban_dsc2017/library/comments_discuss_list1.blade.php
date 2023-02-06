    <div class="thead">
        <div class="th td1">{{ $lang['message_title'] }}</div>
        <div class="th td2">{{ $lang['reply_comment'] }}</div>
        <div class="th td3">{{ $lang['browse'] }}</div>
        <div class="th td4">{{ $lang['article_author'] }}</div> 
        <div class="th td5">{{ $lang['time'] }}</div>
    </div>
    <div class="tbody">
    	
@if($discuss_list['list'])

        
@foreach($discuss_list['list'] as $list)

        <div class="tr">
            <div class="td td1">
                
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
                <img src="{{ skin('/images/image_s.jpg') }}">
                
@else

                <a href="single_sun.php?act=discuss_show&did={{ $list['dis_id'] }}" target="_blank">{{ $list['dis_title'] }}</a>
                
@endif

            </div>
            <div class="td td2">{{ $list['reply_num'] }}</div>
            <div class="td td3">{{ $list['dis_browse_num'] }}</div>
            <div class="td td4">{{ $list['user_name'] }}</div>
            <div class="td td5">{{ $list['add_time'] }}</div>
        </div>
        
@endforeach

        
@else

        <div class="no_records no_comments_qt">
            <i class="no_icon no_icon_three"></i>
            <span class="block">{{ $lang['not_discussion'] }}</span>
        </div>
        
@endif

    </div>
    <div class="clear"></div>
    <div class="s-more">
        <a href="category_discuss.php?id={{ $goods_id }}" target="_blank"><span class="sm-wrap">{{ $lang['click_browse_all'] }}<i class="iconfont icon-right"></i></span></a>
    </div>