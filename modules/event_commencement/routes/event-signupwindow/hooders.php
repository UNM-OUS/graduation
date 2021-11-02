<?php

use Digraph\Forms\Form;
use Digraph\Modules\event_commencement\Signup;
use Formward\Fields\Select;

$package->cache_noStore();

/** @var \Digraph\Modules\event_commencement\SignupWindow */
$signupwindow = $package->noun();

/** @var Signup[] */
$signups = $signupwindow->allSignups();
//filter incomplete signups
$signups = array_filter($signups, function (Signup $e) {
    return $e->complete() && $e->degreeCategory() == 'Doctoral/Terminal';
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

echo "<h2>Hooders assigned</h2>";
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

if ($noHooder) {
    echo "<h2>No hooder assigned</h2>";
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
        $options = [];
        foreach ($hasHooder as $student) {
            if ($student['degree.degree_val.program'] === $program) {
                $hooder = $cms->read($student['hooder.signup']);
                $options[$student['hooder.signup']] = $hooder ? $hooder->name() . ": " . $hooder['unm.college'] . ", " . $hooder['unm.department'] : false;
            }
        }
        if ($options) {
            $form = new Form('', $program);
            $form['hooder'] = new Select('Existing hooders for this program');
            $form['hooder']->required(true);
            $form['hooder']->options($options);
            if ($form->handle()) {
                foreach ($programStudents as $student) {
                    $student['hooder.signup'] = $form['hooder']->value();
                    $student->update();
                }
                $package->redirect($package->url());
            } else {
                echo $form;
            }
        }
        echo "</div>";
    }
}
