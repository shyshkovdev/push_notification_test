<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Notifications extends AbstractMigration
{
    public function up(): void
    {
        $this->table('notifications')
            ->addColumn('title', 'string', ['limit' => 255])
            ->addColumn('message', 'string')
            ->addColumn('country_id', 'integer')
            ->addIndex(['country_id'])
            ->addForeignKey(
                'country_id',
                'countries',
                'id',
                [
                    'delete'=> 'NO_ACTION',
                    'update'=> 'NO_ACTION',
                    'constraint' => 'notifications_country_id',
                ]
            )
            ->create();
    }

    public function down(): void
    {
        $this->table('notifications')
            ->drop()
            ->save();
    }
}
