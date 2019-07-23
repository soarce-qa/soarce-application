# soarce/application

## Version: 0.0.1

## Overview

This package is the application part of SOARCE - a tool for collecting, reading and analyzing PHP code coverage
withing a service oriented architecture / microservice environment. It has to be installed per service as a
dev requirement. It should come with it's own docker container and database.

## Installation

tba

## Configuration

### ENV-Variables

tba

## Known Issues

### Performance

Do not use xdebug in this application, only in the applications and services which are supposed
to be tested.

### Database Keys

We currently use Unique Key Constraints for certain tables so that we don't need transactions, nor
limit writing data to single threads nor spend a lot of CPU time with cleaning up and rewriting hundreds
of thousands of rows. On top of that foreign key constraints with ON DELETE CASCADE are used to delete all
data linked to a usecase when that usecase is re-run. This all causes huge gaps in the primary key sequence.
Because of this we chose quite large integer types.
Until we have a purge command in the GUI, we recommend to clear the database from time to time manually to
reset the keys when you re-run a full test-suite.
For the future we plan to funnel the writing into one process by writing data to redis first, this
will eliminate the need for regular purging and long integers.

