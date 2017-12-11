// обработчик ошибки AJAX запроса
var Handler_ER = function(Request) { alert("status: "+Request.status+" - "+Request.statusText); }

$(function() {
	className = localStorage.getItem("list_class");
	if(className) {
		$("#list").attr('class', className);
	} else {
		$("#list").attr("class","users");
		localStorage.setItem("list_class","users");
	}
	checkLocalStorage();
	updateInfo();	
});

function updateInfo() {
	if($( "#list" ).attr('class')=='users') {
		$( "#list.rooms" ).off("change");
		$( "#list.users").find('option').remove();
		getUsers();
	}
	if($( "#list" ).attr('class')=='rooms') {
		$( "#list.rooms").find('option').remove();
		getRooms();
		getBlockedUser()
		$( "#list.rooms" ).on( "change", function() {
			if(~this.selectedOptions[0].text.indexOf("banned")) {
				 $('#btnSelect').attr('disabled','disabled');
			}
			else {
				 $('#btnSelect').removeAttr('disabled');
			}
		});
	}
	if($( "#list" ).attr('class')=='room-users') {
		// невозможно выделить при постоянном обновлении
		getUsersRoom();
		getMessages();
		$( "#list.room-users" ).on( "change", function() {
			if(this.value==localStorage.getItem("uid")) {
				 $('#btnSelect').attr('disabled','disabled');
			}
			else {
				 $('#btnSelect').removeAttr('disabled');
			}
		});
		timerId = setInterval(getMessages, 3000);
		localStorage.setItem("timerId",timerId);
	}
}

// читаем список юзеров из файла [.\js\users.js]
function getUsers() {
	var Handler_OK = function(Request)
		{
			res = JSON.parse(Request.responseText);
			for(i=0;i < res.length; i++) {
				$('#list.users').append('<option value="' + res[i]["uid"] + '">' + res[i]["uid"] + '</option>');
			}
			
		}
	SendRequest("POST","php/test.php","action=get_users",Handler_OK,Handler_ER);
}

// читаем список юзеров в комнате из файла [.\js\rooms.js]
function getUsersRoom() {
	Handler_OK = function(Request) 
	{ 
		res = JSON.parse(Request.responseText);
		$( "#list.room-users").find('option').remove();
		for(i=0;i < res.length; i++) {
			$('#list.room-users').append('<option value="' + res[i] + '">' + res[i]+ '</option>');
		} 
	}
	arguments = "action=get_users_room&roomId="+localStorage.getItem("roomId");
	SendRequest("POST","php/test.php",arguments,Handler_OK,Handler_ER);
}

// записываем пользователя в файл комнаты [.\js\rooms.js]
function joinRoom() {
	Handler_OK = function(Request) { }
	arguments = "action=put_room_member&roomId="+localStorage.getItem("roomId")+"&uid="+localStorage.getItem("uid");
	SendRequest("POST","php/test.php",arguments,Handler_OK,Handler_ER);
}

// удаляем пользователя из файла комнаты [.\js\rooms.js]
function disconnetRoom() {
	Handler_OK = function(Request) {  }
	arguments = "action=delete_room_member&roomId="+localStorage.getItem("roomId")+"&uid="+localStorage.getItem("uid");
	SendRequest("POST","php/test.php",arguments,Handler_OK,Handler_ER);
}

// создаем нового пользователя в файле [.\js\users.js]
function createUser() {
	Handler_OK = function(Request) { updateInfo(); }
	userName =prompt("Введите имя пользователя","");
	if(userName=="" || userName==null) { return; }
	SendRequest("POST","php/test.php","action=create_user&uid="+userName,Handler_OK,Handler_ER);
}

// удаляем пользователя из файла [.\js\users.js]
function deleteUser(uid) {
	Handler_OK = function(Request) { updateInfo(); }
	if(confirm("Вы точно хотите удалить пользователя?")) {
		SendRequest("POST","php/test.php","action=delete_user&uid="+uid,Handler_OK,Handler_ER);
	}
}

// создаем комнату в БД
function createRoom() {
	// после успешной операции просто обновляем инфу
	Handler_OK = function(Request) { updateInfo(); }
	SendRequest("POST","php/test.php","action=create_room",Handler_OK,Handler_ER);
}

// удаляем комнату из БД
function deleteRoom(uuid) {
	// после успешной операции просто обновляем инфу
	Handler_OK = function(Request) { updateInfo(); }
	SendRequest("POST","php/test.php","action=delete_room&uuid="+uuid,Handler_OK,Handler_ER);
}

// запрашиваем у БД список комнат
function getRooms() {
	Handler_OK = function(Request)
		{
			if(Request.responseText.length>0) {
				res = JSON.parse(Request.responseText);
				for(i=0;i < res.length; i++) {
					$('#list.rooms').append('<option value="' + res[i][0] + '">' + res[i][1] + '</option>');
				}
			} else {
				$('#list.rooms').append('<option value="ER">Список комнат пуст, попробуйте создать</option>');
			}
			getBlockedUser();
		}
	SendRequest("POST","php/test.php","action=get_rooms",Handler_OK,Handler_ER);	
}

// запрашиваем у БД заблокированные комнаты
function getBlockedUser() {
	Handler_OK = function(Request)
		{
			if(Request.responseText.length>0) {
				res = JSON.parse(Request.responseText);
				for(i=0;i < res.length; i++) {
					//$('#list.rooms option[value='+res[i]+']').text($('#list.rooms option[value='+res[i]+']').text()+"(banned)");
					$('#list.rooms option[value='+res[i]+']').text(function(idx, str) { this.text = str + " (banned)"; });
				}
			}
		}
	uid = localStorage.getItem("uid");
	SendRequest("POST","php/test.php","action=get_blocked_user&uid="+uid,Handler_OK,Handler_ER);
}

