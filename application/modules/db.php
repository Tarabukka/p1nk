<?php
	if(!defined('ON_PAGE')) die('Direct script access is not allowed.');
	
	class PinkDB {
		protected static $connection;
		public static $db;
		
		public function __construct() {
			// Make sure we only have one connection/instance of PinkDB
			if(self::$db) {
				return self::$db;
			}
			
			// Try to connect
			try {
				$connection = new PDO('mysql:host=' . Config::DB_HOSTNAME . ';dbname=' . Config::DB_DATABASE, Config::DB_USERNAME, Config::DB_PASSWORD);
				
				// Set fetch mode to fetch objects by default
				$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
			}
			catch(PDOException $ex) {
				throw new PinkDBException($ex->getMessage());
			}
		}
		
		public function query($query, $parameters) {
			// Prepare the query
			$st = self::$connection->prepare($query);
			
			// Execute the query
			$st->execute($parameters);
			
			// Check for errors
			if($st->errorCode() != '00000') {
				$errorInfo = $st->errorInfo();
				
				$ex = new PinkDBQueryException($errorInfo[2], $errorInfo[1]);
				
				$ex->query = $query;
				$ex->parameters = $parameters;
				$ex->errorInfo = $errorInfo;
				
				throw $ex;
			}
			
			return $st;
		}
		
		public function insertID() {
			return self::$connection->lastInsertId();
		}
		
		public function queryAll($query, $parameters) {
			// Run query
			$st = $this->query($query, $parameters);
			
			// Fetch all results
			return $st->fetchAll();
		}
		
		public function queryRow($query, $parameters) {
			// Run query
			$st = $this->query($query, $parameters);
			
			// Fetch result
			return $st->fetch();
		}
		
		public function queryVariable($query, $parameters, $variable) {
			// Run query
			$st = $this->query($query, $parameters);
			
			// Fetch result
			$row = $st->fetch();
			
			// Return variable
			return $row[$variable];
		}
		
		public function simpleSelect($table, $fields, $conditions, $order = '', $limit = 0, $limit_start = 0, $use_db_visible = true) {
			// Craft query
			$query = 'SELECT ';
			
			// Escape and add field names
			foreach($fields as &$field) {
				$field = '`' . strtr(self::$connection->quote($field), array('`' => '')) . '`';
			}
			
			$query .= join(', ', $fields) . ' FROM ';
			
			$query .= $table . ' ';
			
			if($use_db_visible) {
				$conditions[Config::DB_SOFT_FIELD] = 1;
			}
			
			if(count($conditions) != 0) {
				$query .= 'WHERE ';
				
				$conditions_formatted = array();
				
				foreach($conditions as $key => $value) {
					$conditions_formatted[] = '`' . $key . '` = :' . $key;
				}
				
				$query .= join(' AND ', $conditions_formatted);
			}
			
			// Warning: $order is not escaped.
			if(strlen($order) != 0) {
				$query .= ' ORDER BY ' . $order;
			}
			
			if($limit > 0) {
				$query .= ' LIMIT ' . $limit_start . ',' . $limit;
			}
			
			return $this->query($query, $conditions);
		}
		
		public function simpleSelectAll($table, $fields, $conditions, $order = '', $limit = 0, $limit_start = 0, $use_db_visible = true) {
			// Get result
			$st = $this->simpleSelect($table, $fields, $conditions, $order, $limit, $limit_start, $use_db_visible);
			
			// Return all rows
			return $st->fetchAll();
		}
		
		public function simpleSelectRow($table, $fields, $conditions, $order = '', $limit = 1, $limit_start = 0, $use_db_visible = true) {
			// Get result
			$st = $this->simpleSelect($table, $fields, $conditions, $order, $limit, $limit_start, $use_db_visible);
			
			// Return first row
			return $st->fetch();
		}
		
		public function simpleSelectVariable($table, $field, $conditions, $order = '', $limit = 1, $limit_start = 0, $use_db_visible = true) {
			// Get result
			$st = $this->simpleSelect($table, array($field), $conditions, $order, 1, 0, $use_db_visible);
			
			// Get first row
			$row = $st->fetch();
			
			// Return variable
			return $row[$field];
		}
		
		public function insert($table, $parameters) {
			// Craft query
			$query = 'INSERT INTO `' . $table_name . '` (';
			
			$columns = array_keys($parameters);
			
			foreach($columns as $i => $name) {
				$columns[$i] = '`' . $name . '`';
			}
			
			$query .= join(', ', $columns) . ') VALUES(';
			
			$columns_2 = array_keys($parameters);
			
			foreach($columns_2 as $i => $name) {
				$columns_2[$i] = ':' . $name;
			}
			
			$query .= join(', ', $columns_2) . ')';
			
			// Return affected rows
			return $this->query($query, $parameters)->rowCount();
		}
		
		public function update($table, $columns, $id_column, $id) {
			// Craft query
			$query .= 'UPDATE `'.  $table_name . '` SET ';
			
			$columns = array_keys($parameters);
			
			foreach($columns as $i => $name) {
				$columns[$i] = '`' . $name . '` = :' . $name;
			}
			
			$query .= join(', ', $columns) . ' WHERE `' . $id_column . '` = :id';
			
			$parameters['id'] = $id;
			
			// Return affected rows
			return $this->query($query, $parameters)->rowCount();
		}
	}
	
	class PinkDBException extends Exception { }
	
	class PinkDBQueryException extends Exception {
		public $query;
		public $parameters;
		public $errorInfo;
	}