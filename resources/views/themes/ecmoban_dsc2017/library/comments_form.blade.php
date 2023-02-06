<style>

    /*禁用打分a标签hover样式*/
    #is_add_evaluate a {
        pointer-events:none;
    }

</style>
<div class="comment-goods">

    {{--商家满意度评价--}}
    @if($is_add_evaluate == 0 && isset($item['ru_id']) && $item['ru_id'] > 0 && $shop_info && $item['degree_count'] == 0)
    <div class="rate_wrap">
        <div class="user_info">
            <div class="avatar_img">
                <a href="{{ $shop_info['seller_url'] }}"><img src="{{ $shop_info['logo_thumb'] }}" alt=""></a>
            </div>
            <div class="info_list">
                <div class="store">
                    <span>{{ $shop_info['shop_name'] }}</span>
                    <a id="IM" href="javascript:openWin(this)" ru_id="{{ $shop_info['ru_id'] ?? 0 }}" goods_id="{{ $item['goods_id'] }}" class="iconfont icon-kefu user-shop-kefu"></a>
                </div>
                <div class="tel">
                    <span>{{ __('user.phone') }}：</span>
                    <span>@if($shop_info['kf_tel']) {{ $shop_info['kf_tel'] }} @else {{ __('user.No_comment') }} @endif</span>
                </div>
                <div class="count_wrap">
                    <div class="item">
                        <h3>{{ __('common.synthesize') }}</h3>
                        <p class="color_red">{{ $shop_info['merchants_goods_comment']['cmt']['all_zconments']['score'] ?? 0 }}</p>
                    </div>
                    <div class="item">
                        <h3>{{ __('common.goods') }}</h3>
                        <p>{{ $shop_info['merchants_goods_comment']['cmt']['commentRank']['zconments']['score'] ?? 0 }}</p>
                    </div>
                    <div class="item">
                        <h3>{{ __('common.service') }}</h3>
                        <p>{{ $shop_info['merchants_goods_comment']['cmt']['commentServer']['zconments']['score'] ?? 0 }}</p>
                    </div>
                    <div class="item">
                        <h3>{{ __('common.deliver_goods') }}</h3>
                        <p>{{ $shop_info['merchants_goods_comment']['cmt']['commentDelivery']['zconments']['score'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="rate_list">
            <div class="item item-pf">
                <div class="label">{{ __('user.product_desc') }}</div>
                <div class="value" ectype="rates">
                    <div class="commstar" ectype="p_rate">
                        <a href="javascript:;" data-value="1" class="star1">1</a>
                        <a href="javascript:;" data-value="2" class="star2">2</a>
                        <a href="javascript:;" data-value="3" class="star3">3</a>
                        <a href="javascript:;" data-value="4" class="star4">4</a>
                        <a href="javascript:;" data-value="5" class="star5">5</a>
                    </div>
                    <input type="hidden" name="desc_rank" value="0"/>
                    <div class="error" style="display:none;">{{ __('user.Pleas_mark') }}</div>
                </div>
            </div>
            <div class="item item-pf">
                <div class="label">{{ __('user.seller_fwtd') }}</div>
                <div class="value" ectype="rates">
                    <div class="commstar" ectype="p_rate">
                        <a href="javascript:;" data-value="1" class="star1">1</a>
                        <a href="javascript:;" data-value="2" class="star2">2</a>
                        <a href="javascript:;" data-value="3" class="star3">3</a>
                        <a href="javascript:;" data-value="4" class="star4">4</a>
                        <a href="javascript:;" data-value="5" class="star5">5</a>
                    </div>
                    <input type="hidden" name="service_rank" value="0"/>
                    <div class="error" style="display:none;">{{ __('user.Pleas_mark') }}</div>
                </div>
            </div>
            <div class="item item-pf">
                <div class="label">{{ __('user.logistics_speed') }}</div>
                <div class="value" ectype="rates">
                    <div class="commstar" ectype="p_rate">
                        <a href="javascript:;" data-value="1" class="star1">1</a>
                        <a href="javascript:;" data-value="2" class="star2">2</a>
                        <a href="javascript:;" data-value="3" class="star3">3</a>
                        <a href="javascript:;" data-value="4" class="star4">4</a>
                        <a href="javascript:;" data-value="5" class="star5">5</a>
                    </div>
                    <input type="hidden" name="delivery_rank" value="0"/>
                    <div class="error" style="display:none;">{{ __('user.Pleas_mark') }}</div>
                </div>
            </div>
            <div class="item item-pf">
                <div class="label">{{ __('user.logistics_senders') }}</div>
                <div class="value" ectype="rates">
                    <div class="commstar" ectype="p_rate" >
                        <a href="javascript:;" data-value="1" class="star1">1</a>
                        <a href="javascript:;" data-value="2" class="star2">2</a>
                        <a href="javascript:;" data-value="3" class="star3">3</a>
                        <a href="javascript:;" data-value="4" class="star4">4</a>
                        <a href="javascript:;" data-value="5" class="star5">5</a>
                    </div>
                    <input type="hidden" name="sender_rank" value="0"/>
                    <div class="error" style="display:none;">{{ __('user.Pleas_mark') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="user-items">

        <div class="item item-pf">
            <div class="label"><em>*</em>{{ __('user.score') }}：</div>

            @if($is_add_evaluate == 1)
                <div class="value" >
                    <div class="commstar " id="is_add_evaluate" >
                        @for ($i = 0; $i < 5; $i++)
                            <a href="javascript:;" data-value="{{ $i+1 }}" class="star{{ $i+1 }} @if($item['comment_rank'] && ($i + 1 <= $item['comment_rank'])) selected @endif"  >{{ $i+1 }}</a>
                        @endfor
                    </div>
                    <input type="hidden" name="comment_rank" value="{{ $item['comment_rank'] ?? 0 }}"/>
                    <div class="error" style="display:none;">{{ __('user.Pleas_mark') }}</div>
                </div>
            @else
                <div class="value" ectype="rates">
                    <div class="commstar" ectype="p_rate">
                        @for ($i = 0; $i < 5; $i++)
                            <a href="javascript:;" data-value="{{ $i+1 }}" class="star{{ $i+1 }} @if($item['comment_rank'] && ($i + 1 <= $item['comment_rank'])) selected @endif"  >{{ $i+1 }}</a>
                        @endfor
                    </div>
                    <input type="hidden" name="comment_rank" value="0"/>
                    <div class="error" style="display:none;">{{ __('user.Pleas_mark') }}</div>
                </div>
                @endif

        </div>
        
        @if($is_add_evaluate == 0 && !empty($item['goods_product_tag']))
            {{--买家印象--}}
            <div class="item">
                <div class="label"><em>*</em>{{ __('user.Buyer_impression') }}</div>
                <div class="value">
                    @foreach($item['goods_product_tag'] as $impression)
                        <div class="item-item @if($loop->first) selected @endif " data-val="{{ $impression }}" data-recid="{{ $item['rec_id'] }}" ectype="itemTab">
                            <span>{{ $impression }}</span>
                            <b></b>
                        </div>
                    @endforeach
                </div>
            </div>

        @endif

        <div class="item">
            <div class="label"><em>*</em>{{ __('user.Experience') }}：</div>
            <div class="value">
                <textarea name="content" class="textarea" id="textarea" cols="30" rows="10" size="10" placeholder="{{ $lang['Experience_one'] }}" onKeyUp="figure()" maxlength="500"></textarea>
                <div class="clear"></div>
                <div class="error">{{ $lang['common_form_textarea'] }}<span id="sp">500</span>{{ $lang['zi_zc'] }}</div>
            </div>
        </div>

        <div class="item">
            <div class="label">{{ $lang['single_comment'] }}：</div>
            <div class="value">
                <div class="upload-img-box">
                    <div class="img-lists">
                        <ul class="img-list-ul" ectype="imglist">

                        </ul>
                        <div class="upload-btn">
                            <div id="file_upload{{ $loop->iteration }}" class="uploadify">
                                <a href="javascript:void(0);" id="uploadbutton" class="uploadbutton"><i class="iconfont icon-digital"></i></a>
                            </div>
                        </div>
                        <div class="img-utips">{{ $lang['total'] }}<em ectype="num">0</em>{{ $lang['img_number_notic'] }}<span id="img_number" ectype="ima_number">9</span>{{ $lang['zhang'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        
    @if($is_add_evaluate == 0 && $enabled_captcha)
            <div class="item">
                <div class="label"><em>*</em>{{ $lang['comment_captcha'] }}：</div>
                <div class="value">
                    <div class="sm-input">
                        <input type="text" name="captcha" />
                        <img src="captcha_verify.php?captcha=is_user_comment&identify={{ $item['rec_id'] }}&height=28&font_size=14&{{ $rand }}" width="81" height="33" alt="captcha" onClick="this.src='captcha_verify.php?captcha=is_user_comment&identify={{ $item['rec_id'] }}&height=30&font_size=14&'+Math.random()" class="captcha_img">
                    </div>
                    <div class="mt10 hide captcha-err" style=" width:600px; float:left;">
                        <span class="comt-error"></span>
                    </div>
                </div>
            </div>
    @endif

        {{--买家印象--}}
        @if($is_add_evaluate == 0 && !empty($item['goods_product_tag']))
            <input type="hidden" name="is_impression" value="1" />
        @else
            <input type="hidden" name="is_impression" value="0" />
        @endif
        <input type="hidden" name="impression" id="impression" value="{{ $item['goods_product_tag']['0'] ?? '' }}" />

        <input type="hidden" name="order_id" value="{{ $item['order_id'] }}" />
        <input type="hidden" name="order_id" value="{{ $item['order_id'] }}" />
        <input type="hidden" name="goods_id" value="{{ $item['goods_id'] }}" />
        <input type="hidden" name="rec_id" value="{{ $item['rec_id'] }}" />
        <input type="hidden" name="sign" value="{{ $sign ?? 0 }}" />
        <input type="hidden" name="enabled_captcha" value="{{ $enabled_captcha ?? 0 }}" />
        {{--追加评价 须上传首次评价id--}}
        <input type="hidden" name="comment_id" value="{{ $item['comment_id'] ?? 0 }}" />
        <input type="hidden" name="is_add_evaluate" value="{{ $is_add_evaluate }}" />

    </div>
</div>
<script type="text/javascript">
    var comment_id = "{{ $item['comment_id'] ?? 0 }}";

    var uploader_gallery = new plupload.Uploader({//创建实例的构造方法
        runtimes: 'html5,flash,silverlight,html4', //上传插件初始化选用那种方式的优先级顺序
        browse_button: 'uploadbutton', // 上传按钮
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "comment.php?act=ajax_return_images&order_id={{ $item['order_id'] }}&rec_id={{ $item['rec_id'] }}&goods_id={{ $item['goods_id'] }}&userId={{ $user_id }}&sessid={{ $sessid }}&comment_id="+comment_id, //远程上传地址
        filters: {
            max_file_size: '2mb', //最大上传文件大小（格式100b, 10kb, 10mb, 1gb）
            mime_types: [//允许文件上传类型
                {title: "files", extensions: "bmp,gif,jpg,png,jpeg"}
            ]
        },
        multi_selection: true, //true:ctrl多文件上传, false 单文件上传
    });
    
    uploader_gallery.init();

    uploader_gallery.bind("FilesAdded",function(up, files) { //文件上传前
        var len = $("*[ectype='imglist'] li").length + up.files.length;
        
        if(len > 9){
            pbDialog(json_languages.comment_img_number,"",0);
            uploader_gallery.splice();
            return            
        }else{
            var img_number = 9 - Number(len);
            $("*[ectype='num']").html(len);
            $("*[ectype='ima_number']").html(img_number);

            submitBtn();
        }
    });

    uploader_gallery.bind("BeforeUpload",function (uploader,file) {
        //uploader为当前的plupload实例对象，file为触发此事件的文件对象
        console.log("开始上传",uploader,file);
    });

    uploader_gallery.bind("FileUploaded",function(up, file, info) { //文件上传成功的时候触发
        var str_eval = eval;
        var data = str_eval("(" + info.response + ")");
        if(data.error > 0){
            pbDialog(data.msg,"",0);
            return;
        }else{
            $("*[ectype='imglist']").html(data.content);
        }
    });

    uploader_gallery.bind("Error",function(up, err) {
        //上传出错的时候触发
        pbDialog(err.message,"",0);
    })
    
    function submitBtn(){
        //设置传参
        uploader_gallery.setOption("multipart_params");
        //开始控件
        uploader_gallery.start();
    };
    
    //心得评价输入字数计算
    function figure(){
         var textarea=document.getElementById("textarea");
         var maxlength=500;
         var length=textarea.value.length;
         var count=maxlength-length;
         var sp=document.getElementById("sp");
         sp.innerHTML=count;
         if(count<=10){
            sp.style.color="red";
         }else{
            sp.removeAttribute("style");
         }
    }
</script>