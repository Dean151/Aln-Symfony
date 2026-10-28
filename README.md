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
- `POST user/reset/consume` with the above in-url token to get your _authorization token_.
- In swagger UI ; on the top-right "authorize" button ; add your _authorization token_ for all upcoming calls to be authenticated as yourself
- `GET user/me` will allow to make sure the token is valid, that you're authenticated. And it'll respond with your associated feeders ; and your user id. Note it for next step.
- `PATCH user/{id}` with your password to create (or update) your password.

Please note that POST user/register will send a reset password mail if the email already exists (see below)

### Login with email (a.k.a for password reset)

- `POST user/reset` with your email in the body. It'll send an email with an activation link. For now this link is dead, but note the token in this URL for the next step.
- `POST user/reset/consume` with the above in-url token to get your authorization token.
- In swagger UI ; on the top-right "authorize" button ; add your _authorization token_ for all upcoming calls to be authenticated as yourself

Please note that POST user/reset will sent a 200 response code even if the email does not exists.

### Login with password

- `POST user/login` with your email & password in the body to get your authorization token.
- In swagger UI ; on the top-right "authorize" button ; add your _authorization token_ for all upcoming calls to be authenticated as yourself

### Configure your feeder

Before anything, you need to configure your feeder to communicate with our API.

- Find the local IP of your feeder using the method of your choice (router, other?). The network name of my feeder is `HF-LPT120`.
- Enter that IP address in your navigator ; and enter "admin" "admin" as username/password when prompted.
- On the top-right, switch to english; unless you understand chinese.
- Go to "Other settings"
- Update Server Address to `feeder.feedmypet.app` (or your local IP if you deploy the app yourself)
- Make sure that Protocol is `TCP-Client`; and Port ID is `9999`
- Save; and restart your feeder.

If your feeder still points at `api.feedmypet.app`, change it to `feeder.feedmypet.app`. Both reach the same server for now, but `api.feedmypet.app` will move behind a proxy that only carries HTTPS, and feeders still using it will lose their connection.

### Associate your feeder with your account

- `POST /feeders/associate` with your feeder identifier. The identifier of your feeder is in your feeder manual. You can also get it by scanning the QR Code inside your feeder. Mine is `ALE` followed with 9 digits.
- `GET user/me` will send you the list of associated feeder ids. Note the number associated to your feeder for next calls

### Manage your feeder

- `GET feeders/{id}` will send you current status & parameters of your feeder.
- `PATCH feeders/{id}` with a name; to set a name to your feeder. I use the name of my pet for this.
- `POST feeders/{id}/feed` with an amount between 5 and 150 grams. Trigger an immediate meal.
- `PATCH feeders/{id}/amount` with an amount between 5 and 150 grams. Set the amount of food distributed when you press the button.
- `PATCH feeders/{id}/planning` with a set of meals. Allow to update the planned meals times and amount. Note that hours should be provided in UTC timezone, and is not sensitive to DST.

## Test

Tests run using a Docker environment:

- Install dependencies with `composer install`
- Boot a local environment using `docker-compose up -d`.
- Then, run tests using `composer test`.

## Deploy your own self-hosted api

#### Requirements
- PHP >= 8.3
- Composer
- MySQL database
- RabbitMQ queuing system

#### Deploy your own

- Create a .env.local file with:
```dotenv
APP_ENV=prod

# Generate a secret, see https://stackoverflow.com/questions/60837428/symfony-terminal-command-to-generate-a-new-app-secret
APP_SECRET=<secret>

# Configure your mysql database
# <version> is either mysql (e.g. 5.7) or mariadb (mariadb-10.11.4)
DATABASE_URL="mysql://<user>:<password>@<host>:<port>/<database_name>?serverVersion=<version>&charset=utf8mb4"

# Configure RabbitMQ
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USERNAME=guest
RABBITMQ_PASSWORD=guest
```


- Install dependencies:
```shell
composer install --optimize-autoloader
```


- Even if authentication is left disabled, you need to generate a jwt pair of keys:
```shell
./bin/console lexik:jwt:generate-keypair
```


- Expose the Symfony /public/index.php using your preferred method. (I prefer nginx)\
See https://symfony.com/doc/current/deployment.html


- Start the websocket for your feeder to connect to. Using something like supervisor is recommended.
```shell
./bin/console aln:socket:run
```
Make sure your process manager restarts it whenever it exits, even successfully. With supervisor:
```ini
[program:alnpet-socket]
command=php /path/to/project/bin/console aln:socket:run
user=www-data
autostart=true
autorestart=true
```


- After each deploy, restart the websocket so it runs the new code. It exits gracefully and your process manager starts it again. With `--wait`, the command only succeeds once the new process is up (60 seconds timeout by default, see `--timeout`):
```shell
./bin/console aln:socket:restart --wait
```

Now you can follow the steps for configuring your feeder above, and use it.
If you do not enable authentication, you can skip all the authentication steps and use your feeder directly!

#### Health check

`GET /healthz` reports whether the API and its dependencies are up. It requires no authentication, so you can point an uptime monitor at it.

```json
{"status":"ok","checks":{"database":"ok","queue":"ok","socket":"ok"}}
```

- `database`: the MySQL database answers a query.
- `queue`: RabbitMQ accepts a connection.
- `socket`: the `aln:socket:run` process wrote a heartbeat within the last 30 seconds. It writes one every 10 seconds, into `var/share/<env>/pools`.

It responds `200` when every check is `ok`, and `503` with the failing checks marked `fail` otherwise.

If the socket process runs as a different user than PHP (e.g. `www-data`), make sure PHP can read `var/share/<env>/pools`, or the `socket` check will always fail.

The CI deploy job calls this endpoint after deploying when the `HEALTHCHECK_URL` repository variable is set (e.g. `https://api.feedmypet.app/healthz`).
