# JOB's module for CMS

## Add to application

```bash
composer config repositories.cms-job git https://github.com/bravedave/cms-job
composer require bravedave/cms-job
```

## Application Utilities

### File Maintenance

Add to composer

```json
{
    "scripts": {
        "post-update-cmd": [
            "cms\\console\\utility::upgrade",
            "cms\\job\\utility::upgrade"
        ]
    }
}
```

The following commands can also be added

* import-contacts - _import contacts from console_
* import-contractors - _import creditors console and convert them to contractors_
* reset-contractors - _cleans and resets contractor tables - useul for development, probably not in release_

```json
{
    "scripts": {
        "import-contacts": "cms\\console\\utility::import_contacts",
        "import-contractors": "cms\\job\\utility::contractors_import",
        "reset-contractors": "cms\\job\\utility::contractors_reset"
    }
}
```
