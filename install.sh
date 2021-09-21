#!/bin/sh

sudo pkg update && sudo pkg install -g libidn2 ca_root_nss
# Example how to remove conflicting or old versions using pkg
# sudo pkg remove speedtest
sudo pkg add "https://install.speedtest.net/app/cli/ookla-speedtest-1.0.0-freebsd.pkg"
fetch -q -o /usr/local/www/widgets/widgets/speedtest.widget.php https://raw.githubusercontent.com/rudical/pfsense-speedtest-widget/master/speedtest.widget.php