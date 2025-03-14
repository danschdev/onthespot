<?php

try {
    $db = new PDO('mysql:host=localhost;dbname=onthespot;charset=utf8mb4',
    'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(PDOException $exception) {
    echo 'Konnte Verbindung nicht erstellen: '.$exception->getMessage();
}