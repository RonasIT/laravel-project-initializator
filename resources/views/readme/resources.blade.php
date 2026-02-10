### Resources

Below are links to tools and services used in this project:
@foreach($resources as $resource)
@if($resource->isActive)
- [{{ $resource->title }}]({{ $resource->link }}): {{ $resource->description }}@if($resource->isActive && empty($resource->link)) (will be added later)@endif

@endif
@endforeach
- [API Documentation]({{ $apiLink }})
