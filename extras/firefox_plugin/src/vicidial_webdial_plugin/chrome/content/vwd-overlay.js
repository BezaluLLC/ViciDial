// Constant variables for VICIWebDial
var PhonePattern = /(\+[0-9]{1,3})?[^+]?([0-9]+[^+]{0,2})+[0-9]+/ ;
var gPrefs = Components.classes["@mozilla.org/preferences-service;1"].getService(Components.interfaces.nsIPrefBranch);
if (gPrefs.prefHasUserValue("extensions.viciwebdial.viciwebdial_phonelogin")) {
	var phonelogin = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_phonelogin");
	var phonepwd = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_phonepwd");
	var VWD_user = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_user");
	var VWD_pwd = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_pwd");
	var VWD_URL = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_URL");
	var VWD_AGC = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_AGC");
	var VWD_campaign = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_campaign");
	var VWD_ac = gPrefs.getBoolPref("extensions.viciwebdial.viciwebdial_autoconnect");
} else {
	gPrefs.setCharPref("extensions.viciwebdial.viciwebdial_phonelogin", "");
	gPrefs.setCharPref("extensions.viciwebdial.viciwebdial_phonepwd", "");
	gPrefs.setCharPref("extensions.viciwebdial.viciwebdial_user", "");
	gPrefs.setCharPref("extensions.viciwebdial.viciwebdial_pwd", "");
	gPrefs.setCharPref("extensions.viciwebdial.viciwebdial_URL", "");
	gPrefs.setCharPref("extensions.viciwebdial.viciwebdial_AGC", "");
	gPrefs.setCharPref("extensions.viciwebdial.viciwebdial_campaign", "");
	gPrefs.setBoolPref("extensions.viciwebdial.viciwebdial_autoconnect", false);
	if (confirm("Thank you for installing ViciDial-Web-Dial!!\n\nBefore you begin using it, please set your connection preferences in the preferences window.")) {
		openVWDOptionsWindow();
	}
}


var highlightedPhone="";
var AutoConnectAttempts=0;

function getPropertiesValues()
{
	return document.getElementById('viciwebdial-strings');
}

window.addEventListener("load", function () { gBrowser.addEventListener("load",VWDAutoConnect,true); }, false);


if( window.getBrowser ) {
	window.getBrowser().addEventListener("load", VWDload, true);
}

function VWDload(event) {
   	if(document.getElementById("contentAreaContextMenu"))
   	{
  		document.getElementById("contentAreaContextMenu").addEventListener("popupshowing", ParsePhone, true);
   	}
}

function ParsePhone() {
	var highlightedPhone = getBrowserSelection().replace(/[^0-9]/g,'');
	var phoneStr=highlightedPhone;
	if (highlightedPhone.length>0) {
		document.getElementById('viciwebdial-phone-textbox').value=highlightedPhone;
		
		if (highlightedPhone.length>=6)	{
			if (highlightedPhone.length==7) {
				var phoneStr=highlightedPhone.substr(0,3)+"-"+highlightedPhone.substr(3);
			} else if (phoneStr.length==10) {
				var phoneStr=highlightedPhone.substr(0,3)+"-"+highlightedPhone.substr(3,3)+"-"+highlightedPhone.substr(6);
			}
		}
	}

	var menuitem=document.getElementById('context-viciwebdial');
	if (phoneStr.length==0 && document.getElementById('viciwebdial-phone-textbox').value.length==0) {
		phoneStr="";
		menuitem.disabled=true;
	} else {
		menuitem.disabled=false;
	}
	menuitem.label=getPropertiesValues().getString("menu_dial")+" "+document.getElementById('viciwebdial-phone-textbox').value;
}

function VICIWebDialPlaceCall()
{
	var highlightedPhone=document.getElementById('viciwebdial-phone-textbox').value;
	PlaceCall(highlightedPhone, 'DIAL');
}

