<?php

return [
    'class'    => 'yii\db\Connection',
    'dsn'      => $_ENV['MYSQL_DSN'],
    'username' => $_ENV['MYSQL_USER'],
    'password' => $_ENV['MYSQL_PASSWORD'],
    // 'charset'  => 'utf8mb4',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
