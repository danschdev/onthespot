<?php

try {
    $db = new PDO('mysql:host=localhost;dbname=onthespot;charset=utf8mb4',
    'root', '');
}

catch(PDOException $exception) {
    echo 'Konnte Verbindung nicht erstellen: '.$exception->getMessage();
}