// vicidial_chat_agent.js
//
// Copyright (C) 2014  Joe Johnson, Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
//
// Used by agc_agent_manager_chat_interface.php - this contains all the Javascript functions that
// execute the various actions that run in chat_db_query.php, such as creating chats, displaying 
// chats, ending chats, and sending messages.
//
// Builds:
// 150121-2229 - First build
//

// ################################################################################
// Show parent alert
	function chat_alert_box(temp_message)
		{
		window.parent.document.getElementById("AlertBoxContent").innerHTML = temp_message;

		parent.showDiv('AlertBox');

		window.parent.document.alert_form.alert_button.focus();
		}

/// Functions for agent/manager chatting
function CreateAgentToAgentChat() {
	var agent_message=encodeURIComponent(document.getElementById("agent_message").value);
	var user=document.getElementById("user").value;
	var agent=document.getElementById("agent").value;

	if (!agent_message || agent_message=="")
		{
			chat_alert_box("Please enter a chat message");
			return false;
		}
	if (!agent || agent=="")
		{
			chat_alert_box("Please select an agent to chat with");
			return false;
		}

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
		var chat_SQL_query = "action=CreateAgentToAgentChat&agent_manager="+user+"&agent_user="+agent+"&manager_message="+agent_message+"&user="+user;
		xmlhttp.open('POST', 'chat_db_query.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatText = null;
				ChatText = xmlhttp.responseText;
				var ChatText_array=ChatText.split("|");

				if (ChatText.match(/^Error/)) 
					{
					chat_alert_box(ChatText);
					}
				else 
					{
					document.getElementById("agent_message").value="";
					document.getElementById("AgentNewChatSpan").style.display='none';
					document.getElementById("AgentChatSpan").style.display='block';
					document.getElementById("AgentEndChatSpan").style.display = 'block';
					document.getElementById("AgentManagerOverride").value=agent;
					document.getElementById("CurrentActiveChat").value=ChatText_array[0];
					document.getElementById("CurrentActiveChatSubID").value=ChatText_array[1];
					}
				}
			}
		delete xmlhttp;
		}
}

// Displays selected chat, also marks any message on it as read.
function DisplayMgrAgentChat(manager_chat_id, manager_chat_subid) {
	if (manager_chat_id)
		{
		document.getElementById("CurrentActiveChat").value=manager_chat_id;
		document.getElementById("CurrentActiveChatSubID").value=manager_chat_subid;
		} 
	else 
		{
		var manager_chat_id=document.getElementById("CurrentActiveChat").value;
		var manager_chat_subid=document.getElementById("CurrentActiveChatSubID").value;
		}

	if (!manager_chat_id || !manager_chat_subid)
		{
		document.getElementById("AllowAgentReplies").style.display = 'none';
		// document.getElementById("ActiveManagerChatTranscript").innerHTML=ChatText_array[1];
		// document.getElementById("ActiveChatStartDate").innerHTML=ChatText_array[2];
		// document.getElementById("ActiveChatManager").innerHTML=ChatText_array[3];
		return false;
		}
	var user=document.getElementById("user").value;
	var agent_override=document.getElementById("AgentManagerOverride").value;

	// JCJ - commented out 10/19 so any agent in a-2-a chat can end it.
	// if (agent_override && agent_override!="0" && agent_override!="")
	//	{
		document.getElementById("AgentEndChatSpan").style.display = 'block';
	//	}
	// else 
	//	{
	//	document.getElementById("AgentEndChatSpan").style.display = 'none';
	//	}

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
		var chat_SQL_query = "action=DisplayMgrAgentChat&user="+user+"&manager_chat_id="+manager_chat_id+"&manager_chat_subid="+manager_chat_subid;
		xmlhttp.open('POST', 'chat_db_query.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatText = null;
				ChatText = xmlhttp.responseText;
				var ChatText_array=ChatText.split("\n");

				var allow_agent_replies=ChatText_array[0];
				var new_messages=ChatText_array[4];

				if (allow_agent_replies=="Y")
					{
					document.getElementById("AllowAgentReplies").style.display = 'block';
					} 
				else 
					{
					document.getElementById("AllowAgentReplies").style.display = 'none';
					}

				document.getElementById("ActiveManagerChatTranscript").innerHTML=ChatText_array[1];
				document.getElementById("ActiveChatStartDate").innerHTML=ChatText_array[2];
				document.getElementById("ActiveChatManager").innerHTML=ChatText_array[3];
				document.getElementById("ActiveManagerChatTranscript").scrollTop = document.getElementById("ActiveManagerChatTranscript").scrollHeight;
				RefreshActiveChatView();
				}
			}
		delete xmlhttp;
		}
}

