# Enabel: User Bundle

[![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)
[![SymfonyInsight](https://insight.symfony.com/projects/b4f74722-7cc0-471c-919b-605447c4fb6f/mini.svg)](https://insight.symfony.com/projects/b4f74722-7cc0-471c-919b-605447c4fb6f)
[![codecov](https://codecov.io/gh/Enabel/user-bundle/graph/badge.svg?token=cKRnx9kvSx)](https://codecov.io/gh/Enabel/user-bundle)
[![CI](https://github.com/Enabel/user-bundle/actions/workflows/CI.yml/badge.svg)](https://github.com/Enabel/user-bundle/actions/workflows/CI.yml)

## Introduction

The bundle aims to help with user management, including:

- Forms (Login, SSO Azure)
- Commands to generate, promote and demote users
- A EasyAdmin interface to manage users

## Installation & usage

You can check docs [here](docs/index.md)

## Versions & dependencies

The current version of the bundle works with Symfony 6.0+.
The project follows SemVer.

You can check the [changelog](CHANGELOG.md).

## Contributing

Feel free to contribute, like sending [pull requests](https://github.com/enabel/user-bundle/pulls) to add features/tests
or [creating issues](https://github.com/enabel/user-bundle/issues)

Note there are a few helpers to maintain code quality, that you can run using these commands:

```bash
composer cs # Code style check
composer stan # Static analysis
vendor/bin/simple-phpunit # Run tests
```

