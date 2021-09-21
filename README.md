# Speedtest dashboard widget for pfSense

![speedtes-ip](https://user-images.githubusercontent.com/19557850/132254559-4a0d9aab-117a-465e-a77d-9d7464af2d52.JPG)

This version show IP address source of test.

## Manual Installation

To use this widget you will need to install the speedtest package:

pfSense 2.4.x:

```
pkg update ; pkg install -y py37-speedtest-cli
```
pfSense 2.5.x or 21.xx (PLUS):

```
pkg update ; pkg install -y py38-speedtest-cli
```

Copy the widget file **speedtest.widget.php** to **/usr/local/www/widgets/widgets/** on your pfSense machine.

Enable the widget on your dashboard.

## Auto Installation

- Run this command:

fetch -q -o - https://raw.githubusercontent.com/joarli/pfsense-speedtest-widget/master/install.sh | sh

Enable the widget on your Dashboard.

Done.



sudo pkg update && sudo pkg install -g libidn2 ca_root_nss
# Example how to remove conflicting or old versions using pkg
# sudo pkg remove speedtest
sudo pkg add "https://install.speedtest.net/app/cli/ookla-speedtest-1.0.0-freebsd.pkg"