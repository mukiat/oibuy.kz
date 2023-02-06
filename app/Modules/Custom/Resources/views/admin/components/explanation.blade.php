
<div class="explanation" id="explanation">
    <div class="ex_tit">
        <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span>
    </div>
    <ul>

    @if(isset($tips) && !empty($tips))

        @foreach($tips as $v)
            <li>{!! $v !!}</li>
        @endforeach

    @endif

    </ul>
</div>
