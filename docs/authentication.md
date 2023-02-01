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
        - { path: ^/auth/login, roles: PUBLIC_ACCESS }
        - { path: ^/azure/login, roles: PUBLIC_ACCESS }
        - { path: ^/azure/check, roles: PUBLIC_ACCESS }
        - { path: ^/auth/logout, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_ADMIN }
        ...
```

## Azure

### Client & secret id
You need to request a new clientId and clientSecret for a new application on Azure.

1. Go to `Azure portal` https://portal.azure.com
2. Go to `Active Directory`
3. Go to `App registrations`
4. Click on `new Registration` and follow the wizard.  
   (give it a name like 'auth-connector-APP_NAME' and make a decision on the supported accounts, single tenant should be enough but this depends on your organisation)
5. When created the application is created write down the following details
6. 'Application (client) id', this will be your `AZURE_CLIENT_ID`
7. Then we go in the menu to the `API permissions` to view the permissions that are required
8. You should see that the following delegated permissions are granted:  
   Microsoft Graph:
    - User.Read
9. Click on the `Grant admin consent for ...Company...`
10. Go in the menu to `Certificates & secrets`
11. Click on `new client secret`
12. Give it a description and expiry date and the value will be your `AZURE_CLIENT_SECRET`
13. Then we go in the menu to the `Manifest` to accept the additional information that is returned in the token [see the user details section](#user-details-from-azure-ad)
14. Change the value of `acceptMappedClaims` from `null` to `true`
15. Finally, go in the menu to `Authentication`
16. Click on `Add a platform`
17. Choose Web applications `Web`
18. Fill the info & click on `Configure`
    - Redirect URIs: `https://127.0.0.1:8000/azure/check`
    - Front-channel logout URL: leave blank
    - Implicit grant and hybrid flows: Select `ID tokens`
19. Optionally, you can add more URIs like for testing environments or also localhost by clicking on `Add URI`
20. That's it, include the previous information `AZURE_CLIENT_ID` & `AZURE_CLIENT_SECRET` in your `.env.local` file.

Example:
```dotenv
AZURE_CLIENT_ID=19b725a4-1a39-5fa6-bdd0-7fe992bcf33c
AZURE_CLIENT_SECRET=kW74Q~.nWu9HVZ7Rnj.2y][x9.cQTuef:et_
```

### User details from Azure AD

The authenticator needs additional information from Azure AD for the user's profile.
Follow these steps to add this information to the authentication token.

1. Go to `Azure portal` https://portal.azure.com
2. Go to `Active Directory`
3. Go to `App registrations`
4. Retrieve the application registered in [the previous step](#client--secret-id)
5. In the overview, go to `Managed application in local directory: auth-connector-...`
6. Go to `Single sign-on` and edit/add the Attributes & Claims as follows:
     
    | Claim name  | Type | Value                  |
    |-------------|------|------------------------|
    | country     | JWT  | user.country           |
    | displayName | JWT  | user.displayname       |
    | employeeId  | JWT  | user.employeeid        |
    | jobTitle    | JWT  | user.jobtitle          |
    | language    | JWT  | user.preferredlanguage |

7. That's it, you will now be able to connect with your Azure account.