<?php

$baseDir = dirname( __FILE__ );

require $baseDir . '/Http/ClientInterface.php';
require $baseDir . '/Http/RequestInterface.php';
require $baseDir . '/Http/ResponseInterface.php';
require $baseDir . '/Http/CurlClient.php';
require $baseDir . '/Http/Request.php';
require $baseDir . '/Http/Response.php';
require $baseDir . '/Api/AplazameExceptionInterface.php';
require $baseDir . '/Api/ApiClientException.php';
require $baseDir . '/Api/ApiCommunicationException.php';
require $baseDir . '/Api/ApiRequest.php';
require $baseDir . '/Api/ApiServerException.php';
require $baseDir . '/Api/Client.php';
require $baseDir . '/Api/DeserializeException.php';
require $baseDir . '/Serializer/JsonSerializable.php';
require $baseDir . '/Serializer/Date.php';
require $baseDir . '/Serializer/Decimal.php';
require $baseDir . '/Serializer/JsonSerializer.php';
