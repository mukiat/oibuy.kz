@include('admin.base.header')

<script type="text/javascript" src="{{ asset('js/jquery-ui/jquery-ui.min.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('js/jquery-ui/jquery-ui.min.css') }}" />

<div class="warpper">
    <div class="title">{{ __('admin/touch_page_nav.menu_' . $device) }} - {{ __('admin/touch_page_nav.title') }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                <li>{!! __('admin/touch_page_nav.tips_' . $device) !!}</li>
            </ul>
        </div>

        <div class="flexilist">
            <div class="main-info">
                <div class="switch_info">
                    <div class="move_div">
                        <div class="move_left">
                            <h4>可用模块</h4>
                            <div class="move_info">
                                <div class="move_list" id="discover-draggable">
                                    <ul></ul>
                                </div>
                            </div>
                        </div>
                        <div class="move_middle">&nbsp;</div>
                        <div class="move_right">
                            <h4>已启用模块</h4>
                            <div class="move_info">
                                <div class="move_list">
                                    <ul id="discover-sortable"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var device = '{{ $device }}';

    $(function(){
        var width = $(".move_left").width();
        var midwidth = $(".move_middle").width();

        // 列表
        function modulesList(){
            $.ajax({
                type: 'GET',
                url: "{{ route('admin/touch_page_nav/modules') }}?device=" + device,
                async: true,
                cache: false,
                dataType: 'json',
                data: {},
                success: function (res) {
                    var list = res.data;
                    var left = list.filter(item => { return item.display == 0 });
                    var right = list.filter(item => { return item.display == 1 });

                    if(left.length > 0){
                        var li = "";

                        left.forEach(v=>{
                            li += `<li class="discover_li lyrow" id="${v.id}"><span class="drag">${v.nav_name}</span></li>`;
                        })

                        $(".move_left .move_list ul").html(li);
                    }else{
                        $(".move_left .move_list ul").html("");
                    }

                    if(right.length > 0){
                        var li = "";

                        right.forEach(v=>{
                            li += `<li class="discover_li lyrow" id="${v.id}"><span class="drag">${v.nav_name}</span><span class="d-close">删除</span></li>`;
                        })

                        $(".move_right .move_list ul").html(li);
                    }else{
                        $(".move_right .move_list ul").html("");
                    }


                    //启用模块
                    $("#discover-draggable .lyrow").draggable({
                        connectToSortable: "#discover-sortable", helper: "clone", handle: ".drag",
                        drag: function (e, t) {
                           t.helper.width(300);
                        },
                        stop: function (e, t) {
                            var id = $(this).attr("id");

                            var toarray = $("#discover-sortable").sortable("toArray");
                            var index = toarray.indexOf("");

                            if(t.position.left > width){
                                modulesUpdate(id,index,1);
                            }
                        }
                    });
                }
            })
        }

        modulesList();

        //排序
        $("#discover-sortable").sortable({
            opacity: .35,
            handle: ".drag",
            update:function(e,t){
                var value = []
                $(this).find("li").each(function(){
                    var val = $(this).attr('id');
                    value.push(val);
                })

                modulesSort(value);
            }
        });


        //关闭启用模块
        $(document).on("click",".d-close",function(){
            var id = $(this).parent().attr("id");

            modulesUpdate(id,0,0);
        })

        //更新
        function modulesUpdate(id,sort,display){
            $.ajax({
                type: 'POST',
                url: "{{ route('admin/touch_page_nav/update') }}",
                async: true,
                cache: false,
                dataType: 'json',
                data: {
                    id:id,
                    sort:sort,
                    display:display
                },
                success: function (res) {
                    if(res.status == 'success'){
                        modulesList();
                    }
                }
            });
        }

        //排序
        function modulesSort(sort){
            $.ajax({
                type: 'POST',
                url: "{{ route('admin/touch_page_nav/resort') }}",
                async: true,
                cache: false,
                dataType: 'json',
                data: {
                    sort:sort
                },
                success: function (res) {
                    console.log(res)
                }
            });
        }
    })
</script>
<style type="text/css">
.move_div{ width:100%; overflow:hidden; margin-top:20px;}
.move_div .move_left,.move_div .move_right{ width:46%; float:left;}
.move_div .move_middle{ width:8%; float:left; margin-top:140px;}
.move_div h4{ color:#333; text-align:center; padding:10px 0;}
.move_div .move_info{ border-radius:5px; position:relative; box-shadow:1px 1px 6px #999 inset; padding:20px;}
.move_div .move_info .move_list{ height:250px; position:relative;}
.move_div .move_info li{ border:1px solid #eaeaea; cursor:move; border-radius: 5px; margin-bottom: 10px;}
.move_div .move_info li:last-child{ margin-bottom: 0; }
.move_div .move_info li span{ display: block; float: left; padding: 8px 10px; width: calc(100% - 50px)}
.move_div .move_info li .d-close{ float: right; width: 50px; text-align: center; cursor: pointer; color: #62b3ff;}
.move_div .move_info li a{ color: #707070; display:inline-block; width:calc(100% - 50px); overflow:hidden; height:36px; float:left;white-space: nowrap;text-overflow: ellipsis;}
.move_div .move_info li .sc_icon_ok{ width:12px; height:12px; display:inline-block; background-position:-118px -479px; margin:12px 8px 0; vertical-align:middle; float:left;}
.move_div .move_info li.current{ background:#f5faff;}
.move_div .move_info li.current .sc_icon_ok{ background-position:-140px -479px;}

.move_div .move_middle .checkbox_item{ margin-right:0; width:100%; text-align:center; margin-bottom:15px;}

.move_div .move_info li .sc_icon_no{ width:12px; height:12px; display:inline-block; background-position:-155px -501px; margin:12px 8px 0; vertical-align:middle; float:left;}
.move_div .move_info li.current .sc_icon_no{ background-position:-140px -501px;}
.move_div .move_right .move_info li.current{ background:#f5faff;}
.move_handle{ float:left; margin:20px 0 0 28px;}
.move_handle *{ float:left;}
.move_handle .btn{ margin-right:10px;}
.move_handle .moveAll{ border-color:#dbdbdb;}
.move_handle .text{ height:23px; line-height:23px; margin-right:5px;}
.move_handle .pj_price_txt,.move_handle .purchase_txt{ margin-right:10px;}

.move_middle .move_point{ background-position: -224px -352px; width:30px; height:30px; margin:0 auto; cursor:pointer;}
</style>

@include('admin.base.footer')
