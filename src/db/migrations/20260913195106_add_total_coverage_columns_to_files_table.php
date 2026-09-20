<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class AddTotalCoverageColumnsToFilesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('file');
        $table->addColumn('total_coverage', MysqlAdapter::PHINX_TYPE_MEDIUM_INTEGER, ['signed' => false, 'default' => 0, 'null' => false])
              ->addColumn('total_coverage_percent', MysqlAdapter::PHINX_TYPE_DECIMAL, ['signed' => false, 'default' => "0.00", 'null' => false, 'precision' => 5, 'scale' => 2,])
              ->addIndex(['total_coverage_percent'], ['name' => 'idx_total_coverage_percent']);
        $table->update();
    }
}
