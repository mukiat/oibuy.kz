
@foreach($record_data as $data)

<div class="w-lie">
	
@if($data['main_attr'])

	<div class="w-l-left">
		
@foreach($data['main_attr'] as $main)

		<span class="mr10">{{ $main['attr_value'] }}</span>
		
@endforeach

	</div>
    
@endif

	<div class="w-l-right
@if(!$data['main_attr'])
 w-l-r-curr
@endif
">
		
@foreach($data['end_attr'] as $end)

		<div class="w-td" style="width:20%;">{{ $end['attr_value'] }}(<em>{{ $end['attr_num'] }}</em>)</div>
		
@endforeach

	</div>
</div>

@endforeach
