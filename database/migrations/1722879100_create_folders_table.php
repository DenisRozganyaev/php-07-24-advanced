<?php

return new class implements \Core\Contract\Migration
{
    /**
    * Run migration script 
    * @return string
    */
    public function up(): string
    {
        return '
        CREATE TABLE folders (
           id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
           user_id INT UNSIGNED DEFAULT NULL,
           title VARCHAR(255) NOT NULL,
           created_at DATETIME DEFAULT NOW(),
           updated_at DATETIME DEFAULT NOW(),
           
           FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        ';
    }

    /**
    * Rollback migration script
    * @return string
    */
    public function down(): string
    {
        return 'DROP TABLE folders;';
    }
};
