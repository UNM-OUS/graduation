<?php

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Digraph\Forms\Form;
use Formward\Fields\FileMulti;

$package->cache_noStore();

$form = new Form('');
$form['files'] = new FileMulti('Spreadsheet files');

if ($form->handle()) {
    ini_set('max_execution_time', 300);
    /** @var \PDO */
    $pdo = $cms->pdo('oldnetids');
    foreach ($form['files']->value() as $file) {
        switch (pathinfo($file['name'], PATHINFO_EXTENSION)) {
            case 'xlsx':
                $reader = ReaderEntityFactory::createXLSXReader();
                break;
            case 'csv':
                $reader = ReaderEntityFactory::createCSVReader();
                break;
            default:
                throw new \Exception("Only xlsx and csv files are supported");
        }
        $reader->open($file['file']);
        $headers = null;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->getCells();
                if (!$headers) {
                    $headers = array_flip(array_map(function ($cell) {
                        return strtolower(trim($cell->getValue()));
                    }, $cells));
                } else {
                    // row data keyed by headers in first row
                    $rowData = array_map(function ($i) use ($cells) {
                        return @$cells[$i]->getValue();
                    }, $headers);
                    // preprocess row data
                    if (($netid = strtolower(trim($rowData['netid']))) && ($email = strtolower(trim($rowData['email'])))) {
                        $query = $pdo->prepare('INSERT INTO user (email, netid) VALUES (?, ?)');
                        $query->execute([$email, $netid]);
                    }
                }
            }
        }
        $reader->close();
    }
    $cms->helper('notifications')->flashConfirmation('Added users');
    $package->redirect($package->url());
} else {
    echo $form;
}
