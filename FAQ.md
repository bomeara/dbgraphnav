If your issue is not resolved here, please report it on the issue tracker.

# It's not working! #

Q: My server gives me this error:

`Fatal error: Call to undefined method Image_GraphViz::_escape() in /home2/www/treetapper.nescent.org/site/html/dbgraphnav/connections.php on line 67`

A: You aren't running Image\_Graphviz 1.3.0RC3 or higher. You probably installed the release (1.2.1) rather than beta package using pear. The RC package is, in fact, very stable, and includes numerous features this program takes advantage of. Consult the Installation instructions for more details.

`MDB2 Error: connect failed, _doConnect: [Error message: unable to establish a connection] ** pgsql(pgsql)://username:xxx@server.org/database `

A: MDB2 can't connect. Check your username and password (make sure there are no extra spaces or newlines within the XML tag), and the other connection information in config.xml.