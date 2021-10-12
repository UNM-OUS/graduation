<?php

use Digraph\Modules\internal_signin\Users\PastDegreeUserField;

unset($form['displayname']);

$form['person'] = new PastDegreeUserField('Associated past degree/identity');
$form['person']->required(true);
