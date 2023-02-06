@include('admin.base.header')

<div class="warpper">
    <div class="title">{{ lang('admin/users.user_rights') }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(lang('admin/users.user_rights_index_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="flexilist">
            <div class="common-head mt10">
                <div class="fl">
                    <a href="{{ route('admin/user_rights/list') }}">
                        <button type="button" class="btn btn-info">{{ lang('admin/users.manage_rights') }}</button>
                    </a>
                </div>
            </div>

            @if (isset($plugins) && !empty($plugins))
            <div class="switch_info">
                <div class="common-content">
                    <div class="mkc_content">

                            @foreach($plugins as $key => $group)

                            <div class="mkc_dl">
                                <div class="mkc_dt">{{ lang('admin/users.rights_group_name_' . $key) }}</div>
                                <div class="mkc_dd">
                                    <ul>

                                        @foreach($group as $k => $vo)
                                            <li>
                                                <a href="{{ route('admin/user_rights/edit', array('code' => $vo['code'], 'handler' => 'edit')) }}" title="{{ $vo['name'] }}" >
                                                    <em><img class="img-rounded" src="{{ $vo['icon'] ?? '' }}" /></em>
                                                    <div class="info">
                                                        <h2>{{ $vo['name'] }}</h2>
                                                        <span>{{ $vo['description'] }}</span>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach

                                    </ul>
                                </div>
                            </div>

                            @endforeach

                    </div>
                </div>
            </div>
            @endif

        </div>

    </div>
</div>
<script>
    $(document).on("mouseenter", ".list-div tbody td", function () {
        $(this).parents("tr").addClass("tr_bg_blue");
    });

    $(document).on("mouseleave", ".list-div tbody td", function () {
        $(this).parents("tr").removeClass("tr_bg_blue");
    });

</script>

@include('admin.base.footer')