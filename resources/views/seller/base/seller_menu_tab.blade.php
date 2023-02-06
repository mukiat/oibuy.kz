{{--商家后台右侧顶部导航菜单切换--}}
<div class="tabmenu">
    <ul class="tab">
        @if(!empty($tab_menu))
        @foreach($tab_menu as $menu)
        <li  @if(!empty($menu['curr'])) class="active" @endif ><a href="@if(!empty($menu['href'])) {{ $menu['href'] }} @else javascript:void(0); @endif"  @if(!empty($menu['ectype'])) {{ $menu['ectype'] }} @endif {{ $menu['text'] ?? '' }}>{{ $menu['text'] ?? '' }}</a></li>
        @endforeach
        @else
        <li class="active"><a href="javascript:;">{{ $ur_here ?? '' }}</a></li>
        @endif
    </ul>
</div>

@if(!empty($action_link))
<div class="btn-info">
    @if(!empty($action_link))
    <a class="sc-btn sc-blue-btn" href="{{ $action_link['href'] ?? '' }}"><i class="{{ $action_link['class'] ?? '' }}"></i>{{ $action_link['text'] ?? '' }}</a>
    @endif

     @if(!empty($action_link2))
    <a class="sc-btn sc-blue-btn" href="{{ $action_link2['href'] ?? '' }}"><i class="{{ $action_link2['class'] ?? '' }}"></i>{{ $action_link2['text'] ?? '' }}</a>
    @endif

    @if(!empty($action_link3))
    <a class="sc-btn sc-blue-btn" href="{{ $action_link3['href'] ?? '' }}"><i class="{{ $action_link3['class'] ?? '' }}"></i>{{ $action_link3['text'] ?? '' }}</a>
    @endif
</div>
@endif