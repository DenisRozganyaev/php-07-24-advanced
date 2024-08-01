<?php

new class implements \Core\Contract\Migration
{
    /**
    * Run migration script 
    * @return string
    */
    public function up(): string
    {
        return 'CREATE TABLE users (
           id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT
        )';
    }

    /**
    * Rollback migration script
    * @return string
    */
    public function down(): string
    {
        return 'DROP TABLE users';
    }
};
