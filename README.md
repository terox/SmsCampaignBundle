# SmsCampaignBundle

## About

The **SmsCampaignBundle** provides tools and services to create and send SMS campaigns to a high volume of users. Currently,
it can send SMS though SMPP client, the most used protocol for send high volumes.

## Installation:

#### 1. Require the bundle and its dependencies with composer:

```
$ composer require terox/sms-campaign-bundle
```

#### 2. Register SmsCampaignBundle and RabbitMQBundle in your AppKernel.php:

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

#### 3. Add **SmsCampaignBundle** configuration to your ```config.yml```:

```yaml
terox_sms_campaign:
    debug:
        transport: false
        smpp: false

    providers:
        example_provider:
            host: "%smpp_host%"
            port: "%smpp_port%"
            login: "%smpp_login%"
            password: "%smpp_password%"
            timeout_sender: "%smpp_timeout_sender%"
            timeout_receiver: "%smpp_timeout_receiver%"
```

#### 4. If you haven't added the **RabbitMQ bundle** configuration, add it to your ```config.yml```:
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

...

## TODO

* Improve documentation
* Improve "bundle"
* Entity refactor and Message model extension

## License

See: resources/meta/LICENSE.md