function PlaceCall(phone_number, VWD_action) {
	var alert_msg="";
	GetVWDOptionVariables();

	if (!phonelogin || phonelogin.length==0) {alert_msg+=" - Phone login not set.\n";}
	if (!phonepwd || phonepwd.length==0) {alert_msg+=" - Phone password not set.\n";}
	if (!VWD_user || VWD_user.length==0) {alert_msg+=" - User login not set.\n";}
	if (!VWD_pwd || VWD_pwd.length==0) {alert_msg+=" - User password not set.\n";}
	if (!VWD_URL || VWD_URL.length==0) {alert_msg+=" - Vicidial api.php URL not set.\n";}
	if (alert_msg.length>0) {
		if (confirm("There are problems with your preference settings:\n\n"+alert_msg+"\nPlease open the ViciDial-web-dial preferences window and make the necessary changes.")) {
			openVWDOptionsWindow();
		}
		return false;
	}

	var phone_number = document.getElementById('viciwebdial-phone-textbox').value;

	var xmlhttp=false;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
			xmlhttp = false;
		}
	}
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	}

	if (xmlhttp) { 
		var vicidial_query="&source=VWD&value="+phone_number+"&function=external_dial&preview=NO&search=YES&focus=YES&user="+VWD_user+"&agent_user="+VWD_user+"&pass="+VWD_pwd;
		xmlhttp.open('POST', VWD_URL);
		xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		xmlhttp.send(vicidial_query); 
		xmlhttp.onreadystatechange = function() { 
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var VDrslt = null;
				VDrslt = xmlhttp.responseText;
				var VWD_errcheck=VDrslt.match(/^SUCCESS/);
				if (!VWD_errcheck) {alert(VDrslt);}
			}
		}
		delete xmlhttp;
	}
}

function VWDSetMenuValues(menuStr) {
	var menuitem=document.getElementById('connect-viciwebdial');
	menuitem.label=getPropertiesValues().getString(menuStr);
}

function openVWDOptionsWindow(optionsTitle) {
	var windows = Components.classes['@mozilla.org/appshell/window-mediator;1']
		.getService(Components.interfaces.nsIWindowMediator)
		.getEnumerator(null);
	while (windows.hasMoreElements())
	{
		var win = windows.getNext();
		if (win.document.documentURI == "chrome://viciwebdial/content/options.xul")
		{
			win.focus();
			return;
		}
	}
    window.openDialog("chrome://viciwebdial/content/options.xul",
    	"viciwebdial-preferences",
        "chrome,titlebar,toolbar,centerscreen,modal",
        null,
		optionsTitle);  
}

function openVWDAboutWindow() {
	var windows = Components.classes['@mozilla.org/appshell/window-mediator;1']
		.getService(Components.interfaces.nsIWindowMediator)
		.getEnumerator(null);
	while (windows.hasMoreElements())
	{
		var win = windows.getNext();
		if (win.document.documentURI == "chrome://viciwebdial/content/about.xul")
		{
			win.focus();
			return;
		}
	}
    window.openDialog(
        "chrome://viciwebdial/content/about.xul", 
        "viciwebdial-about",
        "chrome,centerscreen,modal",
        null);  
}

function VWDAutoConnect() {
	if (VWD_ac) {
		if (AutoConnectAttempts==0) {
			VWDConnect("Connect to Vicidial");	
		}
		AutoConnectAttempts++;
	}
}

function VWDConnect(VWD_action) {
	GetVWDOptionVariables();

	var alert_msg="";
	if (!VWD_AGC || VWD_AGC.length==0) {alert_msg+=" - Vicidial AGC URL not set.\n";}
	if (!phonelogin || phonelogin.length==0) {alert_msg+=" - Phone login not set.\n";}
	if (!phonepwd || phonepwd.length==0) {alert_msg+=" - Phone password not set.\n";}
	if (!VWD_user || VWD_user.length==0) {alert_msg+=" - User login not set.\n";}
	if (!VWD_pwd || VWD_pwd.length==0) {alert_msg+=" - User password not set.\n";}
	if (!VWD_URL || VWD_URL.length==0) {alert_msg+=" - Vicidial api.php URL not set.\n";}
	if (alert_msg.length>0) {
		if (confirm("There are problems with your preference settings:\n\n"+alert_msg+"\nPlease open the ViciDial-web-dial preferences window and make the necessary changes.")) {
			openVWDOptionsWindow();
		}
		return false;
	}
	if (VWD_action=="Connect to Vicidial") {
		var URL=VWD_AGC+"?phone_login="+phonelogin+"&phone_pass="+phonepwd+"&VD_login="+VWD_user+"&VD_pass="+VWD_pwd+"&VD_campaign="+VWD_campaign+"&SUBMIT=SUBMIT"
		content.wrappedJSObject.location=URL;
	}
}

function GetVWDOptionVariables() {
	gPrefs = Components.classes["@mozilla.org/preferences-service;1"].getService(Components.interfaces.nsIPrefBranch);
	phonelogin = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_phonelogin");
	phonepwd = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_phonepwd");
	VWD_user = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_user");
	VWD_pwd = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_pwd");
	VWD_URL = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_URL");
	VWD_AGC = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_AGC");
	VWD_campaign = gPrefs.getCharPref("extensions.viciwebdial.viciwebdial_campaign");
}
