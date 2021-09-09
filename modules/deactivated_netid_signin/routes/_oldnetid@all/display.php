<?php
$package->cache_noStore();
$package['fields.page_name'] = 'Class of 2020 sign-in';
$url = $cms->helper('urls')->parse('_user/signin');
$cms->helper('notifications')->printNotice("If your NetID is still active and you remember the password, it is easier to <a href='$url'>sign in with it instead</a>.")
?>
<p>
    This system is to allow users who have graduated and no longer have access to their UNM NetID to sign into this site.
    To sign in this way, you need to use the link you received in your non-UNM email.
</p>
<p>
    We attempted to gather as many non-UNM emails from 2020 graduates as possible, but it was not possible to match all graduates to an outside email address.
    If you did not receive an email, please contact the Office of the Secretary at <a href='mailto:graduation@unm.edu'>graduation@unm.edu</a> or (505) 277-4664.
    You will need to provide:
</p>
<ul>
    <li>your name</li>
    <li>the year and semester you graduated</li>
    <li>your old NetID username (the first half of your UNM email address, not your Banner ID number)</li>
    <li>an email address you currently have access to so that you can receive your sign-in link</li>
</ul>