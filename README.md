# Demo sylius environment

## How to use this environment

* Clone sylius demo repo:
    ```shell
    git clone git@github.com:Elastic-Suite/demo-sylius.git connectors/sylius
    cd connectors/sylius
    ```
* Edit .env file and update the value of :

| Var                 | Description                           | Example value              |
|---------------------|---------------------------------------|----------------------------|
| `SYLIUS_DOMAIN`     | The sylius domain you want to use     | sylius.connector.localhost |
| `GALLY_SERVER_NAME` | The server name you defined for gally | gally.connector.local      |
| `DOCKER_USER`       | Your user id and group id             | 1000:1000                  |

* Install sylius
    ```shell
    make  init
    ```

* Add gally plugin
    ```shell
    git clone git@github.com:Elastic-Suite/gally-sylius-connector.git packages/GallyPlugin
    docker compose exec php composer config repositories.gally-connector '{ "type": "path", "url": "./packages/GallyPlugin", "options": { "versions": { "gally/sylius-plugin": "2.0.0"}} }'
    docker compose exec php composer require gally/sylius-plugin:2.0.0
    ```

* Start your traefik if it is not already running

  > After this step you should have a running sylius instance
  > * Backend: https://sylius.connector.localhost/admin (sylius/sylius)
  > * Frontend: https://sylius.connector.localhost/

* Open Sylius Admin, head to Configuration > Gally and configure the Gally endpoint (URL, credentials). Then you must enable Gally on each channel you need it.

* Run this commands from your Sylius instance. This commands must be runned only once to synchronize the structure.
    ```shell
    bin/console gally:structure:sync   # Sync catalog et source field data with gally
    ```
* Run a full index from Sylius to Gally. This command can be run only once. Afterwards, the modified products are automatically synchronized.
    ```shell
    bin/console gally:index            # Index category and product entity to gally
    ```

And gally connector has been installed according to the doc:
https://github.com/Elastic-Suite/gally-sylius-connector
