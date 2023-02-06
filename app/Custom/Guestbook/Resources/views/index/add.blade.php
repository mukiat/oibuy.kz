@extends('guestbook::layout')

{{--设置标题--}}
@section('title', $page_title ?? '标题')

{{--加载独立css js--}}
@push('scripts')

@endpush

{{--正文--}}
@section('content')

    {{--引入nav模板文件--}}
    @include('guestbook::components.nav')

    <div class="jumbotron">
        <div class="container">
            <h1>Hello, This is a guestbook DEMO </h1>
            <p>This is a template for custom.</p>
        </div>
    </div>

    <div class="container">
        <form class="form-horizontal" action="{{ route('guestbook.save') }}" method="post">

            <div class="form-group">
                <div>标题：<input class="form-control" type="text" name="title"></div>
            </div>

            <div class="form-group">
                内容: <textarea class="form-control" rows="3" name="content" placeholder="留言内容"></textarea>
            </div>

            @csrf
            <div class="form-group">
                <div class="col-sm-4 ">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>

        @include('guestbook::components.copyright')
    </div>

@endsection

{{--底部引入js--}}
@push('footer_scripts')

@endpush
