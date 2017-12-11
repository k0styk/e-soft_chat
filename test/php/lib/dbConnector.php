<?php
include "ChatIF.php";
class dbConnector implements ChatIF {
	private $errorString;
	private $connectionString;
	private $connection;
	
	function __construct() {
		$this->errorString = "Не удается подключитьяс к серверу";
		$this->connectionString = "host=localhost port=5432 dbname=chatDB user=postgres password=1";
		$this->connection = null;
	}
	
	function __destruct() {
		$this->disconnect();
	}
	
	function connect() {
		$this->connection = pg_connect($this->connectionString) or die($this->errorString);
		return $this->checkConnection();
	}
	
	function disconnect() {
		if ($this->checkConnection()) {
				pg_close($this->connection);
		}
	}
	
	function checkConnection() {
		$stat = pg_connection_status($this->connection);
		if ($stat === PGSQL_CONNECTION_OK) {
			return true;
		} else {
			return false;
		}
	}
	
	///
	/// MAIN BLOCK
	/// 
	
	/**
     * Put message to the room
     * @param string $uid user uuid
     * @param string $roomId room uuid
     * @param string $msg
     * @return boolean
     */
    function putMessage($uid, $roomId, $msg) {
		while(!$this->checkConnection()) {
			$this->connect();
		}
		$retVal = false;
		$result = pg_prepare($this->connection, "put_message", 'insert into public.messages (uid,room_id,message) values($1,$2,$3)');
		if($result) {
			$result = pg_execute($this->connection, "put_message", array($uid,$roomId,$msg));
			if($result) { $retVal = true; }
		}
		$this->disconnect();
		return $retVal;
	}
    /**
     * Get messages by room id 
     * @param string $roomId room uuid 
     * @return array
     */
    function getMessages($roomId) {
		while(!$this->checkConnection()) {
			$this->connect();
		}
		$res = null;
		$result = pg_prepare($this->connection, "get_messages", 'select uid,message from public.messages where room_id=$1');
		if($result) {
			$result = pg_execute($this->connection, "get_messages", array($roomId));
			if($result) { 
				$res = pg_fetch_all($result);
			}
		}
		$this->disconnect();
		$messages = [];
		for ($i = 0; $i < count($res); $i++) {
			$messages[$i][0] = $res[$i]['uid'];
			$messages[$i][1] = $res[$i]['message'];
		}
		return $messages;
	}
    /**
     * Create new room 
     * @return string room uuid
     */
    function createRoom() {
		while(!$this->checkConnection()) {
			$this->connect();
		}
		$name = "room";
		$result = pg_query($this->connection, 'select max("ID") from public.rooms');
		if($result) {
			$id = pg_fetch_all($result);
		}
		// создание комнат на основе id
		if(isset($id[0]["max"])) { $name .= intval($id[0]["max"]) + 1; } else { $name .="1"; }
		$result = pg_prepare($this->connection, "create_room", 'insert into public.rooms ("name") values($1)');
		if($result) {
			$result = pg_execute($this->connection, "create_room", array($name));
			if($result) {
				$this->disconnect();
				return $name;
			}
		}
		$this->disconnect();
		return NULL;	
	}
    /**
     * Delete room
     * @param string $roomId room uuid 
     * @return bool
     */
    function deleteRoom($roomId) {
		while(!$this->checkConnection()) {
			$this->connect();
		}
		$retVal = false;
		$result = pg_prepare($this->connection, "delete_room", 'DELETE FROM public.rooms WHERE "ID"=$1');
		if($result) {
			$result = pg_execute($this->connection, "delete_room", array($roomId));
			if($result) $retVal = true;
		}
		$this->disconnect();
		return $retVal;
	}
    /**
     * Put room member
     * @param string $roomId room uuid
     * @param string $uid user uuid 
     * @return bool
     */
    function putRoomMember($roomId, $uid) {
		$file = "../js/rooms.json";
		$rooms = json_decode(file_get_contents($file),true);
		$rooms[] = array("roomId"=>$roomId,"uid"=>$uid);
		$json = json_encode($rooms);
		if(file_put_contents($file, $json)) return true;
		return false;
	}
    /**
     * Delete room member 
     * @param string $roomId room uuid
     * @param string $uid user uuid
     */
    function deleteRoomMember($roomId, $uid) {
		$file = "../js/rooms.json";
		$rooms = json_decode(file_get_contents($file), true);
		$flag = false;
		foreach ($rooms as $key => $value) {
			if (strcmp($value['roomId'], $roomId) == 0 and strcmp($value['uid'], $uid) == 0) {
				unset($rooms[$key]);
				$flag = true;
			}
		}
		if($flag) {
			$json = json_encode($rooms);
			file_put_contents($file, $json);
			return true;
		}
		return false;
	}
    /**
     * Blocking member on the room
     * @param string $roomId room uuid
     * @param string $uid user uuid
     * @param string $expiration time stamp
     */
    function blockRoomMember($roomId, $uid, $expiration) {
		while(!$this->checkConnection()) {
			$this->connect();
		}
		$expiration = date('Y-m-d H:i:s', strtotime($expiration));
		$retVal = false;
		$result = pg_prepare($this->connection, "block_user", 'insert into public.room_blocked (room_id,uid,expiration) values($1,$2,$3)');
		if($result) {
			$result = pg_execute($this->connection, "block_user", array($roomId,$uid,$expiration));
			if($result) $retVal = true;
		}
		$this->disconnect();
		return $retVal;
	}
	
