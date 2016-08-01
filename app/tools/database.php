<?php
/**
 * Created by PhpStorm.
 * User: matpif
 * Date: 27/04/16
 * Time: 20:53
 */

include_once 'SQLite.php';
include_once 'ReadIni.php';

$SQLite = new SQLite();

$stmt = $SQLite->prepareQuery('PRAGMA user_version;');
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
$queries = [];

if (isset($result['user_version'])) {

    if ($result['user_version'] < 1) {

        $queries = ['Create Utilisateur' => 'CREATE TABLE `utilisateur` (
            `id`	INTEGER PRIMARY KEY AUTOINCREMENT,
            `login`	TEXT NOT NULL UNIQUE,
            `first_name`	TEXT,
            `last_name`	TEXT,
            `password`	TEXT NOT NULL,
            `email`	TEXT NOT NULL UNIQUE,
            `privilege`	INTEGER NOT NULL DEFAULT -1
        );'
        ];
    }
}

$_error = false;
foreach ($queries as $key => $query) {

    echo 'Start: '.$key."\n";
    $stmt = $SQLite->prepareQuery($query);
    if (!$stmt->execute()) {
        echo 'Error: '.$key."\n";
        $_error = true;
        break;
    }

    echo 'Finish: '.$key."\n";
}

if (!$_error) {

    $_version = ReadIni::getInstance()->getAttribute('sqlite', 'user_version');
    $stmt = $SQLite->prepareQuery("PRAGMA user_version={$_version}");
    $stmt->execute();
}
