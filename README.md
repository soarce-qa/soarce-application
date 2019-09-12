# soarce/application [![Packagist](https://img.shields.io/packagist/dt/soarce/application.svg)](https://packagist.org/packages/soarce/application)

## Version: 0.12.1

## Overview

This package is the application part of SOARCE - a tool for collecting, reading and analyzing (PHP) code coverage
and function call traces within a service oriented architecture / microservice environment.
The application receives and stores the information from all services and allows filtering the coverage by usecase
and even request within a use case. And reverse search is also possible by seeing which usecases touch a certain
line of code or call a certain function.
The client (soarce/client) has to be installed per service as a dev requirement - see it's documentation.
This main application comes with a docker-container for each PHP, NginX, MySQL and Redis server.

## Installation

This covers only the basic installation of the `soarce/application` package.
We assume you have git, composer, docker and docker-compose already installed on your system. 

You can either download or clone the project from GitHub and run composer manually:
```
git clone https://github.com/soarce-qa/soarce-application.git
composer install --prefer-dist
```

Or use composer to install the project directly:
```
composer create-project "soarce/application" --prefer-dist
```

Then build and start the containers with a simple invocation of
```
docker-compose up
```

When docker has finished building and starting the containers, you can open your webbrowser and point it to
either https://localhost:8444  or  http://localhost:8001 - although we recommend setting up a an entry
in /etc/hosts that points to `soarce.local`.

## Further topics

Further topics are covered in the Documentation available inside the application.
