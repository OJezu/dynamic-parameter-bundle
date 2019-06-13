# OJezu/DynamicParameterBundle

This bundle enables having multiple configurations (installations) in one Symfony application. It does so, by supplying
two independent features:

 * Installation-aware kernel, and installation-dependent parameters
 * Advanced parameter provider, that can read parameters from any source.

Combination of this features enable implementing multi-tenant applications, configured to use different resources
separated at infrastructure level for each tenant. The resources can be database connection, filesystem adapters, etc.
Moreover, configuration storage can be off-loaded to a database server, JSON file, zookeeper instance, with a custom or
build-in parameter provider service, which will load appropriate configuration based on installation selected in
application kernel.

### Requirements

This bundle requires Symfony 3.4, as it depends on advanced environment variable processing.

### Usage

**Note:** Processors from this bundle *do not* read actual environment variables.

#### Multi-installation

Provide kernel with information about installation (see configuration below), and then in your configuration you can
use that information as parameters.

```yaml
#app/config/config.yml

ojezu_dynamic_parameter:
    multi_installation: true

file_storage:
    bucket: "/myapp/installation/%env(ojezu_installation:name)%/"
```

Usage is similar to plain environment variables, but gives more control, as it's developer who decides what and from
where will find its way into `Installation` object. Installation parameters can be set based on request headers, php-cli
arguments and any other data source, as implemented in application using this bundle (see configuration below).

All `Installation` class instance public properties can be accessed, and Installation class can be extended,
with properties you need.

#### Advanced parameter provider

After configuring advanced parameter provider (see below), you are able to map parameters to abstract configuration
paths used to obtain parameter values from any source, as long as there is a provider for that source. Providers are
very simple services, that just have to implement `ParameterProviderInterface`. JSON local and remote file provider is
already provided by this bundle, more powerful than Symfony built-in "json:" env variable processor.

```yaml
# app/config/config.yml

ojezu_dynamic_parameter:
    advanced_parameters:
        provider: 'OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider'
        parameter_map:
            database_host: { path: ['database', 'host'] }

doctrine:
    dbal:
        driver:   pdo_mysql
        server_version: 5.7
        host:     "%env(ojezu_param:database_host)%"
```

```yaml
# services.yml

services:
    OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider:
        arguments:
            - '%kernel.root_dir%/config/config.json'
```

This configuration will find database.host value in JSON config file, and provide it to DBAL configuration.

#### Using them together

While both features offer more than what's built in Symfony 3.4, using them together allows for easy management of
multiple configurations supported by same Symfony application.

```yaml
#app/config/config.yml

ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        provider: 'OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider'
        parameter_map:
            database_name: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'name'] }

doctrine:
    dbal:
        driver:   pdo_mysql
        server_version: 5.7
        host:   "mysql.example.com"
        dbname: "%env(ojezu_param:database_name)%"
```

```yaml
#services.yml

services:
    OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider:
        arguments:
            - '%kernel.root_dir%/config/config.json'
```

```json
{
  "installation": {
    "application1": {
      "database": {
        "name": "app1_database",
      }
    },
    "application2": {
      "database": {
        "name": "app2_database",
      }
    }
  }
}
```

### Configuration

#### Multi-installation

In order to be able to use multi-installation support:

1. Enable it in configuration:

    ```yaml
    #app/config/config.yml

    ojezu_dynamic_parameter:
        multi_installation: true
    ```

2. Change your AppKernel to extend `\OJezu\DynamicParameterBundle\Kernel\Kernel`

    ```php
    <?php

    use \OJezu\DynamicParameterBundle\Kernel\Kernel;

    class AppKernel extends Kernel
    {
        (...)
    }
    ```

3. Make sure that in all places where kernel is created in your application, it is provided with `Installation`
instance. Kernel is usually created by `web/*.php` or `public/*.php` files, but remember to modify your `bin/console`
too.

    ```php
    <?php
    (...)
    $installation = new Installation($requestedInstallation);
    $kernel = new AppKernel($installation, $env, $debug);
    ```

   Complete examples can be found in `doc/examples` directory of this repository.

4. In `bin/console` be sure to also swap `Application` with one provided by this bundle, if you want to specify
installation via CLI option - otherwise parsing of argv may introduce problems.

Complete examples can be found in `doc/examples` directory

#### Advanced parameter provider

