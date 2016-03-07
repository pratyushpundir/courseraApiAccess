# Coursera API Data Access and Export
Uses Laravel 5.2.15 to access the Coursera API and persist data to a MySQL DB. Also provides ability to export the persisted data to Excel files. Updation and export of data (and sends via email) can also be easily scheduled.


# Record Types
 - Courses
 - Partners
 - Instructors


# Pre-Requisites
This is a laravel 5 app so the same requirements apply:

 - PHP >= 5.5.9
 - OpenSSL PHP Extension
 - PDO PHP Extension
 - Mbstring PHP Extension
 - Tokenizer PHP Extension 
 - Composer - https://getcomposer.org/


# Setting Up Instructions
 - GIT CLONE or DOWNLOAD and UNZIP the zip file from this page.
 - Run `composer install` in the root of this directory.
 - Setup a MySQL database on your machine using whatever you want. I prefer MySQL CLI. For GUI options on the Mac - look at http://www.sequelpro.com/
 - Rename the file called `example.env` in the root of this directory to just `.env`. Change details as needed in this file. At the minimum, you will have to enter your database credentials. You should also add your email setup here as needed. Laravel supports "smtp", "mail", "sendmail", "mailgun", "mandrill" and a few others. I chose to go with Mailgun.
 - Run `php artisan key:genrate` to setup the app key.
 - Run `php artisan migrate` to create the databases needed.
 - Run `php artisan serve` in the root. This will launch a simple local server on http://localhost:8000.
 - Visit http://localhost:8000 in a browser of your choice. - NO WORK HAS BEEN DONE ON THE FRONTEND YET SO STAY OFF OF THIS. USE THE COMMANDS DETAILED BELOW INSTEAD.


# Basic Usage Instructions
- Haven't had the time to finish the frontend to so STAY OFF OF IT.
- Besides the frontend, you have terminal access to the main commands:
 - Running `php artisan coursera:update --recordType=courses` will query the Coursera API and update the Database with Courses not yet in our database.
 - Running `php artisan coursera:export --recordType=courses` will export all current database entries for Courses to `.xlsx` files and emails them as per. 
- Other available recordType options usable with above commands - 'partners', 'instructors' and 'all'. Defaults to 'all'. Just replace 'courses' in the above commands to whatever recordType you wish.
- Exported files are stored in `storage/exports/coursera` directory.


# Scheduling Updates and Exports
 - Start the scheduler by setting up the below cron entry (run `crontab -e`)
 - `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`
 - Go to `app/console/kernel.php` and look for the the schedule() method. This is where you can setup when you want the updates to occur and when you want the exports(+email) to occur.
 - Read more about scheduling frequency options here - https://laravel.com/docs/5.2/scheduling.
 - Defaults to 'daily' updates and 'weekly' exports.


# ToDo
 - Abstract a lot of stuff currently in the controller to make them usable for EdX, Udacity and may be more providers. - PARTIALLY DONE (extracted to commands) 
 - Provide a proper frontend rather than the ugly stuff this has right now as an excuse.


# Contact Info
 - skype: pratyushpundir
 - email: pratyushpundir@icloud.com
 - http://www.sublimearts.me