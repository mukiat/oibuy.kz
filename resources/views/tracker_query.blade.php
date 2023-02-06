<html lang="zh-Hans">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{{ trans('user.logistics_tracking') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/tracker/css/mbase_v6.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/tracker/css/query_v6.css') }}"/>
    <style>
        .company ul {
            padding: 0.3rem;
        }

        .company ul li {
            line-height: 2rem;
            color: #5a5a5a;
        }

        .data-img {
            width: 4rem;
            height: 4rem;
            display: inline-block
        }

        .data-img img {
            width: 4rem;
            height: auto;
        }

        .kd-content {
            margin-bottom: 10px;
            padding: .3rem .3rem 0 .3rem;
            background: #fff;
        }

        .kd-content:last-child {
            margin-bottom: 0;
        }

        .more {
            text-align: center;
            font-size: 14px;
            color: #999;
            padding: 10px 0;
        }

        .result-list {
            overflow: hidden;
            transition: all 0.3s;
        }

        .result-list li.other {
            display: none;
        }
    </style>
</head>
<body>

<div class="container" id="main">
    <div class="main">
        <div class="kd-content">
            <section class="result-box">
                <div class="company">
                    <ul>
                        <li>{{ trans('common.shipping_method') }}：{{$shipping_name}}</li>
                        <li>{{ trans('common.shipping_number') }}：{{$shipping_no}}</li>
                    </ul>
                </div>

                <div class="result-success" id="success">
                    <div class="result-top" id="resultTop">
                        <span id="sortSpan" class="col1-up">{{ lang('order.time') }}</span>
                        <span class="col2">{{ lang('order.location_tracking_progress') }}</span>
                    </div>
                    <ul id="result" class="result-list">
                        @foreach ($traces as $key => $v)
                            <li @if ($key == 0) class="last" @endif>
                                <div class="col1">
                                    <dl>
                                        <dt>{{$v['time']}}</dt>
                                    </dl>
                                </div>
                                <div class="col2"><span></span></div>
                                <div class="col3">{{$v['context']}}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        </div>
    </div>
</div>
</body>
</html>
