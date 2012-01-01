<?php
/*
 * Copyright (c) 2011-2012, LiXianlin <xianlinli at gmail dot com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *   * Neither the name of Redis nor the names of its contributors may be used
 *     to endorse or promote products derived from this software without
 *     specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

define('ADMIN_USERNAME', 'redis'); // admin username
define('ADMIN_PASSWORD', '5f4dcc3b5aa765d61d8327deb882cf99'); // admin password

/**
 * RedisCli
 *
 * @author xianlinli@gmail.com
 */
class RedisCli {
    /**
     * command argument count
     * @var array
     */
    private $_argcArr = array(
        'get' => 2,
        'set' => 3,
        'setnx' => 3,
        'setex' => 4,
        'append' => 3,
        'strlen' => 2,
        'del' => -2,
        'exists' => 2,
        'setbit' => 4,
        'getbit' => 3,
        'setrange' => 4,
        'getrange' => 4,
        'substr' => 4,
        'incr' => 2,
        'decr' => 2,
        'mget' => -2,
        'rpush' => -3,
        'lpush' => -3,
        'rpushx' => 3,
        'lpushx' => 3,
        'linsert' => 5,
        'rpop' => 2,
        'lpop' => 2,
        'brpop' => -3,
        'brpoplpush' => 4,
        'blpop' => -3,
        'llen' => 2,
        'lindex' => 3,
        'lset' => 4,
        'lrange' => 4,
        'ltrim' => 4,
        'lrem' => 4,
        'rpoplpush' => 3,
        'sadd' => -3,
        'srem' => -3,
        'smove' => 4,
        'sismember' => 3,
        'scard' => 2,
        'spop' => 2,
        'srandmember' => 2,
        'sinter' => -2,
        'sinterstore' => -3,
        'sunion' => -2,
        'sunionstore' => -3,
        'sdiff' => -2,
        'sdiffstore' => -3,
        'smembers' => 2,
        'zadd' => -4,
        'zincrby' => 4,
        'zrem' => -3,
        'zremrangebyscore' => 4,
        'zremrangebyrank' => 4,
        'zunionstore' => -4,
        'zinterstore' => -4,
        'zrange' => -4,
        'zrangebyscore' => -4,
        'zrevrangebyscore' => -4,
        'zcount' => 4,
        'zrevrange' => -4,
        'zcard' => 2,
        'zscore' => 3,
        'zrank' => 3,
        'zrevrank' => 3,
        'hset' => 4,
        'hsetnx' => 4,
        'hget' => 3,
        'hmset' => -4,
        'hmget' => -3,
        'hincrby' => 4,
        'hdel' => -3,
        'hlen' => 2,
        'hkeys' => 2,
        'hvals' => 2,
        'hgetall' => 2,
        'hexists' => 3,
        'incrby' => 3,
        'decrby' => 3,
        'getset' => 3,
        'mset' => -3,
        'msetnx' => -3,
        'randomkey' => 1,
        'select' => 2,
        'move' => 3,
        'rename' => 3,
        'renamenx' => 3,
        'expire' => 3,
        'expireat' => 3,
        'keys' => 2,
        'dbsize' => 1,
        'auth' => 2,
        'ping' => 1,
        'echo' => 2,
        'save' => 1,
        //'bgsave' => 1,
        //'bgrewriteaof' => 1,
        //'shutdown' => 1,
        'lastsave' => 1,
        'type' => 2,
        //'multi' => 1,
        //'exec' => 1,
        //'discard' => 1,
        //'sync' => 1,
        //'flushdb' => 1,
        //'flushall' => 1,
        'sort' => -2,
        'info' => 1,
        //'monitor' => 1,
        'ttl' => 2,
        'persist' => 2,
        //'slaveof' => 3,
        'debug' => -2,
        'config' => -2,
        //'subscribe' => -2,
        //'unsubscribe' => -1,
        //'psubscribe' => -2,
        //'punsubscribe' => -1,
        //'publish' => 3,
        //'watch' => -2,
        //'unwatch' => 1,
        'object' => -2,
        'client' => -2,
        'slowlog' => -2,
    );

    /**
     * config array
     * @var array
     */
    private $_configArr = array();

    /**
     * file pointer
     * @var resource
     */
    private $_fp = NULL;

