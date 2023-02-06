@if($collect_store > 0)
<a href="javascript:void(0);" class="btn">{{ $lang['follow_yes'] }}</a>
@else
<a onclick="get_collect_store(2, {{ $ru_id }});" href="javascript:void(0);" class="btn">{{ $lang['follow_store'] }}</a>
@endif
