# Cornelius: Core PrestaShop module

[![compatibility](https://img.shields.io/badge/Compatibility-8.x-brightgreen?style=for-the-badge&logo=prestashop)](https://github.com/tapandagr/tp_cookies_free)

## Description

It adds useful hooks, functions and libraries to PrestaShop

## Use instructions

Let us assume that our module directory is named _**mymodule**_

### 1. Install tables

Tvcore::installTables('mymodule');

It will install

1. The module mandatory tables, if the file **_/sql/tables.php_** exists
2. Table overrides, if the file **_/sql/table_overrides.php_** exists
3. Additional tables sorted by module directory name in the directory **_/sql/additional_**, given the fact that the
   respective modules are enabled

### 2. Uninstall tables

Tvcore::uninstallTables('mymodule');

It will revert the db schema back to its original condition (with the equivalent functions from the install function).

### 3. Install tabs

Tvcore::installTabs('mymodule');

### 4. Import data

Tvcore::importData('mymodule');

### 5. RegisterHooks

Tvcore::registerHooks('mymodule');

There is a directory named **_/sql/demo_** where you can find examples for making your module work smoothly

Bonus: For module developers, you need to add index only in the basic module directory, and add the gist to your admin
directory like any [cronjob](https://gist.github.com/tivuno/ba81febd7ca94b3bce1033b80c86c712)

Feel free to contact us @ [hi@tivuno.com ](mailto:hi@tivuno.com) if you have any questions or just to say hi!
