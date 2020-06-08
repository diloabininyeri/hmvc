<?php

/**
 * @noinspection AutoloadingIssuesInspection
 * @noinspection PhpIllegalPsrClassPathInspection
 **/

use App\Components\Database\Migration\MigrationBuilder;
use App\Components\Database\Migration\Schema;
use App\Interfaces\MigrationInterface;

class PaymentsCreateMigration implements MigrationInterface
{
    public function create():void
    {
        Schema::connection('default')->create('$table_name', static function (MigrationBuilder $migration) {
            $migration->primaryKey('id')->length(100)->unique();
            $migration->timestamp();
        });
    }


    /**
     * @throws Exception
     */
    public function drop():void
    {
        Schema::connection('default')->drop('$table_name');
    }
}
