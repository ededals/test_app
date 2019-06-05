<?php
    include "config.php";
    /**
     * Class is responsible for database access. Instances of it are used whenewer some class
     * needs to access database
     */
    class Connection{

        private $pdo = null;

        function __construct(){
            $this->pdo = new PDO('mysql:host='.HOST.';port='.PORT.';dbname='.DBNAME.';charset=utf8', USER, PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        function __destruct(){
            if ($this->pdo!==null) {
                $this->pdo = null;
            }
        }
        /**
         * Function responsible for sanitization of user inpot and query execution
         * @param string $sql
         * @param array $args
         * @return array|boolean 
         */
        public function get($sql, $args=null){
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($args);
            $result = $stmt->fetchAll();
            return $result;
        }
    }
?>