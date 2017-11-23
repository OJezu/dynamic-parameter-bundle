# OJezu/DynamicParameterBundle


This bundle enables having multiple configurations in one Symfony application. It does so, by providing two independent features:

 * Installation-aware kernel, and installation-dependent parameters
 * Advanced parameter provider, that can read parameters from any source and supports paths.


Combination of this features allow for example, implementation of multi-tenant application, that is configured to use completely different resources (data base connection, filesystem adapters, etc.) for each tenant. Moreover, configuration management can be off-loaded to a database server, JSON file, zookeeper instance...

### Requirements


This bundle requires Symfony 3.4, as it depends on advanced environment variable processing.

### Usage

**Note:** Processors from this bundle *do not* read actual environment variables.

#### Multi-installation

Provide kernel with information about installation (see configuration below), and then in your configuration you can use those parameters.

```yaml
#app/config/config.yml

ojezu_dynamic_parameter:
    multi_installation: true

file_storage:
    bucket: "/myapp/installation/%env(ojezu_installation:name)%/"
```

It's similar to plain environment variables, but gives more control, as it's developer who decides what and from where will find its way into `Installation` object.  All `Installation` public properties can be accessed, and you can swap it for extended one, with properties you need.

#### Advanced parameter provider

After configuring advanced parameter provider (see below), you are able to map parameters to abstract configuration paths used to obtain parameter values from any source, as long as there is a provider for that source. Providers are very simple services, that just have to implement `ParameterProviderInterface`. JSON file provider is already provided by this bundle, more powerful than Symfony built-in "json:" env variable processor.

```yaml
#app/config/config.yml

ojezu_dynamic_parameter:
    advanced_parameters:
        json_provider:
            file_path: '%kernel.root_dir%/config/config.json'
        processor:
            parameter_map:
                database_host: { path: ['database', 'host'] }

doctrine:
    dbal:
        driver:   pdo_mysql
        server_version: 5.7
        host:     "%env(ojezu_param:database_host)%"
```

This configuration will find database.host value in JSON config file, and provide it to DBAL configuration.

#### Using them together

While both features offer more than what's built in Symfony 3.4, using them together allows for easy management of multiple configurations supported by same Symfony application.

```yaml
#app/config/config.yml

ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        json_provider:
            file_path: '%kernel.root_dir%/config/config.json'
        processor:
            parameter_map:
                database_name: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'name'] }

doctrine:
    dbal:
        driver:   pdo_mysql
        server_version: 5.7
        host:   "mysql.example.com"
        dbname: "%env(ojezu_param:database_name)%"
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

3. Make sure that in all places where kernel is created in your application, it is provided with `Installation` instance. Kernel is usually created by `web/*.php` or `public/*.php` files, but remember to modify your `bin/console` too.

    ```php
    <?php
    (...)
    $installation = new Installation($requestedInstallation);
    $kernel = new AppKernel($installation, $env, $debug);
    ```

   Complete examples can be found in `doc/examples` directory of this repository.

4. In `bin/console` be sure to also swap `Application` with one provided by this bundle, if you want to specify installation via CLI option - otherwise parsing of argv may introduce problems.

Complete examples can be found in `doc/examples` directory


#### Advanced parameter provider

You must provide mapping for supported parameters. It is required due to limitations in `%env(processor:variable)%` syntax, and to allow paths that can be easily adapted to any configuration storage.

```yaml
ojezu_dynamic_parameter:
    advanced_parameters:
        json_provider:
            file_path: '%kernel.root_dir%/config/config.json'
        processor:
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

In paths other parameters can be used, including `ojezu_installation` parameters.

```yaml
ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        json_provider:
            file_path: '%kernel.root_dir%/config/config.json'
        processor:
            parameter_map:
                database_host: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'host'] }
                database_name: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'name'] }
                database_user: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'user'] }
```

##### Changing providers

You can swap out json_provider for any other service implementing `OJezu\DynamicParameterBundle\Service\ParameterProviderInterface` interface, by removing json_provider section, and adding provider configuration.

```yaml
ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        provider:
            service: 'MyAppBundle\Services\RedisParameterProvider' # this is service! not class.
        processor:
            parameter_map:
                database_host: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'host'] }
                database_name: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'name'] }
                database_user: { path: ['installation', '%env(ojezu_installation:name)%', 'database', 'user'] }
```

Keep in mind that your provider is a service - it can have its arguments injected, it can be tagged etc. As long as there is no cycle it will work like any other service (no use trying to inject `ojezu_param`s there!


##### Defaults?

Yes.

```yaml
ojezu_dynamic_parameter:
    advanced_parameters:
        json_provider:
            file_path: '%kernel.root_dir%/config/config.json'
        processor:
            parameter_map:
                database_host:
                    path: ['database', 'host']
                    default: 'localhost'
```

Keep in mind that your provider is a service - it can have its arguments injected, it can be tagged etc. As long as there is no cycle it will work like any other service (no use trying to inject `ojezu_param`s there!

##### No config mode

In some instances there is no configuration to be loaded - e.g. when warming cache. For those instances there is no config mode, in which provider won't be used, and all variables will be resolved to null, unless given explicit value for use in those scenarios. *Defaults won't be used.*

Enable it by using `load_configuration` option in processor section:

```yaml
ojezu_dynamic_parameter:
    multi_installation: true
    advanced_parameters:
        json_provider:
            file_path: '%kernel.root_dir%/config/config.json'
        processor:
            load_configuration: '%env(bool:ojezu_installation:name)%'
            parameter_map:
                database_host: {path: ['installation', '%env(ojezu_installation:name)%', 'database', 'host']}
                log_channel: {path: ['log', '%env(ojezu_installation:name)%'], default: 'default', no_config_value: 'default'}
                bucket_name: {path: ['buckets', '%env(ojezu_installation:name)%'], no_config_value: '%env(LOCAL_BUCKET)%'}
```

### Extending this bundle

Points for expansion are

 * `Installation` value object for ojezu_installation
 * Parameter providers

If you need more options from `Installation`, extend that class with additional public properties or methods, and use your extended class in its place.

New providers can be written by extending `OJezu\DynamicParameterBundle\Service\ParameterProviderInterface` and configured as described in Configuration part of this ReadMe

### Testing

would be nice.


License
===
MIT
