<?php

use Formward\Fields\Input;

$package->cache_noStore();
$package['fields.page_name'] = 'String corrections';
$page = $package->noun();
$group = $cms->helper('graph')->nearest($page['dso.id'], 'event-group');
if (!$group) {
    $cms->helper('notifications')->printError('This program doesn\'t have a parent event-group so string corrections can\'t be saved.');
    return;
}

echo "<p>The following rules will apply to all program graduate lists within " . $group->link() . ".</p>";

$categories = $cms->config['grad-program.stringcorrections.categories'];
$helper = $cms->helper('program-grads');
$gradList = $helper->gradList($page, null, true);
$corrections = [];

$form = $cms->helper('forms')->form('');
foreach ($categories as $category) {
    $corrections[$category] = [];
    foreach ($gradList->category($category) as $string) {
        $hash = md5(serialize([$category, $string]));
        $field = new Input($category . ': ' . $string);
        $field->required(true);
        $field->default($string);
        foreach ($group['stringcorrections.' . $category] ?? [] as $c) {
            if ($c[0] == $string) {
                $field->default($c[1]);
            }
        }
        $corrections[$category][] = [
            $string,
            $form[$hash] = $field,
        ];
    }
}

if ($form->handle()) {
    $result = array_map(
        function ($c) {
            return array_values(array_filter(array_map(
                function ($e) {
                    $e[1] = $e[1]->value();
                    if ($e[0] != $e[1]) {
                        return $e;
                    } else {
                        return false;
                    }
                },
                $c
            )));
        },
        $corrections
    );
    $group['stringcorrections'] = $result;
    $group->update();
}

echo $form;
