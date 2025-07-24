# AuthBundle

Symfony Bundle that provides some utilities when working with Symfony authentication.

```bash
composer req survos/auth-bundle
```

wget -O - https://raw.githubusercontent.com/<username>/<project>/<branch>/<path>/<file> | bash

```bash
ciine rec auth-demo.cast 
symfony new auth-demo --webapp --version=next && cd auth-demo
echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" > .env.local

# this is just Symfony
composer config extra.symfony.allow-contrib true
composer require symfonycasts/verify-email-bundle
sed -i "s|# MAILER_DSN|MAILER_DSN|" .env
bin/console make:user --is-entity --identity-property-name=email --with-password User -n
echo ",,," | sed "s/,/\n/g"  | bin/console make:security:form-login

bin/console make:controller AppController
sed -i "s|/app|/|" src/Controller/AppController.php 

echo ",,no,admin@test.com,AuthDemoBot,yes,app_homepage,no" | sed "s/,/\n/g"  | bin/console make:registration-form
bin/console doctrine:schema:update --force
symfony server:start -d

echo "import '@picocss/pico';\n" >> assets/app.js
echo "import '@picocss/pico/css/pico.min.css';\n" >> assets/app.js


cat > templates/app/index.html.twig <<END
{% extends 'base.html.twig' %}
{% block body %}
    <div>
        <a href="{{ path('app_app') }}">Home</a>
    </div>

    {% if is_granted('IS_AUTHENTICATED_FULLY') %}
        <a class="btn btn-primary" href="{{ path('app_logout') }}">Logout {{ app.user.email }} </a>
    {% else %}
        <a class="btn btn-primary" href="{{ path('app_register') }}">Register</a>
        <a class="btn btn-secondary" href="{{ path('app_login') }}">Login</a>
    {% endif %}
{% endblock %}
END
symfony open:local

# add survos/auth-bundle to create users from the CLI
composer config allow-plugins.endroid/installer true
composer req survos/auth-bundle
bin/console survos:user:create admin@test.com password --roles ROLE_ADMIN
bin/console survos:user:create bob@test.com password
bin/console survos:user:create carol@test.com password
symfony server:start -d
symfony open:local --path=/login


```

## Deprecated

```bash
sed  -i "s|some_route|app_app|" src/Security/AppAuthenticator.php
sed  -i "s|// return new|return new|" src/Security/AppAuthenticator.php
sed  -i "s|throw new|//throw new|" src/Security/AppAuthenticator.php
```

