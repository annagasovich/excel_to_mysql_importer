<?php 

namespace App;

use App\Model;
use PHPExcel;
use PHPExcel_IOFactory;

class Importer extends Model{

	private $table_name;
	private $file_name;
	private $file;

	function __construct($table_name = 'goods', $file_name = '/test.xlsx') {
		parent::__construct();
		$columns = $this->getSqlColumns($table_name);
		$this->table_name = $table_name;
		$this->file_name = $file_name;
		$path = __DIR__ . '/../' . $file_name;
		$this->file = $this->getExcelFileContent($path);
	}

	public function validate(){
		return array_values($this->getSqlColumns($this->table_name)) == array_values($this->file[1]);
	}

	public function import(){
		$valid = $this->validate();
		if (!$valid){
			echo('Столбцы таблицы ' . $this->table_name . ' не соответствуют столбцам файла ' . $this->file_name);
			die();
		}
		$columns = $this->getSqlColumns($this->table_name);
		$prepare = 'INSERT INTO `' . $this->table_name . '`(' .implode(', ', $columns). ') VALUES ({data})';
		$i = 2;
		while ($this->file[$i]) {
			$data = '"'.implode('", "', $this->file[$i]).'"';
			$query = str_replace('{data}', $data, $prepare);
			$this->query($query);
			$i++;
		}
		return true;
	}
	
	public function getSqlColumns($table_name){
		$columns = $this->query('SHOW COLUMNS FROM '.$table_name);
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