# Guzzle Services

Provides an implementation of the Guzzle Command library that uses Guzzle service descriptions to describe web services, serialize requests, and parse responses into easy to use model structures.

```php
use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;

$client = new Client();
$description = new Description([
	'baseUri' => 'http://httpbin.org/',
	'operations' => [
		'testing' => [
			'httpMethod' => 'GET',
			'uri' => '/get{?foo}',
			'responseModel' => 'getResponse',
			'parameters' => [
				'foo' => [
					'type' => 'string',
					'location' => 'uri'
				],
				'bar' => [
					'type' => 'string',
					'location' => 'query'
				]
			]
		]
	],
	'models' => [
		'getResponse' => [
			'type' => 'object',
			'additionalProperties' => [
				'location' => 'json'
			]
		]
	]
]);

$guzzleClient = new GuzzleClient($client, $description);

$result = $guzzleClient->testing(['foo' => 'bar']);
echo $result['args']['foo'];
// bar
```

## Installing

This project can be installed using Composer:

``composer require guzzlehttp/guzzle-services``

For **Guzzle 5**, use ``composer require guzzlehttp/guzzle-services:0.6``.

**Note:** If Composer is not installed [globally](https://getcomposer.org/doc/00-intro.md#globally) then you may need to run the preceding Composer commands using ``php composer.phar`` (where ``composer.phar`` is the path to your copy of Composer), instead of just ``composer``.

## Plugins

* Load Service description from file [https://github.com/gimler/guzzle-description-loader]

## Transition guide from Guzzle 5.0 to 6.0
 
### Change regarding PostField and PostFile

The request locations `postField` and `postFile` were removed in favor of `formParam` and `multipart`. If your description looks like
 
```php
[
    'baseUri' => 'http://httpbin.org/',
    'operations' => [
        'testing' => [
            'httpMethod' => 'GET',
            'uri' => '/get{?foo}',
            'responseModel' => 'getResponse',
            'parameters' => [
                'foo' => [
                    'type' => 'string',
                    'location' => 'postField'
                ],
                'bar' => [
                    'type' => 'string',
                    'location' => 'postFile'
                ]
            ]
        ]
    ],
]
```

you need to change `postField` to `formParam` and `postFile` to `multipart`. 

More documentation coming soon.

## Cookbook

### Changing the way query params are serialized

By default, query params are serialized using strict RFC3986 rules, using `http_build_query` method. With this, array params are serialized this way:

```php
$client->myMethod(['foo' => ['bar', 'baz']]);

// Query params will be foo[0]=bar&foo[1]=baz
```

However, a lot of APIs in the wild require the numeric indices to be removed, so that the query params end up being `foo[]=bar&foo[]=baz`. You
can easily change the behaviour by creating your own serializer and overriding the "query" request location:

```php
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\RequestLocation\QueryLocation;
use GuzzleHttp\Command\Guzzle\QuerySerializer\Rfc3986Serializer;
use GuzzleHttp\Command\Guzzle\Serializer;

$queryLocation = new QueryLocation('query', new Rfc3986Serializer(true));
$serializer = new Serializer($description, ['query' => $queryLocation]);
$guzzleClient = new GuzzleClient($client, $description, $serializer);
```

You can also create your own serializer if you have specific needs.

## Security

If you discover a security vulnerability within this package, please send an email to security@tidelift.com. All security vulnerabilities will be promptly addressed. Please do not disclose security-related issues publicly until a fix has been announced. Please see [Security Policy](https://github.com/guzzle/guzzle-services/security/policy) for more information.

## License

Guzzle is made available under the MIT License (MIT). Please see [License File](LICENSE) for more information.

## For Enterprise

Available as part of the Tidelift Subscription

The maintainers of Guzzle and thousands of other packages are working with Tidelift to deliver commercial support and maintenance for the open source dependencies you use to build your applications. Save time, reduce risk, and improve code health, while paying the maintainers of the exact dependencies you use. [Learn more.](https://tidelift.com/subscription/pkg/packagist-guzzlehttp-guzzle-services?utm_source=packagist-guzzlehttp-guzzle-services&utm_medium=referral&utm_campaign=enterprise&utm_term=repo)