    /**
     * construct
     * @param array $configArr array($host, $port);
     */
    public function __construct($configArr) {
        $this->_configArr = array(
            'host' => $configArr[0],
            'port' => $configArr[1],
        );
    }

    /**
     * open socket connection
     */
    private function __connect() {
        $this->_fp = fsockopen($this->_configArr['host'], $this->_configArr['port'], $errNo, $errStr, 3);
        if (!$this->_fp) {
            $this->error("Error:{$errStr}({$errNo}).");
        }
    }

    /**
     * close socket connection
     */
    private function __close() {
        if ($this->_fp !== NULL) {
            fclose($this->_fp);
            $this->_fp = NULL;
        }
    }

    /**
     * parse raw command
     * @param string $str
     * @return array
     */
    public function parseRawCommand($str) {
        $index = 0;
        $len = strlen($str);
        $inQuote = false;
        $tokenArr = array();
        $token = '';
        while (true) {
            if ($index >= $len) {
                if ($token !== '') {
                    $tokenArr[] = $token;
                }
                break;
            }
            $char = $str[$index];
            if ($char === '"') {
                if ($inQuote && $index >= 1 && $str[$index - 1] === '\\') {
                    $token .= $char;
                } else if ($inQuote) {
                    $tokenArr[] = str_replace('\"', '"', $token);
                    $token = '';
                    $inQuote = false;
                } else {
                    $inQuote = true;
                }
            } else if (in_array($char, array(' ', "\t")) && !$inQuote) {
                if ($token !== '') {
                    $tokenArr[] = $token;
                    $token = '';
                }
            } else {
                $token .= $char;
            }
            ++$index;
        }
        return $tokenArr;
    }

    /**
     * make command
     * @param array $tokenArr
     * @return string
     */
    public function makeCommand($tokenArr) {
        $argc = count($tokenArr);
        $key = strtolower($tokenArr[0]);
        if (!array_key_exists($key, $this->_argcArr)) {
            $this->error("Unknown or disabled command {$tokenArr[0]}!");
        }
        $arity = $this->_argcArr[$key];
        if ($arity > 0 && $argc > $arity) {
            $argc = $arity;
        } else if (( $arity > 0 && $argc !== $arity) || $argc < abs($arity)) {
            $positiveArity = abs($arity);
            $this->error("Command {$tokenArr[0]} need {$positiveArity} parameter!");
        }
        $command = '*' . $argc . "\r\n";
        for ($i = 0; $i < $argc; ++$i) {
            $command .= '$' . strlen($tokenArr[$i]) . "\r\n" . $tokenArr[$i] . "\r\n";
        }
        return $command;
    }

    /**
     * exec command
     * @param string $command
     * @return array
     */
    public function execCommand($command) {
        if ($this->_fp === NULL) {
            $this->__connect();
        }
        if (fwrite($this->_fp, $command) === false) {
            $this->error('Failed write content to stream!');
        }
        $replyType = fgetc($this->_fp);
        if ($replyType === false) {
            return array('?', '');
        }
        $replyData = '';
        switch ($replyType) {
            case '+':
                $replyData = substr(fgets($this->_fp), 0, -2);
                break;
            case '-':
                $replyData = substr(fgets($this->_fp), 0, -2);
                break;
            case ':':
                $replyData = substr(fgets($this->_fp), 0, -2);
                break;
            case '$':
                $replyData = $this->__readBulkData($this->_fp);
                break;
            case '*':
                $bulkCount = intval(trim(fgets($this->_fp)));
                $replyData = array();
                for ($i = 0; $i < $bulkCount; ++$i) {
                    fseek($this->_fp, 1, SEEK_CUR); // skip "$"
                    $replyData[] = $this->__readBulkData($this->_fp);
                }
                break;
        }
        return array($replyType, $replyData);
    }

    /**
     * read bulk data
     * @return string
     */
    private function __readBulkData($fp) {
        $bulkLen = intval(rtrim(fgets($fp)));
        if ($bulkLen === 0) {
            $dataStr = '';
            fseek($fp, 2, SEEK_CUR); // skip "\r\n"
        } else if ($bulkLen > 0) {
            $dataStr = '';
            do {
                if ($bulkLen >= 8192) {
                    $dataStr .= fread($fp, 8192);
                    $bulkLen -= 8192;
                } else {
                    $dataStr .= fread($fp, $bulkLen);
                    break;
                }
            } while ($bulkLen > 0);
            fseek($fp, 2, SEEK_CUR); // skip "\r\n"
        } else {
            $dataStr = NULL; // undefined
        }
        return $dataStr;
    }

