<?php 

namespace App;

class Model{

	public $pdo;

	function __construct() {

	global $config;

	$host = $config['db']['host'];
    $db   = $config['db']['dbname'];
    $user = $config['db']['user'];
    $pass = $config['db']['pass'];
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    
    $this->pdo = new \PDO($dsn, $user, $pass, $opt);

   }

   function query($request){
        $sql = $this->pdo->prepare($request);
        $sql->execute();
        return $sql;
   }

   function get($request){
        $sql = $this->query($request);
        while($row = $sql->fetch(\PDO::FETCH_ASSOC)){
            $result[] = $row;
        }
        return $result;
   }
}

?>