# SmsCampaignBundle

## About The Project

The **SmsCampaignBundle** provides tools and services to create and send SMS campaigns to a high volume of users. Currently,
it can send SMS though SMPP client, the most used protocol for send high volumes.

### About the SMPP Client

Due the PHP limitations on persistent and async processes management, I have created the utility [smpp-cli](https://github.com/terox/smpp-cli)
for NodeJS. This is a little daemon that implements a ```DNode protocol``` to connect the PHP consumers with ***smpp-cli*** 
processes that sent the SMS through SMPP server.

The impact of implement this architecture is minimum and solves a lot of headaches with SMPP providers like repetitive 
connections, timeouts, two processes (one to sent, one to receive) etc.

This is completely transparent for developer.

<small>**Note**: The customized [php-smpp](https://github.com/terox/php-smpp) is definitely deprecated.</small>

## Installation:

#### 1. Require the bundle and its dependencies with composer:

```bash
composer require terox/sms-campaign-bundle
```

#### 2. Install ```smpp-cli```:

```bash
npm install -g smpp-cli
```

#### 2. Register ```SmsCampaignBundle``` and ```RabbitMQBundle``` in your AppKernel.php:

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
        new Terox\SmsCampaignBundle\TeroxSmsCampaignBundle()
        // ...
    );
    
    return $bundles;
}
```

#### 3. Add the ```SmsCampaignBundle``` configuration to your ```config.yml```:

```yaml
terox_sms_campaign:
    providers:
        example_provider:
            host: "%smpp_host%"
            port: "%smpp_port%"
            login: "%smpp_login%"
            password: "%smpp_password%"
            rpc:
              port: 7070
```

#### 4. If you haven't added the ```RabbitMQBundle``` configuration, add it to your ```config.yml```:
You are free to configure the RabbitMQ bundle. The producers and consumers **are preconfigured in SmsCampaignBundle**.

```yaml
old_sound_rabbit_mq:
    connections:
        default:
            host:     "%rabbitmq_host%"
            port:     "%rabbitmq_port%"
            user:     "%rabbitmq_user%"
            password: "%rabbitmq_password%"
            vhost:    "%rabbitmq_vhost%"
            lazy:     true
            connection_timeout: 3
            read_write_timeout: 3

            # requires php-amqplib v2.4.1+ and PHP5.4+
            keepalive: false

            # requires php-amqplib v2.4.1+
            heartbeat: 0
```

## Usage

There are two ways to start the SMPP daemon (```smpp-cli```):

### A. Start the SMPP daemon manually (recommended):

This will execute the daemon and will listen the ```dnode``` port. You need to pass your credentials manually:

```bash
smpp-cli listen 7070 -h smpp.example.com -p 2675 -L userLogin -P userPassword -C "/var/www/bin/console sms:dlr:update"
```
***Note:*** You can use as many demons as you need, for example if you have more than one SMPP provider; only change the port number.

I recommend you that executes this command inside [supervisor](http://supervisord.org/) to keep it running if something was
wrong (like server updates, unexpected errors...).

#### Keep in mind:
* The credentials could be shown at the process manager (like ```htop```) depending on how you pass the credentials to smpp-cli.
* If you use some deployment scripts, be sure to hide the sensitive data where you store these scripts.

### B. Start the SMPP daemon with a Symfony console:

The propose of this command is to use the Symfony configuration params to configure the ```smpp-cli``` daemons:

```bash
/var/www/bin/console sms:start:client
```
***Warning!*** This command still not working as I expected. It need little more research. I hope fix it in the next major release,
but you are free to sent your PR.

## TODO
[ ] Message model extension

## License

See: [LICENSE.md](LICENSE.md)
