<?php

namespace Daviswwang\LaravelLog;

class LaravelLog
{

    public static $debug = [];
    public static $starTime;
    public static $starMemory;
    public static $fileName = null;
    public static $mysqlDebug = [];


    public function __construct()
    {
        $this->starTime = microtime(true);
        $this->starMemory = memory_get_usage();
    }

    public function debug(array $params)
    {
        self::$debug[] = $params;
    }

    public function addMysqlDebug(array $params)
    {
        self::$mysqlDebug[] = $params;
    }

    public function setFileName($shopCode, $userId)
    {
        $filePath = storage_path('/logs/' . date('Y-m-d'));

        if (!file_exists($filePath)) {
            mkdir($filePath, 755, true);
        }

        $this->fileName = $filePath . '/' . $userId . '_' . date('H-i-s') . '_' . mt_rand(100, 999) . '.md';
    }


    /**
     * 保存
     * @param  $request
     */
    public function save($request, $response)
    {
        if (!self::$fileName || !self::$debug) return;

        $data = [];
        $data[] = "## 请求数据\n";
        $data[] = " - METHOD:\t\t{$request->getMethod()}\n";
        $data[] = " - GET_URL:\t\t{$request->getUri()}\n";
        $data[] = " - SERV_IP:\t\t" . ($_SERVER['SERVER_ADDR'] ?? '') . "\n";
        $data[] = " - USER_IP:\t\t" . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";
        $data[] = " - REAL_IP:\t\t" . ($_SERVER['X-REAL-IP'] ?? '') . "\n";
        $data[] = " - DATETIME:\t" . date('Y-m-d H:i:s') . "\n";
        $data[] = " - AGENT:\t\t" . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n";
        $data[] = " - Router:\t\t{$request->getRequestUri()}\n";

        $data[] = " \n\n\n\n";
        //一些路由结果，路由结果参数

        $data[] = "## 路由参数\n";
        $Params = $request->all();
        $data[] = " - Request请求参数:\t\t\n";
        if (!empty($Params)) {
            foreach ($Params as $k => $v) {
                if (is_array($v)) {
                    $data[] = " \t\t- {$k}中参数:\t\t\n";
                    foreach ($v as $kk => $item) {
                        if (is_array($item)) $item = json_encode($item, JSON_UNESCAPED_UNICODE);
                        $data[] = "\t\t\t\t- {$kk}\t{$item}\n";
                    }
                } else {
                    $data[] = "- {$k}\t{$v}\n";
                }
            }
        }

        //执行时间 内存消耗
        $data[] = "## 执行时间 内存消耗\n```\n";
        $time = sprintf('% 9.3f', (microtime(true) - self::$starTime) * 1000);
        $memo = sprintf('% 9.3f', (memory_get_usage() - self::$starMemory) / 1024);
        $total = sprintf('% 9.3f', (memory_get_usage()) / 1024);
        $data[] = "\t\tuTime\tuMem\t\ttMem\t\n";
        $data[] = "  {$time}\t{$memo}\t{$total}\t\n```\n";


        if (count(self::$mysqlDebug)) {
            $slow = [];
            $data[] = "\n## Mysql 顺序：\n";
            $data[] = " - 当前共执行MYSQL：\t" . count(self::$mysqlDebug) . " 次\n";
            foreach (self::$mysqlDebug as $i => $value) {
                $data[] = "\t\t执行时间\t:" . ($value['time']) . "\n";
                $data[] = "\t\tsql\t:" . ($value['sql'] ?? '') . "\n";
                $data[] = "\t\tbind参数\t :" . json_encode($value['parmars']) . "\n";
                $data[] = "\n";
            }
        }

        //程序执行顺序
        $data[] = "## 程序执行顺序\n```\n";

        foreach (self::$debug as $value) {
            $data[] = "\t\t文件位置:" . ($value[0] ?? '') . "\n";
            $data[] = "\t\t行数:" . ($value[1] ?? '') . "\n";
            $data[] = "\t\t描述:" . ($value[2] ?? '') . "\n";
            $data[] = "\t\t详情:\n";
            $value3 = $value[3] ?? '';

            if (is_array($value3) || is_object($value3)) {
                $value3 = json_decode(json_encode($value3), true);

                foreach ($value3 as $k => $v) {
                    if (is_array($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                    $data[] = " \t\t\t\t-{$k}\t{$v}\n";
                }
            }
            $data[] = "\n";
        }


        $data[] = "## 返回数据\n```\n";

        foreach ($response as $key => $value) {

            if (is_array($value) || is_object($value)) {
                $value = json_decode(json_encode($value), true);

                foreach ($value as $k => $v) {
                    if (is_array($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                    $data[] = " \t\t\t\t-{$k}\t{$v}\n";
                }
            } else {
                $data[] = "- {$key}\t{$value}\n";
            }
            $data[] = "\n";
        }


        $data[] = "\n```\n";

        $data[] = "\n";

        file_put_contents(self::$fileName, $data, LOCK_EX);
    }
}
