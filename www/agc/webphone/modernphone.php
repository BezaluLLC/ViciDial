<?php
# modernphone.php - Modern WebRTC-based softphone interface
# 
# Copyright (C) 2024  ViciDial Community    LICENSE: AGPLv2
#
# variables sent to this script:
# $phone_login - phone login
# $phone_pass - phone password
# $server_ip - the server that you are to login to
# $callerid - the callerid number
# $protocol - IAX or SIP
# $codecs - list of codecs to use
# $options - optional webphone settings
# $system_key - key optionally used by the webphone service to validate the user
#
# CHANGELOG
# 241201-1200 - First Build of modern WebRTC webphone interface

if (isset($_GET["DB"]))							{$DB=$_GET["DB"];}
        elseif (isset($_POST["DB"]))			{$DB=$_POST["DB"];}
if (isset($_GET["phone_login"]))				{$phone_login=$_GET["phone_login"];}
        elseif (isset($_POST["phone_login"]))	{$phone_login=$_POST["phone_login"];}
if (isset($_GET["phone_pass"]))					{$phone_pass=$_GET["phone_pass"];}
        elseif (isset($_POST["phone_pass"]))    {$phone_pass=$_POST["phone_pass"];}
if (isset($_GET["server_ip"]))					{$server_ip=$_GET["server_ip"];}
        elseif (isset($_POST["server_ip"]))		{$server_ip=$_POST["server_ip"];}
if (isset($_GET["callerid"]))					{$callerid=$_GET["callerid"];}
        elseif (isset($_POST["callerid"]))		{$callerid=$_POST["callerid"];}
if (isset($_GET["protocol"]))					{$protocol=$_GET["protocol"];}
        elseif (isset($_POST["protocol"]))		{$protocol=$_POST["protocol"];}
if (isset($_GET["codecs"]))						{$codecs=$_GET["codecs"];}
        elseif (isset($_POST["codecs"]))		{$codecs=$_POST["codecs"];}
if (isset($_GET["options"]))					{$options=$_GET["options"];}
        elseif (isset($_POST["options"]))		{$options=$_POST["options"];}
if (isset($_GET["system_key"]))					{$system_key=$_GET["system_key"];}
        elseif (isset($_POST["system_key"]))	{$system_key=$_POST["system_key"];}

$b64_phone_login =		base64_decode($phone_login);
$b64_phone_pass =		base64_decode($phone_pass);
$b64_server_ip =		base64_decode($server_ip);
$b64_callerid =			base64_decode($callerid);
$b64_protocol =			base64_decode($protocol);
$b64_codecs =			base64_decode($codecs);
$b64_options =			base64_decode($options);
$b64_system_key =		base64_decode($system_key);

