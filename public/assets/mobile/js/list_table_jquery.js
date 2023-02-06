/*
* * 上海商创网络科技有限公司
* * team:made by https://www.dscmall.cn
* * Author:made by zhuofuxi
* * Date:2018-04-06 09:30:00
*/

/**
 * 依赖 jquery.js, layer.js, utils.js
 */

if (typeof Utils != 'object')
{
  layer.msg('Utils object doesn\'t exists.','',0);
}

var listTable = new Object;

listTable.query = "query";
listTable.filter = new Object;
listTable.url = location.href.lastIndexOf("?") == -1 ? location.href.substring((location.href.lastIndexOf("/")) + 1) : location.href.substring((location.href.lastIndexOf("/")) + 1, location.href.lastIndexOf("?"));
listTable.url += "?is_ajax=1";

/**
 * 创建一个可编辑区
 */
listTable.edit = function(obj, act, id)
{
  var tag = obj.firstChild.tagName;

  if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
  {
    return;
  }

  /* 保存原始的内容 */
  var org = obj.innerHTML;
  var val = Browser.isIE ? obj.innerText : obj.textContent;

  /* 创建一个输入框 */
  var txt = document.createElement("INPUT");
  txt.value = (val == 'N/A') ? '' : val;
  txt.style.width = (obj.offsetWidth + 12) + "px" ;

  /* 隐藏对象中的内容，并将输入框加入到对象中 */
  obj.innerHTML = "";
  obj.appendChild(txt);
  txt.focus();

  /* 编辑区输入事件处理函数 */
  txt.onkeypress = function(e)
  {
    var evt = Utils.fixEvent(e);
    var obj = Utils.srcElement(e);

    if (evt.keyCode == 13)
    {
      obj.blur();

      return false;
    }

    if (evt.keyCode == 27)
    {
      obj.parentNode.innerHTML = org;
    }
  }

  /* 编辑区失去焦点的处理函数 */
  txt.onblur = function(e)
  {
    if (Utils.trim(txt.value).length > 0)
    {
        var res = $.post(this.url, "act="+act+"&val=" + encodeURIComponent(Utils.trim(txt.value)) + "&id=" +id, null, 'json');

        if (res.msg)
        {
            layer.msg(res.msg);
        }

        obj.innerHTML = (res.error == 0) ? res.content : org;
    }
    else
    {
        obj.innerHTML = org;
    }
  }
}

/**
 * 切换状态
 */
listTable.toggle = function(obj, act, id)
{
    var val = (obj.src.match(/yes.gif/i)) ? 0 : 1;

    var res = $.post(this.url, "act="+act+"&val=" + val + "&id=" +id, null, 'json');

    if (res.msg)
    {
        layer.msg(res.msg);
    }

    if (res.error == 0)
    {
        obj.src = (res.content > 0) ? 'images/yes.gif' : 'images/no.gif';
    }

}

/* 按钮切换 by wu */
listTable.switchBt = function(obj, act, id)
{
    var obj = $(obj);

    var val = (obj.attr('class').match(/active/i)) ? 0 : 1;

    var res = $.post(this.url, "act="+act+"&val=" + val + "&id=" +id, null, 'json');

    if (res.msg)
    {
        layer.msg(res.msg);
    }

    if (res.error == 0)
    {
        obj.src = (res.content > 0) ? 'images/yes.gif' : 'images/no.gif';
    }
}

/**
 * 切换排序方式
 */
listTable.sort = function(sort_by, sort_order)
{
  var args = "act="+this.query+"&sort_by="+sort_by+"&sort_order=";

  if (this.filter.sort_by == sort_by)
  {
    args += this.filter.sort_order == "DESC" ? "ASC" : "DESC";
  }
  else
  {
    args += "ASC";
  }

  for (var i in this.filter)
  {
    if (typeof(this.filter[i]) != "function" && i != "sort_order" && i != "sort_by" && !Utils.isEmpty(this.filter[i]))
    {
      args += "&" + i + "=" + this.filter[i];
    }
  }

  this.filter['page_size'] = this.getPageSize();

  $.post(this.url, args, this.listCallback, 'json');
}

/**
 * 翻页
 */
listTable.gotoPage = function(page)
{
  if (page != null) this.filter['page'] = page;

  if (this.filter['page'] > this.pageCount) this.filter['page'] = 1;

  this.filter['page_size'] = this.getPageSize();

  this.loadList();
}

/**
 * 载入列表
 */
listTable.loadList = function()
{
    var args = "act="+this.query+"" + this.compileFilter();

    $.post(this.url, args, this.listCallback, 'json');
}

/**
 * 删除列表中的一个记录
 */
listTable.remove = function(id, cfm, opt)
{
  if (opt == null)
  {
    opt = "remove";
  }

  if (confirm(cfm))
  {
    var args = "act=" + opt + "&id=" + id + this.compileFilter();

    $.post(this.url, args, function(data){
        layer.msg(data.msg);
        if(data.error == 0){
            if(data.url){
                window.location.href = data.url;
            }else{
                window.location.reload();
            }
        }
        return false;
    }, 'json');
  }
}

listTable.gotoPageFirst = function()
{
  if (this.filter.page > 1)
  {
    listTable.gotoPage(1);
  }
}

listTable.gotoPagePrev = function()
{
  if (this.filter.page > 1)
  {
    listTable.gotoPage(this.filter.page - 1);
  }
}

listTable.gotoPageNext = function()
{
  if (this.filter.page < listTable.pageCount)
  {
    listTable.gotoPage(parseInt(this.filter.page) + 1);
  }
}

listTable.gotoPageLast = function()
{
  if (this.filter.page < listTable.pageCount)
  {
    listTable.gotoPage(listTable.pageCount);
  }
}

listTable.changePageSize = function(e)
{
    var evt = Utils.fixEvent(e);
    if (evt.keyCode == 13)
    {
        listTable.gotoPage();
        return false;
    };
}

listTable.listCallback = function(result, txt)
{
  if (result.error > 0)
  {
    layer.msg(result.message);
  }
  else
  {
    try
    {
        var ById = "listDiv";
        if (result.class) {
            ById = result.class;
        }

        document.getElementById(ById).innerHTML = result.content;

        if (typeof result.filter == "object")
        {
          listTable.filter = result.filter;
        }

        listTable.pageCount = result.page_count;
    }
    catch (e)
    {
      layer.msg(e.message);
    }
  }
}

listTable.selectAll = function(obj, chk)
{
  if (chk == null)
  {
    chk = 'checkboxes';
  }

  var elems = obj.form.getElementsByTagName("INPUT");

  for (var i=0; i < elems.length; i++)
  {
    if (elems[i].name == chk || elems[i].name == chk + "[]")
    {
      elems[i].checked = obj.checked;
    }
  }
}

listTable.compileFilter = function()
{
  var args = '';
  for (var i in this.filter)
  {
    if (typeof(this.filter[i]) != "function" && typeof(this.filter[i]) != "undefined")
    {
      args += "&" + i + "=" + encodeURIComponent(this.filter[i]);
    }
  }

  return args;
}

listTable.getPageSize = function()
{
  var ps = 10;

  pageSize = document.getElementById("pageSize");

  if (pageSize)
  {
    ps = Utils.isInt(pageSize.value) ? pageSize.value : 10;
    document.cookie = "ECSCP[page_size]=" + ps + ";";
  }
}
