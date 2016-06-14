<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class monolog {
    public function __construct($type, $message) {
        $this->saveLog($type, $message);
    }

    private function saveLog($type, $message) {
        $log = new Logger(CONFIG_APP_NAME);

        if('DEBUG' == $type) {

        } elseif('INFO' == $type) {

        } elseif('NOTICE' == $type) {

        } elseif ('WARNING' == $type) {
            $log->pushHandler(new StreamHandler(CONFIG_LOG_DIR, Logger::WARNING));
            $log->addWarning($message);
        } elseif ('ERROR' == $type) {
            $log->pushHandler(new StreamHandler(CONFIG_LOG_DIR, Logger::ERROR));
            $log->addError($message);
        } elseif ('CRITICAL' == $type) {

        } elseif ('ALERT' == $type) {

        } elseif ('EMERGENCY' == $type) {

        }
    }
}
