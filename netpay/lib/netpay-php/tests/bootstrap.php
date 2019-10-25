<?php

require_once(__DIR__ . '/NetPayMock.php');

define("MOCK_MINIMUM_VERSION", "0.57.0");

if (\NetPay\NetPayMock::start()) {
    register_shutdown_function('\NetPay\NetPayMock::stop');

    define("MOCK_HOST", "localhost");
    define("MOCK_PORT", \NetPay\NetPayMock::getPort());
} else {
    define("MOCK_HOST", getenv("NETPEY_MOCK_HOST") ?: "localhost");
    define("MOCK_PORT", getenv("NETPAY_MOCK_PORT") ?: 12111);
}

define("MOCK_URL", "http://" . MOCK_HOST . ":" . MOCK_PORT);

// Send a request to netpay-mock
$ch = curl_init(MOCK_URL);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_NOBODY, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$resp = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Couldn't reach netpay-mock at `" . MOCK_HOST . ":" . MOCK_PORT . "`. Is " .
         "it running? Please see README for setup instructions.\n";
    exit(1);
}

// Retrieve the NetPay-Mock-Version header
$version = null;
$headers = explode("\n", $resp);
foreach ($headers as $header) {
    $pair = explode(":", $header, 2);
    if ($pair[0] == "NetPay-Mock-Version") {
        $version = trim($pair[1]);
    }
}

if ($version === null) {
    echo "Could not retrieve NetPay-Mock-Version header. Are you sure " .
         "that the server at `" . MOCK_HOST . ":" . MOCK_PORT . "` is a netpay-mock " .
         "instance?";
    exit(1);
}

if ($version != "master" && version_compare($version, MOCK_MINIMUM_VERSION) == -1) {
    echo "Your version of netpay-mock (" . $version . ") is too old. The minimum " .
         "version to run this test suite is " . MOCK_MINIMUM_VERSION . ". " .
         "Please see its repository for upgrade instructions.\n";
    exit(1);
}

require_once __DIR__ . '/TestCase.php';
