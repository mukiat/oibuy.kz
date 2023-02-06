<div class="consignee-warp">
    
@if($user_address)

        
@foreach($user_address as $address)

        <div class="cs-w-item
@if($consignee && $consignee['address_id'] == $address['address_id'] && !$leader_id)
 cs-selected
@endif
" data-addressid="{{ $address['address_id'] }}" ectype="cs-w-item">
            <div class="item-tit">
                <h3 class="username">{{ $address['consignee'] }}</h3>
                <span class="remark">{{ $address['sign_building'] }}</span>
                
@if($address['is_checked'] == 1)

                <span class="remark" style="font-size: 12px;background: #FD9125;color: #fff;padding: 0 2px;">{{ $lang['default_address'] }}</span>
                
@endif

            </div>
            <div class="item-tel">
                <span class="contact">{{ $address['mobile'] }}</span>
            </div>
            <div class="item-address">{{ $address['region'] }} &nbsp; {{ $address['address'] }}</div>
            <i class="icon"></i>
            <a href="javascript:void(0);" class="edit" ectype="dialog_checkout" data-value='{"divId":"edit_address","id":{{ $address['address_id'] }},"url":"ajax_flow_address.php?step=edit_Consignee","width":900,"title":"{{ $lang['edit_consignee_address'] }}"}'>{{ $lang['modify'] }}</a>
            
@if($address['is_checked'] == 0)

            <a href="javascript:void(0);" class="delete" ectype="dialog_checkout" data-value='{"divId":"del_address","id":{{ $address['address_id'] }},"url":"ajax_flow_address.php?step=delete_Consignee","width":450,"height":50,"title":"{{ $lang['remove_consignee_address'] }}"}'>{{ $lang['drop'] }}</a>
            
@endif


            <input type="radio" 
@if($consignee && $consignee['address_id'] == $address['address_id'])
checked="checked"
@endif
 class="ui-radio" name="consignee_radio" value="{{ $address['address_id'] }}" id="radio_{{ $address['address_id'] }}" class="hookbox" />
        </div>
        
@endforeach

        <div class="cs-w-item">
            <a href="javascript:void(0);" class="add-new-address" ectype="dialog_checkout" data-value='{"divId":"new_address","id":0,"url":"ajax_flow_address.php?step=edit_Consignee","width":900,"height":450,"title":"{{ $lang['add_consignee_address'] }}"}'>
                <i class="iconfont icon-add-quer"></i><span>{{ $lang['add_new_address'] }}</span>
            </a>
        </div>
    
@else

        <div class="cs-w-item">
            <a href="javascript:void(0);" class="add-new-address" ectype="dialog_checkout" data-value='{"divId":"new_address","id":0,"url":"ajax_flow_address.php?step=edit_Consignee","width":900,"height":450,"title":"{{ $lang['add_consignee_address'] }}"}'>
                <i class="iconfont icon-add-quer"></i><span>{{ $lang['add_new_address'] }}</span>
            </a>
        </div>
    
@endif

    <div class="clear"></div>
    <input type="hidden" id="address_count" value="{{ config('app.address_count', 50) }}"/>
</div>

<div class="community_post 
@if($leader_id)
 cs-selected
@endif
 
@if($nearleader['count'] == 0)
hide
@endif
" >
    <p>{{ $lang['community_stage'] }}</p>
    
@if($leader_id)

    <p>{!! $leader_address !!}<a href="javascript:;">{{ $lang['edit_stage'] }}</a></p>
    
@else

    <p>{{ $lang['nearby_stage'] }} <a href="javascript:;" data-load="firstload">{{ $lang['select_stage'] }}</a></p>
    
@endif

    <i class="icon"></i>
</div>


@if($cross_border_version)

<input type="hidden" id="is_kj" value="{{ $is_kj }}" >

@if($is_kj == 1)

<div class="ck-step mt20">
    <div class="ck-step-tit">
        <h3>{{ $lang['certification'] }}</h3>
    </div>
    <div class="ck-step-cont">
    <span>{{ $lang['real_name'] }}：<input type="text" id="rel_name" name="rel_name" class="text" value="{{ $consignee['rel_name'] }}" placeholder="{{ $lang['input_name'] }}" /></span><span style="margin-left:30px;">{{ $lang['id_card'] }}：<input type="text" id="id_num" name="id_num" class="text" value="{{ $consignee['id_num'] }}" placeholder="{{ $lang['input_user_id'] }}" /></span>
    </div>
</div>

@endif


@endif




    
<script type="text/jscript" src="{{ asset('js/template-web.js') }}"></script>
    
<script id="post-list" type="text/html">
    <% if (isShow) { %>
        <% for(var i = 0; i < list.length; i++){ %>
            <div class="post_list_item" data-item="<%= list[i] %>">
                <div class="post_item_left"></div>
                <div class="post_item_right">
                    <div class="post_item_title"><%= i + 1 %> <%= list[i].pick_up_point %> <span><%= list[i].distance %>km</span></div>
                    <p>地址: <%= list[i].address %></p>
                    <span>电话: <%= list[i].phone %></span>
                </div>
            </div>
        <% } %>
    <% } %>
