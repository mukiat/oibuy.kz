<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/iconfont.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/font-awesome.min.css') }}">
    <script type="text/javascript" src="{{ asset('js/jquery-1.12.4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.json.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/lib_ecmobanFunc.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.nyroModal.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.validation.min.js') }}"></script>
</head>

<body class="iframe_body">

<div class="warpper">
    <div class="title">{{ lang('admin/suppliers.basic_setting') }} - {{ lang('admin/suppliers.basic_setting_logo') }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(lang('admin/suppliers.basic_setting_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="flexilist">
            <div class="mian-info basic_set_info">
                <form enctype="multipart/form-data" name="theForm" action="{{ route('basic_receive') }}" method="post" id="shopConfigForm">
                    @foreach($config_list as $key=>$var)
                        <div class="item {{ $var['code'] }}"  data-val="{{ $var['id'] }}">
                            <div class="label">{{ $var['name'] }}：</div>
                            <div class="label_value">
                                <div class="type-file-box">
                                    @csrf
                                    <input type="button" name="button" id="button" class="type-file-button" value="" />
                                    <input type="file" class="type-file-file"  name="{{ $var['code'] }}" size="30" data-state="imgfile" hidefocus="true" value="" />
                                    @if(!empty($var['img_path']))
                                    <span class="show">
                                                      <a href="{{ $var['img_path'] }}" target="_blank" class="nyroModal"><i class="icon icon-picture" data-tooltipimg="{{ $var['img_path'] }}" ectype="tooltip" title="tooltip"></i></a>
                                                  </span>
                                    @endif
                                    <input type="text" name="textfile" class="type-file-text" id="textfield" readonly />
                                </div>
                                @if(!empty($var['img_path']))
                                    <a href="{{ route('basic_del',array('code'=>$var['code'])) }}" class="btn red_btn h30 mr10 fl" style="line-height:30px;">{{ lang('admin/common.drop') }}</a>
                                @else
                                    @if(!empty($var['value']))
                                        <img src="{{ __TPL__ }}/images/yes.gif" alt="yes" class="fl mt10" />
                                    @else
                                        <img src="{{ __TPL__ }}/images/no.gif" alt="no" class="fl mt10" />
                                    @endif
                                @endif
                                    <div class="form_prompt"></div>
                                @if(!empty($var['desc']))
                                    <div class="notic">
                                        {{ $var['desc'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                        <div class="item">
                            <div class="label">&nbsp;</div>
                            <div class="label_value info_btn">
                                <input type="button" value="{{ lang('admin/common.button_submit') }}" ectype="btnSubmit" class="button" >
                            </div>
                        </div>
                </form>
            </div>
        </div>

    </div>
</div>

@include('admin.base.footer')

<script type="text/javascript" src="{{ __TPL__ }}/js/jquery.purebox.js"></script>
<script type="text/javascript" src="{{ __TPL__ }}/js/region.js"></script>
<script type="text/javascript">

    $(function(){

        //图片点击放大
        $('.nyroModal').nyroModal();

        //表单验证
        $("[ectype='btnSubmit']").click(function(){
            var invoice_type = $("input[name='invoice_type[]']");
            var invoice_type_val = "";
            var arr = [];

            invoice_type.each(function(){
                invoice_type_val = $(this).val();
                arr.push(invoice_type_val);
            });

            var nary = arr.sort();

            for(var i = 0; i < nary.length - 1; i++)
            {
                if (nary[i] == nary[i+1])
                {
                    alert("{$lang.btnSubmit_notice}" + nary[i]);
                    return false;
                }
            }

            if($("#shopConfigForm").valid()){
                //防止表单重复提交
                if(checkSubmit() == true){
                    $("#shopConfigForm").submit();
                }
                return false
            }
        });

        var count='<div style="padding: 15px 10px;margin-top: 25px;border-top: 1px solid #BEBEBE;border-bottom: 1px solid #BEBEBE;">{$lang.seller_commission}</div>';

        $(".evnet_use_pay_fee").on("click",function(){
            var value = $(this).val();
            if(value == 1){
                pb({
                    id:"transfer_dialog",
                    title:jl_reminder,
                    width:400,
                    height:130,
                    content:count,
                    xBtn :true,				//是否显示关闭按钮
                    cBtn : false,
                    drag:false,
                    foot:false,
                    onOk:function(){
                        $("#moveCategory").submit();
                    }
                });
            }
        });

        $('#shopConfigForm').validate({
            errorPlacement:function(error, element){
                var error_div = element.parents('div.label_value').find('div.form_prompt');
                element.parents('div.label_value').find(".notic").hide();
                error_div.append(error);
            },
            rules:{
                "value[101]" :{
                    required : true
                },
                // 验证积分支付比例 不能大于100
                "value[212]": {
                    required : true,
                    max: 100
                }
            },
            messages:{
                "value[101]" :{
                    required : '<i class="icon icon-exclamation-sign"></i>'
                },
                "value[212]" :{
                    required : '<i class="icon icon-exclamation-sign"></i>'
                }
            }
        });
    });
</script>