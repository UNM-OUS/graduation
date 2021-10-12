<?php

/** @var \PDO */
$pdo = $cms->pdo('degrees');
$query = $pdo->prepare('SELECT * FROM degree WHERE id = :q');
if ($query->execute(['q' => $form['person']->value()])) {
    $pastDegree = $query->fetch(PDO::FETCH_ASSOC);
} else {
    throw new \Exception("Invalid past degree ID");
}

// set user's values from past degree data
$user->name(
    $pastDegree['firstname'] . ' ' . $pastDegree['lastname']
);
$user['netid'] = $pastDegree['netid'];
$user['pastdegreeid'] = $pastDegree['id'];
