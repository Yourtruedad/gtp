<?php

class curl {
    /*
    public function __construct($url, $postData = null) {
        return $this->connect($url, $postData);
    }
    */

    public function connect($url, $postData = null) {
        $curlConnection = curl_init($url);
        curl_setopt($curlConnection, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curlConnection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlConnection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlConnection, CURLOPT_FOLLOWLOCATION, 1);
        if (!empty($postData)) {
            curl_setopt($curlConnection, CURLOPT_POSTFIELDS, implode('&', $postData));
        }
        $result = curl_exec($curlConnection);
        curl_close($curlConnection);

        return $result;
    }
}
