@include('admin.app.pageheader')

<div class="wrapper">
    <div class="title">{{ lang('admin/app.client_manage') }}</div>
    <div class="content_tips">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span></div>
            <ul>
                <li>{{ lang('admin/app.client_manage_li_0') }}</li>
            </ul>
        </div>

        <div class="flexilist">
            <div class="common-head">
                <div class="fl">
                    <a href="{{ route('admin/app/client_product') }}" class="fancybox fancybox.iframe">
                        <div class="fbutton">
                            <div class="add" title="{{ lang('admin/app.add_product') }}"><span><i class="fa fa-plus"></i>{{ lang('admin/app.add_product') }}</span></div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="common-content">
                <div class="switch_info">
                    <ul class="items-box version-extend">
                        @forelse($client_list as $val)
                            <li class="item_wrap">
                                <div class="left">
                                    <div class="tit">{{ $val['name'] }}</div>
                                    <div class="desc">AppID： {{ $val['appid'] }}</div>
                                </div>
                                <div class="right">
                                    <a href="{{ route('admin/app/client_product_list', ['client_id' => $val['id']]) }}" class="manage">{{ lang('admin/common.manage') }}</a>
                                    <a href="javascript:if(confirm('{{ lang('admin/app.client_delete_confirm') }}')){window.location.href='{{ route('admin/app/del_client', ['client_id' => $val['id']]) }}'};" class="client_delete">{{ lang('admin/app.delete') }}</a>
                                </div>
                            </li>
                        @empty

                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(function () {
    //弹出框
    $(".fancybox").fancybox({
        width: '60%',
        height: '60%',
        closeBtn: true,
        title: ''
    });
});

</script>

@include('admin.base.footer')
