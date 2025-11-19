<?php
    // Use 127.0.0.1 explicitly to avoid MySQL user host matching issues with 'localhost'
    define('DB_DSN', 'mysql:host=127.0.0.1;port=3306;dbname=serverside;charset=utf8');
    define('DB_USER', 'serveruser');
    define('DB_PASS', 'gorgonzola7!');     
    
   //  PDO is PHP Data Objects
   //  mysqli <-- BAD. 
   //  PDO <-- GOOD.
    try {
        // Try creating new PDO connection to MySQL.
        $db = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        print "Error: " . $e->getMessage();
        die(); // Force execution to stop on errors.
        // When deploying to production you should handle this
        // situation more gracefully. ¯\_(ツ)_/¯
    }
?>