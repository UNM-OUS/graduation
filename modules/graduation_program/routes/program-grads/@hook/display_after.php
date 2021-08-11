<?php

use Digraph\Modules\graduation_program\GradList;

$package['response.ttl'] = 3600*2;
$helper = $cms->helper('program-grads');
$page = $package->noun();

// build list of personalized pages
$personalPages = [];
$ids = [];
foreach ($cms->helper('graph')->children($page['dso.id'], 'event-program-signupwindow') as $window) {
    $ids = $ids + $cms->helper('graph')->childIDs($window['dso.id'], 'event-signupwindow-signup');
}
if ($ids) {
    $search = $cms->factory()->search();
    $search->where(implode(" AND ",[
        '${complete.state} = "complete"',
        '${moderation.state} = "approved"',
        '(${personalpage.activate} is not null AND ${personalpage.activate} <> 0)',
        '${dso.id} in ("'.implode('","',$ids).'")'
    ]));
    foreach ($search->execute() as $signup) {
        $personalPages[$signup['signup.for']] = $signup->personalizedPage();
    }
}

$gradList = $helper->gradList($page, $page['gradspage.category']);

echo "<div class='grad-program'>";

echo "<div class='digraph-card incidental'>";
echo "<p>";
echo "The following list is based on information gathered from the Banner database prior to our publication deadline. ";
echo "Given the number of graduates and evolving status of many prospective graduates' degree status, mistakes and omissions may have occurred. ";
echo "Being listed or not here has no bearing on a student's official graduation status. ";
echo "This list currently includes:";
echo "</p>";
echo "<ul>";
foreach ($gradList->semesters() as $semester) {
    echo "<li>$semester ";
    $semesterList = $gradList->filter(function ($grad) use ($semester) {
        return $grad->semester() == $semester;
    });
    $statuses = implode(", ", $semesterList->category('status'));
    $statuses = preg_replace('/, ([^,]+)/', ' and $1', $statuses);
    echo "$statuses degrees";
    echo "</li>";
}
echo "</ul>";
echo "</div>";

if ($page['gradspage.category'] == 'Associate') {
    // group by campus for associate degrees
    foreach ($gradList->category('campus') as $campus) {
        echo "<div class='college-section'>";
        echo "<h2 class='college-header'>$campus</h2>";
        $sectionList = $gradList->filter(function ($grad) use ($campus) {
            return $grad->campus() == $campus;
        });
        printSection($sectionList, $personalPages);
        echo "</div>"; // end .college-section
    }
} else {
    // default grouping by college
    foreach ($gradList->colleges() as $college) {
        echo "<div class='college-section'>";
        echo "<h2 class='college-header'>$college</h2>";
        $sectionList = $gradList->filter(function ($grad) use ($college) {
            return $grad->college() == $college;
        });
        printSection($sectionList, $personalPages);
        echo "</div>"; // end .college-section
    }
}

echo "</div>"; //end .grad-program

function printSection(GradList $sectionList, array $personalPages = [])
{
    foreach ($sectionList->category('program') as $program) {
        echo "<div class='program-section'>";
        echo "<h3 class='program-header'>$program</h3>";
        $programList = $sectionList->filter(function ($grad) use ($program) {
            return $grad->program() == $program;
        });
        echo "<div class='grad-program-list'>";
        foreach ($programList->grads() as $grad) {
            echo "<div class='grad-program-person'>";
            if ($page = @$personalPages[$grad->netID()]) {
                echo '<a href="' . $page->url() . '">' . $grad->name() . '</a>';
            } else {
                echo $grad->name();
            }
            echo "</div>";
        }
        echo "</div>";
        echo "</div>"; // end .program-section
    }
}
