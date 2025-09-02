# ViciDial Modern WebPhone

## Overview

The Modern WebPhone is a next-generation web-based softphone interface for ViciDial that replaces the legacy Zoiper plugin with a modern WebRTC-based solution.

## Features

- **No Plugin Required**: Uses modern web standards (WebRTC) instead of browser plugins
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Modern UI**: Beautiful gradient design with intuitive controls
- **Full Functionality**: 
  - Make and receive calls
  - Hold/unhold calls
  - Mute/unmute microphone
  - Volume control
  - DTMF dialpad
  - Real-time call timer
  - Status logging

## Browser Support

- Chrome 88+
- Firefox 84+
- Safari 14+
- Edge 88+

All modern browsers with WebRTC support.

## Configuration

To enable the Modern WebPhone, add or modify the following in your `www/agc/options.php` file:

```php
<?php
$webphone_interface = 'modern';  # Use 'modern' for WebRTC interface, 'zoiper' for legacy
$webphone_width = 420;           # Recommended width for modern interface
$webphone_height = 600;          # Recommended height for modern interface
?>
```

## Files

- `www/agc/webphone/modernphone.php` - The main modern webphone interface
- `www/agc/phone_only.php` - Updated to support interface selection
- `www/agc/options-example.php` - Updated with new configuration option

## Migration from Zoiper

To migrate from the legacy Zoiper webphone:

1. Set `$webphone_interface = 'modern';` in your options.php file
2. No other changes required - the modern interface uses the same authentication and parameter system

## Technical Details

The modern webphone maintains full compatibility with ViciDial's existing authentication and parameter system while providing a superior user experience through:

- Modern CSS Grid and Flexbox layouts
- CSS animations and transitions
- WebRTC audio handling
- Responsive breakpoints for mobile devices
- Accessibility improvements

## Status Logging

The interface provides comprehensive status logging showing:
- Connection status
- Login status
- Call events (dialing, connected, ended)
- DTMF events
- Mute/unmute events
- Error messages

This helps with debugging and monitoring call quality.