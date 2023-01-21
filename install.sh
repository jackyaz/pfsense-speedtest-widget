#!/bin/sh

pkg update && pkg install -g libidn2 ca_root_nss
# Example how to remove conflicting or old versions using pkg
# pkg remove speedtest
pkg add "https://install.speedtest.net/app/cli/ookla-speedtest-1.2.0-freebsd12-x86_64.pkg"
fetch -q -o /usr/local/www/widgets/widgets/speedtest.widget.php https://raw.githubusercontent.com/jackyaz/pfsense-speedtest-widget/master/speedtest.widget.php
