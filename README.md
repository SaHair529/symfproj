Before running next commands you need to install [symfony cli](https://symfony.com/download)

Commands for running up:
- docker-compose up -d
- symfony composer install
- symfony composer update
- symfony console doctrine:migrations:migrate
- symfony serve -d

New user registration command:
- symfony console app:create-user {login} {password}
Example:
- symfony console app:create-user admin admin

New token generation command:
- symfony console app:create-token {login} {password}
Example:
- symfony console app:create-token admin admin