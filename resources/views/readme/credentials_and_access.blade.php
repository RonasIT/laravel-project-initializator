## Credentials and Access
@foreach($credentials_and_access as $resource)
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
