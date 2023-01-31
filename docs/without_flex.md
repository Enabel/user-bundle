# Applications that don't use Symfony Flex

## Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
composer require enabel/user-bundle
```

## Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Enabel\UserBundle\EnabelUserBundle::class => ['all' => true],
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => ['all' => true],
];
```

## Step 3: Import routing configuration

enable the routes by adding it to the list of registered routes
in the `config/routes.yaml` file of your project:

```yaml
# config/routes.yaml

enabel_user:
  resource: "@EnabelUserBundle/config/routes.yaml"
```

## Step 4: Create the configuration

Create a file `/config/packages/enabel_user.yaml` with this content:

```yaml
enabel_user:
  login_redirect_route: 'app_home'
  user_class: 'App\Entity\Enabel\User'
  user_repository: 'App\Repository\Enabel\UserRepository'
  available_locales: 'fr|en'

knpu_oauth2_client:
  clients:
    azure_o365:
      type: azure
      client_id: '%env(AZURE_CLIENT_ID)%'
      client_secret: '%env(AZURE_CLIENT_SECRET)%'
      api_version: 'v1.0'
      url_api: 'https://graph.microsoft.com/'
      redirect_route: enabel_azure_check
```

Add the Azure variable in you .env file: 

```dotenv
###> enabel/user-bundle ###
AZURE_CLIENT_ID=paste_here_the_client_id
AZURE_CLIENT_SECRET=paste_here_the_secret_id
###< enabel/user-bundle ###
```

## Step 5: Create entity & repository

Create a entity and repository that extends the bundle one.

The User entity `/src/entity/Enabel/User.php`
```php
<?php

declare(strict_types=1);

namespace App\Entity\Enabel;

use App\Repository\Enabel\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Enabel\UserBundle\Entity\User as BaseUser;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends BaseUser
{
}
```

The according repository `/src/Repository/Enabel/UserRepository.php`
```php
<?php

declare(strict_types=1);

namespace App\Repository\Enabel;

use App\Entity\Enabel\User;
use Doctrine\Persistence\ManagerRegistry;
use Enabel\UserBundle\Repository\UserRepository as BaseUserRepository;

/**
 * @method User|null   find($id, $lockMode = null, $lockVersion = null)
 * @method User|null   findOneBy(array $criteria, array $orderBy = null)
 * @method array<User> findAll()
 * @method array<User> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends BaseUserRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
```

## Step 6: Create the admin crud controller

Create a easyadmin crud controller that extends the bundle one.

To manage user `/src/Controller/Admin/Enabel/UserCrudController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controller\Admin\Enabel;

use App\Entity\Enabel\User;
use Enabel\UserBundle\Controller\Admin\UserCrudController as BaseUserCrudController;

class UserCrudController extends BaseUserCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
}
```

## Step 7: Setup the database

```bash
bin/console make:migration
bin/console doctrine:migration:migrate
```

# Usage

## Authentication:

To enable the authentication follow [these instructions](authentication.md)

## Easyadmin:

To manage users in your Easyadmin dashboard follow [these instructions](easyadmin.md)

## Command:

This bundle come with a bunch of commands, [here](command.md) is the documentation
