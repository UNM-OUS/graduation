<?php

namespace Digraph\Modules\event_commencement\Chunks;

use Digraph\Modules\event_commencement\SignupWindow;
use Digraph\Modules\ous_event_management\Chunks\AbstractChunk;

class Hooder extends AbstractChunk
{
    protected $label = 'Hooder';

    function body_incomplete()
    {
        echo "You must either select a hooder or indicate that you need one assigned.";
    }

    function body_complete()
    {
        $signup = $this->signup[$this->name . '.signup'];
        if ($signup == '--none--' || !($signup = $this->signup->cms()->read($signup))) {
            echo "A hooder will be assigned for you from the attending faculty on the day of the ceremony.";
            echo " If you have a faculty member you would prefer to be hooded by, please edit this section to search for them.";
        } else {
            echo "<strong>" . $signup->contactInfo()->name() . "</strong><br>";
            echo implode(', ', array_filter([
                $signup['unm.college'],
                $signup['unm.department']
            ]));
        }
    }

    function form_map(): array
    {
        $signupWindows = array_filter(
            $this->signup->eventGroup()->signupWindows(),
            function (SignupWindow $w) {
                return $w['signup_windowtype'] == 'faculty';
            }
        );
        $signupWindows = array_map(
            function (SignupWindow $w) {
                return $w['dso.id'];
            },
            $signupWindows
        );
        return [
            'hooder' => [
                'label' => 'Preferred hooder',
                'class' => HooderAutocomplete::class,
                'field' => $this->name . '.signup',
                'required' => true,
                'default' => '--none--',
                'tips' => [
                    "Search by name.",
                    "If your preferred hooder doesn't appear in the search results, it means they haven't completed a signup yet, please contact them and ask them to sign up as soon as possible.",
                    "If your preferred hooder cannot attend, you can select 'Choose a hooder for me' and we will pick a hooder for you from the attending faculty on the day of the event."
                ],
                'call' => [
                    'srcArg' => ['windows', implode(',', $signupWindows)]
                ]
            ]
        ];
    }
}
