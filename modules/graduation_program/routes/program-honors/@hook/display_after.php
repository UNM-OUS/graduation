<?php
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

$gradList = $helper->gradList($page, 'Bachelor');
$gradList = $gradList->filter(function ($grad) {
    return !!$grad->honors();
});

echo "<div class='grad-program'>";

echo "<div class='digraph-card incidental'>";
echo "<p>";
echo "The following list is based on information gathered from the Banner database prior to our publication deadline. ";
echo "Given the number of graduates and evolving status of many prospective graduates' degree status, mistakes and omissions may have occurred. ";
echo "Being listed or not here has no bearing on a student's official honors status. ";
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

echo "<div class='grad-program-list long-lines'>";
$last = null;
foreach ($gradList->grads() as $grad) {
    if ($last && $last->name() == $grad->name() && $last->honors() == $grad->honors()) {
        continue;
    }
    echo "<div class='grad-program-person'>";
    if ($page = @$personalPages[$grad->netID()]) {
        echo '<a href="' . $page->url() . '">' . $grad->name() . '</a>';
    } else {
        echo $grad->name();
    }
    echo ", <em>" . $grad->honors() . "</em>";
    echo "</div>";
    $last = $grad;
}
echo "</div>";

echo "</div>"; //end .grad-program
