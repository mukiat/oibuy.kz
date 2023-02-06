@include('admin.app.pageheader')

<div class="wrapper">
    <div class="title"><a href="{{ route('admin/app/client_product_list', ['client_id' => $client_id]) }}" class="s-back">{{ lang('admin/common.back') }}</a>{{ $product_action }} - {{ $client_name }}</div>
    <div class="content_tips">
        <div class="explanation" id="explanation">
            <div class="ex_tit"><i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span
                        id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span></div>
            <ul>
                <li>{{ lang('admin/app.client_product_info_li_0') }}</li>
            </ul>
        </div>

        <div class="flexilist">
            <div class="common-content">
                <div class="main-info">
                    <form action="{{ route('admin/app/client_product_info') }}" method="post" enctype="multipart/form-data"
                          role="form">
                        <div class="switch_info">
                            <div class="item">
                                <div class="label-t"><em class="color-red">*</em> {{ lang('admin/app.version_code') }}：
                                </div>
                                <div class="label_value">
                                    <input type="text" name="data[version_id]" class="text"
                                           value="{{ $product_info['version_id'] ?? '' }}" id="version_id"
                                           autocomplete="off"/>
                                </div>
                                <div class="form_prompt">{{ lang('admin/app.version_code_notice') }}</div>
                            </div>

                            <div class="item">
                                <div class="label-t"><em class="color-red">*</em>{{ lang('admin/app.update_desc') }}：</div>
                                <div class="label_value">
                                    <textarea name="data[update_desc]" cols="60" rows="4" class="textarea">{{ $product_info['update_desc'] ?? '' }}</textarea>
                                </div>
                                <div class="form_prompt">{{ lang('admin/app.update_desc_notice') }}</div>
                            </div>

                            <div class="item">
                                <div class="label-t"><em class="color-red">*</em>{{ lang('admin/app.download_url') }}：</div>
                                <div class="label_value">
                                    <input type="text" name="data[download_url]" class="text"
                                           value="{{ $product_info['download_url'] ?? '' }}" id="download_url"
                                           placeholder="https://" autocomplete="off"/>
                                </div>
                                <div class="form_prompt">{{ lang('admin/app.download_url_notice') }}</div>
                            </div>
                            <div class="item">
                                <div class="label-t">{{ lang('admin/app.is_show') }}：</div>
                                <div class="label_value w300">
                                    <div class="checkbox_items">
                                        <div class="checkbox_item">
                                            <input type="radio" class="ui-radio" name="data[is_show]" id="is_show_1" value="1" checked="true" />
                                            <label for="is_show_1" class="ui-radio-label">{{ lang('admin/common.yes') }}</label>
                                        </div>
                                        <div class="checkbox_item">
                                            <input type="radio" class="ui-radio" name="data[is_show]" id="is_show_0" value="0"/>
                                            <label for="is_show_0" class="ui-radio-label">{{ lang('admin/common.no') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form_prompt">{{ lang('admin/app.is_show_notice') }}</div>
                            </div>
                            <div class="item">
                                <div class="label-t">{{ lang('admin/app.update_time') }}：</div>
                                <div class="label_value w300">
                                    <div class="text_time" id="text_time_start">
                                        <input type="text" class="text" name="data[update_time]" id="update_time" value="{{ $product_info['update_time'] ?? '' }}" autocomplete="off" readonly>
                                    </div>
                                </div>
                                <div class="form_prompt">{{ lang('admin/app.update_time_notice') }}</div>
                            </div>

                            <div class="item">
                                <div class="label-t">&nbsp;</div>
                                <div class="label_value info_btn">
                                    @csrf
                                    <input type="hidden" name="product_id"
                                           value="{{ $product_info['id'] ?? 0 }}"/>
                                    <input type="hidden" name="client_id"
                                           value="{{ $client_id ?? 0 }}"/>
                                    <input type="submit" value="{{ lang('admin/app.button_submit') }}"
                                           class="button btn-danger bg-red"/>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('js/echarts-all.js') }}"></script>
<script type="text/javascript">

    //日期选择插件调用start sunle
    var opts1 = {
        'targetId':'update_time',//时间写入对象的id
        'triggerId':['update_time'],//触发事件的对象id
        'alignId':'update_time',//日历对齐对象
        'format':'-',//时间格式 默认'YYYY-MM-DD HH:MM:SS'
        'min':'' //最小时间
    }
    xvDate(opts1);

</script>

@include('admin.base.footer')
