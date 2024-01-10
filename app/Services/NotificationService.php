<?php


namespace App\Services;


use App\Models\Message;
use App\Models\PushNotification;
use Cake\Datasource\Exception\RecordNotFoundException;

class NotificationService
{
    /**
     * @param int $notification_id
     * @return array
     */
    public function getNotificationInfo(int $notification_id): array
    {
        $notification = DatabaseService::findById('notifications', $notification_id);

        if (!$notification) {
            throw new RecordNotFoundException();
        }

        return $notification;
    }

    /**
     * @param string $title
     * @param string $message
     * @param int $country_id
     * @return int
     */
    public function createNewNotification(string $title, string $message, int $country_id): int
    {
        return DatabaseService::create(
            'notifications',
            [':title' => $title, ':message' => $message, ':country_id' => $country_id]
        );
    }

    /**
     * @param int $country_id
     * @param int $notification_id
     */
    public function addMessagesToQueue(int $country_id, int $notification_id): void
    {
        $users = DatabaseService::findAll('users', [':country_id' => $country_id]);

        foreach ($users as $user) {
            $devices = DatabaseService::findAll('devices', [':user_id' => $user['id'], ':expired' => 0]);

            foreach ($devices as $device) {
                DatabaseService::create('messages', [':device_id' => $device['id'], ':notification_id' => $notification_id, ':status' => null]);
            }
        }
    }

    /**
     * @param array $notification
     * @return array
     */
    public function getStatistics(array $notification): array
    {
        $stats = [Message::SENT_STATUS => 0, Message::FAILED_STATUS => 0, 'in_progress' => 0, 'in_queue' => 0];

        $status_stats = DatabaseService::findAll(
            'messages',
            [':notification_id' => $notification['id']],
            ['fields' => [
                'status', 'count(*) as count'],
                'groupBy' => 'status'
            ]
        );

        $in_progress_stats = DatabaseService::find(
            'messages',
            [':notification_id' => $notification['id'], ':in_progress' => 1],
            ['fields' => [
                'count(*) as count'],
                'groupBy' => 'in_progress'
            ]
        );

        if (!empty($in_progress_stats['count'])) {
            $stats['in_progress'] = $in_progress_stats['count'];
        }


        foreach ($status_stats as $stat) {
            $stats[$stat['status'] ?? 'in_queue'] = $stat['count'];
        }

        return $stats;
    }

    /**
     * @return array
     */
    public function getMessagesForDelivery(): array
    {
        $sql = 'SELECT messages.id, notifications.title, notifications.message, messages.notification_id, devices.token 
                    FROM messages 
                    LEFT JOIN notifications 
                    ON notifications.id = messages.notification_id
                    LEFT JOIN devices 
                    ON devices.id = messages.device_id
                    WHERE in_progress = 0 AND status IS NULL
                    LIMIT ' . config('PUSH_TO_N_DEVICES_BY_CRONE');
        $messages = DatabaseService::findBySqlQuery($sql);

        DatabaseService::updateBySqlQuery(
            'UPDATE messages 
                    SET in_progress=:in_progress_value 
                    WHERE in_progress=:in_progress_condition AND status IS NULL
                    LIMIT ' . config('PUSH_TO_N_DEVICES_BY_CRONE'),
            [':in_progress_value' => 1, ':in_progress_condition' => 0]
        );

        return $messages;
    }

    /**
     * @param array $messages
     * @return array
     * @throws \Exception
     */
    public function sendMessageToDevices(array $messages): array
    {
        $result = [];

        foreach ($messages as $message) {
            if (empty($result[$message['notification_id']])) {
                $result[$message['notification_id']] = [
                    'notification_id' => $message['notification_id'],
                    'title' => $message['title'],
                    'message' => $message['message'],
                    Message::SENT_STATUS => 0,
                    Message::FAILED_STATUS => 0,
                ];
            }

            $is_sent = PushNotification::send($message['title'], $message['message'], $message['token']);

            $result[$message['notification_id']][$is_sent ? Message::SENT_STATUS : Message::FAILED_STATUS]++;
            DatabaseService::update(
                'messages',
                [':status' => $is_sent ? Message::SENT_STATUS : Message::FAILED_STATUS, ':in_progress' => 0],
                [':id' => $message['id']]
            );
        }

        return array_values($result);
    }

}