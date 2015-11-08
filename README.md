# CloudScaler
Scale up or down the number of virtual servers for your application with just one command!

Typing `scaleup web` can launch a new server at your cloud hosting provider, with a premade image you created. Then it will add the new server's IP address to you DNS provider for round-robin DNS. Traffic will start flowing to the new server immediately with no configuration required.

Currently works with DigitalOcean servers and CloudFlare for DNS. But it's design to be very easy to add more providers. HAProxy provider anyone?

All configuration is done with one config.php file and then you're ready to go. See the included `example.config.php`.

## Example

### Scale up by adding a web server

#### Before
[![](http://img.ctrlv.in/img/15/11/08/563ef20fc9391.png)](http://ctrlv.in/665109)
[![](http://img.ctrlv.in/img/15/11/08/563ef216c4f65.png)](http://ctrlv.in/665110)

[![](http://img.ctrlv.in/img/15/11/08/563ef21992711.png)](http://ctrlv.in/665111)

### After
[![](http://img.ctrlv.in/img/15/11/08/563ef22a02b54.png)](http://ctrlv.in/665112)
[![](http://img.ctrlv.in/img/15/11/08/563ef2329beb4.png)](http://ctrlv.in/665113)

### And back down
[![](http://img.ctrlv.in/img/15/11/08/563ef23a312aa.png)](http://ctrlv.in/665114)

## Installation
```
git clone https://github.com/antriver/cloud-scaler.git
cd cloud-scaler
composer install
cp config.example.php config.php
vi config.php # Edit your config file
console list # Show available commands
console listservices # Run a command
```

## Commands

### `console listhosts [<service>]`
List all the virtual servers that exist. Optionally only those for the specified service.

### `console listservices`
List all the services defined in the config file.

### `console scaleup <service>`
Launch an additional virtual server at the hosting provider and add DNS entries for it at the DNS provider.

### `console scaledown <service>`
Delete the newesst virtual at the hosting provider and remove DNS entries for it from the DNS provider.
