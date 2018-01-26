# EnvSwitcher for Magento 1

EnvSwitcher helps you take a production Magento database and update it
such that it can be safely used in other environments (staging, dev, etc.).

## Requirements

Magento 1.x


## Installation

### Install this package
Edit your `composer.json` to require the package
```js
"require-dev": {
    "branchlabs/m1-env-switcher": "1.*"
}
```

Then run composer update in your terminal.  After installation is 
complete, copy the contents of `./stub/` into `./MAGENTO_WEB_ROOT/shell/`:

```bash
# from magento web root
cp -r vendor/branchlabs/m1-env-switcher/stub/EnvSwitcher/ shell/;
```
## Configuration + Setup

#### Define Environments  
In `shell/EnvSwitcher/Migrate.php`, modify the `getAllowedEnvironments()` method 
such that it returns an array of valid environment identifiers where you will run 
migrations.  If you only have a dev and staging environment, it can be left untouched.

#### Update Settings
The files in `shell/EnvSwitcher/config/` should be modified for your needs, most importantly 
`core-config-data.php`.  If you use dotEnv (`env()`), create a `.env` in your working directory
and supply the necessary variables.  It's also not a bad idea to create a `.env.sample` so other
developers know what to expect.

#### Extra Database Updates

You can run custom queries within the `shell/EnvSwitcher/Migrate.php` file via the `MagentoHelper` 
class, if needed:
```php
MagentoHelper::customWriteQuery(
    'UPDATE ' . MagentoHelper::getTableName('some_table') . ' SET some_column = :value;',[
        'value' => 'abc'
    ]
);
```

#### Define Configuration

## Usage

```bash
# from magento web root
php shell/EnvSwitcher/Migrate.php -- --env dev;
```
Replace `dev` with the environment from which the script is being run.

