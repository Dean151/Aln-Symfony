# Aln-Symfony

Aln-Symfony is a replacement for the Aln original API powering pet feeding machines.

It is a pure PHP Symfony implementation of my legacy [Aln-NodeJs](https://github.com/Dean151/Aln-NodeJs) implementation. It relies heavily on tests to prevent regressions.

This API is hosted on [http://api.feedmypet.app](http://api.feedmypet.app). Please use it with your machine if you feel to!

### Why?

Because the original API is [not secure](https://www.thomasdurand.fr/security/iot/how-anyone-could-feed-my-cat/), not maintained, and as of the latest news, is offline. 

### How?

When the original (not-secured) API was still alive, I was able to use it to reverse engineer the whole set of communications between my pet feeding machine, and their server. Then, I could spoof their connection to my server, and make my machine communicate with only my server

## Usage

You will find a hosted version of this server that you can use for your machine without self-hosting the code if you want: 

- [http://api.feedmypet.app](http://api.feedmypet.app)

Above, you'll also find a Swagger UI that will document available endpoints, responses and body requirements for each of them.

### Create a user

- `POST user/register` with your email in the body. It'll send an email with an activation link. For now this link is dead, but note the token in this URL for the next step.
- `POST user/reset/consume` with the above in-url token to get your authorization token.
- In swagger UI ; on the top-right "authorize" button ; add "Bearer <token>" value for making all following calls to be authenticated as yourself
- `GET user/me` will allow to make sure the token is valid, that you're authenticated. And it'll respond with your associated feeders ; and your user id. Note it for next step.
- `PUT user/{id}` with your password to create (or update) your password.

### Login with email

- `POST user/reset` with your email in the body. It'll send an email with an activation link. For now this link is dead, but note the token in this URL for the next step.
- `POST user/reset/consume` with the above in-url token to get your authorization token.
- In swagger UI ; on the top-right "authorize" button ; add "Bearer <token>" value for making all following calls to be authenticated as yourself

### Login with password

- `POST user/login` with your email & password in the body to get your authorization token.
- In swagger UI ; on the top-right "authorize" button ; add "Bearer <token>" value for making all following calls to be authenticated as yourself

### Configure your feeder

Before anything, you need to configure your feeder to communicate with our API.

- Find the local IP of your feeder using the method of your choice (router, other?). The network name of my feeder is `HF-LPT120`.
- Enter that IP address in your navigator ; and enter "admin" "admin" as username/password when prompted.
- On the top-right, switch to english; unless you understand chinese.
- Go to "Other settings"
- Update Server Address to `api.feedmypet.app`
- Make sure that Protocol is `TCP-Client`; and Port ID is `9999`
- Save; and restart your feeder.

### Associate your feeder with your account

- `POST /feeders/associate` with your feeder identifier. The identifier of your feeder is in your feeder manual. You can also get it by scanning the QR Code inside your feeder. Mine is `ALE` followed with 9 digits.
- `GET user/me` will send you the list of associated feeder ids. Note the number associated to your feeder for next calls

### Manage your feeder

- `GET feeders/{id}` will send you current status & parameters of your feeder.
- `PUT feeders/{id}` with a name; to set a name to your feeder. I use the name of my pet for this.
- `POST feeders/{id}/feed` with an amount between 5 and 150 grams. Trigger an immediate meal.
- `PUT feeders/{id}/amount` with an amount between 5 and 150 grams. Set the amount of food distributed when you press the button.
- `PUT feeders/{id}/planning` with a set of meals. Allow to update the planned meals times and amount. Note that hours should be provided in UTC timezone, and is not sensitive to DST.

## Test

Tests run using a Docker environment:

- Install dependencies with `composer install`
- Boot a local environment using `composer boot`.
- Then, run tests using `composer test`.

## Deploy your own self-hosted api

#### Requirements
- PHP 8.1
- MySQL database
- RabbitMQ queuing system

#### Deployment

> Work in progress
