<?php
    # $Id: boths.php,v 1.13 2006/03/07 14:17:45 frabcus Exp $

    # The Public Whip, Copyright (C) 2003 Francis Irving and Julian Todd
    # This is free software, and you are welcome to redistribute it under
    # certain conditions.  However, it comes with ABSOLUTELY NO WARRANTY.
    # For details see the file LICENSE.html in the top level of the source.

    require_once "common.inc";
    require_once "db.inc";
    require_once "parliaments.inc";

    function head_cell($url, $current_sort, $heading, $heading_sort, $title)
    {
        print "<td>";
        if ($current_sort != $heading_sort)
        {
            print "<a href=\"" . $url . "sort=$heading_sort\" title=\"$title\">$heading</a>";
        }
        else
        {
            print $heading;
        }
        print "</td>";
    }

    $mps_query_start = "select first_name, last_name, title, constituency,
            party, pw_mp.mp_id as mp_id, 
            round(100*rebellions/votes_attended,0) as rebellions,
            round(100*votes_attended/votes_possible,0) as attendance, 
            entered_reason, left_reason, entered_house, left_house,
            house
            from pw_mp,
            pw_cache_mpinfo where
            pw_mp.mp_id = pw_cache_mpinfo.mp_id";

    $sort = trim($_GET["sort"]);
    if ($sort == "")
    {
        $sort = "date";
    }
    if ($sort == "date")
    {
        $order = "order by division_date desc, division_number desc, last_name, first_name, constituency";
    }
    elseif ($sort == "lastname")
    {
        $order = "order by last_name, first_name, constituency, division_date desc, division_number desc";
    }

    # TODO: remember to add title in when doing this for lords
    $rows=$pwpdo->fetch_all_rows("select first_name, last_name, constituency, party,
        entered_house, left_house, 
        division_number, division_date, division_name from pw_mp,
        pw_division, pw_vote where pw_mp.mp_id = pw_vote.mp_id and
        pw_division.division_id = pw_vote.division_id and vote = 'both' 
        and pw_mp.house = 'commons'
        $order",array());
    $count = count($rows);

    $title = $count . " both votes detected";
    pw_header();

?>
<p>
    This table lists all instances of double voting. In the UK this is allowed
    but here in Australia that's not the case, so this will just show errors
    in our data. You can select the column headings to sort it by MP name or by
    division date.
</p>

<?php
    $url = "boths.php?";

    $prettyrow = 0;
    $lastparl = "";
    foreach ($rows as $row)
    {
        $thisparl = date_to_parliament($row[7]);
        if ($lastparl == "" or ($thisparl != $lastparl and $sort == "date"))
        {
            if ($lastparl != "")
                print "</table>\n";
            if ($sort == "date")
                print "<h2>" . parliament_name($thisparl) . " Parliament</h2>\n";

            print "<table class=\"mps\"><tr class=\"headings\">\n";
            print "<tr class=\"headings\"><td>No.</td>";
            head_cell($url, $sort, "Date", "date", "Sort by division date");
            print "<td>Division</td>";
            head_cell($url, $sort, "MP", "lastname", "Sort by surname");
            print "<td>Constituency</td><td>Party</td>";
            print "</tr>";
        }
        $lastparl = $thisparl;

        $prettyrow = pretty_row_start($prettyrow);
        print '<td>'.$row['division_number'].'</td><td>'.pretty_date($row['division_date']).'</td><td><a href="division.php?date=' . urlencode($row['division_date']) .
        '&number=' . urlencode($row['division_number']) . '">'.$row['division_name'].'</a></td>';
        print '<td><a href="mp.php?firstname=' . urlencode($row['first_name']) .
            '&lastname=' . urlencode($row['last_name']) . '&constituency=' .
            urlencode($row['constituency']) . '">'.
            $row['first_name'].' '.$row['last_name'].'</a></td> <td>'.$row['constituency'].'</td>
            <td>' . pretty_party($row['party'], $row['entered_house'], $row['left_house']) . '</td>';
        print "</tr>\n";
    }

    print "</table>\n";

    pw_footer();
