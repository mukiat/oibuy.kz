{{--删除评价图片、删除举报图片--}}
@if($img_list)

@foreach($img_list as $list)

<li>
    <a href="{{ $list['comment_img'] }}" target="_blank"><img width="78" height="78" alt="" src="{{ $list['comment_img'] }}"></a>
    <i class="iconfont icon-cha"

       @if($report == 1) ectype="reimg-remove" @elseif($report == 2) ectype="compimg-remove" @else ectype="cimg-remove" @endif

       data-imgid="{{ $list['id'] }}"></i>
</li>

@endforeach

@endif
