<?php

/** @var \Digraph\Users\UserInterface */

use Digraph\Modules\internal_signin\Users\PastDegreeUser;

$user = $cms->helper('users')->user();
if ($user instanceof PastDegreeUser && !$user['email.verified']) {
    define('SIGNUP_BLOCKED', true);
}
