@if($act == 'merchants_article')
    <div class="w w1200">
        <div class="settled-article-warp">
            <div class="step-nav">
                <div class="title">
                    <h3>{{ $title }}</h3>
                </div>
                <div class="sett-r-btn">
                    <a href="{{ $url_merchants_steps }}" class="imrz">{{ $lang['settled_down'] }}</a>
                    <a href="{{ $url_merchants_steps_site }}"
                       class="view-prog">{{ $lang['settled_down_schedule_step'] }}</a>
                </div>
            </div>
            <div class="sett-a-item">
                <div class="sett-cont">
                    {!! $article !!}
                </div>
            </div>
        </div>
    </div>
@elseif ($act == 'goods_rank_prices')
    <dt>
        <span>{{ $lang['rank'] }}</span>
        <span>{{ $lang['prices'] }}</span>
    </dt>
    @foreach($rank_prices as $price_key => $rank)
        <dd>
            <span>{{ $rank['rank_name'] }}</span>
            <span>{{ $rank['price'] }}</span>
        </dd>
    @endforeach
@else
    @foreach($regions_list as $list)
        <div class="option" data-value="{{ $list['region_id'] }}" data-type="{{ $type }}"
             data-text="{{ $list['region_name'] }}" ectype="ragionItem">{{ $list['region_name'] }}</div>
    @endforeach
@endif
