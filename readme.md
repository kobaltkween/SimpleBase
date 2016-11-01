# SimpleBase Library

This is a set of simple classes to use as a foundation for building a basic site, with resources that can be seen by everyone and edited by any of the admins.  It is meant to serve as a basis for building a standard web site with REST capabilities, a single SQL database, and only admin users.  

It assumes the structure like
* yourApp
    <classes based on library>
    * init.php
* simplebase
* purifier library folder: If you use the HTMLFilter, you need to include **HTMLPurifier.auto.php**
* public_html
    * .htaccess - routes all requests to index.php
    * index.php - references yourApp/init.php, then handles all the requests
    
It uses the namespace **Kobalt\SimpleBase**.  To get access to all of the classes, require the **init.php** file from the library's folder.  I based the coding style off of the [Flow Framework coding style example] (http://flowframework.readthedocs.io/en/stable/_downloads/TYPO3_Flow_Coding_Guidelines_on_one_page.pdf).

If you want information not found in either this ReadMe or the index.php example and testing file, the source files include comments for the classes, their methods, and their properties.  I've put a lot of effort into the internal documentation, as well as making the code as readable as possible.  If you have any questions or feedback, please contact me at kobaltkween@gmail.com.  

## Installation

Upload the files in the simplebase folder to the directory above your web site folder.  Require  **init.php**  from the library's folder to use the classes.

