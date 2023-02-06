

@if($category_topic)


@foreach($category_topic as $topic)

<a href="{{ $topic['topic_link'] }}" target="_blank">{{ $topic['topic_name'] }}</a>

@endforeach


@endif


