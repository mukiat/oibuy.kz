<div class="search_history">
	<div class="mt">
		<h1>{{ $lang['Recent_browse'] }}</h1>
		<a onclick="clear_history()" class="clear_history ftx-05 fr mt10 mr10" style="margin-top:-33px;" href="javascript:void(0);">{{ $lang['clear'] }}</a>
	</div>
	<div class="mc" ectype="history_mian">
		{!! insert_history() !!}
	</div>
</div>