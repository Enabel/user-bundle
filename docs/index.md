# Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Installation with Symfony Flex

Open a command console, enter your project directory and execute:

```bash
composer require enabel/user-bundle
```

### Setup database

```bash
bin/console make:migration
bin/console doctrine:migration:migrate
```

## Installation without Symfony Flex

For a installation without Symfony Flex, follow [these instructions](without_flex.md)

# Usage

## Authentication:

To enable the authentication follow [these instructions](authentication.md)

## Easyadmin:

To manage users in your Easyadmin dashboard follow [these instructions](easyadmin.md)

## Command:

This bundle come with a bunch of commands, [here](command.md) is the documentation
