<p align="center">
  <a href="https://movidagrafica.co">
    <img alt="image" src="https://user-images.githubusercontent.com/9261546/176410648-abd10a26-1b44-4269-b42e-91464db88d80.png">
  </a>
</p>

<p align="center">
  <a href="LICENSE.md">
    <img alt="MIT License" src="https://img.shields.io/github/license/adriansalvatori/SVCMS2022?style=for-the-badge" />
  </a>

  <a href="https://packagist.org/packages/salvatori/svcms">
    <img alt="Packagist" src="https://img.shields.io/packagist/v/salvatori/svcms?include_prereleases&style=for-the-badge" />
  </a>

  <a href="https://github.com/roots/adriansalvatori/actions/workflows/ci.yml">
    <img alt="Build Status" src="https://img.shields.io/github/workflow/status/roots/bedrock/CI?style=for-the-badge" />
  </a>

  <a href="https://twitter.com/salvatori_dev">
    <img alt="Follow Adrián Salvatori" src="https://img.shields.io/twitter/follow/rootswp.svg?style=for-the-badge&color=1da1f2" />
  </a>
  
  <a href="">
    <img alt="Laravel" src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  </a>

  <a href="">
    <img alt="Wordpress" src="https://img.shields.io/badge/Wordpress-21759B?style=for-the-badge&logo=wordpress&logoColor=white" />
  </a>
  
</p>

<p align="center">
  <strong>A modern WordPress stack</strong>
</p>

<p align="center">
  <a href="https://roots.io/"><strong><code>Website</code></strong></a> &nbsp;&nbsp; <a href="https://docs.roots.io/bedrock/master/installation/"><strong><code>Documentation</code></strong></a> &nbsp;&nbsp; <a href="https://github.com/roots/bedrock/releases"><strong><code>Releases</code></strong></a> &nbsp;&nbsp; <a href="https://discourse.roots.io/"><strong><code>Support</code></strong></a>
</p>

## Supporting

Tools added: 
- Enhanced Dashboard and UI
- Advanced Custom fields
- Advanced Custom Fields Extended
- Post Layouts
- Soil
- Password Bcrypt
- Stage Switcher
- AntiMalware Security
- Anti Brute-Force Attack Security
- 2FA for Authentication
- reCaptcha v3
- Default SMTP
- Automated SEO with Autodescription
- MiniApp for Banner Design
- MiniApp for Live CRM
- Enhanced User Administration tools
- Enhanced Reaction to Articles
- Enhanced Blocks for Inner Pages and Articles
- Collaboration tool for commenting and tasking on-site

## Overview

Movidagrafica CMS is a modern WordPress stack that helps you get started with the best development tools and project structure.

Much of the philosophy behind Bedrock is inspired by the [Twelve-Factor App](http://12factor.net/) methodology including the [WordPress specific version](https://roots.io/twelve-factor-wordpress/).

## Features

- Better folder structure
- Dependency management with [Composer](https://getcomposer.org)
- Easy WordPress configuration with environment specific files
- Environment variables with [Dotenv](https://github.com/vlucas/phpdotenv)
- Autoloader for mu-plugins (use regular plugins as mu-plugins)
- Enhanced security (separated web root and secure passwords with [wp-password-bcrypt](https://github.com/roots/wp-password-bcrypt))

## Requirements

- PHP >= 7.4
- Composer - [Install](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
- If you're developing locally, you need a local domain *.mg.test for the recaptcha to let you in. 
- If you're developing in a public domain (example.com) you need to ask for domain approval. You can mail adriansalvatori@gmail.com

## Installation

1. Create a new project:
   ```sh
   $ composer create-project salvatori/svcms <project-name> dev-master
   ```
2. Update environment variables in the `.env` file. Wrap values that may contain non-alphanumeric characters with quotes, or they may be incorrectly parsed.

- Database variables
  - `DB_NAME` - Database name
  - `DB_USER` - Database user
  - `DB_PASSWORD` - Database password
  - `DB_HOST` - Database host
  - Optionally, you can define `DATABASE_URL` for using a DSN instead of using the variables above (e.g. `mysql://user:password@127.0.0.1:3306/db_name`)
- `WP_ENV` - Set to environment (`development`, `staging`, `production`)
- `WP_HOME` - Full URL to WordPress home (https://example.com)
- `WP_SITEURL` - Full URL to WordPress including subdirectory (https://example.com/wp)
- `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT`
  - Generate with [wp-cli-dotenv-command](https://github.com/aaemnnosttv/wp-cli-dotenv-command)
  - Generate with [our WordPress salts generator](https://roots.io/salts.html)

3. Add theme(s) in `web/app/themes/` as you would for a normal WordPress site
4. Set the document root on your webserver to Bedrock's `web` folder: `/path/to/site/web/`
5. Import the Default SQL Database located at /config/SVCMS2022/movidagrafica_svcms2022_latest.sql
6. Access WordPress admin at `https://example.com/wp/wp-admin/`


