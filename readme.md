<p align="center"><img src="https://www.voxie.com/wp-content/uploads/elementor/thumbs/VOXIE-black-o1ol2ora2qld6vodwmsrb0qd5dj7ere5midef05xk8.png" width="400"></p>

## Small Test for Voxie Systems, Inc.

Given a table that has the following fields for contacts (named fields below) build a Laravel app that will
take an uploaded CSV file, reads out the columns and allow the user to map their CSV's columns  to the table's fields.
Once done import the file into the contacts table. Anything that is not mapped import into a "custom_attributes" table
which will have "key" and "value" columns that would correlate to the CSV column and row value.
Make sure to include unit/feature tests along with your code.

## Requirements

* PHP >= 7.2
    * BCMath PHP Extension
    * Ctype PHP Extension
    * JSON PHP Extension
    * Mbstring PHP Extension
    * OpenSSL PHP Extension
    * PDO PHP Extension
    * Tokenizer PHP Extension
    * XML PHP Extension
* Composer
* MySQL
* Redis
* npm

## Install

1. `git clone https://github.com/tpenaranda/voxie_test`

2. Create an empty mysql database.

3. Rename `.env.example` file to `.env` and configure DB values

4. `composer update`

5. `php artisan migrate`

6. `php artisan key:generate`

7. `npm install`

8. `npm run dev`

9. `php artisan serve` to start dev server.

## Tests (PHP SQLite driver required)

`vendor/phpunit/phpunit/phpunit`
