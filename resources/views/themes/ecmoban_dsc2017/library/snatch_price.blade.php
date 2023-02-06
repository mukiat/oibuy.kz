
@if($filename == 'snatch')

    
@if($price_list)

    <table class="offer-table">
        <colgroup>
            <col width="15%">
            <col width="35%">
            <col width="30%">
            <col>
        </colgroup>
        <thead>
            <tr>
                <th>{{ $lang['au_bid_status'] }}</th>
                <th>{{ $lang['au_ren'] }}</th>
                <th>{{ $lang['price'] }}</th>
                <th>{{ $lang['time'] }}</th>
            </tr>
        </thead>
        <tbody>
            
@foreach($price_list as $item)

            
@if($loop->iteration < 12)

            <tr>
                
@if($loop->index == 0)

                <td><span class="offer-statu active">{{ $lang['latest_price'] }}</span></td>
                
@else

                <td><span class="offer-statu">{{ $lang['bid'] }}</span></td>
                
@endif

                <td class="red">{{ $item['user_name'] }}</td>
                <td>{{ $item['bid_price'] }}</td>
                <td>{{ $item['bid_date'] }}</td>
            </tr>
            
@endif

            
@endforeach

        </tbody>
    </table>
    
@else

    <div class="no_records">
        <i class="no_icon_two"></i>
        <div class="no_info">
            <h3>{{ $lang['snatch_price_null'] }}</h3>
        </div>
    </div>
    
@endif


@elseif ($filename == 'auction')

    
@if($auction_log)

    <table class="offer-table">
        <colgroup>
            <col width="15%">
            <col width="35%">
            <col width="30%">
            <col>
        </colgroup>
        <thead>
            <tr>
                <th>{{ $lang['au_bid_status'] }}</th>
                <th>{{ $lang['au_ren'] }}</th>
                <th>{{ $lang['price'] }}</th>
                <th>{{ $lang['time'] }}</th>
            </tr>
        </thead>
        <tbody>
            
@foreach($auction_log as $item)

            
@if($loop->iteration < 12)

            <tr>
                
@if($loop->index == 0)

                <td><span class="offer-statu active">{{ $lang['au_bid_ok'] }}</span></td>
                
@else

                <td><span class="offer-statu">{{ $lang['out'] }}</span></td>
                
@endif

                <td class="red">{{ $item['user_name'] }}</td>
                <td>{{ $item['bid_price'] }}</td>
                <td>{{ $item['bid_time'] }}</td>
            </tr>
            
@endif

            
@endforeach

        </tbody>
    </table>
    
@else

    <div class="no_records">
        <i class="no_icon_two"></i>
        <div class="no_info">
            <h3>{{ $lang['snatch_price_null'] }}</h3>
        </div>
    </div>
    
@endif


@endif

