# Speedtest dashboard widget for pfSense

<img width="620" alt="Screen Shot 2021-09-21 at 4 24 53 PM" src="https://user-images.githubusercontent.com/6041726/134243097-4328bc0d-b50f-4c1e-8972-148d87838e3f.png">


This version uses the offical Ookla Speedtest Cli.

## Manual Installation

To use this widget you will need to install the speedtest package:

```
sudo pkg update && sudo pkg install -g libidn2 ca_root_nss
# Example how to remove conflicting or old versions using pkg
# sudo pkg remove speedtest
sudo pkg add "https://install.speedtest.net/app/cli/ookla-speedtest-1.0.0-freebsd.pkg"
```

Copy the widget file **speedtest.widget.php** to **/usr/local/www/widgets/widgets/** on your pfSense machine.

Enable the widget on your dashboard.

## Auto Installation

- Run this command:

fetch -q -o - https://raw.githubusercontent.com/rudecles/pfsense-speedtest-widget/master/install.sh | sh

Enable the widget on your Dashboard.

