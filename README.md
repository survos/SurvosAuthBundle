# AuthBundle

Symfony Bundle that provides some utilities when working with Symfony authentication.

```bash
composer req survos/auth-bundle
```

```bash
symfony new auth-demo --webapp && cd auth-demo
sed -i 's/"php": "8.1.0"//' composer.json 
composer config extra.symfony.allow-contrib true
bin/console make:user --is-entity --identity-property-name=email --with-password User -n
echo "1,AppAuthenticator,,," | sed "s/,/\n/g"  | bin/console make:auth
echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" > .env.local
bin/console doctrine:schema:update --force --complete
symfony server:start -d
symfony open:local --path=/login

sed  -i "s|some_route|app_homepage|" src/Security/AppAuthenticator.php
sed  -i "s|//return|return|" src/Security/AppAuthenticator.php
sed  -i "s|throw new|//throw new|" src/Security/AppAuthenticator.php


composer req survos/auth-bundle
bin/console survos:create:user 


symfony server:start -d

```
