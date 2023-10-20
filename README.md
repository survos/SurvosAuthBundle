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

bin/console make:controller AppController
sed -i "s|/app|/|" src/Controller/AppController.php 

sed  -i "s|some_route|app_app|" src/Security/AppAuthenticator.php
sed  -i "s|// return new|return new|" src/Security/AppAuthenticator.php
sed  -i "s|throw new|//throw new|" src/Security/AppAuthenticator.php

cat > templates/app/index.html.twig <<END
{% extends 'base.html.twig' %}

{% block body %}
{% if is_granted('IS_AUTHENTICATED_FULLY') %}
    Welcome, {{ app.user.email }}! (roles: {{ app.user.roles|join(',') }})
    <a href="{{ path('app_logout') }}">Logout</a>
    {% else %}
    Welcome, visitor!
    <a href="{{ path('app_login') }}">Log In</a>
    {{ include('@SurvosAuth/_social_media_login_buttons.html.twig') }}

{% endif %}
{% endblock %}
END


composer req survos/auth-bundle
bin/console survos:user:create admin@example.com password ROLE_ADMIN
bin/console survos:user:create tacman@gmail.com tt
symfony open:local --path=/login

symfony server:start -d

```
