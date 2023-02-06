@include('admin.base.admin_html_head')

<style>
    .user_set_baitiao .user-info {
        display: inline-block;
        background-color: #eeeeee;
        border-radius: 6px;
    }

    .user_set_baitiao .user-info .img {
        margin: 10px;
    }

    .user_set_baitiao .user-info .name {
        margin: 5px;
    }

    .user_set_baitiao .btn {
        background: url({{ asset('assets/mobile/img/console/admin-icon.png') }}) no-repeat;
    }

    .user_set_baitiao .search_btn {
        border: 0;
        width: 30px;
        height: 28px;
        background-position: -118px -327px;
        float: left;
        padding: 0;
        cursor: pointer;
        margin-top: 2px;
        font-style: normal;
        margin-left: -40px;
    }

</style>
<script src="{{ asset('assets/mobile/vendor/layer/layer.js') }}"></script>
<script type="text/javascript">
    /*这里把JS用到的所有语言都赋值到这里*/
    @foreach(__('admin::user_baitiao_log.js_languages') as $key => $item)
    var {{ $key }} = "{{ $item }}";
    @endforeach

</script>

<div class="warpper">
    <div class="title"><a href="{{ $action_link2['href'] ?? '' }}" class="s-back">{{ __('common.back') }}</a>{{ __('admin/common.08_members') }} - {{ $ur_here ?? '' }}</div>
    <div class="content">
        <div class="tabs_info mt10">
            <ul>
                <li class="curr"><a href="{{ url(ADMIN_PATH . '/user_baitiao_log.php?act=list') }}">{{ __('admin::user_baitiao_log.bt_list') }}</a></li>
                <li ><a href="{{ url(ADMIN_PATH . '/user_baitiao_log.php?act=setting') }}">{{ __('admin::user_baitiao_log.set_baitiao') }}</a></li>
            </ul>
        </div>

        <div class="flexilist of">
            <div class="mian-info">

                <div class="common-head">
                    <div class="fl">
                        <a href="{{ $action_link['href'] ?? '' }}"><div class="fbutton"><div class="add" title="{{ $action_link['text'] ?? '' }}"><span><i class="icon icon-plus"></i>{{ $action_link['text'] ?? '' }}</span></div></div></a>
                    </div>
                </div>
                <div class="clear"></div>

                <div class="switch_info user_set_baitiao">
                    <form method="post" action="{{ url(ADMIN_PATH . '/user_baitiao_log.php?act=bt_edit') }}" name="theForm" id="user_baitiao_log">
                        <div class="common-content">
                            <div class="item">
                                <div class="label">{!! __('admin/common.require_field') !!}&nbsp;{{ __('admin::user_baitiao_log.user_name') }}：</div>

                                @if($user_id > 0)
                                <div class="label_value font14">
                                    {{ $user_info['user_name'] ?? '' }}
                                    <input type="hidden" name="user_id" value="{{ $user_info['user_id'] ?? 0 }}" />
                                </div>
                                @else
                                    <div class="label_value font14">
                                        <input type="text" name="user_name" class="text" autocomplete="off" />
                                        <input type="hidden" name="user_id" value="0" />
                                        <input type="button" value="" class="btn search_btn">
                                        <div class="notic">{{ __('admin::user_baitiao_log.search_user_name') }}</div>
                                    </div>
                                @endif

                            </div>

                            @if($user_id == 0)
                            <div class="item js-user-info hide">
                                <div class="label">{{ __('admin::user_baitiao_log.user_info') }}：</div>
                                <div class="label_value user-label">
                                    <div class="user-info w300">
                                        <div class="img fl">
                                        </div>
                                        <div class="name fl">
                                        </div>
                                    </div>
                                    <div class=""></div>
                                </div>
                            </div>
                            @endif

                            <div class="item">
                                <div class="label">{!! __('admin/common.require_field') !!}&nbsp;{{ __('admin::user_baitiao_log.financial_credit') }}：</div>
                                <div class="label_value">
                                    <input type="number" name="amount" class="text" autocomplete="off" value="{{ $bt_info['amount'] ?? 0 }}"  id="amount"/>
                                    <div class="form_prompt"></div>
                                    <div class="notic">{{ __('admin::user_baitiao_log.notice_financial_credit') }}</div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{!! __('admin/common.require_field') !!}&nbsp;{{ __('admin::user_baitiao_log.Credit_payment_days') }}：</div>
                                <div class="label_value">
                                    <input type="number" name="repay_term" class="text" autocomplete="off" value="{{ $bt_info['repay_term'] ?? 0 }}" id="repay_term"/>
                                    <div class="form_prompt"></div>
                                    <div class="notic">{{ __('admin::user_baitiao_log.notice_Credit_payment_days') }}</div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">{!! __('admin/common.require_field') !!}&nbsp;{{ __('admin::user_baitiao_log.Suspended_term') }}：</div>
                                <div class="label_value">
                                    <input type="number" name="over_repay_trem" class="text" autocomplete="off" value="{{ $bt_info['over_repay_trem'] ?? 0 }}" id="over_repay_trem"/>
                                    <div class="form_prompt"></div>
                                    <div class="notic">{{ __('admin::user_baitiao_log.notice_Suspended_term') }}</div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="label">&nbsp;</div>
                                <div class="label_value info_btn">
                                    @csrf
                                    <a href="javascript:;" class="button" id="submitBtn_bt">{{ __('admin/common.button_submit') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<div id="footer" class="copyright">
    @include('admin.base.copyright')
</div>
<script type="text/javascript">
    $(function(){

        //设置白条验证
    
        $("#submitBtn_bt").click(function(){
            if($("#user_baitiao_log").valid()){
                //防止表单重复提交
                if(checkSubmit() == true){
                    $("#user_baitiao_log").submit();
                }
                return false
            }
        });
        $('#user_baitiao_log').validate({
            errorPlacement:function(error, element){
                var error_div = element.parents('div.label_value').find('div.form_prompt');
                element.parents('div.label_value').find(".notic").hide();
                error_div.append(error);
            },
            rules:{
                amount:{
                    required:true
                },
                repay_term:{
                    required:true
                },
                over_repay_trem:{
                    required:true
                }
            },
            messages:{
                amount:{
                    required:'<i class="icon icon-exclamation-sign"></i>'+amount_not_null
                },
                repay_term:{
                    required:'<i class="icon icon-exclamation-sign"></i>'+repay_term_not_null
                },
                over_repay_trem:{
                    required:'<i class="icon icon-exclamation-sign"></i>'+over_repay_trem_not_null
                }
            }
        });

        // 搜索
        $('.search_btn').on('click', function () {
            var user_name = $('input[name="user_name"]').val()

            showUser(user_name);
        });

        function showUser(user_name) {
            // 请求商品列表
            var url = "{{ url(ADMIN_PATH . '/user_baitiao_log.php?act=search_user') }}";

            $.post(url, {user_name:user_name}, function(data){

                if (data.error == 0) {
                    if (data.data) {
                        var user = data.data;

                        var html = '<div class="user-info w300">';
                        html += '<div class="img fl"> <img class="img-rounded" src="' + user.user_picture + '" width="50" height="50"/> </div>';
                        html += '<div class="name fl">' + user.nick_name + ' <br> ' + user.mobile_phone + ' </div>';
                        html += '</div>';

                        html += '<div class=""><span class="js-remove"><a href="javascript:;">{{ __('common.clear') }}</a></span></div>';

                        $(".user-label").html(html);

                        $(".js-user-info").removeClass('hide');
                        $('input[name="user_id"]').val(user.user_id);
                    }
                } else {
                    layer.msg(data.msg);
                    $(".js-user-info").addClass('hide');
                    $(".user-label").html('');
                }
                return false;
            }, 'json');
        }


        // 清除
        $('.js-user-info').on('click', '.js-remove', function () {
            $(".js-user-info").addClass('hide');
            $(".user-label").html('');
            $('input[name="user_name"]').val('');
            $('input[name="user_id"]').val('');
        });
       
    });
</script>

</body>
</html>