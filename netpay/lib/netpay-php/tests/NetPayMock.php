<?php

namespace NetPay;

use \Symfony\Component\Process\Process;

class NetPayMock
{
    protected static $process = null;
    protected static $port = -1;

    public static function start()
    {
        if (!file_exists(static::getPathSpec())) {
            return false;
        }

        if (!is_null(static::$process) && static::$process->isRunning()) {
            echo "netpay-mock already running on port " . static::$port . "\n";
            return true;
        }

        static::$port = static::findAvailablePort();

        echo "Starting netpay-mock on port " . static::$port . "...\n";

        static::$process = new Process(join(' ', [
            'netpay-mock',
            '-http-port',
            static::$port,
            '-spec',
            static::getPathSpec(),
            '-fixtures',
            static::getPathFixtures(),
        ]));
        static::$process->start();
        sleep(1);

        if (static::$process->isRunning()) {
            echo "Started netpay-mock, PID = " . static::$process->getPid() . "\n";
        } else {
            die("netpay-mock terminated early, exit code = " . static::$process->wait());
        }

        return true;
    }

    public static function stop()
    {
        if (is_null(static::$process) || !static::$process->isRunning()) {
            return;
        }

        echo "Stopping netpay-mock...\n";
        static::$process->stop(0, SIGTERM);
        static::$process->wait();
        static::$process = null;
        static::$port = -1;
        echo "Stopped netpay-mock\n";
    }

    public static function getPort()
    {
        return static::$port;
    }

    private static function findAvailablePort()
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($sock, "localhost", 0);
        $addr = null;
        $port = -1;
        socket_getsockname($sock, $addr, $port);
        socket_close($sock);
        return $port;
    }

    private static function getPathSpec()
    {
        return  __DIR__ . '/openapi/spec3.json';
    }

    private static function getPathFixtures()
    {
        return  __DIR__ . '/openapi/fixtures3.json';
    }
}
