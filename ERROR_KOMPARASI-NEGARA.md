# Illuminate\Database\QueryException - Internal Server Error

SQLSTATE[42S22]: Column not found: 1054 Unknown column 'bendera' in 'field list' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: project_scm_horus-monitoring, SQL: select `id`, `kode_iso`, `nama`, `bendera` from `negara` where `status_pemantauan` = 1 order by `nama` asc)

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
9 - app\Http\Controllers\PengontrolKomparasi.php:28
10 - vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php:46
11 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:265
12 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:211
13 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:822
14 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
15 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:50
16 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
17 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php:63
18 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
19 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
20 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
21 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
22 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
23 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
24 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
25 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
26 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
27 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
28 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
29 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
30 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
31 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
32 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
33 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
34 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
35 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
36 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
37 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
38 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
39 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
40 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
41 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
42 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
43 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
44 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
45 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
46 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
47 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
48 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
49 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
50 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
51 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
52 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
53 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
54 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
55 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
56 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
57 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
58 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
59 - public\index.php:20
60 - vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23

## Request

GET /komparasi

## Headers

* **host**: 127.0.0.1:8000
* **connection**: keep-alive
* **sec-ch-ua**: "Google Chrome";v="149", "Chromium";v="149", "Not)A;Brand";v="24"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://127.0.0.1:8000/negara/IDN
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9
* **cookie**: remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6Ik1JK1RQRDFqbTQ1a081bVE1UzJvRXc9PSIsInZhbHVlIjoiV05sUitJTHR6K0xSRVhTUTZQZGdwT216TWIvTzlrSmhOOWJzekVGWDRnTXBSdHFmcUNhckxwNGg1UllQYnhGL3JJcnI2MmNhUHJsNUhPWjJUN0lCNjdzL2k1Zjh5cUt6L21qMS9yc2RIcjB5N01GOFBReXdmTFB3QW5jam9qQ0orbkYzeGRaT1g5MG5mTDRidmNHZE5BWGZ6UHNQNlp1UUU3dkpBQ2ZCTkxVenhDcWt5UVRpZk9tZ0dDNzZWVVJROGlTZWI3QVRYUmRCcktBaVRENUM3VWIzdG93YTVTeU53U2pTNUlTWnN1cz0iLCJtYWMiOiIwM2U4MTNhYjhkMzEyOGE3NDA3ZTllYzJmODhmODk4YzVlYjFiYWExNTgzY2FjMTI0ZWJhZjcxYzY3Njc2MGY4IiwidGFnIjoiIn0%3D; XSRF-TOKEN=eyJpdiI6IlJzaTM1SUo2NlJXS1MwZEFyaGtjMXc9PSIsInZhbHVlIjoidWR6TXBSL0xGMUI1K0tYdnN3MUtveXNKSER5SzNOT2wzRk9iV3B2NkhSZWRqY3FYZHl6SmxlSFJHc1VqdG9DNEp3ZWR3QWllZmFCejVrZ3ZyNFFzYjhOZEpVSEh1ZHRxeEozUnhhSWU0ZUZIaDlYdmorOE84ZEZpTWdqaEhWQWIiLCJtYWMiOiJjNTg3MDRmMDY0ODc2YmZhM2VlM2Y2MGVmZTE2Y2I5NTBiODQ5MDc0OWEyZGQ5ZjcyYjk0MzE1MGRlNTVjN2NiIiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6IjB5em9OUm0rZ3VwTWQyemdNWURmYVE9PSIsInZhbHVlIjoiWUcxSkFFbXFwcGVPb2tYQ1F4RERkNE80cWFnaVAzUmZGY1VyOFdwU1kwTDNtSGRXOXZaYnpPbEdtb1F2bjhhZzIrYjhPYmF1OWZuVzJVbjl2cTBKVWpFcmswMWdMSTQ0cXVFSzhGK2JnbDlxWS9DTEtMNlZLRThxQmNnRFpZdmsiLCJtYWMiOiJlYzU0M2VkZjU5ZGIyMzExYjZkZjczNmEzYzYyMzIwZjE2NTlhNjRlOTE0MDc1MTVlMTlmZTU5YmJlOThkZGNhIiwidGFnIjoiIn0%3D

## Route Context

controller: App\Http\Controllers\PengontrolKomparasi@indeks
route name: komparasi.indeks
middleware: web, auth

## Route Parameters

No route parameter data available.

## Database Queries

* mysql - select * from `sesi` where `id` = '9qzKZDiZ8IGXRAfyKK6PJxztvqfIySDJAv74jAJs' limit 1 (5.8 ms)
* mysql - select * from `pengguna` where `id` = 1 and `pengguna`.`deleted_at` is null limit 1 (1.79 ms)
