In order to start the application, you need to have the following installed:
- [PHP](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/download)
- [MySQL](https://dev.mysql.com/downloads/mysql/)
- [Apache](https://httpd.apache.org/download.cgi)

Once you have installed the above, you can start the application by following the steps below:
- You need to create a virtual host for the application. You can do this by adding the following to your `httpd-vhosts.conf` file:
```apache
<VirtualHost *:80>
    ServerName ddmarket.local
    DocumentRoot "C:/path/to/ddmarket/public"
    <Directory "C:/path/to/ddmarket/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
- You also need to configure your database connection in the `App/Config.php` file.
- You can also put your own secret key for hashing in the `App/Config.php` file.
- Run `composer install` to install the dependencies.
