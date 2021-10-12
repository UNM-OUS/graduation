<?php

namespace Digraph\Modules\internal_signin\Users;

use Digraph\Forms\Fields\AbstractAutocomplete;

/**
 * Used to search for a past degree user so that their data can be imported into
 * a new internal user account.
 */
class PastDegreeUserField extends AbstractAutocomplete
{
    const SOURCE = 'pastdegreeuser';
    protected function construct()
    {
        $this->addTip('Search past degree recipients by name. Your name may not appear on this list if you graduated less than a year ago, prior to 2020, or had a privacy flag on your account when you graduated.');
    }
}
