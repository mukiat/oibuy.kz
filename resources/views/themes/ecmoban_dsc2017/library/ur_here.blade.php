@if($filename == 'category' || $filename == 'goods' || $filename == 'category_discuss' || $filename == 'single_sun' || $filename == 'exchange' || $filename == 'presale' || $filename == 'seckill')
    <div class="crumbs-nav">
        <div class="crumbs-nav-main clearfix">
            @foreach($data['body'] as $cat)
                <div class="crumbs-nav-item">
                    <div class="menu-drop">
                        <div
                            class="trigger @if(!$cat['cat_tree']) bottom @endif">
                            <a href="{{ $cat['url'] }}"><span>{{ $cat['cat_name'] }}</span></a>
                            <i class="iconfont icon-down"></i>
                        </div>
                        @if($cat['cat_tree'])
                            <div class="menu-drop-main">
                                <ul>
                                    @foreach($cat['cat_tree'] as $tree)
                                        <li><a href="{{ $tree['url'] }}">{{ $tree['cat_name'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    @if(!$loop->last || $data['foot'])
                        <i class="iconfont icon-right"></i>
                    @endif
                </div>
            @endforeach
            @if($data['foot'])
                <span class="cn-goodsName">{!! $data['foot'] !!}</span>
            @endif
        </div>
    </div>

@elseif ($filename == 'article' || $filename == 'article_cat')

    <div class="extra">
        @foreach($data['body'] as $cat)
            <a href="{{ $cat['url'] }}">{{ $cat['cat_name'] }}</a>
            <i>&gt;</i>
        @endforeach

        @if($data['foot'])
            <span>{!! $data['foot'] !!}</span>
        @endif
    </div>

@elseif ($filename == 'group_buy' || $filename == 'snatch' || $filename == 'category_compare' || $filename == 'package')

    <div class="crumbs-nav">
        <div class="crumbs-nav-main clearfix">
            <div class="crumbs-nav-item">
                <a href="index.php">{{ $lang['home'] }}</a>
                <i class="iconfont icon-right"></i>
            </div>

            @if($filename == 'snatch')
                <div class="crumbs-nav-item">
                    {{ $lang['snatch'] }}
                    <i class="iconfont icon-right"></i>
                </div>
            @else

                @foreach($data['body'] as $cat)
                    <div class="crumbs-nav-item">
                        <a href="{{ $cat['url'] }}">{{ $cat['cat_name'] }}</a>
                        <i class="iconfont icon-right"></i>
                    </div>
                @endforeach
            @endif

            @if($data['foot'])
                <span>{!! $data['foot'] !!}</span>
            @endif
        </div>
    </div>

@else

    {!! $ur_here !!}

@endif