To use the HTMLFilter class, download [HTMLPurifier library] (http://htmlpurifier.org) to a folder, and include HTMLPurifier.auto.php from the HTMLPurifier library folder.

## Classes

* Autoloader: A class with the methods for autoloading the library.  The init file calls on this, so you don't have to.

* Test: A basic class you can use to make sure classes are loading properly.

* Router: A base class for taking the relative URL, breaking it into elements, and then sending the request to the right controller (or sending it along, if the request fits the ignore list).

* Controller: A generic controller made for smart URLs, JSON or HTML form data, and optional JSON or HTML output.  It holds several methods for setting the HTTP Response Code and label to different states.  It has the following main methods:
    * getInput: Sets the controller's parameters ("param") with $_GET variables and posted parameters.  Takes JSON, application/x-www-form-urlencoded, and multipart/form-data posts.
    * setMethod: Sets the request method based on the request method and (for browser access) an optional "reqMethod" parameter for PUT and DELETE requests
    * setAction: A method to turn request methods and smart URLs into model method names**
    * setMV: Sets the model name and creates a new instance of that model based on the smart URL used.  Also determines which view to use.  **
    * ** Designed to serve as an example, and meant for a really basic API.  You're most likely to override it in your subclasses.
    
* Model: A base class to use as both an example and a source for common methods.

* View: A base view that takes in a code, 

* JSONView: A view that outputs data as JSON.

* DbTable: A representation of the database tables.  It includes properties like the name of the column holding the primary key, the names of columns with foreign keys and the tables they point at, and the column to sort the table by.  it also lets you alias columns.  

* DbManager: It uses PDO to connect to your database and perform some common queries.  The queries are designed for MySQL, but are standard enough that they should work in any standard SQL database without a lot of effort.  You can subclass it to add any other queries your app commonly performs.

* QueryBuilder: A class to make it easier for the DbManager to dynamically build SELECT, INSERT, UPDATE, and DELETE queries.  Allows you to add joins, conditions, sorting, and limits programmatically.

* DataHolder: A class to make it a little easier to batch load data into the database.  Meant to be used when setting up the site and loading the initial data.

* Request Generator: A class to make testing a REST system a little easier.  Uses cURL.

* DbExcept: The exception thrown by the DbManager.  It's empty, but you can add features if you need them.  And even if you don't, it can help give your exceptions some context. 

* The library includes a set of filters to clean or verify user input for the model, .  They also have display methods to help models display their data in views.  Most are made with a focus on HTML display.  Your models can use them to filter input, and your views can use them to escape output.

    Filter | Filter Method | Allows | Error Behavior | Display Type
    ------ | ------------- | ------ | -------------- | ------------
    DataFilter | filter($val) | White listed values (set via setWhiteList($array) method) | Exception | htmlentities (default)
    PhoneFilter | filter($val) | Numbers, space, dash, period, parentheses and x | Removes violations | default
    EmailFilter | filter($val) | Email addresses allowed by PHP [filter_var] (http://php.net/manual/en/filter.filters.php) | Exception | PHP [filter_var](http://php.net/manual/en/filter.filters.php) sanitation
    URLFilter | filter($val) | URLs allowed by PHP [filter_var](http://php.net/manual/en/filter.filters.php) | Exception | PHP [filter_var](http://php.net/manual/en/filter.filters.php) sanitation
    FNFilter | filter($path_to_file, $extension_without_dot) | A-z, numbers, underscore, period, exclamation point, question mark, dash, ampersand, and dollar sign. | Removes violations | Checks if full path filename exists, and either returns the filename or raises an exception
    TextFilter | filter($val) | Text that doesn't include HTML tags | Removes violations | default
    HTMLFilter | filter($val) | HTML with the following tags: p, ul, ol, li, blockquote, a[href], strong, em, and br | Removes violations | Same filter as for input
    IntFilter | filter($val) | Integers allowed by PHP [filter_var] (http://php.net/manual/en/filter.filters.php).  NOTE: Constructor takes precision. | Exception | PHP [filter_var](http://php.net/manual/en/filter.filters.php) sanitation
    FloatFilter | filter($val) | Floating point numbers allowed by  PHP [filter_var](http://php.net/manual/en/filter.filters.php) | Exception |  PHP [filter_var](http://php.net/manual/en/filter.filters.php) sanitation
    BoolFilter | filter($val) | True, false (case insensitive), PHP true, PHP false, 0, and 1. | Exception | If given 0 or 1, returns it, otherwise does nothing.
    FileFilter | filter($path_to_file) | Files that meet mimetype and filesize restrictions (set in constructor). | Exception | Return filename (including path) if the file exists, otherwise raise an exception.
    ImageFilter | imageFilter($path_to_file, $thumb_boolean) | Image files that are JPG, PNG, or GIF that meet the filesize and image dimensions | Inherits display method from FileFilter, also has writeThumb($path_to_new_thumb) method for thumbnails
    
It also includes an optional utility.php file with small helper functions.  To use it, include the utility.php file.

The DBManagerTest page tests all the classes and their methods, but the tests are particular to my own setup.  If you use it, you should include your own database access information, and use your own variables for host, database, login, and password.  You should also point the file and image filtering tests at your own test files.

Failures throw exceptions, so you can let them bubble up and catch them in the top layer of your application.  I added some empty custom exception classes, to enable custom responses due to the type of exception.  I also tried to keep the exception messages useful, so they give some information about what's going on.

The queries use prepared PDO statements where they can.  Where they can't, you can use the filters.  For instance, if you want to order by ascending or descending, you can use a DataPrepper filter to filter out anything but "ASC" or "DESC."

TODO:
* Make a simple HTML View class that brings together a header, main navigation, body, and footer.
* Add FileUpload and ImageUpload classes to the library
* Improve the Controller and Model classes



## Requirements
The HTMLFilter class uses the [HTMLPurifier library] (http://htmlpurifier.org), so you need to add a copy to the site where you use this and include it.

This uses the PDO object, so you need to use PHP 5.1 or higher.  This was tested on a server using PHP 7.


## Usage

Subclass the following classes to make them work for your application:
* Router
* Controller
* View (and JSONView)
* Model

You might also need to subclass the following to support more advanced database queries:
* DbManager
* DbTable
* QueryBuilder

TODO: Build a simple example application using Bootstrap for the UI


## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

## History
2016.11.01 Initial upload.


## Credits

Code by Malaika Boyd.

## License

TODO: Choose a license.  Basically, I'd just like credit if you find it useful.  I'd love to hear from you, too.
