<?php
$package->cache_noStore();

$package['fields.page_title'] = $package['url.text'];

$type = 'convocation';
$forms = $this->helper('forms');
$form = $forms->addNoun($type, $package->noun());

foreach ($this->helper('routing')->allHookFiles($type, 'form.php') as $file) {
    include $file['file'];
}
foreach ($this->helper('routing')->allHookFiles($type, 'form_add.php') as $file) {
    include $file['file'];
}

// $cms->helper('notifications')->warning('Due to COVID restrictions in-person events will require event approval through the request portal: <a href="http://campusexperience.unm.edu/resources/unm-event-request.html">Bringing Back the Pack – UNM Event Request</a>. Please ensure your event has been approved before adding it here.');

$form->handle(
    function ($form) use ($package, $type) {
        foreach ($this->helper('routing')->allHookFiles($type, 'form_handled.php') as $file) {
            include $file['file'];
        }
        foreach ($this->helper('routing')->allHookFiles($type, 'form_add_handled.php') as $file) {
            include $file['file'];
        }
    }
);
if ($form->handle()) {
    if ($form->object->insert()) {
        $object = $cms->read($form->object['dso.id'], false, true);
        $cms->helper('edges')->create($object['eventgroup'], $object['dso.id']);
        $cms->helper('edges')->create($package['noun.dso.id'], $object['dso.id']);
        $cms->helper('hooks')->noun_trigger($object, 'added');
        $cms->helper('notifications')->flashConfirmation(
            $cms->helper('strings')->string(
                'notifications.add.confirmation',
                ['name' => $object->link()]
            )
        );
        $package->redirect(
            $form->object->hook_postAddUrl()
        );
    } else {
        $cms->helper('notifications')->error(
            $cms->helper('strings')->string(
                'notifications.add.error'
            )
        );
    }
}

echo $form;
