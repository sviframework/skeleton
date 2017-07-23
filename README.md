SVI is the Silex voracious interface

This is common code which provides own ORM based on DBAL, twig, controllers, Bundles and etc using Silex and Symfony bundles as base.

SVI requires PHP 7.0 as a minimum.

# Installing

To install SVI you must cd into your project directory and run <br/> 
`git clone --depth=1 https://github.com/shibaon/svi.git .`

Don't forget to remove binding to the SVI git repository: `rm -Rf .git`

Next steps:

1. cd to your new project directory
2. cp app/config/config.php.dist app/config/config.php #This file is main config of your project.
3. cp app/config/parameters.php.dist app/config/parameters.php #and make needed changes in new file: db config, mail, etc.<br/>
Don't forget to generate a key for encryption ("secret"), it's very important for security
4. cp app/config/bundles.php.dist to app/config/bundles.php #This file declares bundles which will be loaded and accessible in your project.
5. chmod 777 for app/cache/
6. chmod 777 for app/logs/
7. cp web/.htaccess.dist web/.htaccess #(if you use nginx you know what to do)
8. Do not forget to config nginx, if you use that, for file web/files/.htaccess
9. chmod 777 for web/files
10. cp composer.json.dist composer.json
11. wget https://getcomposer.org/composer.phar
12. php composer.phar install

See https://github.com/shibaon/svi/wiki for Documentation and Samples.