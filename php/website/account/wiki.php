<?php require_once "../common.inc";
# $Id: wiki.php,v 1.36 2008/01/09 17:16:03 publicwhip Exp $
# vim:sw=4:ts=4:et:nowrap

# The Public Whip, Copyright (C) 2003 Francis Irving and Julian Todd
# This is free software, and you are welcome to redistribute it under
# certain conditions.  However, it comes with ABSOLUTELY NO WARRANTY.
# For details see the file LICENSE.html in the top level of the source.

require_once "../database.inc";
require_once "user.inc";

require_once "../db.inc";
require_once "../cache-tools.inc";
require_once "../wiki.inc";
require_once "../pretty.inc";
require_once "../DifferenceEngine.inc";
require_once "../divisionvote.inc";
require_once "../forummagic.inc";
$db = new DB(); 

$just_logged_in = do_login_screen();

if (user_isloggedin()) # User logged in, show settings screen
{
    $type = db_scrub($_GET["type"]);
    if ($type == 'motion')
        $params = array(db_scrub($_GET["date"]), db_scrub($_GET["number"]), db_scrub($_GET["house"]));
    else
        trigger_error("Unknown wiki type " . htmlspecialchars($type), E_USER_ERROR);

    # Convert from Australian to UK house
    if ($params[2] == "representatives")
        $params[2] = "commons";
    else if ($params[2] == "senate")
        $params[2] = "lords";

    $newtext = $_POST["newtext"];
    $newtitle = $_POST["newtitle"];
    $newdescription = $_POST["newdescription"];
    $submit = db_scrub($_POST["submit"]);
    $rr = db_scrub($_GET["rr"]);

    $division_details = $pwpdo->get_single_row("select * from pw_division where division_date = ?
        and division_number = ? and house = ?", array($params[0], $params[1], $params[2]));
    $prettydate = date("j M Y", strtotime($params[0]));
    $title = "Edit division description - " . $division_details['division_name'] . " - $prettydate - Division No. $params[1]";
    $debate_gid = str_replace("uk.org.publicwhip/debate/", "", $division_details['debate_gid']);
    $debate_gid = str_replace("uk.org.publicwhip/lords/", "", $debate_gid);

    if ($type == "motion") {
        $motion_data = get_wiki_current_value("motion", array($params[0], $params[1], $params[2]));
        $prev_name = extract_title_from_wiki_text($motion_data['text_body']);
        $prev_description = extract_motion_text_from_wiki_text($motion_data['text_body']);
        $prev_description_editable = extract_motion_text_from_wiki_text_for_edit($motion_data['text_body']);
    }
    
    if ($submit && (!$just_logged_in))
    {
        if ($submit == "Save") {
            if ($type == 'motion') {
                if (trim($newtitle) == false) {
                    # TODO: Fail gracefully
                    trigger_error('Title cannot be blank', E_USER_ERROR);
                }
                $newtext = add_motion_missing_wrappers($newdescription, $newtitle);
            
                $curr_name = extract_title_from_wiki_text($newtext);
                $curr_description = extract_motion_text_from_wiki_text($newtext);
                $name_diff = format_linediff(trim($prev_name), trim($curr_name), false); # always have link
                $description_diff = format_linediff(trim($prev_description), trim($curr_description), true);
                # forum escapes <, > and the like already
                $description_diff = html_entity_decode(html_entity_decode($description_diff, ENT_QUOTES), ENT_QUOTES);
                $name_diff = html_entity_decode(html_entity_decode($name_diff, ENT_QUOTES), ENT_QUOTES);
                global $domain_name;
                divisionvote_post_forum_action($db, $params[0], $params[1], $params[2], 
                    "Changed title and/or description of division.\n\n[b]Title:[/b] ".
                    "[url=http://$domain_name/division.php?date=".$division_details['division_date']."&number=".$division_details['division_number']."&house=".$division_details['house']."]".
                    $name_diff."[/url]\n[b]Description:[/b] ".$description_diff);
            }
            $db->query_errcheck("insert into pw_dyn_wiki_motion
                (division_date, division_number, house, text_body, user_id, edit_date) values
                ('$params[0]', '$params[1]', '$params[2]', '".mysql_real_escape_string($newtext)."', '" . user_getid() . "', now())");
            audit_log("Edited $type wiki text $params[0] $params[1] $params[2]");
            if ($type == 'motion') {
                notify_motion_updated($db, $params[0], $params[1], $params[2]);
            }
        }
        header("Location: ". $rr);
        exit;
    }
    else
    {
        pw_header();

        $values = get_wiki_current_value($type, $params);

        if ($type == 'motion') {
?>
        <p>Describe the <i>result</i> of this division.  This will require you
        to check through the debate leading up to the vote.
        The raw, and frequently
        wrong, motion text is there by default.  Feel free to remove it when
        you've replaced it with something better. </p>

        <p>Please, keep it accurate, authorative, and as jargon-free as
        possible so that new readers who don't know Parliamentary procedure can
        gain enlightenment. You are urged to include links to the
        legislation, reports, and committee proceedings that are referred to so
        that readers who want to follow the story further will know where to look.</p>

        <p>
<?
        if ($debate_gid != "") {
            if ($division_details['house'] == 'lords')
                $link_url = "http://www.openaustralia.org/senate/?id=$debate_gid";
            else
                $link_url = "http://www.openaustralia.org/debates/?id=$debate_gid";
            print "<b>Read the <a target=\"_blank\" href=\"$link_url\">debate leading up to the vote</a>.</b>";
        } else {
            print "Warning: old division; need to make hyperlink to old Parl data from division details.";
        }
?>
        </p>

        <!-- use tables here as textarea style width=64% behaves differently on IE vs. Firefox) -->
        <table border="0" width="100%">
        <tr>

        <td width="64%" valign="top">

        <P>
        <FORM ACTION="<?php echo $REQUEST_URI?>" METHOD="POST">
        <B>Division title:</b> <BR><INPUT TYPE="TEXT" NAME="newtitle" style="width: 100%" VALUE="<?php echo html_scrub(str_replace("&#8212;", "-", $prev_name))?>" SIZE="50" MAXLENGTH="250">
        <P><B>Division description:</b> <textarea name="newdescription" style="width: 100%" rows="25" cols="45"><?php echo html_scrub($prev_description_editable)?></textarea>
        <p>
        <INPUT TYPE="SUBMIT" NAME="submit" VALUE="Save" accesskey="S">
        <INPUT TYPE="SUBMIT" NAME="submit" VALUE="Cancel">
        </FORM>
        </P>
        <p><a href="<?php echo get_wiki_history_link($type, $params)?>">View change history</a>
<?
        } else {
            trigger_error("Unknown type for wiki", E_USER_ERROR);
        }
?>
        </td>

      <td width="3%">&nbsp;</td>
        
      <td width="33%" valign="top">

<?
        $discuss_url = "/division-forum.php?date=".$division_details["division_date"].
            "&number=".$division_details["division_number"]."&house=".$division_details["house"];
?>

        <p><span class="ptitle">Useful links for you to research</span>
		<ul>
            <li><a href="http://www.aph.gov.au/Parliamentary_Business/Bills_Legislation/Bills_before_Parliament">Bills before Parliament</a></li>
            <li><a href="http://www.aph.gov.au/Parliamentary_Business/Committees/House_of_Representatives_Committees?url=info/curr_inq.htm">Current House Committee Inquiries</a></li>
            <li><a href="http://www.aph.gov.au/Parliamentary_Business/Committees/Senate/Current_Inquiries">Current Senate Committee Inquiries</a></li>
            <li><a href="http://www.aph.gov.au/About_Parliament/House_of_Representatives/Powers_practice_and_procedure">House Powers, Practice and Procedure</a></li>
            <li><a href="http://www.aph.gov.au/About_Parliament/Senate/Powers_practice_n_procedures">Senate Powers, Practice and Procedure</a></li>
            <li><a href="http://www.aph.gov.au/Parliamentary_Business/Hansard/Hansreps_2011">House Official Hansard</a></li>
            <li><a href="http://www.aph.gov.au/Parliamentary_Business/Hansard/Hanssen261110">Senate Official Hansard</a></li>
        </ul>

        <p><span class="ptitle">Formatting codes</span>. You can use the following
        to mark paragraphs, lists and so on.
        <ul>
        <li><code>''italic''</code> - <em>italic</em>
        <li><code>[http://example.com A link]</code> - <a href="http://example.com">link</a>
        <li><code>* A point</code> - bulleted list item
        <li><code>An idea[1]</code> - link to a footnote
        <li><code>* [1] Ideas are good</code> - footnote
        <li><code>@A comment</code> - comments aren't displayed
        <li><code>@MP voted aye to say that little ponies are great</code> - puts this text on the motion page "[MP Name] voted to say that little ponies are great"
        <li><code>@MP voted no</code> - does it for MPs that voted no
        </ul>

        </td>

 

        </tr></table>
<?

    }
    pw_footer();
}
else
{
    login_screen();
}

?> 
