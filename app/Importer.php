<?php 

namespace App;

use App\Model;
use PHPExcel;
use PHPExcel_IOFactory;

class Importer extends Model{

	private $table_name;
	private $file_name;
	private $file;
	private $columns;

	function __construct($table_name = 'goods', $file_name = '/test.xlsx') {
		parent::__construct();
		$this->columns = $this->getSqlColumns($table_name);
		$this->table_name = $table_name;
		$this->file_name = $file_name;
		$path = __DIR__ . '/../' . $file_name;
		$this->file = $this->getExcelFileContent($path);
		if (!$this->validate()){
			echo('Столбцы таблицы ' . $this->table_name . ' не соответствуют столбцам файла ' . $this->file_name);
			die();
		}
		unset($this->file[1]);
	}

	public function validate(){
		return array_values($this->columns) == array_values($this->file[1]);
	}

	public function odku($key = 'name'){
		//ON DUBLICATE KEY UPDATE
		foreach ($this->file as $item) {
			//var_dump($item);
			//var_dump($this->columns);
			$stuff = array_combine($this->columns, array_values($item));
			//var_dump($stuff);
			echo '<br>';
			$id = $this->checkExistanceByKey($key, $stuff[$key]);
			if($id)
				$this->update($stuff, $id);
			else
				$this->insert($stuff);
		}
	}

	public function checkExistanceByKey($key, $value){
		$item = $this->get('SELECT * FROM ' . $this->table_name . ' WHERE ' . $key . ' = "' . $value . '" ORDER BY id DESC LIMIT 1');
		//echo 'SELECT * FROM ' . $this->table_name . ' WHERE ' . $key . ' = "' . $value . '" ORDER BY id DESC LIMIT 1 <br>';
		if (!$item)
			return false;
		else
			return $item[0]['id'];
	}

	public function import(){
		
		$i = 2;
		while ($this->file[$i]) {
			$data = '"'.implode('", "', $this->file[$i]).'"';
			$query = str_replace('{data}', $data, $prepare);
			$this->query($query);
			$i++;
		}
		return true;
	}

	public function insert($arr){
		$prepare = 'INSERT INTO `' . $this->table_name . '`(' .implode(', ', $this->columns). ') VALUES ({data})';
		$data = '"'.implode('", "', $arr).'"';
		$query = str_replace('{data}', $data, $prepare);
		$this->query($query);
	}

	public function update($arr, $id){
		$values = array();
		foreach ($arr as $key => $value) {
			$values[]  = '`' . $key . '` = "' . $value . '"';
		}
		$query = 'UPDATE `' . $this->table_name . '` SET ' . implode(', ', $values) . ' WHERE id = ' . $id;
		echo $query;
		$this->query($query);
	}

	public function getSqlColumns($table_name){
		$columns = $this->get('SHOW COLUMNS FROM '.$table_name);
		if(!$columns){
			echo('Не обнаружено таблицы '.$table_name);
			die();
		}
		$result = [];
		foreach ($columns as $value) {
			$result[] = $value['Field'];
		}
		return array_diff($result, array('id'));
	}

	public function getExcelFileContent($path = '/test.xlsx'){
		/** Include path **/
		$oExcel = PHPExcel_IOFactory::load($path);
		$sheetData = $oExcel->getActiveSheet()->toArray(null,true,true,true);
		return $sheetData;
	}
}

?>