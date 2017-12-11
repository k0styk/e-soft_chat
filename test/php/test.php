<?php
	
	include "lib\dbConnector.php";
	$db = new dbConnector();
	
	if(isset($_POST['action'])) {
		
		$db = new dbConnector();
		
		// запрашиваем список пользователей
		if($_POST['action'] == 'get_users') {
			$res = $db->getUsers();
			if($res=="") {
				header("Status: 204 No Content");
				echo "";
				exit;
			}
			echo $res;
		}
		
		// блокируем пользователя, реализовано так, что перезайти в комнату не получится
		if($_POST['action'] == 'block_user') {
			// $res = $db->getUsers();
			// if($res=="") {
				// header("Status: 204 No Content");
				// echo "";
				// exit;
			// }
			// echo $res;
			$res = $db->blockRoomMember($_POST['roomId'],$_POST['uid'],$_POST['edate']);
			if($res) {
				echo "OK";
				exit;
			}
			header("Status: 400 Bad Request ");
			exit;
		}
		
		// запрашиваем список комнат в которых заблокирован пользователь
		if($_POST['action'] == 'get_blocked_user') {
			$res = $db->getBlockedUsers($_POST['uid']);
			if($res == NULL) {
				header("Status: 204 No Content");
				echo "null";
				exit;
			}
			echo json_encode($res);
		}
		
		if($_POST['action'] == 'create_user') {
			$res = $db->createUser($_POST['uid']);
			if($res) {
				echo "OK";
				exit;
			}
			header("Status: 400 Bad Request ");
			echo "<script>alert('Status: 400 Bad Request');</script>";
			exit;
		}
		
		if($_POST['action'] == 'delete_user') {
			$res = $db->deleteUser($_POST['uid']);
			if($res) {
				echo "OK";
				exit;
			}
			header("Status: 400 Bad Request ");
			echo "<script>alert('Status: 400 Bad Request');</script>";
			exit;
		}
		
		// запрашиваем список комнат
		if($_POST['action'] == 'get_rooms') {
			$res = $db->getRooms();
			if($res == NULL) {
				// так и не получилось отправить нормально ответ, может IIS моросит
				$res = array("ER","Комнат нет, попробуйте создать");
				header("Status: 204 No Content");
				header('Content-type: application/json');
				echo json_encode("meow");
				exit;
			}
			echo json_encode($res);
		}
		
		// создание комнаты
		if($_POST['action'] == 'create_room') {
			$res = $db->createRoom();
			if($res == NULL) {
				header("Status: 400 Bad Request ");
				echo "<script>alert('Status: 400 Bad Request');</script>";
				exit;
			}
			echo "OK";
		}
		
		// удаление комнаты
		if($_POST['action'] == 'delete_room') {
			$res = false;
			if(isset($_POST['uuid'])) {
				$res = $db->deleteRoom($_POST['uuid']);
			}
			if($res) {
				echo "OK";
				exit;
			}
			header("Status: 400 Bad Request ");
			echo "<script>alert('Status: 400 Bad Request');</script>";
			exit;
		}
		
		// заходим в комнату пользователем
		if($_POST['action'] == 'put_room_member') {
			if($db->putRoomMember($_POST['roomId'],$_POST['uid'])) {
				echo "OK";
				exit;
			} 
			header("Status: 400 Bad Request ");
			echo "<script>alert('Status: 400 Bad Request');</script>";
			exit;
		}
		
		if($_POST['action'] == 'delete_room_member') {
			if($db->deleteRoomMember($_POST['roomId'],$_POST['uid'])) {
				echo "OK";
				exit;
			} 
			header("Status: 400 Bad Request ");
			echo "<script>alert('Status: 400 Bad Request');</script>";
			exit;
		}
		
		// TODO
		if($_POST['action'] == 'get_users_room') {
			$res = $db->getUsersRoom($_POST['roomId']);
			if($res) {
				echo $res;
				exit;
			} 
			header("Status: 400 Bad Request ");
			echo "<script>alert('Status: 400 Bad Request');</script>";
			exit;
		}
		
		if($_POST['action'] == 'put_message') {
			if(isset($_POST['uid'])) {
				if(isset($_POST['roomId'])) {
					if(isset($_POST['message'])) {
						if($db->putMessage($_POST['uid'],$_POST['roomId'],$_POST['message'])) {
							echo "OK";
							exit;
						}
					}
				}
			}
			header("Status: 400 Bad Request ");
			echo "<script>alert('Status: 400 Bad Request');</script>";
			exit;
		}
			
		if($_POST['action'] == 'get_messages') {
			if(isset($_POST['roomId'])) {
						$res = $db->getMessages($_POST['roomId']);
						echo json_encode($res);
						exit;
			}
			header("Status: 400 Bad Request ");
			echo "<script>alert('Status: 400 Bad Request');</script>";
			exit;			
		}
		
	}
			
?>