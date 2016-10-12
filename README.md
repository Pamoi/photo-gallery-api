# photo-gallery-api

This project is a simple REST API to publish and manage photos organized in albums.
It is built with PHP and the [Symfony](https://symfony.com/) framework.

A web client is available to interactively view, comment or add photos shared through
this API: [photo-gallery-webclient](https://github.com/Pamoi/photo-gallery-webclient).

## Installation
To set up the API you have to clone this repository and install the dependencies using
[composer](https://getcomposer.org/):

```bash
git clone https://github.com/Pamoi/photo-gallery-api.git
cd photo-gallery-api
composer install
```

Composer will automatically ask you to enter local parameters such as the database
user and password. You can then create the database needed by the application by
running the following commands:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

You can then add users to the system:

```bash
php bin/console user:add <name> <email> <password>
```

Note that the email field is currently not used.

The poject uses the [image magick](http://www.imagemagick.org/script/index.php)
library. On Debian based systems you can install it by typing:

```bash
sudo apt-gt install php-imagick
```

Finally, serve the `web/app.php` file with your favorite web server.


**Additional note**: do not forget to authorize large enough uploads in your web server
configuration, otherwise the server will reject large photo files. For apache, set
the fields `post_max_size` and `upload_max_filesize` to suitable values (e.g. 20M)
in your `php.ini`.
