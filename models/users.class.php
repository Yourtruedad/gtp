<?php

class users {
    // database connection link
    protected $db;

    // curl instance
    //protected $curl;

    public function __construct() {
        $this->db = new db();
        //$this->curl = new curl();
    }

    /**
     * Check if provided login credentials are correct or not.
     *
     * @param string $email
     * @param string $password
     *
     * @return bool
     */
    public function checkIfLoginDetailsAreCorrect($email, $password) {
        $sql = '
            SELECT 
                `id`,
                `email`,
                `password`
            FROM 
                `users` 
            WHERE 
                `email` = :email             
            ;';
        $query = $this->db->pdo->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetch(PDO::FETCH_ASSOC);

        if (!empty($results)) {
            if (true === password_verify($password, $results['password']))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if login name is used.
     *
     * @param string $email
     *
     * @return bool
     */
    public function checkIfLoginNameIsFree($email) {
        $sql = '
            SELECT 
                `email`
            FROM 
                `users` 
            WHERE 
                `email` = :email             
            ;';
        $query = $this->db->pdo->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetch(PDO::FETCH_COLUMN);
        
        if (empty($results)) {
            return true;
        }
        return false;
    }

    /**
     * Create a user account.
     *
     * @param string $email
     * @param string $password
     *
     * @return bool
     */
    public function createUserAccount($email, $password) {
        $hash = $this->hashPassword($password);
        if (true === password_verify($password, $hash)) {
            $sql = '
                INSERT INTO 
                    `users` 
                    (`email`, `password`) 
                VALUES 
                    (:email, :password)
                ;';
            $query = $this->db->pdo->prepare($sql);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':password', $hash, PDO::PARAM_STR);
            $results = $query->execute();
            
            return $results;
        }
        return false;
    }

    /**
     * Hash password.
     *
     * @param string $password
     *
     * @return string
     */
    private function hashPassword($password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        if (false !== $hash) {
            return $hash;
        }
        return '';
    }

    /**
     * Save search error report.
     *
     * @param string $query
     *
     * @return bool
     */
    public function saveSearchErrorReport($query) {
        $sql = '
            INSERT INTO 
                `search_error_reports` 
                (`query_content`) 
            VALUES 
                (:query)
            ;';
        $query = $this->db->pdo->prepare($sql);
        $query->bindParam(':query', $query, PDO::PARAM_STR);
        $results = $query->execute();
            
        return $results;
    }
}
