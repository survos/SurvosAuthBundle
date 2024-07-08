## FOS User Bundle

This exists for historical purposes only.  It is no longer used.

```bash
symfony new fos-demo --webapp && cd fos-demo
sed -i 's/"php": "8.1.0"//' composer.json 
composer config extra.symfony.allow-contrib true

cat > config/packages/fos_user.yaml <<END
fos_user:
            db_driver: orm # other valid values are 'mongodb' and 'couchdb'
            firewall_name: main
            service:
              mailer: fos_user.mailer.noop
            user_class: App\Entity\User
            from_email:
                address: "tt@survos.com"
                sender_name: "tt"
END

cat > config/routes/fos_user.yaml <<END
fos_user:
            resource: '@FOSUserBundle/Resources/config/routing/all.xml'
END

composer require friendsofsymfony/user-bundle "^3.0"

bin/console make:entity User
sed  -i "s/class User/class User extend FosBase/"  src/Entity/User.php

# bin/console make:user --is-entity --identity-property-name=email --with-password User -n
echo "1,AppAuthenticator,,," | sed "s/,/\n/g"  | bin/console make:security
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
    <a href="{{ path('fos_user_security_logout') }}">Logout</a>
    {% else %}
    Welcome, visitor!
    <a href="{{ path('fos_user_security_login') }}">Log In</a>

{% endif %}
{% endblock %}
END


composer req survos/auth-bundle
bin/console survos:user:create admin@example.com password --roles=ROLE_ADMIN
bin/console survos:user:create tacman@gmail.com password
symfony open:local --path=/login

symfony server:start -d

```

```yaml

```
