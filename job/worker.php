<?php
require __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

use App\Helpers\PushHelper;
use App\Config\Loader;

// User Model
use App\DB\Models\Device as DeviceModel;
use App\DB\Models\User as UserModel;

$loader = new Loader();
$config = $loader->load();

$context = [
    'config'    => $config
];

$gmWorker = new GearmanWorker();
// Add local server as client
$gmWorker->addServer();

$gmWorker->addFunction("spz_shopping_request_create"    , "spz_shopping_request_create", $context);
$gmWorker->addFunction("spz_account_reset_password"     , "spz_account_reset_password", $context);
$gmWorker->addFunction("spz_account_forgot_password"    , "spz_account_forget_password", $context);
$gmWorker->addFunction("spz_bid_create"                 , "spz_bid_create", $context);
$gmWorker->addFunction("spz_bid_approve"                , "spz_bid_approve", $context);
$gmWorker->addFunction("spz_bid_decline"                , "spz_bid_decline", $context);

print "Waiting for job...\n";
while($gmWorker->work())
{
    if ($gmWorker->returnCode() != GEARMAN_SUCCESS)
    {
        echo "return_code: " . $gmWorker->returnCode() . "\n";
        break;
    }
}

function spz_shopping_request_create($job, &$context){
    error_log("Process spz_shopping_request_create task");
    error_log( "Workload: \n");
    error_log($job->workload());

    $data = json_decode($job->workload(), true);
    $config = $context['config'];
    $db = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};port=3306", $config['db_user'], $config['db_password']);

    $device_model = new DeviceModel($db);
    $devices = $device_model->list_all_shopper_device_nearby_request($data['id']);
    if (count($devices) > 0) {
        $androidRegistrationIds = array();
        $appleRegistrationIds   = array();

        foreach($devices as $device) {
            $device_type    = $device['device_type'];
            $device_token   = $device['device_token'];

            if ($device_type == 'android') {
                if (!in_array($device_token, $androidRegistrationIds)) {
                    $androidRegistrationIds[] = $device_token;
                }
            } else {
                if (in_array($device_token, $appleRegistrationIds) == false) {
                    array_push($appleRegistrationIds, $device_token);
                }
            }
        }

        /******send push notification*****/
        $msg = 'New request found in your area.';
        $data = [
            'message'   => $msg,
            'type'      => 'shopping_request_create',
            'request_id'    => $data['id']
        ];
        if (count($androidRegistrationIds) > 0) {
            PushHelper::apply_push_fcm($config['firebase_partner_key'], $msg, $data, $androidRegistrationIds);
        }

//        if (count($appleRegistrationIds) > 0) {
//            $text = 'New request was found in your area.';
//            $message['aps'] = [
//                'alert' => $text,
//                'request_id' => $data['id'],
//                'sound' => 'default'
//            ];
//            PushHelper::apply_apple_push_dev($appleRegistrationIds,$message);
//        }
    }
    $db = null;
}

function spz_account_reset_password() {

}

function spz_account_forget_password() {

}
function spz_bid_create($job, &$context) {
    error_log( "spz_bid_create" . "\n");

    $data = json_decode($job->workload(), true);
    $config = $context['config'];
    $db = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};port=3306", $config['db_user'], $config['db_password']);

    $request_id = $data['request_id'];

    $user_model = new UserModel($db);
    $device_model = new DeviceModel($db);

    $customer = $user_model->list_one_customer_by_request($request_id);
    $devices = $device_model->list_all_by_user($customer['id']);

    if (count($devices) > 0) {
        $androidRegistrationIds = array();
        $appleRegistrationIds   = array();
        echo json_encode($devices) . "\n";
        foreach($devices as $device) {
            $device_type    = $device['device_type'];
            $device_token   = $device['device_token'];

            if ($device_type == 'android') {
                if (!in_array($device_token, $androidRegistrationIds)) {
                    $androidRegistrationIds[] = $device_token;
                }
            } else {
                if (in_array($device_token, $appleRegistrationIds) == false) {
                    array_push($appleRegistrationIds, $device_token);
                }
            }
        }

        /******send push notification*****/
        $msg = 'Bid was placed on your request';
        $data = [
            'message'       => $msg,
            'type'          => 'bid_create',
            'bid_id'        => $data['id'],
            'shopper_id'    => $data['shopper_id']
        ];
        error_log(json_encode($androidRegistrationIds) . "\n");
        if (count($androidRegistrationIds) > 0) {
            PushHelper::apply_push_fcm($config['firebase_customer_key'], $msg, $data, $androidRegistrationIds);
        }

//        if (count($appleRegistrationIds) > 0) {
//            $text = 'Bid was placed.';
//            $message['aps'] = [
//                'alert' => $text,
//                'bid_id' => $data['id'],
//                'sound' => 'default'
//            ];
//            PushHelper::apply_apple_push_dev($appleRegistrationIds,$message);
//        }
    }
    $db = null;
}

