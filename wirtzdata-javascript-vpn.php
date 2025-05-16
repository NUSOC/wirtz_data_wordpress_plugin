<?php
/*
Description: Bætir við ✅ eða 🔐🌐 við VPN tengla eftir IP tölu notanda.
Description (EN): Adds ✅ or 🔐🌐 to VPN links based on user's IP address.
Version: 1.0
*/

add_action('wp_enqueue_scripts', function () {
    // Skráum tómt handfang fyrir inline JavaScript
    wp_register_script('vpn-inline-base', '');
    wp_enqueue_script('vpn-inline-base');

    
    $inline_js = <<<JS
    (async function() {
        // Breyta IP tölu í töluform
        function ipToLong(ip) {
            return ip.split('.').reduce((acc, octet) => (acc << 8) + parseInt(octet), 0);
        }

        // Athuga hvort IP tala sé í VPN bili
        function isInTraditionalVPN(ip) {
            const ipLong = ipToLong(ip);
            const start = ipToLong("165.124.160.0");
            const end = ipToLong("165.124.167.255");
            return ipLong >= start && ipLong <= end;
        }

        // Ná í IP tölu notanda
        const response = await fetch("https://api.ipify.org?format=json");
        const data = await response.json();
        const userIp = data.ip;

        // Athuga hvort IP tala sé leyfð
        const isAllowed = isInTraditionalVPN(userIp);

        // Finna alla tengla á síðunni
        document.querySelectorAll("a").forEach(link => {
            // Athuga hvort texti tengils inniheldur "VPN"
            if (link.textContent.toUpperCase().includes("(VPN)")) {
                // Fjarlægja "(VPN Required)" og bæta við ✅ ef IP er leyfð, annars 🔐🌐
                const icon = isAllowed ? " ✅" : " [ 🔐🌐 VPN Required ]";
                link.textContent = link.textContent.replace("(VPN)", "") + icon;
            }
        });

        // Bæta við upplýsingum neðst á síðunni
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
