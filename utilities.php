<?php
function millioneyez_set_blogger_state($state, $key) {
    global $millioneyez_plugin_version;
    $service_url = 'https://api.millioneyez.com/v1.0/curator/setConnectionState?authorization=Bearer+'.$key.'&version='.$millioneyez_plugin_version;
    $curl = curl_init($service_url);
    $curl_post_data = array(
        'state' => $state
    );

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));

    $curl_response = curl_exec($curl);
    curl_close($curl);
}
function millioneyez_post_plugin_info_request($key, $version) {
    $service_url = 'https://api.millioneyez.com/v1.0/curator/setPluginInfo?authorization=Bearer+'.$key;
    $curl = curl_init($service_url);
    $curl_post_data = array(
        'pluginVersion' => $version
    );

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));

    $curl_response = curl_exec($curl);
    curl_close($curl);
}
?>