# Prerequisites:

1. Know how to use composer
2. Mysql server
  - Create a database (dbname: `dev_hangman`, collation `utf8_general_ci` might be nice to choose)
  - Create a user that has permissions for this db (username: `dev_hangman`) and remember the password


# Installation:

1. git clone https://github.com/dhensen/Hangman.git
2. cd Hangman
3. run `php composer.phar install`
  * Symfony prompts to fill in some missing parameters:
    * database_driver => pdo_mysql
    * database_host => 127.0.0.1 or whatever ip you want to run it on
    * database_port => null for default or whatever super special port you want
    * database_name => dev_hangman or whatever name you like
    * database_user => dev_hangman is the user I created
    * database_password => <fil_in_your_password_for_the_user_in_previous_step>
    * mailer_transport, mailer_host, mailer_user, mailer_password => just press enter for defaults
    * locale => press enter for defaults
    * secret => type your secret
    * debug_toolbar => press enter
    * debug_redirects => press enter
    * use_assetic_controller => press enter
4. run `php app/console doctrine:schema:update --force`
  * Your database is created
5. run `phpunit -c app src/Dino/HangmanBundle/Tests/`
  * If you followed all steps correctly you will see all tests passing
  * (make sure you are running with the phpunit version that comes with this install and not some older version defined in your PATH envvar)
6. run `php app/console server:run` to run the php webserver.
7. Use the url that is reported in the previous step to test the API


### Hangman API

| Method  | URL        | Description  |
| ------- |----------- | ------------ |
| POST    | /games     | Starts a new game, returns JSON data containing: word, tries_left, status                             |
| GET     | /games     | Returns JSON data containing all game data                                                            |
| GET     | /games/:id | Returns JSON data containing: word, tries_left, status for game with given id                         |
| POST    | /games/:id | Guesses char=<char> and returns JSON data containing: word, tries_left,status, for game with given id |

### Test result:
```
PHPUnit 4.3.4 by Sebastian Bergmann.

Configuration read from C:\Apache24\htdocs\Hangman\app\phpunit.xml.dist

...................................

Time: 1.3 seconds, Memory: 44.75Mb

OK (35 tests, 62 assertions)
```

