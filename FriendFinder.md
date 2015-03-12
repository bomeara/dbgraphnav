The 

<friend\_finder>

 section of the config file is the configuration
for how DBGraphNav builds the graph network. It helps to consult the
example file while reading this description.

Each result from the database must have a type. Within this type, you
can specify multiple queries which return information on that type:



<query\_base>

 This query is used when this type of node is the starting
node for a graph. It returns the necessary information to display that
node. This query should return (at minimum) a single result with the
columns `value`, `type`, and `display_name`. `value` is the unique (on a
per-type basis) database entry ID for a node.



<query\_string parentnode="$1">

 This is the heart of the program. This
query searches for the items which are **connected to** a node of the
type specified in the enclosing tag. The default $1 substitution in
the tag header is a string which will be replaced by the ID of the
node whose friends are being found. If the string $1 does not work
well for you, you may change that value.

This query needs to return, at minimum, the columns `value`, `type`,
and `display_name`. `value` and `type` form a unique ID for each node
on the display output. `display_name` is the text which will be displayed within the center of a node.

Optionally, either `query_base` or `query_string` may also return the
columns `callback_url` and `display_options`. `callback_url` is the
URL given to the image map generation program which generates the link
a user is taken to if they click a specific node. Often, this will be
a link to a general page which contains another instance of
DBGraphNav. The contents of the higher-level `<callback_url>` tag are
prepended to this value.

`display_options` allows changes to the appearance of each
node. Values such as
[color](http://www.graphviz.org/doc/info/attrs.html#k:color),
[shape](http://www.graphviz.org/doc/info/shapes.html#polygon), and
tooltip may be specified here. Since this must be a single string, use
the format "color=blue, shape=triangle", separating values with
commas. You may also use the higher level tag `<display_options>` in
the type definition. Values specified there will be over-ridden by
values specified in the actual query itself. More values and options
may be found in the graphviz documentation:
http://www.graphviz.org/doc/info/attrs.html

The tag `<display_options_limited>` is a set of display options which
are applied to a node when any of its children are removed from the graph by
the limiting algorithm. These values over-ride all others when put
into effect.