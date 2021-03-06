<?php
require_once "../common.inc";
require_once "../db.inc";
# $Id: data.php,v 1.28 2009/06/08 00:56:09 publicwhip Exp $

# The Public Whip, Copyright (C) 2003 Francis Irving and Julian Todd
# This is free software, and you are welcome to redistribute it under
# certain conditions.  However, it comes with ABSOLUTELY NO WARRANTY.
# For details see the file LICENSE.html in the top level of the source.

    $title = "Raw Data"; pw_header();
?>

<p>Here you can find raw data compiled by the Public Whip project.
For example, if you want to load a voting record into a spreadsheet.  

<p>For legal
and copyright information, see <a href="../faq.php#legal">our FAQ</a>. However,
we ask that if you do anything fun or important with this data, you let us
know!  Any problems using the data, or requests for a different format?  Email
<a href="mailto:team@publicwhip.org.uk">team@publicwhip.org.uk</a>.

<p>You can <a href="../data/">browse all the data</a>. The data all goes back
to parliaments from 1997.  It is generated by Public Whip from the division
listings in the <a href="http://ukparse.kforge.net/parlparse/">debates files
made by the Parliament Parser project</a>.  You can find lots of other parliament
data there.

<h2>MP votes for each division</h2>

<p>The .dat files are tab-separated text files for loading into a spreadsheet.
They contain a matrix of every vote of each MP/Lord in each division.  The columns
are headed by the identifiers of the MPs/Lords, and the rows begin with the date,
number and title of the division.  Each .txt file explains what number
represents aye, no, abstain and so on, and gives a key to the MP/Lord
identifiers.

<p>
<a href="../data/votematrix-1997.dat">votematrix-1997.dat</a>
<a href="../data/votematrix-1997.txt">votematrix-1997.txt</a>
<br><a href="../data/votematrix-2001.dat">votematrix-2001.dat</a>
<a href="../data/votematrix-2001.txt">votematrix-2001.txt</a>
<br><a href="../data/votematrix-2005.dat">votematrix-2005.dat</a>
<a href="../data/votematrix-2005.txt">votematrix-2005.txt</a>
<br><a href="../data/votematrix-2010.dat">votematrix-2010.dat</a>
<a href="../data/votematrix-2010.txt">votematrix-2010.txt</a>
<br><a href="../data/votematrix-lords.dat">votematrix-lords.dat</a>
<a href="../data/votematrix-lords.txt">votematrix-lords.txt</a>

<p>You may have problems using these files because they have more than 256
columns, and some spreadsheets don't go beyond column IV.  See if your
spreadsheet can import "from column x" so you can load the files in chunks.
OpenOffice (or StarOffice) has a "Column type" drop down on the import dialog. 
You can select multiple columns and choose "Hide", then more of the other
columns will be loaded.  Try to find a copy of Quattro Pro, it works fine with
more columns.  If you are really stuck, email me and I'll export the data in
multiple files.

<h2>MP attendance and rebelliousness rates</h2>

<p>You can open XML files in any text editor, XML viewer or some spreadsheets.
In the files there are comments with more information.  

<p><a href="../mp-info.xml">mp-info.xml</a> - list of division attendance rate
and rebelliousness for MPs in the all-members.xml file.  This is a live file,
correct to the latest division in the Public Whip database.  The field data_date
shows the date it applies up to.  For members who have left the house it says
"complete".

<p><a href="../mp-info.xml?house=lords">mp-info.xml?house=lords</a> - 
likewise for the House of Lords.


<h2>Database dumps</h2>

<p><a href="../data/pw_static_tables.sql.bz2">pw_static_tables.sql.bz2</a> -
Text dump of MySQL tables containing raw voting and MP data.
<br><a href="../data/pw_dynamic_tables.sql.bz2">pw_dynamic_tables.sql.bz2</a> -
Text dump of MySQL tables containing policy (Dream MP) votes and edited motion text.
<br><a href="../data/pw_cache_tables.sql.bz2">pw_cache_tables.sql.bz2</a> -
Text dump of MySQL tables containing cached calculations.

<?php pw_footer() ?>
