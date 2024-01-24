# Manage users in your Easyadmin dashboard

Go to your dashboard controller, example : `src/Controller/Admin/DashboardController.php`

```php
<?php

namespace App\Controller\Admin;

...
use Enabel\UserBundle\Controller\Admin\UserTrait;
use Enabel\UserBundle\Controller\Admin\DashboardTrait;

class DashboardController extends AbstractDashboardController
{
    ...
    use UserTrait;
    use DashboardTrait;

    ...
    public function configureMenuItems(): iterable
    {
        ...
        yield from $this->userMenuEntry();

        ...
```