</script>
<script type="text/javascript">
    $(function() {
        // 初始化地图
        let lat = Number('{{ $nearleader['lat'] }}');
        let lng = Number('{{ $nearleader['lng'] }}');
        let position = null;
        let map = null;
        let postMarker = null;
        let leaderid = '';
        let addressid = '';
        let consigneeName = '';
        let consigneePhone = '';
        window.init = function(){
            position = new AMap.LngLat(lng, lat);
            map = new AMap.Map('container', {
                center:position, //中心点坐标
                zoom:15, // 地图缩放级别
                viewMode: "3D", //使用3D视图
                resizeEnable: true //是否监控地图容器尺寸变化
            });
            // 创建一个 Marker 实例：
            let marker = new AMap.Marker({position});
            // 设置label标签
            // label默认蓝框白底左上角显示，样式className为：amap-marker-label
            marker.setLabel({
                offset: new AMap.Pixel(10, 0),  //设置文本标注偏移量
                content: "<div class='marker_info_label'><div></div><span>您的收货地址</span></div>", //设置文本标注内容
                direction: 'right' //设置文本标注方位
            });
            // 将创建的点标记添加到已有的地图实例：
            map.add(marker);
        };
        // 点击选择驿站显示弹框
        $(document).on('click', '.community_post a', () => {
            if ($('.community_post a').attr('data-load')) {
                $('.community_post a').removeAttr('data-load');
                $('.affirm_btn').removeClass('cs-selected');
                $('.post_scroll_box').html('<div style="text-align: center">加载中...</div>')
                Ajax.call('get_ajax_content.php', 'act=post_list', function({post_list: data}){

                    const postListObj = {
                        isShow: true,
                        list: data
                    };
                    
                    const html = template('post-list', postListObj);
                    $('.post_scroll_box').html(html);
                }, 'GET', 'JSON');
                consigneeName = $('.consignee-warp > .cs-selected .username').text();
                consigneePhone = $('.consignee-warp > .cs-selected .contact').text();
                $('.post_consignee_info .consignee span').text(consigneeName);
                $('.post_consignee_info .consignee_phone span').text(consigneePhone);
            };
            
            $('.post_dialog_box').show();
            $('body').css('overflow-y', 'hidden');
        });
        // 隐藏弹框
        $('.post_dialog_title').on('click', 'a', closeDialog);
        // 选择驿站
        $('.post_scroll_box').on('click', '.post_list_item', function() {
            $('.post_scroll_box').find('.post_list_item').removeClass('current_post');
            $('.post_scroll_box').find('.post_item_current').removeClass('post_item_current');
            $('.post_scroll_box').find('.post_current_title').removeClass('post_current_title');
            $(this).addClass('current_post');
            $(this).children('.post_item_left').addClass('post_item_current');
            $(this).find('.post_item_title').addClass('post_current_title');
            const postInfo = JSON.parse($(this).attr('data-item'));
            // 移除已创建的 marker
            if (postMarker) map.remove(postMarker);
            // 创建一个 Marker 实例：
            let postCurrentPositon = new AMap.LngLat(postInfo.lng, postInfo.lat);
            postMarker = new AMap.Marker({
                position: postCurrentPositon,
                title: postInfo.pick_up_point
            });
            // 将创建的点标记添加到已有的地图实例：
            map.add(postMarker);
            map.panTo(postCurrentPositon);
 
            $('.affirm_btn').attr('ectype', 'cs-w-item');
            $('.affirm_btn').attr('data-leaderid', postInfo.leader_id);
            $('.affirm_btn').attr('data-addressid', $('.cs-selected').attr('data-addressid'));
        });
        // 点击确认按钮
        $('.post_dialog_footer .affirm_btn').on('click', function() {
            leaderid = $('.affirm_btn').attr('data-leaderid');
            if (!leaderid) return alert('请选择社区驿站');
            $('.post_dialog_box').hide();
            $('body').css('overflow-y', 'visible');
        });
        // 点击取消按钮
        $('.post_dialog_footer .cancel_btn').on('click', closeDialog);
        // 取消或关闭
        function closeDialog() {
            if ($('.affirm_btn').attr('data-leaderid')) {
                addressid = $('.affirm_btn').attr('data-addressid');
                $('.affirm_btn').removeAttr('ectype');
                $('.affirm_btn').removeAttr('data-leaderid');
                $('.affirm_btn').removeAttr('data-addressid');
                $('.post_scroll_box').find('.post_list_item').removeClass('current_post');
                $('.post_scroll_box').find('.post_item_current').removeClass('post_item_current');
                $('.post_scroll_box').find('.post_current_title').removeClass('post_current_title');
            }
            if (leaderid) {
                $('.post_scroll_box .post_list_item').each(function() {
                    const postInfo = JSON.parse($(this).attr('data-item'));
                    if (postInfo.leader_id == leaderid) {
                        $(this).addClass('current_post');
                        $(this).children('.post_item_left').addClass('post_item_current');
                        $(this).find('.post_item_title').addClass('post_current_title');
                        $('.affirm_btn').attr('ectype', 'cs-w-item');
                        $('.affirm_btn').attr('data-leaderid', postInfo.leader_id);
                        $('.affirm_btn').attr('data-addressid', addressid);
                        return false;
                    };
                });
            }
            $('.post_dialog_box').hide();
            $('body').css('overflow-y', 'visible');
        }
    })
</script>

<script src="https://webapi.amap.com/maps?v=1.4.15&key=2761558037cb710a1cebefe5ec5faacd&callback=init"></script>