You must provide mapping for supported parameters. It is required due to limitations in `%env(processor:variable)%`
Symfony DI Container configuration syntax, and to allow paths that can be easily adapted to any configuration provider.

```yaml
ojezu_dynamic_parameter:
    advanced_parameters:
        provider: 'OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider'
        parameter_map:
            database_host: { path: ['database', 'host'] }
            database_name: { path: ['database', 'name'] }
            database_user: { path: ['database', 'user'] }
```

Those parameters can later be used in all places in your application configuration, no matter support from configured bundle:

```yaml
doctrine:
    dbal:
        driver:   pdo_mysql
        server_version: 5.7
        host:   "%env(ojezu_param:database_host)%"
        dbname: "%env(ojezu_param:database_name)%"
        user:   "%env(ojezu_param:database_user)%"
```

##### Using other parameters

In paths all previously loaded parameters can be used, including `ojezu_installation` parameters.

```yaml
ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        provider: 'OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider'
        parameter_map:
            database_host: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'host'] }
            database_name: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'name'] }
            database_user: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'user'] }
```

### Parameter providers

#### Built-in parameter providers

### LocalJsonFileParameterProvider
Opens and parses json file from local disk.

```yaml
# services.yml

services:
    OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider:
        arguments:
            - '%kernel.root_dir%/etc/installation/%env(ojezu_installation:name)%.json'
```

```yaml
# app/config/config.yml

ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        provider: 'OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider'
```

### RemoteJsonFileParameterProvider
Downloads json file with GuzzleHttp client and optionally caches it in a PSR-16 Simple Cache compatible cache.

```yaml
# services.yml

services:
    app.guzzle.configuration:
        class: GuzzleHttp\Client
        arguments:
            - timeout: 5
              auth: ['configuration', 'configuration']

    app.cache.configuration:
        class: Symfony\Component\Cache\Simple\ApcuCache
        arguments:
            - 'installation_configuration'

    OJezu\DynamicParameterBundle\Service\RemoteJsonFileParameterProvider:
        arguments:
            - 'http://configuration_server/%env(ojezu_installation:name)%.json'
            - '@app.guzzle.configuration'
            - '@app.cache.configuration'
```

```yaml
# app/config/config.yml

ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        provider: 'OJezu\DynamicParameterBundle\Service\RemoteJsonFileParameterProvider'
```

#### Custom parameter providers

You can use any service implementing `OJezu\DynamicParameterBundle\Service\ParameterProviderInterface` interface.

```yaml
ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        provider: 'MyAppBundle\Services\RedisParameterProvider' # this is service! not class.
        parameter_map:
            database_host: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'host'] }
            database_name: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'name'] }
            database_user: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'user'] }
```

Keep in mind that your provider is a service - it can have its arguments injected, it can be tagged etc. As long as
there is no cycle it will work like any other service (no use trying to inject `ojezu_param`s there!)


##### Defaults?

Yes.

```yaml
ojezu_dynamic_parameter:
    advanced_parameters:
        provider: 'OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider'
        processor:
            database_host:
                path: ['database', 'host']
                default: 'localhost'
```

##### No config mode

In some instances there is no configuration to be loaded - e.g. when warming cache. For those instances there is
"no config mode", in which provider won't be used, and all variables will be resolved to null, unless given explicit
value for use in those scenarios. *Defaults won't be used.*

Enable it by using `load_configuration` option in processor section:

```yaml
ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        provider: 'OJezu\DynamicParameterBundle\Service\LocalJsonFileParameterProvider'
        load_configuration: '%env(bool:ojezu_installation:name)%'
        parameter_map:
            database_host: {path: ['installation', '%env(ojezu_installation:name)%', 'database', 'host']}
            log_channel: {path: ['log', '%env(ojezu_installation:name)%'], default: 'default', no_config_value: 'default'}
            bucket_name: {path: ['buckets', '%env(ojezu_installation:name)%'], no_config_value: '%env(LOCAL_BUCKET)%'}
```

### Extending this bundle in your application

Points for expansion are

 * `Installation` value object for ojezu_installation
 * Parameter providers

If you need more options from `Installation`, extend that class with additional public properties or methods,
and use your extended class in place of the base Installation.

New providers can be written by extending `OJezu\DynamicParameterBundle\Service\ParameterProviderInterface` and
configured as described in Configuration part of this ReadMe

### Testing

would be nice.

License
===
MIT
