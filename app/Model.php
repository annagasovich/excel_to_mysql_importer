<?php 

namespace App;

class Model{

	public $pdo;

	function __construct() {

	global $sqlconnetor;
  $config = $sqlconnetor[PROJECT];
    $host = $config['server'];
    $db   = $config['database'];
    $user = $config['user'];
    $pass = $config['word'];
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