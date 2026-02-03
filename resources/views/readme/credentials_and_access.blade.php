## Credentials and Access

@if(!empty($admin))
Default admin access:

```
{{ $admin['email'] }}
```

```
{{ $admin['password'] }}
```

@endif
@if(!empty($telescope))
Laravel Telescope access:

```
{{ $telescope['email'] }}
```

```
{{ $telescope['password'] }}
```

@endif
@if(!empty($nova))
Laravel Nova access:

```
{{ $nova['email'] }}
```

```
{{ $nova['password'] }}
```
@endif