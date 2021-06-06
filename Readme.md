# MVP - Minimum Viable Product

> _For PHP using composer initiated AutoLoad (PSR-4)_

In deployment PHP is easily maintained using Composer. Composer handles distribution and updating.

This simple agnostic example is extended using a _Markdown Parser_.

## What can I do with this ?

> _Easily extend the environment - and to publish the extension for use in this and any PSR-4 enviroment - See example at <https://github.com/bravedave/pages>_

### Install (Windows 10)

1. Install Pre-Requisits
   1. Install PHP : <http://windows.php.net/download/>
      * Install the non threadsafe binary
        * Test by running php -v from the command prompt
          * If required install the VC++ runtime available from the php > download page
        * by default there is no php.ini (required)
          * copy php.ini-production to php.ini
   2. Install Git : <https://git-scm.com/>
      * Install the *Git Bash Here* option
   3. Install Composer : <https://getcomposer.org/>

2.Setup a new project

```bash
composer create-project bravedave/mvp <my-project> @dev
```

3.Install dependencies &amp; run

```bash
cd <my-project>
composer update
run.cmd
```

> ... the result is visible at <http://localhost/>

### Extend with _erusev/parsedown_

* Install Extension &amp; run

```bash
composer require erusev/parsedown
run.cmd
```

* remove Extension

```bash
composer remove erusev/parsedown
```

### Extend with bravedave/pages

* Install Extension &amp; run

```bash
composer require bravedave/pages
run.cmd
```

* remove Extension

```bash
composer remove bravedave/pages
```

### more

> Look at _src/app/launcher.php_ ...
