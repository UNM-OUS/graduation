<?php

use Digraph\Forms\Form;
use Digraph\Modules\event_commencement\Chunks\HooderAutocomplete;
use Digraph\Modules\event_commencement\Signup;
use Digraph\Modules\event_commencement\SignupWindow;

$package->cache_noStore();

/** @var \Digraph\Modules\event_commencement\SignupWindow */
$signupwindow = $package->noun();

printf(
    "<p><a href='%s'>Printable report for marshals</a></p>",
    $signupwindow->url('hooder_report')
);

/** @var Signup[] */
$signups = $signupwindow->allSignups();
//filter incomplete signups, or signups that aren't for a primary event
$signups = array_filter($signups, function (Signup $e) {
    if ($e->degreeCategory() != 'Doctoral/Terminal') {
        return false;
    }
    if (!$e->complete()) {
        return false;
    }
    if (!$e->primaryEvents()) {
        return false;
    }
    return true;
});
//sort
usort($signups, function (Signup $a, Signup $b) {
    $sortOrder = [
        'hooder.signup',
        'degree.degree_val.program',
        'contact.lastname',
        'contact.firstname'
    ];
    foreach ($sortOrder as $k) {
        if ($a[$k] != $b[$k]) {
            return strcasecmp($a[$k], $b[$k]);
        }
    }
    return 0;
});

$hasHooder = array_filter(
    $signups,
    function (Signup $signup) {
        return $signup['hooder.signup'] && $signup['hooder.signup'] !== '--none--';
    }
);
$noHooder = array_filter(
    $signups,
    function (Signup $signup) {
        return !$signup['hooder.signup'] || $signup['hooder.signup'] === '--none--';
    }
);

echo "<h2>Hooders assigned (" . count($hasHooder) . ")</h2>";
$hooders = [];
$hooderID = '--none--';
$hooder = null;
foreach ($hasHooder as $student) {
    if ($hooderID != $student['hooder.signup']) {
        if ($hooderID) {
            echo "</ul>";
        }
        $hooderID = $student['hooder.signup'];
        if ($hooder = $cms->read($student['hooder.signup'])) {
            $hooders[$hooderID] = $hooder;
            echo "<h3 class='digraph-card' style='text-align:left;'>" . $hooder->link();
            echo "<br><small>" . $hooder->signupWindow()->name() . ": " . $hooder['unm.college'] . ", " . $hooder['unm.department'] . "</small></h3>";
        }
        echo "<ul>";
    }
    echo "<li>";
    echo $student->link();
    echo ": " . $student['degree.degree_val.program'];
    echo "</li>";
}
echo "</ul>";

echo "<h2>Hooding faculty</h2>";
printf(
    "<p><strong>Email addresses</strong><br><textarea style='width:100%%;height:5em;'>%s</textarea></p>",
    implode("; ", array_map(
        function (Signup $signup): string {
            return $signup->contactInfo()->email();
        },
        $hooders
    ))
);
printf(
    "<ul>%s</ul>",
    implode("", array_map(
        function (Signup $signup): string {
            return sprintf(
                "<li>%s<br><small>%s, %s</small></li>",
                $signup->name(),
                $signup['unm.college'],
                $signup['unm.department']
            );
        },
        $hooders
    ))
);

if ($noHooder) {
    echo "<h2>No hooder assigned (" . count($noHooder) . ")</h2>";
    printf(
        "<p><strong>Email addresses (don't forget to BCC)</strong><br><textarea style='width:100%%;height:5em;'>%s</textarea></p>",
        implode("; ", array_map(
            function (Signup $signup): string {
                return $signup->contactInfo()->email();
            },
            $noHooder
        ))
    );
    $programs = array_unique(array_map(
        function (Signup $signup): string {
            return $signup['degree.degree_val.program'];
        },
        $noHooder
    ));
    foreach ($programs as $program) {
        echo "<div class='digraph-card'>";
        echo "<h3>$program</h3>";
        echo "<ul>";
        $programStudents = [];
        foreach ($noHooder as $student) {
            if ($student['degree.degree_val.program'] === $program) {
                $programStudents[] = $student;
                echo "<li>";
                echo $student->link();
                echo "</li>";
            }
        }
        echo "</ul>";

        // form for picking arbitrary hooder
        $signupWindows = array_filter(
            $package->noun()->eventGroup()->signupWindows(),
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
        $form = new Form('', $program);
        $form['hooder'] = new HooderAutocomplete('Select a hooder');
        $form['hooder']->srcArg('windows', implode(',', $signupWindows));
        $form['hooder']->required(true);
        if ($form->handle()) {
            foreach ($programStudents as $student) {
                $student['hooder.signup'] = $form['hooder']->value();
                $student->update();
            }
            $package->redirect($package->url());
        } else {
            echo $form;
        }

        echo "</div>";
    }
}
