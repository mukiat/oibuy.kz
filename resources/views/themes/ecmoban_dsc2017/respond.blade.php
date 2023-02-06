<!DOCTYPE html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="{{ $keywords }}" />
<meta name="Description" content="{{ $description }}" />

<title>{{ $page_title }}</title>

<link rel="shortcut icon" href="favicon.ico" />

{{-- 包含脚本文件 --}}


<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
@include('frontend::library/js_languages_new')
</head>
<body>
@include('frontend::library/page_header_common')

<div class="wrapper">
	<div class="boxCenterList RelaArticle" align="center">
      <div style="margin:158px auto;">
      <p style="font-size:14px; font-weight:bold; color: red; margin-bottom:10px;">{{ $message }}</p>

@if($virtual_card)

        <table width="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="#dddddd">

@foreach($virtual_card as $vgoods)


@foreach($vgoods['info'] as $card)

            <tr>
            <td bgcolor="#FFFFFF">{{ $vgoods['goods_name'] }}</td>
            <td bgcolor="#FFFFFF">

@if($card['card_sn'])
<strong>{{ $lang['card_sn'] }}:</strong>{{ $card['card_sn'] }}
@endif


@if($card['card_password'])
<strong>{{ $lang['card_password'] }}:</strong>{{ $card['card_password'] }}
@endif


@if($card['end_date'])
<strong>{{ $lang['end_date'] }}:</strong>{{ $card['end_date'] }}
@endif

            </td>
            </tr>

@endforeach


@endforeach

        </table>

@endif

        <a href="{{ $shop_url }}">{{ $lang['back_home'] }}</a>
  	</div>
	</div>
</div>
@include('frontend::library/page_footer')
</body>
</html>
