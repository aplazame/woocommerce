<?php

$baseDir = dirname( __FILE__ );

include $baseDir . '/Http/ClientInterface.php';
include $baseDir . '/Http/RequestInterface.php';
include $baseDir . '/Http/ResponseInterface.php';
include $baseDir . '/Http/CurlClient.php';
include $baseDir . '/Http/Request.php';
include $baseDir . '/Http/Response.php';
include $baseDir . '/Api/AplazameExceptionInterface.php';
include $baseDir . '/Api/ApiClientException.php';
include $baseDir . '/Api/ApiCommunicationException.php';
include $baseDir . '/Api/ApiRequest.php';
include $baseDir . '/Api/ApiServerException.php';
include $baseDir . '/Api/Client.php';
include $baseDir . '/Api/DeserializeException.php';
include $baseDir . '/Serializer/JsonSerializable.php';
include $baseDir . '/Serializer/Date.php';
include $baseDir . '/Serializer/Decimal.php';
include $baseDir . '/Serializer/JsonSerializer.php';
