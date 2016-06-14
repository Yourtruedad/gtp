<?php

class test {
    // database connection link
    protected $db;

    // curl instance
    protected $curl;

    public function __construct() {
        $this->db = new db();
        $this->curl = new curl();
    }

    public function logIn($forumsId) {
        $forumDetails = $this->db->getForumDetails($forumsId);
        if (!empty($forumDetails)) {
            $accountDetails = $this->db->getAccountDetails($forumsId);
            if (!empty($accountDetails)) {
                $curlConneciton = $this->curl->connect($forumDetails['link']);
                return $curlConneciton;
                //return $accountDetails;
            }
        }
    }
}
