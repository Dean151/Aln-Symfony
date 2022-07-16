# Aln API

Aln API is a replacement for the Aln original API powering pet feeding machines.

It is a pure PHP Symfony implementation of the legacy [Aln-NodeJs](https://github.com/Dean151/Aln-NodeJs) implementation.

### Why?

Because the original API is [not secure](https://www.thomasdurand.fr/security/iot/how-anyone-could-feed-my-cat/), not maintained, and as of the latest news, is offline. 

### How?

When the original (not-secured) API was still alive, I was able to use it to reverse engineer the whole set of communications between my pet feeding machine, and their server. Then, I could spoof their connexion to my server, and make my machine communicate with only my server

## Install, test and run

#### Requirements
- PHP 8.1
- MySQL database

#### Install

```
composer install
```

#### Run tests

```
# Linter
composer sniff

# Analysis
composer stan

# Unit tests
composer unit

# All of them
composer test
```

#### Serve

Work in progress

## Usage

Work in progress