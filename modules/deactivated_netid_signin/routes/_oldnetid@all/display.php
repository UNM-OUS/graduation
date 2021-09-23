<?php

use Digraph\Forms\Form;
use Digraph\Modules\ous_digraph_module\Fields\EmailOrNetID;

$package->cache_noStore();
$package['fields.page_name'] = 'Deactivated NetID sign-in';
/** @var Digraph\Modules\deactivated_netid_signin\DeactivatedNetidHelper */
$helper = $cms->helper('deactivated-netids');

if ($cms->helper('users')->user()) {
    $cms->helper('notifications')->printConfirmation('You are already signed in');
    return;
}

$url = $cms->helper('urls')->parse('_user/signin');
$cms->helper('notifications')->printNotice("If your NetID is still active and you remember the password, it is much easier to <a href='$url'>sign in with it instead</a>.")
?>
<p>
    This form is for students who already graduated and no longer have access to their UNM NetID to sign into this site and register for commencement.
    To sign in, you need either your old NetID username (the first half of your UNM email address, not your Banner ID number), or your non-UNM email address.
    If we have a matching record on file you will be emailed a sign-in link to a non-UNM email address.
</p>
<?php

$form = new Form('');
$form['netid'] = new EmailOrNetID('NetID or non-UNM email address');
$form['netid']->required(true);
$form['netid']->addValidatorFunction(
    'helper-validation',
    function (EmailOrNetID $field) use ($helper) {
        $value = $field->value();
        return "No matching record for that NetID or email address was found. Please try a different email address, or see below for how to contact us for access.";
    }
);
if ($form->handle()) {
    $helper->message($form['netid']->value());
}
echo $form;

?>
<p>
    We attempted to gather as many non-UNM emails from 2020 graduates as we could, but it was not possible to match all graduates to an outside email address.
    If you can't use this form, please contact the Office of the Secretary at <a href='mailto:graduation@unm.edu'>graduation@unm.edu</a> or (505) 277-4664.
    You will need to provide:
</p>
<ul>
    <li>your name</li>
    <li>the year and semester you graduated</li>
    <li>your old NetID username (the first half of your UNM email address, not your Banner ID number)</li>
    <li>an email address you currently have access to so that you can receive your sign-in link</li>
</ul>