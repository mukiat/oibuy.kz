@include('admin.base.header')

<style>
    /*div+js模仿select效果*/
    .imitate_select{ float: left; position:relative;border: 1px solid #dbdbdb;border-radius: 2px;height: 32px;line-height: 30px; margin-right:10px;font-size: 12px;}
    .imitate_select .cite{ background: #fff url({{ asset('assets/admin/images/xjt.png') }}) right 11px no-repeat; padding: 0 10px; cursor:pointer;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left;}
    .imitate_select ul{ position:absolute; top:28px; left:-1px; background:#fff; width:100%; border:1px solid #dbdbdb; border-radius:0 0 3px 3px; display:none; z-index:199; max-height:280px; overflow:hidden;}
    .imitate_select ul li{ padding:0 10px; cursor:pointer;}
    .imitate_select ul li:hover{ background:#f5faff;}
    .imitate_select ul li a{ display:block;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; text-align:left; color:#707070;}

    .imitate_select ul li.li_not{ text-align:center;padding: 20px 10px;}
    .imitate_select .upward{ top:inherit; bottom:28px; border-radius:3px 3px 0 0;}
    /*div+js模仿select效果end*/

    .form-control-select {
        display: block;
        width: 150px;
        height: 30px;
        padding: 0 6px;
        font-size: 13px;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
        border-radius: 3px;
        line-height: 28px;
     }
</style>

<div class="warpper">
    <div class="title"><a href="{{ route('admin/touch_nav/index', ['device' => $device]) }}" class="s-back">{{ __('common.back') }}</a> {{ __('admin/touch_nav.menu_' . $device) }} - {{ __('admin/touch_nav.touch_nav_title') }}</div>
    <div class="content">

        <div class="flexilist">
            <div class="main-info">
                <form action="{{ route('admin/touch_nav/edit', ['device' => $device]) }}" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
                    <div class="switch_info" style="overflow: inherit">

                        <div class="item hide">
                            <div class="label-t">{{ __('admin/touch_nav.touch_nav_page') }}：</div>
                            <div class="label_value col-md-4">
                                <div style="width:299px;">
                                    <select name="data[page]" class="form-control input-sm w300">
                                        <option value="">{{ __('admin/common.select_please') }}</option>
                                        <option value="user" selected>user</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t"><em class="color-red"> * </em> {{ __('admin/touch_nav.touch_nav_name') }}：</div>
                            <div class="label_value col-md-4">
                                <input type="text" name="data[name]" class="form-control input-sm w300" value="{{ $info['name'] ?? '' }}"/>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t"><em class="color-red"> * </em> {{ __('admin/touch_nav.touch_nav_parent') }}：</div>
                            <div class="label_value col-md-4">
                                <div style="width:299px;">
                                    <select name="data[parent_id]" class="form-control input-sm" id="parent_nav">
                                        <option value="">{{ __('admin/common.select_please') }}</option>

                                        @if(!empty($parent_nav))
                                            @foreach($parent_nav as $val)

                                                <option value="{{ $val['id'] }}"
                                                        @if((isset($info['parent_id']) && $info['parent_id'] == $val['id']) || $parent_id == $val['id'])
                                                        selected
                                                        @endif
                                                >{{ $val['name'] }}</option>

                                            @endforeach
                                        @endif

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t"><em class="color-red"> * </em> {{ __('admin/touch_nav.touch_nav_pic') }}：</div>
                            <div class="label_value col-md-4">
                                <div class="type-file-box">
                                    <input type="button" id="button" class="type-file-button">
                                    <input type="file" class="type-file-file" name="pic" size="30" data-state="imgfile" >
                                    <span class="show">
                                        <a href="#inline" class="nyroModal fancybox" title="{{ __('admin/common.preview') }}">
                                            <i class="fa fa-picture-o"></i>
                                        </a>
                                    </span>
                                    <input type="text" name="file_path" class="type-file-text hide" value="{{ $info['pic'] ?? '' }}" id="textfield" style="display:none">
                                </div>
                                <div class="notice">{{ __('admin/touch_nav.pic_notice') }}</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t"><em class="color-red"> * </em> {{ __('admin/touch_nav.touch_nav_url') }}：</div>
                            <div class="label_value col-md-4 w600">
                                <input type="text" name="data[url]" class="form-control input-sm " value="{{ $info['url'] ?? '' }}"/>
                            </div>
                            <div class="imitate_select form-control-select col-md-4">
                                <div class="cite">
                                    {{ __('admin/common.please_select') }}
                                </div>
                                <ul style="overflow: auto">
                                    <li><a href="javascript:;" data-value="-1">{{ __('admin/touch_nav.select_nav_url') }} </a></li>
                                    @if(!empty($device_url))
                                        @foreach($device_url as $val)

                                            @if ($device == 'h5')
                                            <li><a href="javascript:;" data-value="{{ $val['url'] ?? '' }}">{{ $val['cat_name'] ?? '' }}</a></li>
                                            @elseif ($device == 'wxapp')
                                            <li><a href="javascript:;" data-value="{{ $val['applet_page'] ?? '' }}">{{ $val['cat_name'] ?? '' }}</a></li>
                                            @elseif($device == 'app')
                                            <li><a href="javascript:;" data-value="{{ $val['app_page'] ?? '' }}">{{ $val['cat_name'] ?? '' }}</a></li>
                                            @endif

                                        @endforeach
                                    @endif
                                </ul>
                                <input type="hidden" value="-1">
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/common.sort_order') }}：</div>
                            <div class="label_value col-md-2">
                                <input type="text" name="data[vieworder]" class="form-control input-sm w80" value="{{ $info['vieworder'] ?? '50' }}"/>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/common.whether_display')  }}：</div>
                            <div class="label_value col-md-10">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[ifshow]" class="ui-radio evnet_show" id="value_117_0" value="1"
                                               @if((isset($info['ifshow']) && $info['ifshow'] == 1) || !isset($info['ifshow']))
                                               checked
                                                @endif
                                        >
                                        <label for="value_117_0" class="ui-radio-label @if((isset($info['ifshow']) && $info['ifshow'] == 1) || !isset($info['ifshow'])) active @endif ">{{ __('admin/common.yes') }}</label>
                                    </div>
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[ifshow]" class="ui-radio evnet_show" id="value_117_1" value="0"
                                               @if(isset($info['ifshow']) && $info['ifshow'] == 0)
                                               checked
                                                @endif
                                        >
                                        <label for="value_117_1" class="ui-radio-label @if(isset($info['ifshow']) && $info['ifshow'] == 0) active @endif ">{{ __('admin/common.no') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value col-md-4">
                                <div class="info_btn">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $info['id'] ?? '' }}"/>
                                    <input type="hidden" name="data[device]" value="{{ $device ?? 'h5' }}"/>
                                    <input type="submit" value="{{ __('admin/common.button_submit') }}" class="button btn-danger bg-red fn"/>
                                    <input type="reset" value="{{ __('admin/common.button_reset') }}" class="button button_reset fn"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <div class="panel panel-default" style="display: none;" id="inline">
        <div class="panel-body">
            <img src="{{ $info['pic'] ?? '' }}" class="img-responsive"/>
        </div>
    </div>

</div>

<script type="text/javascript">
    //div仿select下拉选框 start
    $(document).on("click", ".imitate_select .cite", function () {
        $(".imitate_select ul").hide();
        $(this).parents(".imitate_select").find("ul").show();
    });
    $(document).on("click", ".imitate_select li a", function () {
        var _this = $(this);
        var val = _this.data('value');
        var text = _this.html();
        _this.parents(".imitate_select").find(".cite").html(text);
        _this.parents(".imitate_select").find("input[type=hidden]").val(val);
        _this.parents(".imitate_select").find("ul").hide();

        if (val != '-1') {
            // 赋值
            $('input[name="data[name]"]').val(text);
            $('input[name="data[url]"]').val(val);
        }
    });
    //div仿select下拉选框 end

    //select下拉默认值赋值
    $('.imitate_select').each(function () {
        var sel_this = $(this)
        var val = sel_this.children('input[type=hidden]').val();
        sel_this.find('a').each(function () {
            if ($(this).attr('data-value') == val) {
                sel_this.children('.cite').html($(this).html());
            }
        })
    });

    $(document).click(function(e){
        /**
         * 点击空白处隐藏展开框
         */

        //仿select
        if(e.target.className !='cite' && !$(e.target).parents("div").is(".imitate_select")){
            $('.imitate_select ul').hide();
        }
    });


    $(function () {

        //file移动上去的js
        $(".type-file-box").hover(function () {
            $(this).addClass("hover");
        }, function () {
            $(this).removeClass("hover");
        });

        // fancybox 弹出框
        $(".fancybox").fancybox({
            width: '60%',
            height: '50%',
            closeBtn: true,
            title: ''
        });

        // 上传图片预览
        $("input[name=pic]").change(function (event) {
            // 根据这个 <input> 获取文件的 HTML5 js 对象
            var files = event.target.files, file;
            if (files && files.length > 0) {
                // 获取目前上传的文件
                file = files[0];

                // 那么我们可以做一下诸如文件大小校验的动作
                if (file.size > 1024 * 200) {
                    layer.msg('{{ __('file.file_size_limit') }}');
                    return false;
                }

                // 预览图片
                var reader = new FileReader();
                // 将文件以Data URL形式进行读入页面
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    $(".img-responsive").attr("src", this.result);
                };
            }
        });

        $(".form-horizontal").submit(function () {

            var name = $('input[name="data[name]"]').val();
            if (!name) {
                layer.msg('{{ __('admin/touch_nav.validate_nav_name_empty') }}');
                return false;
            }

            var parent_nav = $('#parent_nav').val();
            if (!parent_nav) {
                layer.msg('{{ __('admin/touch_nav.validate_nav_parent_empty') }}');
                return false;
            }

            var pic = $(".img-responsive").attr("src");
            if (!pic) {
                layer.msg('{{ __('admin/touch_nav.validate_nav_pic_empty') }}');
                return false;
            }

            var url = $('input[name="data[url]"]').val();
            if (!url) {
                layer.msg('{{ __('admin/touch_nav.validate_nav_url_empty') }}');
                return false;
            }


        });
    })
</script>
@include('admin.base.footer')