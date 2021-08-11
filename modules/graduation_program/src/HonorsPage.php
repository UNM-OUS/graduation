<?php
namespace Digraph\Modules\graduation_program;

use Digraph\DSO\Noun;
use Digraph\Modules\ous_event_management\SignupWindow;
use Digraph\Modules\ous_event_management\UserList;

class HonorsPage extends Noun
{
    public function childEdgeType(Noun $child): ?string
    {
        if ($child instanceof UserList) {
            return 'event-program-userlist';
        }
        if ($child instanceof SignupWindow) {
            return 'event-program-signupwindow';
        }
        return null;
    }
}
