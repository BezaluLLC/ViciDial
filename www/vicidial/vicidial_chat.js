// vicidial_chat.js
//
// Copyright (C) 2015  Joe Johnson, Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
//
// Used by manager_chat_actions, this file contains all Javascript functions that grab 
// information off of the web page and utilize AJAX to pass data to manager_chat_actions.php,
// which will respond with data used to update the manager chat interface.
//
// Builds:
// 150612-2334 - First build
//

function RefreshChatDisplay(manager_chat_id) {
	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest!='undefined')
		{
		xmlhttp = new XMLHttpRequest();
		}
	if (xmlhttp) 
		{ 
		chat_SQL_query = "reload_chat_span=1";
		xmlhttp.open('POST', 'manager_chat_actions.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatText = null;
				ChatText = xmlhttp.responseText;
				document.getElementById("ManagerChatAvailabilityDisplay").innerHTML=ChatText;
				}
			}
		delete xmlhttp;
		}
	}

function CheckNewMessages(manager_chat_id) {
	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest!='undefined')
		{
		xmlhttp = new XMLHttpRequest();
		}
	if (xmlhttp) 
		{
		chat_SQL_query = "action=CheckNewMessages&manager_chat_id="+manager_chat_id;
		// alert(chat_SQL_query);
		xmlhttp.open('POST', 'manager_chat_actions.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatResponseText = null;
				ChatResponseText = xmlhttp.responseText;
				if (ChatResponseText==0)
					{
					return false;
					}
				else
					{
					var sub_ids=[];
					var ChatText_array=ChatResponseText.split("\n");
					for (var i=0; i<ChatText_array.length; i++) 
						{
						if (ChatText_array[i].length>0) {sub_ids.push(ChatText_array[i]);}
						}
					// console.log(sub_ids);
					RefreshChatSubIDs(manager_chat_id, sub_ids);
					}
				}
			}
		delete xmlhttp;
		}
	}

function RefreshChatSubIDs(manager_chat_id, sub_ids) {
	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest!='undefined')
		{
		xmlhttp = new XMLHttpRequest();
		}
	if (xmlhttp) 
		{
		var sub_id_str="";
		for (var i=0; i<sub_ids.length; i++) 
			{
			sub_id_str+="&chat_sub_ids[]="+sub_ids[i];
			}
		chat_SQL_query = "action=PrintSubChatText&manager_chat_id="+manager_chat_id+sub_id_str;
		// alert(chat_SQL_query);
		xmlhttp.open('POST', 'manager_chat_actions.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatResponseText = null;
				ChatResponseText = xmlhttp.responseText;
				var ChatText_array=ChatResponseText.split("\n");
				for (var j=1; j<ChatText_array.length; j+=2) 
					{
					var manager_chat_span_id=ChatText_array[j-1];
					var ChatText=ChatText_array[j];
					if (document.getElementById(manager_chat_span_id))
						{
						document.getElementById(manager_chat_span_id).innerHTML=ChatText;
						var objDiv = document.getElementById(manager_chat_span_id);
						objDiv.scrollTop = objDiv.scrollHeight;
						}
					}	
				}
			}
		delete xmlhttp;
		}
	}

function PrintSubChatText(manager_chat_id, chat_sub_id) {
	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest!='undefined')
		{
		xmlhttp = new XMLHttpRequest();
		}
	if (xmlhttp) 
		{ 
		var manager_chat_span_id="manager_chat_"+manager_chat_id+"_"+chat_sub_id;
		chat_SQL_query = "action=PrintSubChatText&manager_chat_id="+manager_chat_id+"&chat_sub_id="+chat_sub_id;
		xmlhttp.open('POST', 'manager_chat_actions.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatText = null;
				ChatText = xmlhttp.responseText;
				document.getElementById(manager_chat_span_id).innerHTML=ChatText;
				}
			}
		delete xmlhttp;
		}
	}

function SendChatMessage(manager_chat_id, chat_sub_id, user) {
	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest!='undefined')
		{
		xmlhttp = new XMLHttpRequest();
		}
	if (xmlhttp) 
		{
		var chat_message_field_id="manager_chat_message_"+manager_chat_id+"_"+chat_sub_id;
		var chat_message=encodeURIComponent(document.getElementById(chat_message_field_id).value);

		chat_SQL_query = "action=SendChatMessage&manager_chat_id="+manager_chat_id+"&chat_sub_id="+chat_sub_id+"&chat_message="+chat_message+"&user="+user;
		xmlhttp.open('POST', 'manager_chat_actions.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatText = null;
				ChatText = xmlhttp.responseText;
				if (ChatText.length>0 && ChatText.match(/^Error/)) 
					{
					alert(ChatText);
					}
				else 
					{
					document.getElementById(chat_message_field_id).value="";
					}
				}
			}
		delete xmlhttp;
		}
	}

function EndAgentChat(manager_chat_id, chat_sub_id) {
	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest!='undefined')
		{
		xmlhttp = new XMLHttpRequest();
		}
	if (xmlhttp) 
		{ 
		chat_SQL_query = "action=EndAgentChat&manager_chat_id="+manager_chat_id+"&chat_sub_id="+chat_sub_id;
		xmlhttp.open('POST', 'manager_chat_actions.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatText = null;
				ChatText = xmlhttp.responseText;
				if (ChatText=="ALL CHATS CLOSED")
					{
					window.location.assign("manager_chat_interface.php");
					}
				document.getElementById("ManagerChatDisplay").innerHTML=ChatText;
				}
			}
		delete xmlhttp;
		}
	}
