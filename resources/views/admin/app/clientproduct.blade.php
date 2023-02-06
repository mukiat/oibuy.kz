@include('admin.app.pageheader')

<div class="fancy">
    <div class="title">{{ $client_action }}</div>
    <div class="flexilist of">
        <div class="common-content">
            <div class="main-info">
                <form action="{{ route('admin/app/client_manage') }}" method="post" class="form-horizontal" role="form"
                      onsubmit="return false;">
                    @csrf
                    <div class="switch_info">
                        <div class="item">
                            <div class="label-t"><em class="color-red">*</em>{{ lang('admin/app.client_name') }}：</div>
                            <div class="label_value">
                                <input type="text" name="data[name]" class="text" value="{{ $client['name'] }}"/>
                                <div class="form_prompt">{{ lang('admin/app.client_name_notice') }}</div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ lang('admin/app.appid') }}：</div>
                            <div class="label_value">
                                <input type="text" name="" readonly="" disabled="none" class="text" value="{{ $client['appid'] }}"/>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value">
                                <div class="info_btn">
                                    <input type="hidden" name="client_id" value="{{ $client['id'] }}"/>
                                    <input type="submit" value="{{ lang('admin/common.button_submit') }}" class="button btn-danger bg-red fn"/>
                                    <input type="reset" value="{{ lang('admin/common.button_reset') }}" class="button button_reset fn"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $(".form-horizontal").submit(function () {
            var ajax_data = $(this).serialize();
            $.post("{{ route('admin/app/client_product') }}", ajax_data, function (data) {
                layer.msg(data.msg);
                if (data.status > 0) {
                    window.parent.location = "{{ route('admin/app/client_manage') }}";
                } else {
                    return false;
                }
            }, 'json');
        });
    })
</script>

@include('admin.base.footer')