    /**
     * add slashes
     * @param string $str
     * @return string
     */
    public static function addSlashes($str) {
        $index = 0;
        $len = strlen($str);
        $ret = '';
        while ($index < $len) {
            $char = $str[$index];
            if ($char === '\\') {
                $ret .= '\\\\';
            } else if ($char === '"') {
                $ret .= '\\"';
            } else if ($char === "\n") {
                $ret .= '\\n';
            } else if ($char === "\r") {
                $ret .= '\\r';
            } else if ($char === "\t") {
                $ret .= '\\t';
            } else if ($char === "\a") {
                $ret .= '\\a';
            } else if ($char === "\b") {
                $ret .= '\\b';
            } else if (ctype_print($char)) {
                $ret .= $char;
            } else {
                $ret .= sprintf('\\x%02x', ord($char));
            }
            ++$index;
        }
        return $ret;
    }

    /**
     * string to binary
     * @param string $str
     * @return string
     */
    public static function str2bin($str) {
        if (!is_string($str)) {
            return '';
        }
        $len = strlen($str);
        $index = 0;
        $binStr = '';
        while ($index < $len) {
            $binStr .= sprintf('%08b', ord($str[$index++]));
        }
        return $binStr;
    }

    /**
     * output error message and exit
     * @param string $msg
     */
    public function error($msg) {
        exit($msg);
    }

