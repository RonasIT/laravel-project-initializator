## Credentials and Access
@foreach($credentials as $resource)
@if(!empty($resource->email))

{{ $resource->title }} access:

```
{{ $resource->email }}
```

```
{{ $resource->password }}
```
@endif
@endforeach
