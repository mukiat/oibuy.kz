<div class="acde-right-title"><span class="name" id="bidCount">{{ $lang['bid_record'] }}(&nbsp;<em class="num">
@if($filename == 'snatch')
{{ $price_list_count }}
@elseif ($filename == 'auction')
{{ $auction_count }}
@endif
</em>&nbsp;)</span></div>
<table class="offer-table-s">
    <tr>
        <th>{{ $lang['au_bid_status'] }}</th>
        <th>{{ $lang['price'] }}</th>
    </tr>
    
@if($filename == 'snatch')

        
@forelse($price_list as $item)

        
@if($loop->iteration < 6)

        <tr>
            <td><span class="offer-statu 
@if($loop->index == 0)
active
@endif
">
@if($loop->index == 0)
{{ $lang['latest_price'] }}
@else
{{ $lang['bid'] }}
@endif
</span></td>
            <td class="red">{{ $item['bid_price'] }}</td>
        </tr>
        
@endif

        
@empty

        <tr>
            <td colspan="2">{{ $lang['no_record'] }}</td>
        </tr>
        
@endforelse
		
@if($price_list)

        <tr>
            <td colspan="2"><a href="#detail-slide" class="ftx-05" ectype="snatchType">{{ $lang['View_details'] }} ></a></td>
        </tr>
		
@endif

    
@elseif ($filename == 'auction')

        
@forelse($auction_log as $log)

        
@if($loop->iteration < 6)

        <tr>
            <td><span class="offer-statu 
@if($loop->first)
active
@endif
">
@if($loop->first)
{{ $lang['au_bid_ok'] }}
@else
{{ $lang['out'] }}
@endif
</span></td>
            <td class="red">{{ $log['formated_bid_price'] }}</td>
        </tr>
        
@endif

        
@empty

        <tr>
            <td colspan="2">{{ $lang['no_record'] }}</td>
        </tr>
        
@endforelse

		
@if($auction_log)

        <tr>
            <td colspan="2"><a href="#detail-slide" class="ftx-05" ectype="snatchType">{{ $lang['View_details'] }} ></a></td>
        </tr>
		
@endif

    
@endif

</table>