    /**
     * destruct
     */
    public function __destruct() {
        $this->__close();
    }
}

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] !== ADMIN_USERNAME || md5($_SERVER['PHP_AUTH_PW']) !== ADMIN_PASSWORD) {
    header('WWW-Authenticate: Basic realm="Redis Cli Login"');
    header('HTTP/1.1 401 Unauthorized');
    exit('Please login first!');
} else if (isset($_GET['do']) && $_GET['do'] === 'exec' && !empty($_POST)) {
    set_time_limit(5);
    $host = isset($_POST['host']) ? $_POST['host'] : '127.0.0.1';
    $port = isset($_POST['port']) ? $_POST['port'] : 6379;
    $dbindex = isset($_POST['dbindex']) ? $_POST['dbindex'] : 0;
    $redisCli = new RedisCli(array($host, $port));
    $tokenArr = $redisCli->parseRawCommand($_POST['command']);
    $commandStr = $redisCli->makeCommand($tokenArr);
    $selectCommandStr = $redisCli->makeCommand(array('select', $dbindex));
    $redisCli->execCommand($selectCommandStr);
    $replyArr = $redisCli->execCommand($commandStr);
    switch ($replyArr[0]) {
        case '?':
            $dataStr = 'Sorry,error happend!';
            break;
        case '+':
            $dataStr = $replyArr[1];
            break;
        case '-':
            $dataStr = '(error) ' . $replyArr[1];
            break;
        case ':':
            $dataStr = '(integer) ' . $replyArr[1];
            break;
        case '$':
            if ($replyArr[1] === NULL) {
                $dataStr = '(nil)';
            } else if (strcasecmp($tokenArr[0], 'info') === 0) {
                $dataStr = '<pre>' . $replyArr[1] . '</pre>';
            } else if (strcasecmp($tokenArr[0], 'get') === 0 && isset($tokenArr[2])) {
                $dataStr = '"' . RedisCli::str2bin($replyArr[1]) . '"';
            } else {
                $dataStr = '"' . RedisCli::addSlashes($replyArr[1]) . '"';
            }
            break;
        case '*':
            if (empty($replyArr[1])) {
                $dataStr = '(empty list or set)';
            } else {
                $dataStr = '<ol>';
                foreach ($replyArr[1] as $val) {
                    if ($val === NULL) {
                        $dataStr .='<li>(nil)</li>';
                    } else {
                        $dataStr .='<li>"' . RedisCli::addSlashes($val) . '"</li>';
                    }
                }
                $dataStr .='</ol>';
                if (strcasecmp($tokenArr[0], 'keys') === 0 && isset($tokenArr[2])) {
                    $dataStr .= '<ul><li>"' . implode('" "', $replyArr[1]) . '"</li></ul>';
                }
            }
            break;
    }
    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Redis Cli Display</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            * {margin:0;padding:0;}
            body {background:#000;color:#fff;margin:5px;}
            ol,ul {padding-left:40px;}
        </style>
    </head>
    <body>
        {$dataStr}
    </body>
</html>
EOT;
} else if (isset($_GET['do']) && $_GET['do'] === 'display') {
    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Redis Cli Display</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            * {margin:0;padding:0;}
            body {font-family:verdana,arial,helvetica,sans-serif;font-size:14px;background:#000;color:#fff;margin:5px;}
            ol {padding-left:40px;}
            a {color:#00f;}
        </style>
    </head>
    <body>
        <h4><font colore="blue">Thanks for using Redis Cli!</font></h4>
        <h5>&nbsp;</h5>
        <h5>You can visit <a href="http://redis.io/" target="_blank">http://redis.io/</a> to get the lastest news about redis!</h5>
        <h5>For security reason, some command are disabled.</h5>
        <h5>Type '<font color="red">help</font>' or '<font color="red">help &lt;command&gt;</font>' for help!</h5>
        <h5>Just try the '<font color="red">↑</font>', '<font color="red">↓</font>' key for history, it is usefull!</h5>
    </body>
</html>
EOT;
} else if (isset($_GET['do']) && $_GET['do'] === 'input') {
    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Redis Cli Input</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            * {margin:0;padding:0;}
            body {font-family:verdana,arial,helvetica,sans-serif;font-size:14px;background:#000;color:#fff;}
            input {font-family:verdana,arial,helvetica,sans-serif;font-size:14px;background:#000;color:#fff;border:none;}
            #command {width:90%;}
        </style>
        <script type="text/javascript">
            var commandHelp = {"APPEND":["key value","Append a value to a key","string","1.3.3"],"AUTH":["password","Authenticate to the server","connection","0.08"],"BGREWRITEAOF":["-","Asynchronously rewrite the append-only file","server","1.07"],"BGSAVE":["-","Asynchronously save the dataset to disk","server","0.07"],"BLPOP":["key [key ...] timeout","Remove and get the first element in a list, or block until one is available","list","1.3.1"],"BRPOP":["key [key ...] timeout","Remove and get the last element in a list, or block until one is available","list","1.3.1"],"BRPOPLPUSH":["source destination timeout","Pop a value from a list, push it to another list and return it; or block until one is available","list","2.1.7"],"CONFIG GET":["parameter","Get the value of a configuration parameter","server","2.0"],"CONFIG RESETSTAT":["-","Reset the stats returned by INFO","server","2.0"],"CONFIG SET":["parameter value","Set a configuration parameter to the given value","server","2.0"],"DBSIZE":["-","Return the number of keys in the selected database","server","0.07"],"DEBUG OBJECT":["key","Get debugging information about a key","server","0.101"],"DEBUG SEGFAULT":["-","Make the server crash","server","0.101"],"DECR":["key","Decrement the integer value of a key by one","string","0.07"],"DECRBY":["key decrement","Decrement the integer value of a key by the given number","string","0.07"],"DEL":["key [key ...]","Delete a key","generic","0.07"],"DISCARD":["-","Discard all commands issued after MULTI","transactions","1.3.3"],"ECHO":["message","Echo the given string","connection","0.07"],"EXEC":["-","Execute all commands issued after MULTI","transactions","1.1.95"],"EXISTS":["key","Determine if a key exists","server","0.07"],"EXPIRE":["key seconds","Set a key's time to live in seconds","generic","0.09"],"EXPIREAT":["key timestamp","Set the expiration for a key as a UNIX timestamp","generic","1.1"],"FLUSHALL":["-","Remove all keys from all databases","server","0.07"],"FLUSHDB":["-","Remove all keys from the current database","server","0.07"],"GET":["key","Get the value of a key","string","0.07"],"GETBIT":["key offset","Returns the bit value at offset in the string value stored at key","string","2.1.8"],"GETSET":["key value","Set the string value of a key and return its old value","string","0.091"],"HDEL":["key field","Delete a hash field","hash","1.3.10"],"HEXISTS":["key field","Determine if a hash field exists","hash","1.3.10"],"HGET":["key field","Get the value of a hash field","hash","1.3.10"],"HGETALL":["key","Get all the fields and values in a hash","hash","1.3.10"],"HINCRBY":["key field increment","Increment the integer value of a hash field by the given number","hash","1.3.10"],"HKEYS":["key","Get all the fields in a hash","hash","1.3.10"],"HLEN":["key","Get the number of fields in a hash","hash","1.3.10"],"HMGET":["key field [field ...]","Get the values of all the given hash fields","hash","1.3.10"],"HMSET":["key field value [field value ...]","Set multiple hash fields to multiple values","hash","1.3.8"],"HSET":["key field value","Set the string value of a hash field","hash","1.3.10"],"HSETNX":["key field value","Set the value of a hash field, only if the field does not exist","hash","1.3.8"],"HVALS":["key","Get all the values in a hash","hash","1.3.10"],"INCR":["key","Increment the integer value of a key by one","string","0.07"],"INCRBY":["key increment","Increment the integer value of a key by the given number","string","0.07"],"INFO":["-","Get information and statistics about the server","server","0.07"],"KEYS":["pattern","Find all keys matching the given pattern","generic","0.07"],"LASTSAVE":["-","Get the UNIX time stamp of the last successful save to disk","server","0.07"],"LINDEX":["key index","Get an element from a list by its index","list","0.07"],"LINSERT":["key BEFORE|AFTER pivot value","Insert an element before or after another element in a list","list","2.1.1"],"LLEN":["key","Get the length of a list","list","0.07"],"LPOP":["key","Remove and get the first element in a list","list","0.07"],"LPUSH":["key value","Prepend a value to a list","list","0.07"],"LPUSHX":["key value","Prepend a value to a list, only if the list exists","list","2.1.1"],"LRANGE":["key start stop","Get a range of elements from a list","list","0.07"],"LREM":["key count value","Remove elements from a list","list","0.07"],"LSET":["key index value","Set the value of an element in a list by its index","list","0.07"],"LTRIM":["key start stop","Trim a list to the specified range","list","0.07"],"MGET":["key [key ...]","Get the values of all the given keys","string","0.07"],"MONITOR":["-","Listen for all requests received by the server in real time","server","0.07"],"MOVE":["key db","Move a key to another database","generic","0.07"],"MSET":["key value [key value ...]","Set multiple keys to multiple values","string","1.001"],"MSETNX":["key value [key value ...]","Set multiple keys to multiple values, only if none of the keys exist","string","1.001"],"MULTI":["-","Mark the start of a transaction block","transactions","1.1.95"],"PERSIST":["key","Remove the expiration from a key","generic","2.1.2"],"PING":["-","Ping the server","connection","0.07"],"PSUBSCRIBE":["pattern","Listen for messages published to channels matching the given patterns","pubsub","1.3.8"],"PUBLISH":["channel message","Post a message to a channel","pubsub","1.3.8"],"PUNSUBSCRIBE":["[pattern [pattern ...]]","Stop listening for messages posted to channels matching the given patterns","pubsub","1.3.8"],"QUIT":["-","Close the connection","connection","0.07"],"RANDOMKEY":["-","Return a random key from the keyspace","generic","0.07"],"RENAME":["key newkey","Rename a key","generic","0.07"],"RENAMENX":["key newkey","Rename a key, only if the new key does not exist","generic","0.07"],"RPOP":["key","Remove and get the last element in a list","list","0.07"],"RPOPLPUSH":["source destination","Remove the last element in a list, append it to another list and return it","list","1.1"],"RPUSH":["key value","Append a value to a list","list","0.07"],"RPUSHX":["key value","Append a value to a list, only if the list exists","list","2.1.1"],"SADD":["key member","Add a member to a set","set","0.07"],"SAVE":["-","Synchronously save the dataset to disk","server","0.07"],"SCARD":["key","Get the number of members in a set","set","0.07"],"SDIFF":["key [key ...]","Subtract multiple sets","set","0.100"],"SDIFFSTORE":["destination key [key ...]","Subtract multiple sets and store the resulting set in a key","set","0.100"],"SELECT":["index","Change the selected database for the current connection","connection","0.07"],"SET":["key value","Set the string value of a key","string","0.07"],"SETBIT":["key offset value","Sets or clears the bit at offset in the string value stored at key","string","2.1.8"],"SETEX":["key seconds value","Set the value and expiration of a key","string","1.3.10"],"SETNX":["key value","Set the value of a key, only if the key does not exist","string","0.07"],"SETRANGE":["key offset value","Overwrite part of a string at key starting at the specified offset","string","2.1.8"],"SHUTDOWN":["-","Synchronously save the dataset to disk and then shut down the server","server","0.07"],"SINTER":["key [key ...]","Intersect multiple sets","set","0.07"],"SINTERSTORE":["destination key [key ...]","Intersect multiple sets and store the resulting set in a key","set","0.07"],"SISMEMBER":["key member","Determine if a given value is a member of a set","set","0.07"],"SLAVEOF":["host port","Make the server a slave of another instance, or promote it as master","server","0.100"],"SMEMBERS":["key","Get all the members in a set","set","0.07"],"SMOVE":["source destination member","Move a member from one set to another","set","0.091"],"SORT":["key [BY pattern] [LIMIT offset count] [GET pattern [GET pattern ...]] [ASC|DESC] [ALPHA] [STORE destination]","Sort the elements in a list, set or sorted set","generic","0.07"],"SPOP":["key","Remove and return a random member from a set","set","0.101"],"SRANDMEMBER":["key","Get a random member from a set","set","1.001"],"SREM":["key member","Remove a member from a set","set","0.07"],"STRLEN":["key","Get the length of the value stored in a key","string","2.1.2"],"SUBSCRIBE":["channel","Listen for messages published to the given channels","pubsub","1.3.8"],"SUBSTR":["key start end","Get a substring of the string stored at a key","string","1.3.4"],"SUNION":["key [key ...]","Add multiple sets","set","0.091"],"SUNIONSTORE":["destination key [key ...]","Add multiple sets and store the resulting set in a key","set","0.091"],"SYNC":["-","Internal command used for replication","server","0.07"],"TTL":["key","Get the time to live for a key","generic","0.100"],"TYPE":["key","Determine the type stored at key","generic","0.07"],"UNSUBSCRIBE":["[channel [channel ...]]","Stop listening for messages posted to the given channels","pubsub","1.3.8"],"UNWATCH":["-","Forget about all watched keys","transactions","2.1.0"],"WATCH":["key [key ...]","Watch the given keys to determine execution of the MULTI/EXEC block","transactions","2.1.0"],"ZADD":["key score member","Add a member to a sorted set, or update its score if it already exists","sorted_set","1.1"],"ZCARD":["key","Get the number of members in a sorted set","sorted_set","1.1"],"ZCOUNT":["key min max","Count the members in a sorted set with scores within the given values","sorted_set","1.3.3"],"ZINCRBY":["key increment member","Increment the score of a member in a sorted set","sorted_set","1.1"],"ZINTERSTORE":["destination numkeys key [key ...] [WEIGHTS weight] [AGGREGATE SUM|MIN|MAX]","Intersect multiple sorted sets and store the resulting sorted set in a new key","sorted_set","1.3.10"],"ZRANGE":["key start stop [WITHSCORES]","Return a range of members in a sorted set, by index","sorted_set","1.1"],"ZRANGEBYSCORE":["key min max [WITHSCORES] [LIMIT offset count]","Return a range of members in a sorted set, by score","sorted_set","1.050"],"ZRANK":["key member","Determine the index of a member in a sorted set","sorted_set","1.3.4"],"ZREM":["key member","Remove a member from a sorted set","sorted_set","1.1"],"ZREMRANGEBYRANK":["key start stop","Remove all members in a sorted set within the given indexes","sorted_set","1.3.4"],"ZREMRANGEBYSCORE":["key min max","Remove all members in a sorted set within the given scores","sorted_set","1.1"],"ZREVRANGE":["key start stop [WITHSCORES]","Return a range of members in a sorted set, by index, with scores ordered from high to low","sorted_set","1.1"],"ZREVRANGEBYSCORE":["key max min [WITHSCORES] [LIMIT offset count]","Return a range of members in a sorted set, by score, with scores ordered from high to low","sorted_set","2.1.6"],"ZREVRANK":["key member","Determine the index of a member in a sorted set, with scores ordered from high to low","sorted_set","1.3.4"],"ZSCORE":["key member","Get the score associated with the given member in a sorted set","sorted_set","1.1"],"ZUNIONSTORE":["destination numkeys key [key ...] [WEIGHTS weight] [AGGREGATE SUM|MIN|MAX]","Add multiple sorted sets and store the resulting sorted set in a new key","sorted_set","1.3.10"]}
            var historyArr = [];
            var curIndex = -1;
            function $(id) {
                return document.getElementById(id);
            }
            function beforeSubmit() {
                var obj = $('command');
                if (obj.value == '') {
                    return false;
                } else if (/^[\s]*([a-z]+)[\s]*/i.test(obj.value)) {
                    var command = RegExp.$1.toUpperCase();
                    if (command == 'HELP') {
                        if (/^[\s]*[a-z]+[\s]+([a-z]+[ ]?[a-z]+)/i.test(obj.value)) {
                            var command = RegExp.$1.toUpperCase();
                            if (typeof(commandHelp[command]) == 'undefined') {
                                alert('Invalid command!');
                            } else {
                                var arr = commandHelp[command];
                                var str = [command, ' ', arr[0], '\\nsummary: ', arr[1], '\\ngroup: ', arr[2], '\\nsince: ', arr[3]].join('');
                                alert(str);
                            }
                        } else {
                            var str = 'Command List:\\n\\n';
                            var tab = '\\t\\t\\t';
                            var i = 0;
                            for (var k in commandHelp) {
                                str += k + tab.substr(Math.floor(k.length / 8));
                                if (i % 3 == 2) {
                                    str += '\\n';
                                }
                                ++i;
                            }
                            str += '\\n\\nType "help <command>" to get detail help.';
                            alert(str);
                        }
                        return false;
                    } else if (typeof(commandHelp[command]) == 'undefined' && command != 'CONFIG' && command != 'DEBUG') {
                        alert('Invalid command!');
                        return false;
                    }
                }
                var frm = $('form1');
                frm.submit();
                historyArr.push(obj.value);
                curIndex = historyArr.length;
                obj.value = '';
                obj.focus();
                return false;
            }
            function keyDownHanlder(e) {
                e = e || window.event;
                var obj = $('command');
                switch(e.keyCode) {
                    case 38: // ↑
                        --curIndex;
                        if (curIndex < 0) {
                            curIndex = -1;
                            obj.value = '';
                        } else if (typeof(historyArr[curIndex]) != 'undefined') {
                            obj.value = historyArr[curIndex];
                        }
                        break;
                    case 40: // ↓
                        ++curIndex;
                        if (curIndex > historyArr.length - 1) {
                            curIndex = historyArr.length - 1;
                            obj.value = '';
                        } else if (typeof(historyArr[curIndex]) != 'undefined') {
                            obj.value = historyArr[curIndex];
                        }
                        break;
                    case 46: // Delete
                        var tempArr = [];
                        for (var i = 0; i < historyArr.length; ++i) {
                            if (historyArr[i] != obj.value) {
                                tempArr.push(historyArr[i]);
                            }
                        }
                        historyArr = tempArr;
                        obj.value = '';
                        break;
                }
            }
            window.onload = function () {
                var obj = $('command');
                obj.onmouseover = function () {
                    this.select();
                }
                obj.focus();
            };
        </script>
    </head>
    <body>
        <form id="form1" action="?do=exec" method="post" target="display" onsubmit="return beforeSubmit()">
            <label for="host">Host:</label><input type="text" id="host" name="host" value="127.0.0.1" /><label for="port">Port:</label><input type="text" id="port" name="port" value="6379" /><label for="dbindex">DBIndex:</label><input type="text" id="dbindex" name="dbindex" value="0" /><br />
            <label for="command">redis&gt;&nbsp;</label><input type="text" id="command" name="command" onkeydown="keyDownHanlder(event)" autocomplete="off" /><button type="submit" style="width:0;height:0;border:0;"></button>
        </form>
    </body>
</html>
EOT;
} else {
    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Redis Cli</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <frameset rows="*,38" border="2" frameSpacing="0">
        <frame name="display" src="?do=display"></frame>
        <frame name="input" src="?do=input"></frame>
    </frameset>
</html>
EOT;
}
?>