	///
	/// END MAIN BLOCK
	///
	
	///
	/// INFORMATION BLOCK
	///
	
	function createUser($uid) {
		$file = "../js/users.json";
		$users = json_decode(file_get_contents($file), true);
		$users[] = array("uid"=>$uid);
		return file_put_contents($file, json_encode($users));
		return "OK";
	}
	
	function deleteUser($uid) {
		$file = "../js/users.json";
		$users = json_decode(file_get_contents($file),true);
		$flag = false;
		foreach ($users as $key => $value) {
			if (strcmp($value['uid'], $uid) == 0) {
				unset($users[$key]);
				$flag = true;
			}
		}
		if($flag) {
			file_put_contents($file, json_encode($users));
			return true;
		}
		return false;
	}
	
	function getUsers() {
		$file = "../js/users.json";
		$json = file_get_contents($file);
		return $json;
	}
	
	function getRooms() {
		while(!$this->checkConnection()) {
			$this->connect();
		}
		$rooms = null;
		$result = pg_query($this->connection, 'select * from public.rooms');
		if($result) {
			$rooms = pg_fetch_all($result);
		}
		$this->disconnect();
		$res = NULL;
		if($rooms) {
			for ($i = 0; $i < count($rooms); $i++) {
				$res[$i] = array($rooms[$i]['ID'],$rooms[$i]['name']);
			}
		}
		return $res;
	}
	
	function getBlockedUsers($uid) {
		while(!$this->checkConnection()) {
			$this->connect();
		}
		$rooms = null;
		$result = pg_prepare($this->connection, "get_blocked_users", 'select room_id from public.room_blocked where uid=$1');
		if($result) {
			$result = pg_execute($this->connection, "get_blocked_users", array($uid));
			if($result) { 
				$rooms = pg_fetch_all($result); 
			}
		}
		$this->disconnect();
		$res = NULL;
		if($rooms) {
			for ($i = 0; $i < count($rooms); $i++) {
				$res[] = $rooms[$i]['room_id'];
			}
		}
		return $res;
	}
	
	function getUsersRoom($roomId) {
		$file = "../js/rooms.json";
		$rooms = json_decode(file_get_contents($file),true);
		$res = NULL;
		foreach ($rooms as $key => $value) {
			if (strcmp($value['roomId'], $roomId) == 0) {
				$res[] = $value['uid'];
			}
		}
		$json = json_encode($res);
		return $json;
	}
	///
	/// END INFORMATION BLOCK
	///
}
?>