// Ends selected displayed chat
function EndAgentToAgentChat() {
	var manager_chat_id=document.getElementById("CurrentActiveChat").value;
	var manager_chat_subid=document.getElementById("CurrentActiveChatSubID").value;
	var user=document.getElementById("user").value;

	if (!manager_chat_id || !manager_chat_subid)
		{
		document.getElementById("AllowAgentReplies").style.display = 'none';
		return false;
		}

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
		var chat_SQL_query = "action=EndAgentToAgentChat&user="+user+"&manager_chat_id="+manager_chat_id+"&manager_chat_subid="+manager_chat_subid;
		xmlhttp.open('POST', 'chat_db_query.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ChatText = null;
				ChatText = xmlhttp.responseText; // echoes number of lines affected - should be greater than zero.

				if (ChatText>0)
					{
					document.getElementById("AllowAgentReplies").style.display = 'none';
					document.getElementById("AgentEndChatSpan").style.display = 'none';
					document.getElementById("ActiveManagerChatTranscript").innerHTML='';	
					document.getElementById("AgentManagerOverride").value='';
					document.getElementById("ActiveChatStartDate").innerHTML='';
					document.getElementById("ActiveChatManager").innerHTML='';
					}

				RefreshActiveChatView();
				}
			}
		delete xmlhttp;
		}
}

function RefreshActiveChatView() {
	var user=document.getElementById("user").value;
	var ChatReloadIDNumber=document.getElementById("ChatReloadIDNumber").value;
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
		var chat_SQL_query = "action=RefreshActiveChatView&user="+user+"&ChatReloadIDNumber="+ChatReloadIDNumber;
		xmlhttp.open('POST', 'chat_db_query.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var ActiveChatText = null;
				ActiveChatText = xmlhttp.responseText;
				if(ActiveChatText!="") 
					{
					var ActiveChatText_array=ActiveChatText.split("|");
					document.getElementById("ChatReloadIDNumber").value=ActiveChatText_array[0];
					document.getElementById("AllActiveChats").innerHTML=ActiveChatText_array[1];
					}
				}
			}
		delete xmlhttp;
		}
}

function ReloadAgentNewChatSpan(user) {
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
		var chat_SQL_query = "action=ReloadAgentNewChatSpan&user="+user;
		xmlhttp.open('POST', 'chat_db_query.php'); 
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(chat_SQL_query); 
		xmlhttp.onreadystatechange = function() 
			{ 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) 
				{
				var Agent2AgentText = xmlhttp.responseText;
				document.getElementById("AgentNewChatSpan").innerHTML=Agent2AgentText;
				}
			}
		delete xmlhttp;
		}
}

function SendMgrChatMessage(manager_chat_id, manager_chat_subid) {
	// if (!manager_chat_id) {return false;}
	if (manager_chat_id)
		{
		document.getElementById("CurrentActiveChat").value=manager_chat_id;
		document.getElementById("CurrentActiveChatSubID").value=manager_chat_subid;
		} 
	else 
		{
		var manager_chat_id=document.getElementById("CurrentActiveChat").value;
		var manager_chat_subid=document.getElementById("CurrentActiveChatSubID").value;
		}

	var user=document.getElementById("user").value;
	var agent_override=document.getElementById("AgentManagerOverride").value;
	var chat_message=encodeURIComponent(document.getElementById("manager_message").value);

	if (!chat_message || chat_message=="") {return false;}

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
		var chat_SQL_query = "action=SendMgrChatMessage&user="+user+"&manager_chat_id="+manager_chat_id+"&manager_chat_subid="+manager_chat_subid+"&chat_message="+chat_message+"&agent_override="+agent_override;
		xmlhttp.open('POST', 'chat_db_query.php'); 
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
					chat_alert_box(ChatText);
					}
				else 
					{
					document.getElementById("manager_message").value="";
					}
				}
			}
		delete xmlhttp;
		}
}

function MgrAgentAutoRefresh() {
	rInt=window.setInterval(function() {DisplayMgrAgentChat()}, 1000);
}
