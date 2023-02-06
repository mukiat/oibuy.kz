/*
* * 上海商创网络科技有限公司
* * team:made by https://www.dscmall.cn
* * Author:made by zhuofuxi
* * Date:2018-04-06 09:30:00
*/

// 获取当前域名path 如 admin/ 、seller/
var path = getUrlRelativePath();
var admin = path.split("/")[1];
var domain =  window.location.protocol + '//' + window.location.host + '/' + admin + '/';

$(function() {

        // 选择品牌
        $('input[name="b_name"]').click(function(){
            $('.ecsc-brand-select > .ecsc-brand-select-container').show();
        });
        $(document).click(function(e){
            if(e.target.id !='b_name' && !$(e.target).parents("div").is(".ecsc-brand-select-container")){
                $('.ecsc-brand-select > .ecsc-brand-select-container').hide();
            }

            //品牌
            if(e.target.id !='brand_name' && !$(e.target).parents("div").is(".brand-select-container")){
                $('.brand-select-container').hide();
                $('.brandSelect .brand-select-container').hide();
            }
            //分类
            if(e.target.id !='category_name' && !$(e.target).parents("div").is(".select-container")){
                $('.categorySelect .select-container').hide();
            }

            if($(e.target).parents("div").is(".selection")){
                $('.select-container').hide();
                $(e.target).parents(".categorySelect").find('.select-container').show();
            }

            //仿select
            if(e.target.className !='cite' && !$(e.target).parents("div").is(".imitate_select")){
                $('.imitate_select ul').hide();
            }
        })

        /* AJAX选择品牌 */
        // 根据首字母查询
        $('.letter[ectype="letter"]').find('a[data-letter]').click(function(){
            var _url = $(this).parents('.brand-index:first').attr('data-url');
            var _tid = $(this).parents('.brand-index:first').attr('data-tid');
            var _letter = $(this).attr('data-letter');
            var _search = $(this).html();
            $.getJSON(_url, {type : 'letter', tid : _tid, letter : _letter}, function(data){
                insertBrand(data, _search);
            });
        });
        // 根据关键字查询
        $('.search[ectype="search"]').find('a').click(function(){
            var _url = $(this).parents('.brand-index:first').attr('data-url');
            var _tid = $(this).parents('.brand-index:first').attr('data-tid');
            var _keyword = $('#search_brand_keyword').val();
            $.getJSON(_url, {type : 'keyword', tid : _tid, keyword : _keyword}, function(data){
                insertBrand(data, _keyword);
            });
        });
        // 选择品牌
        $('ul[ectype="brand_list"]').on('click', 'li', function(){
            $(this).parents('.ecsc-brand-select-container').prev().children('input[name=b_id]').val($(this).attr('data-id'));
            $(this).parents('.ecsc-brand-select-container').prev().children('input[name=b_name]').val($(this).attr('data-name'));
            $('.ecsc-brand-select > .ecsc-brand-select-container').hide();
        });

        // 选择品牌
        $(document).on('click','input[name="brand_name"]',function(){
            $(".brand-select-container").hide();
            $(this).parents(".selection").next(".brand-select-container").show();
            $(".brand-list").perfectScrollbar("destroy");
            $(".brand-list").perfectScrollbar();
        });

        // 选择品牌（关联品牌）
        $(document).on('click','input[data-filter="brand_name"]',function(){
            $(".brand-select-container").hide();
            $(this).parents(".selection").next(".brand-select-container").show();
            $(".brand-list").perfectScrollbar("destroy");
            $(".brand-list").perfectScrollbar();
        });

        /* AJAX选择品牌 */
        // 根据首字母查询
        $(document).on('click','.letter a[data-letter]',function(){
            var goods_id = $("input[name=goods_id]").val();
            var ru_id = $("input[name=ru_id]").val();
            var letter = $(this).attr('data-letter');
            $(".brand-not strong").html(letter);

            goods_id = goods_id ? goods_id : 0;
            $.get(domain + 'get_ajax_content.php', 'act=search_brand_list&goods_id='+goods_id+'&ru_id='+ru_id+'&letter='+letter, function(data){
                if(data.content){
                    $(".brand-list").html(data.content);
                    $(".brand-not").hide();
                }else{
                    $(".brand-list").html("");
                    $(".brand-not").show();
                }
                $(".brand-list").perfectScrollbar("destroy");
                $(".brand-list").perfectScrollbar();
            }, 'json')
        });
        // 根据关键字查询
        $(document).on('click','.b_search a',function(){
            var goods_id = $("input[name=goods_id]").val();
            var ru_id = $("input[name=ru_id]").val();
            var keyword = $(this).prev().val();
            $(".brand-not strong").html(keyword);

            goods_id = goods_id ? goods_id : 0;

            $.get(domain + 'get_ajax_content.php', 'act=search_brand_list&goods_id='+goods_id+'&ru_id='+ru_id+'&keyword='+keyword, function(data){
                if(data.content){
                    $(".brand-list").html(data.content);
                    $(".brand-not").hide();
                }else{
                    $(".brand-list").html("");
                    $(".brand-not").show();
                }
                $(".brand-list").perfectScrollbar("destroy");
                $(".brand-list").perfectScrollbar();
            }, 'json')
        });
        // 选择品牌
        $(document).on('click','.brand-list li', function(){
            $(this).parents('.brand-select-container').prev().find('input[data-filter=brand_id]').val($(this).data('id'));
            $(this).parents('.brand-select-container').prev().find('input[data-filter=brand_name]').val($(this).data('name'));
            $('.brand-select-container').hide();
        });
        
        
      //分类选择
      $.category();

});


