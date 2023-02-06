@extends('guestbook::layout')

{{--设置标题--}}
@section('title', $page_title ?? '标题')

{{--加载独立css js--}}
@push('scripts')

@endpush

{{--正文--}}
@section('content')

<div class="jumbotron">
    <div class="container">
        <h1>Hello, This is a guestbook mobile DEMO </h1>
        <p>This is a template for custom.</p>
    </div>
</div>

<div class="container">
    <!-- Example row of columns -->
    <div class="row">

        @foreach($guestbook_list as $v)

        <div class="col-md-4">
            <h2>{{ $v['username'] }}</h2>
            <p>{{ $v['content'] }}</p>
            <p><a class="btn btn-default" href="{{ route('guestbook.mobile.add', ['id' => $v['id']]) }}" role="button">add Guest &raquo;</a></p>
        </div>

        @endforeach

    </div>

    @include('guestbook::components.copyright')
</div>

@endsection

{{--底部引入js--}}
@push('footer_scripts')

@endpush
