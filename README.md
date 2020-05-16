# WordPress Converter
## Synfony 5 Command application. The purpose of this software is to convert WordPress posts into markdown files using WordPress API.

### Symfony Commands
php bin/console --version
php bin/console about
php bin/console cache:clear
php bin/console debug:autowiring

### Commands
php bin/console app:wp-converter example.com posts-to-markdown
php bin/console app:wp-converter example.com posts-to-markdown page=20

### PUPUNIT
php bin/phpunit
php bin/phpunit tests/Command/WPExportPostsToMarkdownCommandTest
php bin/phpunit tests/Service/DataProviderTest

### WordPress Domain list
sonymusic.com
bbcamerica.com
rollingstones.com
cooperhewitt.org
cure.org
sites.lsa.umich.edu

### WordPress endpoints
* /wp-json/wp/v2/posts
* /wp-json/wp/v2/pages
* https://developer.wordpress.org/rest-api/reference/
* https://developer.wordpress.org/rest-api/reference/posts/
* https://developer.wordpress.org/rest-api/using-the-rest-api/pagination/
* https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/

* https://www.unsitoweb.it/indecisi-eccovi-una-lista-di-siti-creati-con-wordpress.html
* https://www.wired.it/wp-json/wp/v2/posts?page=1
* https://dillinger.io/

### Change log
#### 20200516
* Check header: no wp-total-posts, no WordPress
* Fixed  SSL connect error
* Input arguments: type of conversion, domain, page
* Fixed Invalid URL: no "base_uri"
