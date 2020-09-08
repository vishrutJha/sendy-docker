#  Sendy built with Docker Compose on LAMP Stack

Basic Setup Code Fudged from [This Awesome Source](https://github.com/sprintcube/docker-compose-lamp)

A basic LAMP stack environment With Sendy Setup and Init (4.5)

* PHP
* Apache
* MySQL
* Sendy
* phpMyAdmin
* Redis

As of now, we have several different PHP versions. Use appropriate php version as needed:

* 5.4.x
* 5.6.x
* 7.1.x
* 7.2.x
* 7.3.x
* 7.4.x

> Please note that we simplified the project structure from several branches for each php version, to one centralized master branch. Please let us know if you encouter any problems. 
##  Installation
 
* Clone this repository on your local computer
* configure .env as needed 
* Run the `docker-compose up -d`.

```shell
git clone https://github.com/sprintcube/docker-compose-lamp.git
cd docker-compose-lamp/
cp sample.env .env
// modify sample.env as needed
vim www/includes/connfig.php
// modify credentials as set in .env
docker-compose up -d
// visit localhost
```

Your Sendy Setup is now ready!! You can access it via `http://localhost`.

##  Configuration and Usage

### General Information 
This Docker Stack is build for local development and not for production usage.

### Configuration
This package comes with default configuration options. You can modify them by creating `.env` file in your root directory.
To make it easy, just copy the content from `sample.env` file and update the environment variable values as per your need.

### Configuration Variables
There are following configuration variables available and you can customize them by overwritting in your own `.env` file.

---
#### PHP
---
_**PHPVERSION**_
Is used to specify which PHP Version you want to use. Defaults always to latest PHP Version. 

_**PHP_INI**_
Define your custom `php.ini` modification to meet your requirments. 

---
#### Apache 
---

_**DOCUMENT_ROOT**_

It is a document root for Apache server. The default value for this is `./www`. All your sites will go here and will be synced automatically.

_**VHOSTS_DIR**_

This is for virtual hosts. The default value for this is `./config/vhosts`. You can place your virtual hosts conf files here.

> Make sure you add an entry to your system's `hosts` file for each virtual host.

_**APACHE_LOG_DIR**_

This will be used to store Apache logs. The default value for this is `./logs/apache2`.

---
#### Database
---

_**DATABASE**_
Define which MySQL or MariaDB Version you would like to use. 

_**MYSQL_DATA_DIR**_

This is MySQL data directory. The default value for this is `./data/mysql`. All your MySQL data files will be stored here.

_**MYSQL_LOG_DIR**_

This will be used to store Apache logs. The default value for this is `./logs/mysql`.

---
#### Sendy
---

Use the sendy documentation to setup the rest of the things

* AWS Account + IAM Account with SES, SNS access
* SES Credentials + Sending Quotas
* Have Key and Access token Ready

Setup the config.php file in /www/includes/config.php
As per the settings in your *.env* file

```php
  $dbUser = 'docker'; //MySQL Username
  $dbPass = 'docker'; //MySQL Password
  $dbName = 'docker'; //MySQL Database Name
```

## Web Server

Apache is configured to run on port 80. So, you can access it via `http://localhost`.

#### Apache Modules

By default following modules are enabled.

* rewrite
* headers

> If you want to enable more modules, just update `./bin/webserver/Dockerfile`. You can also generate a PR and we will merge if seems good for general purpose.
> You have to rebuild the docker image by running `docker-compose build` and restart the docker containers.

#### Connect via SSH

You can connect to web server using `docker-compose exec` command to perform various operation on it. Use below command to login to container via ssh.

```shell
docker-compose exec webserver bash
```

## PHP

The installed version of depends on your `.env`file. 

#### Extensions

By default following extensions are installed. 
May differ for PHP Verions <7.x.x

* mysqli
* pdo_sqlite
* pdo_mysql
* mbstring
* zip
* intl
* mcrypt
* curl
* json
* iconv
* xml
* xmlrpc
* gd

> If you want to install more extension, just update `./bin/webserver/Dockerfile`. You can also generate a PR and we will merge if it seems good for general purpose.
> You have to rebuild the docker image by running `docker-compose build` and restart the docker containers.

## phpMyAdmin

phpMyAdmin is configured to run on port 8080. Use following default credentials.

http://localhost:8080/  
username: root  
password: tiger

## Redis

It comes with Redis. It runs on default port `6379`.

## Contributing
We are happy if you want to create a pull request or help people with their issues. If you want to create a PR, please remember that this stack is not built for production usage, and changes should good for general purpose and not overspecialized. 
> Please note that we simplified the project structure from several branches for each php version, to one centralized master branch.  Please create your PR against master branch. 
> 
Thank you! 

## Why you shouldn't use this stack unmodified in production
We want to empower developers to quickly create creative Applications. Therefore we are providing an easy to set up a local development environment for several different Frameworks and PHP Versions. 
In Production you should modify at a minimum the following subjects:

* php handler: mod_php=> php-fpm
* secure mysql users with proper source IP limitations


## Local Dev - Non Docker

Pre-Requisites : Locally running
* Apache 2
* PHP 5.4+
* php-curl and php-xml
* MySQL 5.5+

1. To Setup LAMP Stack use : This Source
https://gist.github.com/EmpireWorld/737fbb9f403d4dd66dee1364d866ba7e#next-steps

2. Modify the config.php file with your local development DB credentials

3. Copy /www/* to your local /var/www/html/

