<div class="panel-body">
    <div class="panel-tit"><span>{{ $title['fields_titles'] }}</span></div>
    <div class="cue">{!! $title['titles_annotation'] !!}</div>
    <div class="list">@include('frontend::library/cententFields')</div>
    <div class="view-sample" style="display:none">
        <div class="img-wrap">
            <img width="180" height="180" alt="" src="/storage/images/common/images/ruzhu/x_1.jpg">
        </div>
        <div class="t-c mt10">
            <a class="link-blue" target="_blank"
               href="/storage/images/common/images/ruzhu/1.jpg">{{ $lang['View_larger'] }}</a>
        </div>
    </div>
</div>

@if($title['cententFields'])

    <script type="text/javascript">
        @foreach($title['cententFields'] as $fields)
            @foreach($fields['dateTimeForm'] as $dk => $date)
        
            /*日期*/
            var opts_{{ $fields['textFields'] }}_{{ $dk }} = {
                'targetId': '{{ $fields['textFields'] }}_{{ $dk }}',
                'triggerId': ['{{ $fields['textFields'] }}_{{ $dk }}'],
                'alignId': '{{ $fields['textFields'] }}_{{ $dk }}',
                'hms': 'off',
                'format': '-'
            }
            xvDate(opts_{{ $fields['textFields'] }}_{{ $dk }});

            @endforeach
        @endforeach
    </script>

@endif

