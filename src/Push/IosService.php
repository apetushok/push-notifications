<?php

namespace Push;

use Push\Service;

class IosService implements Service {

    private $config;
    private $url_service = 'ssl://gateway.sandbox.push.apple.com:2195';
    private $instance;

    public function __construct($config){
        if ( empty($this->instance) ) {
            if(!is_string($config) || !file_exists($config)){
                throw new \Exception("Has the wrong path to the ssl-certificate.");
            }else{
                $this->config = $config;
                $result = $this->connect('test','test');
                if($result !== false ){
                    $this->instance = $this;
                }else{
                    throw new \Exception("Unable to authenticate, check certificate");
                }
            }
        }
        return $this->instance;
    }

    public function sendMessages($device_ids,$message){

        if(empty($device_ids)){
            throw new \Exception("Not empty device ids");
        }elseif(empty($message)){
            throw new \Exception("Not empty message");
        }elseif(!is_string($message)){
            throw new \Exception("Get type string for message, ". gettype($message)." given");
        }else{
            if(!is_array($device_ids) && !is_string($device_ids)){
                throw new \Exception("Get type string or array for device ids, ". gettype($device_ids)." given");
            }else{
                $result = $this->connect($device_ids,$message);

                if($result === false ){
                    throw new \Exception("Unable to authenticate, check certificate");
                }else{
                    return $result;
                }
            }
        }
    }

    private function connect($device_ids,$message){
        if(is_string($device_ids))
            $device_ids = (array)$device_ids;

        $streamContext = stream_context_create();

        @stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->config);

        $fp = @stream_socket_client($this->url_service, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $streamContext);

        @stream_set_blocking ($fp, 0);

        if (!$fp) {
            return false;
        }

        if($device_ids[0] != "test"){

            $load = array(
                'aps' => array(
                    'alert' => $message,
                    'badge' => 1,
                    'sound' => 'chime'
                )
            );

            $payload = json_encode($load);

            $apnsMessage = null;

            foreach ($device_ids as $token) {
                @$apnsMessage = chr (0) . chr (0) . chr (32) . pack ('H*', str_replace(' ', '', $token)) . pack ('n', strlen ($payload)) . $payload;
            }

            fwrite($fp, $apnsMessage);

            usleep(500000);

            $ivalidToken = $this->pullMessage($fp);

            fclose($fp);

            return $ivalidToken;
        }

        return true;
    }

    private function pullMessage($fp){

        $responseBinary = fread($fp, 6);

        if ($responseBinary !== false && strlen($responseBinary) == 6)
        {
            $response = unpack('Ccommand/Cstatus_code/Nidentifier', $responseBinary);
            return $response;
        }

        return true;
    }

    private function __clone(){}
    private function __wakeup(){}
}