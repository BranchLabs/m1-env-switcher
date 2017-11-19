EnvSwitcher for Magento 1
=========================

EnvSwitcher helps you take a production Magento database and update it
such that it can be safely used in other environments (staging, dev, etc.).

Requirements
--------
Magento 1.x


Installation + Setup
--------

Edit your `composer.json` to require the package
```js
"require": {
    "branchlabs/m1-env-switcher": "1.*"
}
```

Then run composer update in your terminal.  After installation is 
complete, copy the contents of `./stub/` into `./MAGENTO_WEB_ROOT/shell/`:

```bash
# from magento web root
cp -r vendor/branchlabs/m1-env-switcher/stub/EnvSwitcher/ shell/;
```


Usage
--------
TBD

