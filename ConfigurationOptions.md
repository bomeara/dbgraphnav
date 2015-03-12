# Overview #
The configuration files for this program reside in /config/config.xml. A config\_defaults.xml is provided, any changes made to config.xml override config\_defaults.xml.

This file documents the meaning of each configuration option. For convenience, this is formatted as a list with the format valuename - explanation. Capitalization does matter.

# Config Values #
All values are wrapped within a single dbgraphnav root tag.
  * database - Main tag for storing database related configuration
    * DSN - stores connection information. Each of these values is passed to MDB2. See here for more detailed formatting info: http://pear.php.net/manual/en/html/package.database.mdb2.intro-dsn.html. This value may be a simple properly formatted DSN string, without the following sub-values.
      * phptype - Database backend used in PHP (i.e. mysql  , pgsql etc.)
      * dbsyntax - Database used with regards to SQL syntax etc.
      * protocol - Communication protocol to use ( i.e. tcp, unix etc.)
      * hostspec - Host specification (hostname[:port])
      * database - Database to use on the DBMS server
      * username - User name for login
      * password - Password for login
      * proto\_opts - Maybe used with protocol
      * option - Additional connection options in URI query string format. options get separated by &.
    * friend\_finder - stores information for finding related nodes given a specific node type.
      * (Type) - each type of node has its own entry. If there are multiple entries of the same type, they will each be evaluated and included in the results. It is recommended to use the SQL UNION statement instead of multiple types where possible for efficiency reasons.
        * DSN - (Optional) a DSN in the same format as above. Uses the above values for any unspecified values (but replaces a simple string). This is useful for over-riding specific attributes (such as hostspec) to pull data from a different database or server.
        * query\_string - string SQL query that returns related data in the following format. Replacement variables are specified with attributes, the currently allowable ones being: parentnode. See the example config file for more. |value|type|display value|(optional)callback url| (optional) display\_options |
|:----|:---|:------------|:---------------------|:----------------------------|
        * callback\_url - (optional) A default callback url. Non-empty query values are appended to this string. Wildcards are not available here.
        * display\_options - (optional) additional display options in the format specified below.
  * graphing
    * graphviz - see for more info: http://pear.php.net/package/Image_GraphViz/docs/1.3.0RC3/GraphViz/Image_GraphViz.html
      * binPath - base path to Graphviz commands
      * dotCommand - path to the Graphviz dot command
      * neatoCommand - path to the Graphviz neato command
      * directed - directed or undirected graph type
      * strict - collapse multiple connections between two nodes into a single line
      * name - sets the name of the graph. Might be useful for multiple separate graphs on a single html page when specifying image maps.
    * caching
      * path\_to\_cache - path to the image, query, and map cache
      * age\_too\_new - graph file is so new we don't even look to see if we should redraw it
      * behavior - can be none (no caching), simple (does not cache query result), complex (caches query result to avoid regenerating graphs with the same values)
    * display\_options - default display options which are overriden by those in a type. Each attribute is a sub-element selected from this list: http://www.graphviz.org/doc/info/attrs.html
    * misc - misc. options which don't fit elsewhere.
      * wordwrap - split node display lines at this many characters