# Commands

## Create a new user

The `enabel:user:add` command creates new users and saves them in the database:

```bash
  bin/console enabel:user:add email password display-name
```

By default, the command creates regular users.
To create administrator users (ROLE_ADMIN), add the --admin option:

```bash
  bin/console enabel:user:add email password display-name --admin
```

To create super administrator users (ROLE_SUPER_ADMIN), add the --super-admin option:

```bash
  bin/console enabel:user:add email password display-name --super-admin
```

If you omit any of the required arguments, the command will ask you to
provide the missing values:

```bash
    # command will ask you for the password and display-name
    bin/console enabel:user:add email
    
    # command will ask you for all arguments
    bin/console enabel:user:add
```

## Promote a user

The `enabel:user:promote` command promote a user to a administrator:

```bash
  bin/console enabel:user:promote email
```

By default, the command promote the user to a administrator (ROLE_ADMIN).
To promote to super administrator users (ROLE_SUPER_ADMIN), add the --super-admin option:

```bash
  bin/console enabel:user:promote email --super-admin
```

If you omit the email, the command will ask you to
provide the missing values:

```bash
    # command will ask you for the email
    bin/console enabel:user:promote
```

## Demote a user

The `enabel:user:demote` command demote a administrator to a user:

```bash
  bin/console enabel:user:demote email
```

By default, the command demote the administrator to a user.
To demote to super administrator users (ROLE_SUPER_ADMIN), add the --super-admin option:

```bash
  bin/console enabel:user:demote email --super-admin
```

If you omit the email, the command will ask you to
provide the missing values:

```bash
    # command will ask you for the email
    bin/console enabel:user:demote
```
