<?php
/**
* 
*/
class PGDatalyaer
{
	private $pglink;
	
	/*The Hostname of the database server to connect to.*/
	private $hostName;
	/*The username of the account on the database server.*/
	private $userName;
	/*The password for the account on the database server.*/
	private $password;
	/*The database name on the server that you will be performing operations on.*/
	private $dbName;
	private $port;
	/*The Hostname of the database server to connect to.*/
	private $eMsg;
	/*The username of the account on the database server.*/
	private $eNum;
	/*The password for the account on the database server.*/
	private $ceMsg;
	/*The database name on the server that you will be performing operations on.*/
	private $ceNum;
	/** The number of rows that the query affected.*/
	private $affected_rows;
	/** The number of fields that the query returned per row.*/
	private $field_count;
	
	/**
	 * The Last SqlCmd that was executed on the server.
	 */
	private $lastSqlCmd;
	/**
	 * The Last Result that was retrieved from the server.
	 */
	private $lastResult;
	/**
	 * The Last Result that was retrieved from the server.
	 */
	private $lastInsertID;
	
	/**
	 *  Gets the last sql command that was executed on the server.
	 * @return The last sql command string that was executed.
	 */
	public function lastSql(){
		return $this->lastSqlCmd;
	}
	/**
	 *  Gets the last error that occured on the link.
	 * @return The error message about the last executed statement
	 */
	public function errorMsg(){
		return $this->eMsg;
	}
	/** 
	 * Gets the last error code that occured on the link.
	 * @return an integer that denotes the last code of the last error that occured on the link
	 */
	public function errorNum(){
		return $this->eNum;
	}
	/** 
	 * Gets the last error message.
	 * @return A string that describes the connection error that occured on the link.
	 */
	public function connErrorMsg(){
		return $this->ceMsg;
	}
	/**
	 *  Gets the error code about any connection errors that occured.
	 * @return An integer that denotes the connection error that occured.
	 */
	public function connErrorNum(){
		return $this->ceNum;
	}
	/** 
	 * Gets the error code about any connection errors that occured.
	 * @return An integer that denotes the number of rows affected by the last query
	 */
	public function affectedRows(){
		return $this->affected_rows;
	}
	public function lastID(){
		return $this->lastInsertID;
	}
	/**
	 *  Gets the error code about any connection errors that occured.
	 * @return An integer that denotes the number of fields contained in the result rows.
	 */
	public function fieldCount(){
		return $this->field_count;
	}
	/**
	 * 
	 * @return mysqli
	 */
	function connection(){
		return $this->pglink;
	}
	
	/**
	 * 
	 */
	function __construct($host="localhost", $dbname="", $usr="", $pwd=""){
		$this->hostName = $host;
		$this->dbName = $dbname;
		$this->userName = $usr;
		$this->password = $pwd;
	}
	/**
	 * 
	 * @return NULL|mysqli
	 */
	function connect(){
			
			$this->pglink = pg_connect("host=$this->hostName dbname=$this->dbName user=postgres");
			
			$this->ceMsg = pg_last_error($this->pglink);
			
			$method = __METHOD__;
			$emsg = $this->ceMsg;
			
			if (!$this->pglink){
				trigger_error(__METHOD__.": Connection Error($emsg)", E_USER_ERROR);
				return null;
			}
			
			return $this->pglink;
	}
	/**
	 * 
	 */
	function close(){
		pg_close($this->pglink);
		unset($this->pglink);
	}
	
	/**
	 * 
	 * @param unknown_type $query
	 * @return Ambigous <unknown, mixed>
	 */
	function Query($query){
		$this->connect();
		$this->lastSqlCmd = $query;
			
		$pgconn = $this->connect();
			
		if(!is_null($pgconn)){
			$result = pg_query($pgconn, $query);
			$this->eMsg = pg_last_error($pgconn);
			if($result)
				$this->affected_rows = pg_affected_rows($result);
			
			$this->lastResult = $result;
		}
		
		$this->close();
		
		return $this->lastResult;
	}
	/**
	 * 
	 * @param unknown_type $sql
	 * @param unknown_type $params
	 * @return Ambigous <unknown, mixed>
	 */
	function Exec($sql, $params=array()){
		if(is_array($params)){
			$i = 0;
			foreach($params as $k => $v){
				if(is_numeric($v)){
					$data = "'$v'";
				}else if(is_string($v)){
					$data = "'".str_replace("'", "''", $v)."'";
				}else{
					$data = "'$v'";
				}
				
				$sql = str_replace($k, $data, $sql);
			}
		}
		
		//return $sql;
		return $this->Query($sql);
	}
}

?>
