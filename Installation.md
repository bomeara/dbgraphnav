# Installation #
Unzip the package archive into a web-accessible directory on your
server. You may check pre-requisites for the program by
running setup.php and following the instructions there. Each time it
stops, you should fix the error, and then reload the page to continue
on to the next step.

# System Requirements #
  * PHP 5
  * Supported Database System
  * Disk space for temp files
  * Server fast enough to run graphviz with acceptible load times

### Supported Databases ###
DBGraphNav supports the same databases as the MDB2 package. See the [MDB2 Documentation](http://pear.php.net/manual/en/html/package.database.mdb2.intro.html) for the most up-to-date list.

  * MySQL
  * MySQLi (PHP5 only)
  * PostgreSQL
  * Oracle
  * Frontbase (unmaintained)
  * Querysim
  * Interbase/Firebird (PHP5 only)
  * MSSQL
  * SQLite

# Prerequisites #
Working installations of:
  * PHP 5
  * Your database of choice
  * MDB2 (requires PEAR)
  * Image\_Graphviz (requires PEAR)
  * Graphviz

### Installing MDB2 ###
More info [on the project page](http://pear.php.net/manual/en/html/package.database.mdb2.intro.html):

In short, install MDB2 and then install the DB package you need:
```
$ pear install MDB2

$ pear install MDB2#pgsql
```

(You need to make sure the php extension for your selected database is installed. For example, on Debian (or Ubuntu), you may need to run `sudo apt-get install php5-pgsql` prior to installing the PEAR module. Don't forget to restart your webserver after installing a new php extension.)

### Installing Image\_Graphviz ###
This program has been developed using version 1.3.0RC3. To install this beta package, issue the command
```
$ pear install image_graphviz-beta
```

# Configuration #
Rename config.xml.example to config.xml and modify it according to the ConfigurationOptions.


# Installation #
Make sure the cache directory indicated in the configuration
file exists and is writeable by the user account that php runs under
(usually the same as the webserver account).

Include main.php in your project as an iframe or directly in-line with
the rest of the page. It is only intended as an example (although it
should work fine in a production environment), so please do customize
it. You may also want to look at jsload.php as an example of how to
use ajax to load the generated images.