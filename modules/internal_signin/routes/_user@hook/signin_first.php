<?php

// only execute this hook if no manager is specified
if ($package['url.args.manager']) {
    return;
}

define('SIGN_IN_BLOCKED', true);

$url_netid = $package->url();
$url_netid['args.manager'] = 'netid';

$url_pastdegree = $package->url();
$url_pastdegree['args.manager'] = 'pastdegree';

$url_signup = $package->url();
$url_signup['args.manager'] = 'pastdegree';
$url_signup['verb'] = 'signup';

?>

<div class="notification" style="background:#63666a;">

    <h2>Graduated less than a year ago or still at UNM?</h2>
    <a class="cta-button red" style='display:block;' href="<?php echo $url_netid; ?>">Sign in with your NetID</a>
    <p>
        If you still have access to your main campus NetID account, <a href="<?php echo $url_netid; ?>">sign in with it here</a>.
    </p>
    <p>
        Your NetID username and password will continue to function for one year after the last semester in which you are enrolled in at least one class.
        If you have forgotten your password, you can troubleshoot and reset it at <a href="https://netid.unm.edu/">netid.unm.edu</a>.
    </p>

</div>

<div class="digraph-card incidental">
    <h2>Graduated more than a year ago and no longer at UNM?</h2>
    <p>
        If you last took a class more than a year ago and no longer have access to your NetID, you can
        <a href="<?php echo $url_signup; ?>">create an account just for this site</a>
        that will allow you to register to attend graduation ceremonies.
        This account will be associated with a non-UNM email address that you currently have access to, have its own password, and will only work on graduation.unm.edu.
    </p>
    <ul>
        <li><a href="<?php echo $url_signup; ?>">Register new account</a></li>
        <li><a href="<?php echo $url_pastdegree; ?>">Sign in with existing account</a></li>
    </ul>
</div>