// запрашиваем у БД сообщения в комнате
function getMessages() {
	Handler_OK = function(Request)
		{
			console.log("UPDATE");
			if(Request.responseText.length>0) {
				res = JSON.parse(Request.responseText);
				$('#msg_box').empty();
				if(res[0][0] == null) return;
				for(i=0;i < res.length; i++) {
					$('#msg_box').append('<li><b>' + res[i][0] + "</b>: " + res[i][1] + '</li>');
				}
			}
		}
	SendRequest("POST","php/test.php","action=get_messages&roomId="+localStorage.getItem("roomId"),Handler_OK,Handler_ER);
}

// отправляем сообщение
function putMessage(messageText) {
	Handler_OK = function(Request) { }
	uid = localStorage.getItem("uid");
	roomId = localStorage.getItem("roomId");
	arguments = "action=put_message&uid="+uid+"&roomId="+roomId+"&message="+messageText;
	SendRequest("POST","php/test.php",arguments,Handler_OK,Handler_ER);
}

// блокируем пользователя в комнате
function blockUser(uid) {
// need date picker 
	Handler_OK = function(Request)
		{
			res = JSON.parse(Request.responseText);
			for(i=0;i < res.length; i++) {
				$('#msg_box').append('<li><b>' + res[i][0] + "</b>: " + res[i][1] + '</li>');
			}
			
		}
	expirationDate = prompt("Укажите дату окончания блокировки (дд.мм.гггг)","");
	if(expirationDate=="") return;
	arguments = "action=block_user&roomId="+localStorage.getItem("roomId")+"&uid="+uid+"&edate="+expirationDate;
	SendRequest("POST","php/test.php",arguments,Handler_OK,Handler_ER);
}

// обработка нажатия кнопки <Выбрать>
function Select(e) {
		if($( "#list" ).attr('class')=='room-users') {
			if($( "#list.room-users" ).val()!=null) {
				if(confirm("Вы точно хотите заблокировать пользователя?")) {
					blockUser($( "#list.room-users" ).val());
				}
				return;
			}
		}	
		if($( "#list" ).attr('class')=='rooms') {
			if($( "#list.rooms" ).val()!=null & $( "#list.rooms" ).val()!="ER") {
				localStorage.setItem("roomId",$( "#list.rooms").val());
				localStorage.setItem("list_class","room-users");
				$("#list.rooms").attr('class', 'room-users');
				joinRoom();
				getMessages();
				$('#btnCreate').hide();
				$('#btnDelete').hide();
				$('#btnSelect').val("Заблокировать");
			}
		}
		if($( "#list" ).attr('class')=='users') {
			if($( "#list.users" ).val()!=null) {
				localStorage.setItem("uid",$( "#list.users").val());
				localStorage.setItem("list_class","rooms");
				$("#list.users").attr('class','rooms');
			}
		}
		updateInfo();
}

// обработка нажатия кнопки <Отмена>
function Cancel(e) {
	if($( "#list" ).attr('class')=='users') {
		return;
	}
	if($( "#list" ).attr('class')=='rooms') {
		localStorage.removeItem("uid");
		localStorage.setItem("list_class","users");
		$("#list.rooms").attr('class', 'users');
		$('#btnSelect').removeAttr('disabled');
	}
	if($( "#list" ).attr('class')=='room-users') {
		clearInterval(localStorage.getItem("timerId"));
		disconnetRoom();
		$('#msg_box').empty();
		localStorage.removeItem("roomId");
		localStorage.setItem("list_class","rooms");
		clearInterval(localStorage.getItem("timerId"));
		localStorage.removeItem("timerId");		
		$("#list.room-users").attr('class', 'rooms');
		$('#btnCreate').show();
		$('#btnDelete').show();
		$('#btnSelect').val("Выбрать");
		$('#btnSelect').removeAttr('disabled');
	}
	updateInfo();
}

// обработка нажатия кнопки <Создать>
function Create(e) {
	if($( "#list" ).attr('class')=='users') {
		createUser();
	}
	if($( "#list" ).attr('class')=='rooms') {
		createRoom();
	}
	
}

// обработка нажатия кнопки <Удалить>
function Delete(e) {
	if($( "#list" ).attr('class')=='users') {
		if($( "#list.users").val()!=null) {
			deleteUser($( "#list.users").val());
		}
	}
	if($( "#list" ).attr('class')=='rooms') {
		if($( "#list.rooms").val()!=null) {
			deleteRoom($("#list.rooms").val());
		}
	}
}

// просто проверка localStorage если обновить страницу
function checkLocalStorage() {
	if($( "#list" ).attr('class')=='users') {
		if(localStorage.removeItem("timerId"))
		{
			clearInterval(localStorage.getItem("timerId"));
			localStorage.removeItem("timerId");
		}
		localStorage.removeItem("roomId");
		localStorage.removeItem("uid");
	}
	if($( "#list" ).attr('class')=='rooms') {
		if(localStorage.removeItem("timerId"))
		{
			clearInterval(localStorage.getItem("timerId"));
			localStorage.removeItem("timerId");
		}
		localStorage.removeItem("roomId");
	}
	if($( "#list" ).attr('class')=='rooms') {
		
	}
}

// обработка кнопки отправить сообщение
function SendMessage(e) {
	putMessage($('#message').val());
	getMessages();
}



