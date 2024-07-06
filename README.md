### Install Composer
- $ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
- $ php -r "if (hash_file('sha384', 'composer-setup.php') 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
- $ php composer-setup.php
- $ php -r "unlink('composer-setup.php');"

### Install a package with Composer
 - $php composer.phar require htmlburger/carbon-fields

### functions.php
- add css, js, fonts, other static assects here via the wordpress lifecycle hooks
  
### Post Types
- create "mu-plugins" folder in "wp-content"
- create a file eg. custom_post_types.php. See university_post_types.php
- Add your post types to this file
- After creating a post type go into wp-admin interface to permalinks in settings and click "save" to setup your new posttype into the system
- create a custom "archive-YOURPOSTTYPENAME.php" based on the default archive.php file you already have and customize it to your liking.  This page will be used to display your posttype on the fontend
- create a custom "single-YOURPOSTTYPENAME.php" based on the default archive.php file you already have and customize it to your liking.  This page will be used to display your posttype on the fontend