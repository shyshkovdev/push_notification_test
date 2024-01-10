<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Messages extends AbstractMigration
{
    public function up(): void
    {
        $this->table('messages')
            ->addColumn('device_id', 'integer')
            ->addColumn('notification_id', 'integer')
            ->addColumn('in_progress', 'boolean', ['default' => false])
            ->addColumn('status', 'string', ['null' => true])
            ->addForeignKey(
                'device_id',
                'devices',
                'id',
                [
                    'delete'=> 'NO_ACTION',
                    'update'=> 'NO_ACTION',
                    'constraint' => 'messages_device_id',
                ]
            )
            ->addForeignKey(
                'notification_id',
                'notifications',
                'id',
                [
                    'delete'=> 'NO_ACTION',
                    'update'=> 'NO_ACTION',
                    'constraint' => 'messages_notification_id',
                ]
            )
            ->create();
    }

    public function down(): void
    {
        $this->table('messages')
            ->drop()
            ->save();
    }
}