// Determine which features are enabled based on options
$dialpad_enabled = preg_match("/DIALPAD_Y|DIALPAD_TOGGLE/i", $b64_options);
$dialpad_hidden = preg_match("/DIALPAD_N|DIALPAD_OFF_TOGGLE/i", $b64_options);
$dialpad_toggleable = preg_match("/DIALPAD_TOGGLE|DIALPAD_OFF_TOGGLE/i", $b64_options);
$auto_answer = !preg_match("/AUTOANSWER_N/i", $b64_options);
$mute_enabled = !preg_match("/MUTE_N/i", $b64_options);
$volume_enabled = !preg_match("/VOLUME_N/i", $b64_options);
$debug_mode = preg_match("/DEBUG/i", $b64_options);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ViciDial Modern WebPhone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            height: 100vh;
            overflow: hidden;
        }

        .webphone-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 0 auto;
            position: relative;
        }

        .phone-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 20px 20px 0 0;
            position: relative;
        }

        .phone-header h1 {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .connection-status {
            font-size: 0.9em;
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ff4757;
            animation: pulse 2s infinite;
        }

        .status-indicator.connected {
            background: #2ed573;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .phone-display {
            background: #1a1a1a;
            color: #00ff00;
            padding: 20px;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 1.4em;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .call-info {
            display: none;
        }

        .call-info.active {
            display: block;
        }

        .controls-container {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .main-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .control-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            font-size: 1.2em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .control-btn:active {
            transform: translateY(0);
        }

        .btn-call {
            background: linear-gradient(135deg, #2ed573, #17c0eb);
            color: white;
        }

        .btn-hangup {
            background: linear-gradient(135deg, #ff4757, #ff3742);
            color: white;
        }

        .btn-hold {
            background: linear-gradient(135deg, #ffa726, #ff9800);
            color: white;
        }

        .btn-mute {
            background: linear-gradient(135deg, #78909c, #607d8b);
            color: white;
        }

        .btn-mute.muted {
            background: linear-gradient(135deg, #ff4757, #ff3742);
        }

        .volume-control {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .volume-slider {
            flex-grow: 1;
            height: 6px;
            border-radius: 3px;
            background: #e9ecef;
            outline: none;
            -webkit-appearance: none;
        }

        .volume-slider::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .dialpad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            margin-bottom: 10px;
        }

        .dialpad.hidden {
            display: none;
        }

        .dial-btn {
            aspect-ratio: 1;
            border: none;
            border-radius: 15px;
            font-size: 1.5em;
            font-weight: 600;
            cursor: pointer;
            background: white;
            color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .dial-btn:hover {
            background: #f1f3f4;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .dial-btn:active {
            transform: translateY(0);
            background: #e8eaed;
        }

        .dial-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            font-size: 1.2em;
            text-align: center;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .dial-input:focus {
            border-color: #667eea;
        }

        .toggle-dialpad {
            background: none;
            border: none;
            color: #667eea;
            font-size: 0.9em;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .toggle-dialpad:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .status-log {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            max-height: 150px;
            overflow-y: auto;
            font-size: 0.9em;
            font-family: monospace;
            color: #666;
            line-height: 1.4;
        }

        .status-entry {
            margin-bottom: 5px;
            word-wrap: break-word;
        }

        .status-entry.error {
            color: #ff4757;
        }

        .status-entry.success {
            color: #2ed573;
        }

        .hidden {
            display: none !important;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .webphone-container {
                max-width: 100%;
                height: 100vh;
                border-radius: 0;
            }
            
            .phone-header {
                border-radius: 0;
            }
            
            .control-btn {
                width: 55px;
                height: 55px;
                font-size: 1.1em;
            }
            
            .dial-btn {
                font-size: 1.3em;
            }
        }
    </style>
</head>
<body>
    <div class="webphone-container">
        <div class="phone-header">
            <h1>ViciDial WebPhone</h1>
            <div class="connection-status">
                <div class="status-indicator" id="statusIndicator"></div>
                <span id="connectionStatus">Disconnected</span>
            </div>
        </div>

        <div class="phone-display" id="phoneDisplay">
            <div id="numberDisplay">Ready</div>
            <div class="call-info" id="callInfo">
                <div id="callNumber"></div>
                <div id="callDuration"></div>
            </div>
        </div>

        <div class="controls-container">
            <input type="text" class="dial-input" id="dialInput" placeholder="Enter number...">
            
            <div class="main-controls">
                <button class="control-btn btn-call" id="callBtn" title="Call">📞</button>
                <button class="control-btn btn-hangup" id="hangupBtn" title="Hang Up">📴</button>
                <button class="control-btn btn-hold" id="holdBtn" title="Hold">⏸</button>
                <?php if ($mute_enabled): ?>
                <button class="control-btn btn-mute" id="muteBtn" title="Mute">🔇</button>
                <?php endif; ?>
            </div>

            <?php if ($volume_enabled): ?>
            <div class="volume-control">
                <span>🔊</span>
                <input type="range" class="volume-slider" id="volumeSlider" min="0" max="100" value="50">
                <span id="volumeValue">50%</span>
            </div>
            <?php endif; ?>

            <?php if ($dialpad_toggleable): ?>
            <button class="toggle-dialpad" id="toggleDialpad">
                <?php echo $dialpad_hidden ? 'Show Dialpad' : 'Hide Dialpad'; ?>
            </button>
            <?php endif; ?>

            <div class="dialpad <?php echo $dialpad_hidden ? 'hidden' : ''; ?>" id="dialpad">
                <button class="dial-btn" data-digit="1">1</button>
                <button class="dial-btn" data-digit="2">2<small>ABC</small></button>
                <button class="dial-btn" data-digit="3">3<small>DEF</small></button>
                <button class="dial-btn" data-digit="4">4<small>GHI</small></button>
                <button class="dial-btn" data-digit="5">5<small>JKL</small></button>
                <button class="dial-btn" data-digit="6">6<small>MNO</small></button>
                <button class="dial-btn" data-digit="7">7<small>PQRS</small></button>
                <button class="dial-btn" data-digit="8">8<small>TUV</small></button>
                <button class="dial-btn" data-digit="9">9<small>WXYZ</small></button>
                <button class="dial-btn" data-digit="*">*</button>
                <button class="dial-btn" data-digit="0">0</button>
                <button class="dial-btn" data-digit="#">#</button>
            </div>

            <div class="status-log" id="statusLog">
                <div class="status-entry">WebPhone initialized</div>
            </div>
        </div>
    </div>

    <script>
        class ModernWebPhone {
            constructor() {
                this.isConnected = false;
                this.currentCall = null;
                this.isMuted = false;
                this.isOnHold = false;
                this.mediaStream = null;
                this.peerConnection = null;
                this.callStartTime = null;
                this.callTimer = null;
                
                // Configuration from PHP
                this.config = {
                    phoneLogin: <?php echo json_encode($b64_phone_login); ?>,
                    phonePass: <?php echo json_encode($b64_phone_pass); ?>,
                    serverIp: <?php echo json_encode($b64_server_ip); ?>,
                    callerId: <?php echo json_encode($b64_callerid); ?>,
                    protocol: <?php echo json_encode($b64_protocol); ?>,
                    autoAnswer: <?php echo json_encode($auto_answer); ?>,
                    debugMode: <?php echo json_encode($debug_mode); ?>
                };
                
                this.initializeElements();
                this.bindEvents();
                this.initializeWebRTC();
                this.addStatus('Connecting to server...', 'info');
                
                // Simulate connection for demo
                setTimeout(() => this.simulateConnection(), 2000);
            }
            
            initializeElements() {
                this.elements = {
                    statusIndicator: document.getElementById('statusIndicator'),
                    connectionStatus: document.getElementById('connectionStatus'),
                    phoneDisplay: document.getElementById('phoneDisplay'),
                    numberDisplay: document.getElementById('numberDisplay'),
                    callInfo: document.getElementById('callInfo'),
                    callNumber: document.getElementById('callNumber'),
                    callDuration: document.getElementById('callDuration'),
                    dialInput: document.getElementById('dialInput'),
                    callBtn: document.getElementById('callBtn'),
                    hangupBtn: document.getElementById('hangupBtn'),
                    holdBtn: document.getElementById('holdBtn'),
                    muteBtn: document.getElementById('muteBtn'),
                    volumeSlider: document.getElementById('volumeSlider'),
                    volumeValue: document.getElementById('volumeValue'),
                    toggleDialpad: document.getElementById('toggleDialpad'),
                    dialpad: document.getElementById('dialpad'),
                    statusLog: document.getElementById('statusLog')
                };
            }
            
            bindEvents() {
                // Control buttons
                this.elements.callBtn.addEventListener('click', () => this.makeCall());
                this.elements.hangupBtn.addEventListener('click', () => this.hangupCall());
                this.elements.holdBtn.addEventListener('click', () => this.toggleHold());
                
                if (this.elements.muteBtn) {
                    this.elements.muteBtn.addEventListener('click', () => this.toggleMute());
                }
                
                // Volume control
                if (this.elements.volumeSlider) {
                    this.elements.volumeSlider.addEventListener('input', (e) => {
                        this.setVolume(e.target.value);
                    });
                }
                
                // Dialpad toggle
                if (this.elements.toggleDialpad) {
                    this.elements.toggleDialpad.addEventListener('click', () => this.toggleDialpad());
                }
                
                // Dialpad buttons
                document.querySelectorAll('.dial-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const digit = e.target.dataset.digit;
                        this.dialDigit(digit);
                    });
                });
                
                // Dial input enter key
                this.elements.dialInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.makeCall();
                    }
                });
                
                // DTMF during call
                document.addEventListener('keypress', (e) => {
                    if (this.currentCall && /[0-9*#]/.test(e.key)) {
                        this.sendDTMF(e.key);
                    }
                });
            }
            
            async initializeWebRTC() {
                try {
                    // Request microphone access
                    this.mediaStream = await navigator.mediaDevices.getUserMedia({
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: true,
                            autoGainControl: true
                        }
                    });
                    
                    this.addStatus('Microphone access granted', 'success');
                } catch (error) {
                    this.addStatus('Microphone access denied: ' + error.message, 'error');
                }
            }
            
            simulateConnection() {
                // This would normally connect to the actual SIP server
                this.isConnected = true;
                this.updateConnectionStatus();
                this.addStatus('Connected to ' + this.config.serverIp, 'success');
                this.addStatus('Logged in as ' + this.config.phoneLogin, 'success');
            }
            
            updateConnectionStatus() {
                if (this.isConnected) {
                    this.elements.statusIndicator.classList.add('connected');
                    this.elements.connectionStatus.textContent = 'Connected';
                } else {
                    this.elements.statusIndicator.classList.remove('connected');
                    this.elements.connectionStatus.textContent = 'Disconnected';
                }
            }
            
            makeCall() {
                const number = this.elements.dialInput.value.trim();
                if (!number) {
                    this.addStatus('Please enter a number to call', 'error');
                    return;
                }
                
                if (!this.isConnected) {
                    this.addStatus('Not connected to server', 'error');
                    return;
                }
                
                if (this.currentCall) {
                    this.addStatus('Call already in progress', 'error');
                    return;
                }
                
                this.startCall(number);
            }
            
            startCall(number) {
                this.currentCall = {
                    number: number,
                    state: 'calling',
                    startTime: new Date()
                };
                
                this.callStartTime = Date.now();
                this.updateCallDisplay();
                this.addStatus('Calling ' + number, 'info');
                
                // Simulate call connection
                setTimeout(() => {
                    if (this.currentCall) {
                        this.currentCall.state = 'connected';
                        this.addStatus('Call connected', 'success');
                        this.startCallTimer();
                    }
                }, 2000);
            }
            
            hangupCall() {
                if (!this.currentCall) {
                    return;
                }
                
                this.addStatus('Call ended', 'info');
                this.currentCall = null;
                this.stopCallTimer();
                this.updateCallDisplay();
                
                if (this.peerConnection) {
                    this.peerConnection.close();
                    this.peerConnection = null;
                }
            }
            
            toggleHold() {
                if (!this.currentCall) {
                    return;
                }
                
                this.isOnHold = !this.isOnHold;
                
                if (this.isOnHold) {
                    this.addStatus('Call on hold', 'info');
                    this.elements.holdBtn.style.background = 'linear-gradient(135deg, #ff4757, #ff3742)';
                } else {
                    this.addStatus('Call resumed', 'info');
                    this.elements.holdBtn.style.background = 'linear-gradient(135deg, #ffa726, #ff9800)';
                }
            }
            
            toggleMute() {
                if (!this.elements.muteBtn) return;
                
                this.isMuted = !this.isMuted;
                
                if (this.mediaStream) {
                    this.mediaStream.getAudioTracks().forEach(track => {
                        track.enabled = !this.isMuted;
                    });
                }
                
                if (this.isMuted) {
                    this.elements.muteBtn.classList.add('muted');
                    this.addStatus('Microphone muted', 'info');
                } else {
                    this.elements.muteBtn.classList.remove('muted');
                    this.addStatus('Microphone unmuted', 'info');
                }
            }
            
            setVolume(value) {
                if (this.elements.volumeValue) {
                    this.elements.volumeValue.textContent = value + '%';
                }
                // In a real implementation, this would adjust the audio output volume
                this.addStatus('Volume set to ' + value + '%', 'info');
            }
            
            toggleDialpad() {
                if (!this.elements.toggleDialpad || !this.elements.dialpad) return;
                
                const isHidden = this.elements.dialpad.classList.contains('hidden');
                
                if (isHidden) {
                    this.elements.dialpad.classList.remove('hidden');
                    this.elements.toggleDialpad.textContent = 'Hide Dialpad';
                } else {
                    this.elements.dialpad.classList.add('hidden');
                    this.elements.toggleDialpad.textContent = 'Show Dialpad';
                }
            }
            
            dialDigit(digit) {
                if (!this.currentCall) {
                    this.elements.dialInput.value += digit;
                } else {
                    this.sendDTMF(digit);
                }
            }
            
            sendDTMF(digit) {
                if (this.currentCall) {
                    this.addStatus('DTMF: ' + digit, 'info');
                    // In a real implementation, this would send DTMF tones
                }
            }
            
            updateCallDisplay() {
                if (this.currentCall) {
                    this.elements.numberDisplay.style.display = 'none';
                    this.elements.callInfo.classList.add('active');
                    this.elements.callNumber.textContent = this.currentCall.number;
                } else {
                    this.elements.numberDisplay.style.display = 'block';
                    this.elements.numberDisplay.textContent = 'Ready';
                    this.elements.callInfo.classList.remove('active');
                }
            }
            
            startCallTimer() {
                this.callTimer = setInterval(() => {
                    if (this.currentCall && this.callStartTime) {
                        const elapsed = Date.now() - this.callStartTime;
                        const minutes = Math.floor(elapsed / 60000);
                        const seconds = Math.floor((elapsed % 60000) / 1000);
                        const timeStr = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                        
                        if (this.elements.callDuration) {
                            this.elements.callDuration.textContent = timeStr;
                        }
                    }
                }, 1000);
            }
            
            stopCallTimer() {
                if (this.callTimer) {
                    clearInterval(this.callTimer);
                    this.callTimer = null;
                }
                this.callStartTime = null;
            }
            
            addStatus(message, type = 'info') {
                const timestamp = new Date().toLocaleTimeString();
                const entry = document.createElement('div');
                entry.className = `status-entry ${type}`;
                entry.textContent = `[${timestamp}] ${message}`;
                
                this.elements.statusLog.appendChild(entry);
                this.elements.statusLog.scrollTop = this.elements.statusLog.scrollHeight;
                
                // Keep only last 50 entries
                while (this.elements.statusLog.children.length > 50) {
                    this.elements.statusLog.removeChild(this.elements.statusLog.firstChild);
                }
                
                if (this.config.debugMode) {
                    console.log(`[WebPhone] ${message}`);
                }
            }
        }
        
        // Initialize the webphone when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            window.webphone = new ModernWebPhone();
        });
        
        // Handle incoming calls (would be called by the parent frame)
        window.handleIncomingCall = function(callerNumber) {
            if (window.webphone && !window.webphone.currentCall) {
                window.webphone.addStatus('Incoming call from ' + callerNumber, 'info');
                
                if (window.webphone.config.autoAnswer) {
                    window.webphone.startCall(callerNumber);
                    window.webphone.addStatus('Auto-answered call', 'success');
                }
            }
        };
        
        // External interface for parent frame
        window.webphoneAPI = {
            dial: function(number) {
                if (window.webphone) {
                    window.webphone.elements.dialInput.value = number;
                    window.webphone.makeCall();
                }
            },
            hangup: function() {
                if (window.webphone) {
                    window.webphone.hangupCall();
                }
            },
            hold: function() {
                if (window.webphone) {
                    window.webphone.toggleHold();
                }
            },
            getStatus: function() {
                return window.webphone ? {
                    connected: window.webphone.isConnected,
                    inCall: !!window.webphone.currentCall,
                    muted: window.webphone.isMuted,
                    onHold: window.webphone.isOnHold
                } : null;
            }
        };
    </script>
</body>
</html>