@include('seller.base.seller_pageheader')

@include('seller.base.seller_nave_header')

<style>
    ul, li {
        /* overflow: hidden; */
    }

    .dates_box_top {
        height: 32px;
    }

    .dates_bottom {
        height: auto;
    }

    .dates_hms {
        width: auto;
    }

    .dates_btn {
        width: auto;
    }

    .dates_mm_list span {
        width: auto;
    }

</style>

<div class="ecsc-layout">
    <div class="site wrapper">
        @include('seller.base.seller_menu_left')

        <div class="ecsc-layout-right">
            <div class="main-content" id="mainContent">
                @include('seller.base.seller_nave_header_title')

                @include('seller.base.seller_menu_tab')

                <div class="wrapper-right of">

                    <div class="explanation clear mb20" id="explanation">
                        <div class="ex_tit"><i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4></div>
                        <ul>
                            @foreach(lang('admin/order.order_delivery_import_tips') as $v)
                                <li>{!! $v !!}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="pt10"></div>

                    <div class="ecsc-form-goods">
                        <form action="{{ route('seller/order_delivery_import') }}" method="post"  name="listForm" enctype="multipart/form-data" role="form" class="form-horizontal" >
                            <div class="wrapper-list border1">

                                <dl>
                                    <dt class="label-t">{{ lang('admin/order.upload_file') }}：</dt>
                                    <dd class="label_value">
                                        <div class="type-file-box">
                                            <input type="button" id="button" class="type-file-button" value="{{ lang('admin/order.upload_file') }}">
                                            <input type="file" class="type-file-file" id="file" name="file" data-state="xlsfile" size="30" hidefocus="true" accept=".xls" />

                                            <input type="text" name="file_path" class="type-file-text"  id="textfield"  value="" style="height: 30px; border: none" autocomplete="off" readonly />
                                        </div>
                                        <div class="form_prompt"></div>
                                        <div class="notic m20" >{{ lang('admin/order.upload_file_notic') }}</div>
                                    </dd>
                                </dl>

                                <dl>
                                    <dt class="label-t">&nbsp;</dt>
                                    <dd class="label_value info_btn">
                                        @csrf
                                        <input type="hidden" name="form_token" value="{{ str_random(32) }}">
                                        <input type="submit" class="sc-btn sc-blueBg-btn btn35" value="{{ lang('admin/common.button_submit') }}" />

                                    </dd>
                                </dl>
                            </div>
                        </form>
                     </div>

                </div>

            </div>
        </div>

    </div>
</div>


<script type="text/javascript">

    /* file上传文件类型 封装函数 start*/
    $(document).on("change", ".type-file-file", function () {
        var state = $(this).data('state');
        var filepath = $(this).val();
        var extStart = filepath.lastIndexOf(".");
        var ext = filepath.substring(extStart, filepath.length).toUpperCase();

        if (state == 'xlsfile' && ext != ".XLS") {
            layer.msg("上传文件限于xls格式");
            $(this).attr('value', '');
            return false;
        }

        $(this).siblings(".type-file-text").val(filepath);
    });

    $(".type-file-box").hover(function () {
        $(this).addClass("hover");
    }, function () {
        $(this).removeClass("hover");
    });
    /* file上传文件类型 封装函数 end*/


    // 提交
    $("form[name='listForm']").submit(function(){
        $('input[type="submit"]').attr('disabled', true);
    });

</script>

@include('seller.base.seller_pagefooter')