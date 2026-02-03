### Resources

Below are links to tools and services used in this project:
@foreach($resources as $resource)
@if($resource['isActive'])
- [{{ $resource['title'] }}]({{ $resource['link'] ?? '' }}): {{ $resource['description'] }}{{ $resource['laterText'] }}
@endif
@endforeach
- [API Documentation]({{ $apiLink }})
