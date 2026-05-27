<?php

$base_dir = __DIR__;

require $base_dir . '/Http/ClientInterface.php';
require $base_dir . '/Http/RequestInterface.php';
require $base_dir . '/Http/ResponseInterface.php';
require $base_dir . '/Http/CurlClient.php';
require $base_dir . '/Http/Request.php';
require $base_dir . '/Http/Response.php';
require $base_dir . '/Api/AplazameExceptionInterface.php';
require $base_dir . '/Api/ApiClientException.php';
require $base_dir . '/Api/ApiCommunicationException.php';
require $base_dir . '/Api/ApiRequest.php';
require $base_dir . '/Api/ApiServerException.php';
require $base_dir . '/Api/Client.php';
require $base_dir . '/Api/DeserializeException.php';
require $base_dir . '/Serializer/JsonSerializable.php';
require $base_dir . '/Serializer/Date.php';
require $base_dir . '/Serializer/Decimal.php';
require $base_dir . '/Serializer/JsonSerializer.php';
