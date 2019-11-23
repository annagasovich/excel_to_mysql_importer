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

	function __construct($table_name = 'url_text', $file_name = '/meta.xlsx') {
		parent::__construct();
		$this->columns = $this->getSqlColumns($table_name);
		$this->table_name = $table_name;
		$this->file_name = $file_name;
		$path = __DIR__ . '/../' . $file_name;
		$this->file = $this->getExcelFileContent($path);
		foreach ($this->file[1] as $key => $value) {
			if(!$value){
				unset($this->file[1][$key]);
			}
		}
		foreach ($this->columns as $key => $value) {
			//var_dump($value);
			//echo '<br>';
			//var_dump($this->file[1]);
			//echo '<br>';
			//var_dump(in_array($value, $this->file[1]));			
			//echo '<br>';
			if(!in_array ($value, $this->file[1])){
				unset($this->columns[$key]);
			}
		}
		//var_dump($this->columns);
		if (!$this->validate()){
			echo('Столбцы таблицы ' . $this->table_name . ' не соответствуют столбцам файла ' . $this->file_name);
			die();
		}
		unset($this->file[1]);
	}

	public function validate(){
		//var_dump(array_intersect(array_values($this->file[1]), (array_values($this->columns))));
		//var_dump(array_values($this->file[1]));
		//Таблица содержит все столбцы файла, но не обязательно в файле есть все столбцы таблицы
		return count(array_intersect(array_values($this->file[1]), (array_values($this->columns)))) == count(array_values($this->file[1]));
	}

	public function odku($key = 'url'){
		//ON DUBLICATE KEY UPDATE
		foreach ($this->file as $item) {
			$item = $this->cleanItem($item);
			//var_dump($item);
			//var_dump($this->columns);
			if(!$item)
				continue;
			$stuff = array_combine($this->columns, array_values($item));
			//var_dump($stuff);
			$stuff['url'] = $this->cleanUrl($stuff['url']);
			$stuff = $this->noChanges($stuff);
			//echo '<br>';
			$id = $this->checkExistanceByKey($key, $stuff[$key]);
			if($id){
				echo '<b>Обновление данных по ключу <span style="color: #3176b8;font-size: 24px;font-weight: normal;">'.$stuff[$key].'</span>, перечень обновляемых данных:</b><br><pre>';
				var_dump($stuff);
				echo '</pre><br>';
				$this->update($stuff, $id);
			}
			else{
				$this->insert($stuff);
			}
		}
	}

	public function cleanItem($item){
		foreach ($item as $key => $value) {
			if(!$value){
				unset($item[$key]);
			}
		}
		return $item;
	}

	public function cleanUrl($url, $host = 'https://rolf-center.ru'){
		$url = str_replace($host, '', $url);
		$url = substr($url, 0, -1);
		return $url;
	}

	public function noChanges($item, $key_phrase = 'оставляем без изменений'){
		foreach ($item as $key => $value) {
			if($value == $key_phrase){
				unset($item[$key]);
			}
		}
		return $item;
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
		//echo $query;
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