function spz_bid_approve($job, &$context) {

    error_log('spz_bid_approve');
    $data = json_decode($job->workload(), true);
    $config = $context['config'];
    $db = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};port=3306", $config['db_user'], $config['db_password']);

    $shopper_id = $data['shopper_id'];

    $device_model = new DeviceModel($db);
    $devices = $device_model->list_all_by_user($shopper_id);
    if (count($devices) > 0) {
        $androidRegistrationIds = array();
        $appleRegistrationIds   = array();

        foreach($devices as $device) {
            $device_type    = $device['device_type'];
            $device_token   = $device['device_token'];

            if ($device_type == 'android') {
                if (!in_array($device_token, $androidRegistrationIds)) {
                    $androidRegistrationIds[] = $device_token;
                }
            } else {
                if (in_array($device_token, $appleRegistrationIds) == false) {
                    array_push($appleRegistrationIds, $device_token);
                }
            }
        }

        /******send push notification*****/
        $msg = 'Your bid was approved. Please start work';
        $data = [
            'message'       => $msg,
            'type'          => 'bid_approve',
            'bid_id'        => $data['id'],
            'request_id'    => $data['request_id']
        ];
        if (count($androidRegistrationIds) > 0) {
            PushHelper::apply_push_fcm($config['firebase_partner_key'], $msg, $data, $androidRegistrationIds);
        }
        error_log(json_encode($androidRegistrationIds));
//        if (count($appleRegistrationIds) > 0) {
//            $text = 'Your bid was approved. Please start work';
//            $message['aps'] = [
//                'alert' => $text,
//                'bid_id' => $data['id'],
//                'sound' => 'default'
//            ];
//            PushHelper::apply_apple_push_dev($appleRegistrationIds,$message);
//        }
    }
    $db = null;
}

function spz_bid_decline($job, &$context) {
    $data = json_decode($job->workload(), true);
    $config = $context['config'];
    $db = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};port=3306", $config['db_user'], $config['db_password']);

    $shopper_id = $data['shopper_id'];

    $device_model = new DeviceModel($db);
    $devices = $device_model->list_all_by_user($shopper_id);
    if (count($devices) > 0) {
        $androidRegistrationIds = array();
        $appleRegistrationIds   = array();

        foreach($devices as $device) {
            $device_type    = $device['device_type'];
            $device_token   = $device['device_token'];

            if ($device_type == 'android') {
                if (!in_array($device_token, $androidRegistrationIds)) {
                    $androidRegistrationIds[] = $device_token;
                }
            } else {
                if (in_array($device_token, $appleRegistrationIds) == false) {
                    array_push($appleRegistrationIds, $device_token);
                }
            }
        }

        /******send push notification*****/
        $msg = 'Your bid was declined.';
        $data = [
            'message'   => $msg,
            'type'      => 'bid_declined',
            'bid_id'    => $data['id']
        ];
        if (count($androidRegistrationIds) > 0) {
            PushHelper::apply_push_fcm($config['firebase_partner_key'], $msg, $data, $androidRegistrationIds);
        }

//        if (count($appleRegistrationIds) > 0) {
//            $text = 'Your bid was declined.';
//            $message['aps'] = [
//                'alert' => $text,
//                'bid_id' => $data['id'],
//                'sound' => 'default'
//            ];
//            PushHelper::apply_apple_push_dev($appleRegistrationIds,$message);
//        }
    }
    $db = null;
}
