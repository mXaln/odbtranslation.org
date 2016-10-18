# README #

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* V-Mast is a web site for running translation process through 8 steps (MAST)
* It is based on [Novaframework 3.73](http://novaframework.com)

### How do I get set up? ###

* Fork the project to a local server folder
* You should install and activate INTL extension on your server
* PHP 5.6 is a minimal requirement
* Use mysql database. You can find mysql dump in the root directory of repository
* You need to make some changes in files to configure project according to your server:
```
app\Templates\Default\Assets\js\socket.js
  * Set sctUrl

app\Config\App.php
  * Set url

app\Config\Database.php
  * Set database, username and password

app\Config\ReCaptcha.php
  * Set siteKey and secret

Nodejs\server.js
  * Set xhr url
```

### Contribution guidelines ###

* Fork the repository
* Code review
* Other guidelines

### Who do I talk to? ###

* Repo owner or admin
* Other community or team contact