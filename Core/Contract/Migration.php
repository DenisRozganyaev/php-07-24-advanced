<?php

namespace Core\Contract;

interface Migration
{
    const TEMPLATE = <<<EOL
<?php

return new class implements \Core\Contract\Migration
{
    /**
    * Run migration script 
    * @return string
    */
    public function up(): string
    {
        return '';
    }

    /**
    * Rollback migration script
    * @return string
    */
    public function down(): string
    {
        return '';
    }
};

EOL;

    public function up(): string;
    public function down(): string;
}
