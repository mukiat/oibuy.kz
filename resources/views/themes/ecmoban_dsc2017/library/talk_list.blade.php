<div class="talk_list_warp">

@forelse($talk_list as $key => $talk_list)

<p class="
@if($talk_list['talk_member_type'] == 1)
accuser
@elseif ($talk_list['talk_member_type'] == 2)
accseller
@else
accadmin
@endif
">{{ $talk_list['talk_time'] }} {{ $lang['talk_member_type'][$talk_list['talk_member_type']] }} ({{ $talk_list['talk_member_name'] }}) {{ $lang['say'] }}：
@if($talk_list['talk_state'] == 1)
{{ $talk_list['talk_content'] }}
@else
&lt;{{ $lang['talk_content_notic'] }}&gt;
@endif
</p>

@empty

{{ $lang['not_conversation'] }}

@endforelse

</div>

