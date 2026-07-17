File ini adalah file salinan error yang saya jumpai di situs. 

Jangan pernah memasukkan file ini dalam antrian push/commit ke Github.

Berikut adalah logs errornya:

# Illuminate\Database\QueryException - Internal Server Error

SQLSTATE[42S22]: Column not found: 1054 Unknown column 'created_at' in 'order clause' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: project_scm_horus-monitoring, SQL: select * from `rekomendasi_risiko` where `status` = Tertunda order by `created_at` desc limit 15 offset 0)

PHP 8.2.12
Laravel 12.62.0
127.0.0.1:8000

## Stack Trace

0 - vendor\laravel\framework\src\Illuminate\Database\Connection.php:838
1 - vendor\laravel\framework\src\Illuminate\Database\Connection.php:794
2 - vendor\laravel\framework\src\Illuminate\Database\Connection.php:411
3 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:3505
4 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:3490
5 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:4080
6 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:3489
7 - vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php:902
8 - vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php:884
9 - vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php:1125
10 - app\Http\Controllers\PengontrolRisiko.php:108
11 - vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php:46
12 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:265
13 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:211
14 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:822
15 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
16 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:50
17 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
18 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php:63
19 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
20 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
21 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
22 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
23 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
24 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
25 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
26 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
27 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
28 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
29 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
30 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
31 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
32 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
33 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
34 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
35 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
36 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
37 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
38 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
39 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
40 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
41 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
42 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
43 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
44 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
45 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
46 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
47 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
48 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
49 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
50 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
51 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
52 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
53 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
54 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
55 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
56 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
57 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
58 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
59 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
60 - public\index.php:20
61 - vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23

## Request

GET /risiko/rekomendasi

## Headers

* **host**: 127.0.0.1:8000
* **connection**: keep-alive
* **sec-ch-ua**: "Not;A=Brand";v="8", "Chromium";v="150", "Google Chrome";v="150"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://127.0.0.1:8000/komparasi
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9
* **cookie**: remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6ImRUb25uN25uZHQzMG1EVkJSVjk5R1E9PSIsInZhbHVlIjoiYVNqQVhodjRGclBZSjNYNmlCa0dZTU5qbS9aRkJqMHhLaEhxNk5LUXN5akVLUTA5WEoxdVJJN0EwMjkzWStYTWpSblpydGdwZmxMSWhIUkVEeFRYL2lrSm1YSkV5Unp4QWo2N1VWa1FXVFRNOGZMd1lWRzUwTVF0SWdINkVZS0tEOEVmNkphcS92ZW1seVRlRGZuRkFTc2xZK2lKcC8wUmoxNkwrVVVwLy9sdm1iN0pNdVppSVJVS1pwM3FVUUg0aklKT09vUjN0cTNKRkhNSEVDVVByMDVobVFSUTAxOXJ2eHh3MFlPeWhNOD0iLCJtYWMiOiIxZWVmNzZjNzM1NzJlNjFiZGI1MmUzNmFkYTg0ZTk5MjMyODdhMzJlYTA5MTY0NzdlM2QyMDk3M2I1YmZhZGQyIiwidGFnIjoiIn0%3D; XSRF-TOKEN=eyJpdiI6IngxZmtzYmpqTlJCYmNnNEZPckxUK1E9PSIsInZhbHVlIjoidGxGVFc3clN4UUk0T3lHWHRUajc2MExhVUR6UVIwRGJFQ3dMd3RhbXVsemhvUkFVWkIvd0xPSERuQ0pwWlh1WVZPSzZjOE5ORVRvUEV2VzUrNTdYR25lVzB6blVqakcrQVhudmE1NU40cDF6QkR4azkwNWJUVWk0WXIyejZNaXMiLCJtYWMiOiI0NGU2NzM3NDI1YzM4YzZiYzZhZGI3YTgxZTQ3M2ZiNGFkOTdjZTE5ZThlYjBhZmExMTliNDdjMzkyNWQ1NzNkIiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6Im0rMG1vRXVPQm5qTm5WbkhoSWxRZWc9PSIsInZhbHVlIjoiazA2RDBiU1I1aWJ1NmVuZzBoMnhBYjhVdlJvN1pzdGVLTWEyeHRscjhqVU9vVVRIY2ZhM1V1bEQyZVlnZzJlbFY5d1VJK09XWHhUVDZnTS9WVTN3MGdkZ2NqYksvWnhrc0RHeGFYZEVibldDVUdnMU1yTVdqcTk2Y0R5SjJPa2UiLCJtYWMiOiIxY2NhODBlYmVhZDFiMDU0OTdlNDQzMDZhMGIwYjc3NzI5NmFkMjc4M2NhZTM3NzJlNWZmMjFkNWE5YjUyZDExIiwidGFnIjoiIn0%3D

## Route Context

controller: App\Http\Controllers\PengontrolRisiko@indeksRekomendasi
route name: risiko.rekomendasi.indeks
middleware: web, auth

## Route Parameters

No route parameter data available.

## Database Queries

* mysql - select * from `sesi` where `id` = 'uc0s40XxCgOivGswJK1RhCT5lUg2Sl1ZMCZytMkt' limit 1 (2.88 ms)
* mysql - select * from `pengguna` where `id` = 1 and `pengguna`.`deleted_at` is null limit 1 (0.62 ms)
* mysql - select count(*) as aggregate from `rekomendasi_risiko` where `status` = 'Tertunda' (0.71 ms)
