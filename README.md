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

## Rebuilding front-end assets (Stimulus)

Since the move to Stimulus (`@hotwired/stimulus` + `@symfony/stimulus-bridge`), front-end assets are compiled with Webpack Encore, driven by yarn.

* From the `nodejs` container :
    ```shell
    make node-shell    # open a shell in the nodejs container
    yarn build         # encore dev, one-shot build
    yarn build:prod    # encore production (minified/versioned assets)
    yarn watch          # encore dev --watch, recompiles automatically on every change
    ```
  or directly without entering the container :
    ```shell
    make node-watch     # runs yarn watch inside the nodejs container
    ```

* Stimulus controllers are declared in `assets/controllers.json` (app controllers and third-party packages, e.g. `@gally/sylius-plugin`) and merged with `assets/shop/controllers.json` / `assets/admin/controllers.json` via `enableStimulusBridge()` in `webpack.config.js`. If you add or change an entry in one of these `controllers.json` files (new Gally plugin controller, `enabled`/`fetch` change...), you need to rerun `yarn build` (or restart `yarn watch`) so Webpack Encore regenerates the compiled `.controllers.json` file under `public/build/`.

* If changes don't show up in the browser after a rebuild :
    ```shell
    docker compose exec php bin/console assets:install --symlink
    docker compose exec php bin/console cache:clear
    ```
  and clear the browser cache if needed (assets are versioned in production via `enableVersioning`, but not in dev).
