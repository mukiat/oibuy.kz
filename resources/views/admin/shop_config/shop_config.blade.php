@include('admin.base.admin_html_head')

<script type="text/javascript">
    /*这里把JS用到的所有语言都赋值到这里*/
    @foreach(__('admin::shop_config.js_languages') as $key => $item)
    var {{ $key }} = "{{ $item }}";
    @endforeach

</script>

<div class="warpper">
    <div class="title">{{ $ur_here ?? '' }}</div>
    <div class="content">

        @if($shop_group == 'return')
            <div class="tabs_info mt10">
                <ul>
                    @foreach($group_list as $group)
                        <li class="@if($loop->first) curr @endif"><a href="javascript:void(0);">{{ $group['name'] }}</a></li>
                    @endforeach
                    {{--附加tab菜单--}}
                    @foreach($tab_menu as $menu)
                        <li><a href="{{ $menu['href'] ?? '' }}">{{ $menu['text'] ?? '' }}</a></li>
                    @endforeach
                </ul>
            </div>
        @else

            @if(count($group_list) > 1)
                <div class="tabs_info mt10">
                    <ul>
                        @foreach($group_list as $group)
                            <li class="@if($loop->first) curr @endif"><a href="javascript:void(0);">{{ $group['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

        @endif

        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                @if(!empty($explanation_tips))
                    @foreach($explanation_tips as $v)
                        <li>{!! $v !!}</li>
                    @endforeach
                @endif
            </ul>
        </div>

        <div class="flexilist of">
            <div class="mian-info">

                @if(!empty($group_list))

                <form enctype="multipart/form-data" name="theForm" action="{{ url(ADMIN_PATH . '/shop_config.php?act=post') }}" method="post" id="shopConfigForm">

                    @foreach($group_list as $group)
                    <div class="switch_info shopConfig_switch" @if($loop->iteration > 1) style="display:none" @endif >

                        @include('admin.shop_config.library.shop_config_form')

                        <div class="item">
                            <div class="label">&nbsp;</div>
                            <div class="label_value info_btn">
                                @csrf
                                <input name="type" type="hidden" value="{{ $type ?? '' }}">
                                <input name="callback" type="hidden" value="{{ urlencode(request()->getRequestUri()) }}">
                                <input type="button" value="{{ __('admin/common.button_submit') }}" ectype="btnSubmit" class="button" >
                            </div>
                        </div>
                    </div>
                    @endforeach
                </form>

                @endif

            </div>
        </div>

    </div>
</div>

<div id="footer" class="copyright">
    @include('admin.base.copyright')
</div>
<script type="text/javascript" src="{{ asset('/js/jquery.picTip.js') }}"></script>

<script type="text/javascript" src="{{ asset('/assets/admin/js/jquery.purebox.js') }}"></script>
<script type="text/javascript" src="{{ asset('/assets/admin/js/region.js') }}"></script>
<script type="text/javascript">
    $(function () {

        //地区三级联动调用
        $.levelLink();

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
                    alert("{{ __('admin::shop_config.btnSubmit_notice') }}" + nary[i]);
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

        var count='<div style="padding: 15px 10px;margin-top: 25px;border-top: 1px solid #BEBEBE;border-bottom: 1px solid #BEBEBE;">{{ __('admin::shop_config.seller_commission') }}</div>';

        $(".evnet_use_pay_fee").on("click",function(){
            var value = $(this).val();
            if(value == 1){
                pb({
                    id:"transfer_dialog",
                    title:"{{ __('admin::common.js_languages.jl_reminder') }}",
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
                    required : '<i class="icon icon-exclamation-sign"></i>'+seller_info_notic
                },
                "value[212]" :{
                    required : '<i class="icon icon-exclamation-sign"></i>'+integral_percent_notic
                }
            }
        });


    });


    /*url重写验证*/
    var ReWriteSelected = null;
    var ReWriteRadiobox = document.getElementsByName("value[209]");

    for (var i=0; i<ReWriteRadiobox.length; i++)
    {
        if (ReWriteRadiobox[i].checked)
        {
            ReWriteSelected = ReWriteRadiobox[i];
        }
    }

    function addCon_amount(obj)
    {
        var obj = $(obj);
        var tbl = obj.parents('#consumtable');
        var fald = true;
        var fald2 = true;
        var error = "";
        var volumeNum = obj.siblings("input");
        var it_val ="";

        var new_it_val = obj.siblings("input[name='invoice_type[]']").val();

        tbl.find(".mt10").each(function(){
            var it_input =$(this).find("input[name='invoice_type[]']");
            it_val = it_input.val();

            if(new_it_val == it_val){
                obj.siblings("input[name='invoice_type[]']").addClass("error");
                fald2 = false;
                error = "{{ __('admin::shop_config.type_already_exists') }}";
            }
        });
        if(fald2 == true){
            volumeNum.each(function(index,element){
                var val = $(this).val();
                if(val == ""){
                    $(this).addClass("error");
                    fald = false;
                    error = "{{ __('admin::shop_config.type_taxrate_not_notic') }}";
                }else if(!(/^[0-9]+.?[0-9]*$/.test(val)) && index == 1){
                    $(this).addClass("error");
                    fald = false;
                    error = "{{ __('admin::shop_config.taxrate_number') }}";
                }else{
                    $(this).removeClass("error");
                    fald = true;
                }
            });

            if(fald == true){
                var input = tbl.find('p:first').clone();
                input.addClass("mt10");
                input.find("input[type='button']").remove();
                input.find(".form_prompt").remove();
                input.append("<a href='javascript:;' class='removeV' onclick='removeCon_amount(this)'><img src='{{ asset('assets/admin/images/no.gif') }}' title='{{ __('admin/common.drop') }}'></a>")
                tbl.append(input);
                volumeNum.val("");
            }else{
                obj.next(".form_prompt").find(".error").remove();
                obj.next(".form_prompt").append("<label class='error'><i class='icon icon-exclamation-sign'></i>"+error+"</label>");
            }
        }else{
            obj.next(".form_prompt").find(".error").remove();
            obj.next(".form_prompt").append("<label class='error'><i class='icon icon-exclamation-sign'></i>"+error+"</label>");
        }
    }

    function removeCon_amount(obj)
    {
        var obj = $(obj);
        obj.parent('p').remove();
    }
</script>

</body>
</html>