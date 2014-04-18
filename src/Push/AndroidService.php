<?php

namespace Push;

use Push\Service;

class AndroidService implements Service {

    private $config;
    private $url_service = 'https://android.googleapis.com/gcm/send';
    private $instance;

    public function __construct($config){
        if ( empty($this->instance) ) {
            if(!is_string($config)){
                throw new \Exception("Get (string) private development key to Google Cloud Messaging for Android");
            }else{
                $this->config = $config;
                $result = $this->connect('test','test');
                if($result !== false ){
                    $this->instance = $this;
                }else{
                    throw new \Exception("Invalid private development key to Google Cloud Messaging for Android");
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
                    throw new \Exception("Invalid private development key to Google Cloud Messaging for Android");
                }else{
                    $result = json_decode($result,true);

                    if(isset($result['results']) && !empty($result['results'])){
                        return $result;
                    }else{
                        throw new \Exception("Not send message to Google Cloud Messaging service, check the correctness of the transmitted device ID");
                    }
                }
            }
        }
    }

    private function connect($device_ids,$message){
        if(is_string($device_ids))
            $device_ids = (array)$device_ids;

        $fields = array(
            'registration_ids' => $device_ids,
            'data' => array("message" => $message)
        );
        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=' . $this->config,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url_service);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === FALSE){
            return false;
        }else{
            return $result;
        }
    }

    private function __clone(){}
    private function __wakeup(){}
}