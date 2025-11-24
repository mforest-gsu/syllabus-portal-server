# mforest-gsu/syllabus-portal-server

API backend for Georgia State University's Syllabus Portal.

## Building/Deploying to production

1. Build package
```shell
$ tar zcvf \
    var/syllabus-portal-server.tar.gz \
    bin \
    config \
    migrations \
    public \
    src \
    .env \
    .gitignore \
    composer.* \
    phpcs.xml \
    phpstan.neon \
    README.md \
    symfony.lock
```

2. Unpack and install
```shell
$ tar zxf syllabus-portal-server.tar.gz
$ composer install --optimize-autoloader
```

3. Check you `.env` files to ensure they look correct

4. Clear Symfony cache and build local database
```shell
$ bin/console cache:clear
$ bin/console doctrine:database:create
$ bin/console doctrine:migrations:migrate
```

Finally, check the permissions on the creates SQLite database `${APP_HOME}/var/SyllabusPortal.db` to make sure that both
the cron user and web user has read/write access to it.
```shell
chmod 664 var/SyllabusPortal.db
```