/*分类搜索的下拉列表*/
jQuery.category = function(){
	$(document).on("click",'.selection input[name="category_name"]',function(){
		$(this).parents(".selection").next('.select-container').show();
	});

	$(document).on('click', '.select-list li', function(){
		var obj = $(this);
		var cat_id = obj.data('cid');
		var cat_name = obj.data('cname');
		var cat_type_show = obj.data('show');
		var user_id = obj.data('seller');
		var url = obj.data('url');
		var table = obj.data('table');

		/* 自定义导航 start */
		if(document.getElementById('item_name')){
			$("#item_name").val(cat_name);
		}

		if(document.getElementById('item_url')){
			$("#item_url").val(url);
		}

		if(document.getElementById('item_catId')){
			$("#item_catId").val(cat_id);
		}
		/* 自定义导航 end */

		$.get(domain + 'get_ajax_content.php', 'act=filter_category&cat_id='+cat_id+"&cat_type_show=" + cat_type_show + "&user_id=" + user_id + "&table=" + table, function(data){
			if(data.content){
				obj.parents(".categorySelect").find("input[data-filter=cat_name]").val(data.cat_nav); //修改cat_name
                                if(data.type != 1){
                                    obj.parents(".select-container").html(data.content);
                                }else{
                                    obj.parents(".select-container").hide();
                                }
				$(".select-list").perfectScrollbar("destroy");
				$(".select-list").perfectScrollbar();
			}
		}, 'json');
		obj.parents(".categorySelect").find("input[data-filter=cat_id]").val(cat_id); //修改cat_id

		var cat_level = obj.parents(".categorySelect").find(".select-top a").length; //获取分类级别
		if(cat_level >= 3){
			$('.categorySelect .select-container').hide();
		}
	});
	//点击a标签返回所选分类 by wu
	$(document).on('click', '.select-top a', function(){

		var obj = $(this);
		var cat_id = obj.data('cid');
		var cat_name = obj.data('cname');
		var cat_type_show = obj.data('show');
		var user_id = obj.data('seller');
		var url = obj.data('url');
		var table = obj.data('table');

		/* 自定义导航 start */
		if(document.getElementById('item_name')){
			$("#item_name").val(cat_name);
		}

		if(document.getElementById('item_url')){
			$("#item_url").val(url);
		}

		if(document.getElementById('item_catId')){
			$("#item_catId").val(cat_id);
		}
		/* 自定义导航 end */

		$.get(domain + 'get_ajax_content.php', 'act=filter_category&cat_id='+cat_id+"&cat_type_show=" + cat_type_show+"&user_id=" + user_id + "&table=" + table, function(data){
			if(data.content){
				obj.parents(".categorySelect").find("input[data-filter=cat_name]").val(data.cat_nav); //修改cat_name
				obj.parents(".select-container").html(data.content);
				$(".select-list").perfectScrollbar("destroy");
				$(".select-list").perfectScrollbar();
			}
		}, 'json');
		obj.parents(".categorySelect").find("input[data-filter=cat_id]").val(cat_id); //修改cat_id
	});
	/*分类搜索的下拉列表end*/
}

// 高级搜索边栏动画
jQuery.gjSearch = function(right){
	$('#searchBarOpen').click(function() {
		$('.search-gao-list').animate({'right': '-40px'},200,
		function() {
			$('.search-gao-bar').animate({'right': '0'},300);
		});
	});
	$('#searchBarClose').click(function() {
		$('.search-gao-bar').animate({'right': right}, 300,
		function() {
			$('.search-gao-list').animate({'right': '0'},  200);
		});
	});
}
// 高级搜索边栏动画end

function getUrlRelativePath()
{
    var url = document.location.toString();
    var arrUrl = url.split("//");

    var start = arrUrl[1].indexOf("/");
    var relUrl = arrUrl[1].substring(start);//stop省略，截取从start开始到结尾的所有字符

    if(relUrl.indexOf("?") != -1){
        relUrl = relUrl.split("?")[0];
    }
    return relUrl;
}
