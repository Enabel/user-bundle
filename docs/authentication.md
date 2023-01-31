# Authentication

## Configure encoders

```yaml
security:
    ...
    password_hashers:
        ...
        Enabel\UserBundle\Entity\User: argon2i
```

## Configure providers

```yaml
security:
    ...
    providers:
        ...
        enabel_user_provider:
            id: enabel_user.provider
```

## Configure firewall

```yaml
security:
    ...
    firewalls:
        ...
        main:
            lazy: true
            provider: enabel_user_provider
            custom_authenticator:
                - enabel_user.local_authenticator
                - enabel_user.azure_authenticator
            # https://symfony.com/doc/current/security/impersonating_user.html
            switch_user: { role: ROLE_ALLOWED_TO_SWITCH }
            remember_me:
                secret: "%env(APP_SECRET)%"
                name: ENABEL_REMEMBER_ME
                lifetime: 31536000
                path: /
                remember_me_parameter: _remember_me
            logout:
                path: enabel_logout
                target: enabel_login

```

## Configure roles

```yaml
security:
    ...
    role_hierarchy:
        ...
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
        ROLE_ADMIN: [ROLE_USER]
        ROLE_USER: []
```

## Setup access control

```yaml
security:
    ...
    access_control:
        ...
        - { path: ^/auth/login$, roles: PUBLIC_ACCESS }
        - { path: ^/auth/logout, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_ADMIN }
```
