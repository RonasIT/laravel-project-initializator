### Resources

Below are links to tools and services used in this project:
@foreach($resources as $resource)
@if($resource->isActive)
- [{{ $resource->title }}]({{ $resource->link }}): {{ $resource->description }}{{ empty($resource->link) ? ' (will be added later)' : '' }}
@endif
@endforeach
- [API Documentation]({{ $apiLink }})
