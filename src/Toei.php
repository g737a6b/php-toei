<?php
namespace Toei;
use PDO;

/**
 * Toei
 *
 * @package Toei
 * @author Hiroyuki Suzuki
 * @copyright Copyright (c) 2017 Hiroyuki Suzuki mofg.net
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version 1.1.0
 */
class Toei{
	/**
	 * @var PDO
	 */
	private $PDO = null;

	/**
	 * @var object
	 */
	private $config = null;

	/**
	 * @var integer
	 */
	private $id = null;

	/**
	 * @param PDO $PDO
	 * @param array|object $config
	 */
	public function __construct($PDO, $config){
		$this->PDO = $PDO;
		$this->config = ( is_array($config) ) ? json_decode(json_encode($config)) : $config;
	}

	/**
	 * @param integer $id
	 */
	public function setId(int $id){
		$this->id = $id;
	}

	/**
	 * @param boolean $execQuery (optional)
	 * @throws PDOException
	 * @return string|array
	 */
	public function project(bool $execQuery = null){
		$query = $this->constructQuery();
		if( $execQuery !== true ) return $query;
		$result = [];
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$statement = $this->PDO->prepare($query);
		$statement->execute();
		while( $row = $statement->fetch(PDO::FETCH_ASSOC) ) $result[] = $row;
		return $result;
	}

	/**
	 * @return integer
	 */
	protected function getOptionsNumber(){
		$count = 0;
		foreach($this->config as $i){
			if( empty($i->options) ) continue;
			$count = max($count, count($i->options));
		}
		return $count;
	}

	/**
	 * @return string
	 */
	protected function constructQuery(){
		if( !isset($this->id) ) return "";
		$subQueries = [];
		$optionsNumber = $this->getOptionsNumber();
		foreach($this->config as $name => $data){
			if( !is_string($name) || $name === "" || empty($data->table) ) continue;
			$idColumn = ( !empty($data->identifyBy) ) ? $data->identifyBy : "id";
			$sortColumn = ( !empty($data->sortBy) ) ? $data->sortBy : "created";

			$fields = [
				"'{$name}' as action",
				"{$idColumn} as id",
				"{$sortColumn} as time"
			];
			for($i = 1; $i <= $optionsNumber; $i++){
				$optionColumn = ( !isset($data->options[$i - 1]) ) ? "null" : $data->options[$i - 1];
				$fields[] = "{$optionColumn} as option{$i}";
			}
			$fieldsString = implode(", ", $fields);

			$conditionString = "{$idColumn} = {$this->id} AND {$sortColumn} IS NOT NULL";
			if( !empty($data->condition) ) $conditionString .= " AND ({$data->condition})";

			$subQueries[] = "SELECT {$fieldsString} FROM {$data->table} WHERE {$conditionString}";
		}
		if( empty($subQueries) ) return "";
		$subQueriesString = " ( ".implode(" ) union all ( ", $subQueries)." ) ";
		return "SELECT a.* FROM ({$subQueriesString}) AS a ORDER BY a.time ASC";
	}
}
