# Sylius EveryPay Plugin

[EveryPay](https://every-pay.com/) payment method for Sylius/Payum.
Uses [EveryPay version 4 API](https://support.every-pay.com/merchant-support/api/).

Currently only the one-off payment and the callback notification are implemented.
There is no support for any other payment scenarios. 

## TODO
- add tests
- implement missing actions
- split this package (Payum is used outside Sylius too) 

## NOTES
This is not an official integration. Authors of this package are not affiliated with EveryPay in any way.

## Tests

### Running the `dev` env

Create you own `tests/Application/.env.local` like this for example :

```dotenv
###> doctrine/doctrine-bundle ###
DATABASE_URL=mysql://my_login:my_password@127.0.0.1/sylius_everypay_%kernel.environment%
###< doctrine/doctrine-bundle ###

###> symfony/swiftmailer-bundle ###
# For Gmail as a transport, use: "gmail://username:password@localhost"
# For a generic SMTP server, use: "smtp://localhost:25?encryption=&auth_mode="
# Delivery is disabled by default via "null://localhost"
MAILER_URL=smtp://localhost:1025
###< symfony/swiftmailer-bundle ###
```

Initialize the environment :

```shell
composer global config --no-plugins allow-plugins.symfony/flex true
composer global require --no-progress --no-scripts --no-plugins symfony/flex

export APP_ENV=dev
SYMFONY_REQUIRE=^6.0 composer install

(cd tests/Application \
  && bin/console doctrine:database:drop --if-exists --force -vvv\
  && bin/console doctrine:database:create -vvv\
  && bin/console doctrine:migrations:migrate -n -vvv -q\
  && bin/console assets:install public -vvv\
  && yarn install && yarn build\
  && bin/console cache:warmup -vvv\
  && bin/console sylius:fixtures:load -n)
```

Launching a symfony server ([symfony binary required here](https://symfony.com/download)) :

```shell
symfony server:ca:install
APP_ENV=dev symfony serve --port=8080
```

Then open you browser to https://localhost:8080

### Running

### Running plugin tests

Initialize the environment :

- PHPUnit

  ```bash
  vendor/bin/phpunit
  ```

- PHPSpec

  ```bash
  vendor/bin/phpspec run
  ```

- Behat (non-JS scenarios)

  ```bash
  vendor/bin/behat --strict --tags="~@javascript"
  ```

- Behat (JS scenarios)

    1. [Install Symfony CLI command](https://symfony.com/download).

    2. Start Headless Chrome:

    ```bash
    google-chrome-stable --enable-automation --disable-background-networking --no-default-browser-check --no-first-run --disable-popup-blocking --disable-default-apps --allow-insecure-localhost --disable-translate --disable-extensions --no-sandbox --enable-features=Metal --headless --remote-debugging-port=9222 --window-size=2880,1800 --proxy-server='direct://' --proxy-bypass-list='*' http://127.0.0.1
    ```

    3. Install SSL certificates (only once needed) and run test application's webserver on `127.0.0.1:8080`:

    ```bash
    symfony server:ca:install
    APP_ENV=test symfony server:start --port=8080 --dir=tests/Application/public --daemon
    ```

    4. Run Behat:

    ```bash
    vendor/bin/behat --strict --tags="@javascript"
    ```

- Static Analysis

    - Psalm

      ```bash
      vendor/bin/psalm
      ```

    - PHPStan

      ```bash
      vendor/bin/phpstan analyse -c phpstan.neon -l max src/  
      ```

- Coding Standard

  ```bash
  vendor/bin/ecs check src
  ```

### Opening Sylius with your plugin

- Using `test` environment:

    ```bash
    (cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=test bin/console server:run -d public)
    ```

- Using `dev` environment:

    ```bash
    (cd tests/Application && APP_ENV=dev bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=dev bin/console server:run -d public)
    ```
