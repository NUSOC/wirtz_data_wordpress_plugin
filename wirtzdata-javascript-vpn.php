<?php
/*
Description: Adds ✅ or 🔐🌐 to VPN links based on user's IP address.
Version: 1.0
*/

add_action('wp_enqueue_scripts', function () {
    // Register empty handle for inline JavaScript
    wp_register_script('vpn-inline-base', '');
    wp_enqueue_script('vpn-inline-base');

    
    $inline_js = <<<JS
    (async function() {
        // Convert IP address to numeric form
        function ipToLong(ip) {
            return ip.split('.').reduce((acc, octet) => (acc << 8) + parseInt(octet), 0);
        }

        // Check if IP address is in VPN range
        function isInTraditionalVPN(ip) {
            const ipLong = ipToLong(ip);
            const start = ipToLong("165.124.160.0");
            const end = ipToLong("165.124.167.255");
            return ipLong >= start && ipLong <= end;
        }

        // Get user's IP address
        const response = await fetch("https://api.ipify.org?format=json");
        const data = await response.json();
        const userIp = data.ip;

        // Check if IP address is allowed
        const isAllowed = isInTraditionalVPN(userIp);

        // Find all links on the page
        document.querySelectorAll("a").forEach(link => {
            // Check if link text contains "VPN"
            if (link.textContent.toUpperCase().includes("(VPN)")) {
                // Remove "(VPN Required)" and add ✅ if IP is allowed, otherwise 🔐🌐
                const icon = isAllowed ? " ✅" : " [ 🔐🌐 VPN Required ]";
                link.textContent = link.textContent.replace("(VPN)", "") + icon;
            }
        });

        // Add information at the bottom of the page
            const info = document.createElement("div");
            info.style.cssText = "margin-top: 2em; font-family: sans-serif; font-size: 0.9em; color: #555;";
            info.innerHTML = 
                "<hr>" +
                "<p><strong>Your IP:</strong> " + userIp + "</p>" +
                "<p><strong>Allowed range</strong> 165.124.160.0 - 165.124.167.255</p>";
            document.body.appendChild(info);

    })();
    JS;

    wp_add_inline_script('vpn-inline-base', $inline_js);
});
