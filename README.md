## Installation
After cloning project run this command.
```shell
docker-compose up -d
```

Then copy `.env.example` to same directory and rename it to `.env`.

Then by running followup command application will be ready for use.
```shell
docker-compose run --rm app composer install
```

## Login
Now you can go to http://localhost:8000/dashboard/login and login with user or admin.

User credentials:

- email: user@example.com
- password: password

Admin credentials:

- email: admin@example.com
- password: password
