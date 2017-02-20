<?php
/**
 * Created by PhpStorm.
 * User: songxun
 * Date: 12/12/2016
 * Time: 8:55 PM
 */
namespace App\Helpers;

class PushHelper
{
    public static function apply_push_fcm($firebase_key, $msg, $payload, $arr_id) {
        $app_name = 'Shopzeely';
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array (
            'notification' => [
                'title' => $app_name,
                'body'  => $msg,
                'sound' => 1
            ],
            'registration_ids' => $arr_id,
            'data' => $payload
        );

        $fields = json_encode ( $fields );

        $headers = array (
            'Authorization:key='.$firebase_key,
            'Content-Type:application/json'
        );

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

        $result = curl_exec ( $ch );
        curl_close ( $ch );

        return $result;
    }

    public static function apply_apple_push_dev($ids,$msg) {

        $passphrase = '123';
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'shopzeelydev.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        $fp = stream_socket_client(
            'ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if($fp) {
            //$ids = explode(',', $registrationIds);
            for ($i = 0; $i < count($ids); $i++) {
                $payload = json_encode($msg);
                $msg = chr(0) . pack('n', 32) . pack('H*', $ids[$i]) . pack('n', strlen($payload)) . $payload;
                $result = fwrite($fp, $msg, strlen($msg));

            }
            fclose($fp);
        }
    }

    public static function apply_apple_push_pro($registrationIds) {
        $text = 'succes';
        $msg['aps'] = array(
            'alert' => $text,
            'sound' => 'default'
        );

        $passphrase = '123';
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'shopzeelydev.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        $fp = stream_socket_client(
            'ssl://gateway.sandbox.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        $ids = explode(',', $registrationIds);
        for ($i = 0; $i < count($ids); $i++) {
            $payload = json_encode($msg);
            $msg = chr(0) . pack('n', 32) . pack('H*', $ids[$i]) . pack('n', strlen($payload)) . $payload;
            $result = fwrite($fp, $msg, strlen($msg));
        }
        fclose($fp);
    }

}