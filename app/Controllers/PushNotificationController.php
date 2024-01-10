<?php


namespace App\Controllers;


use App\Services\NotificationService;

class PushNotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * @param string $title
     * @param string $message
     * @param int $country_id
     * @return array|null
     * @api {post} / Request to send
     *
     * @apiVersion 0.1.0
     * @apiName send
     * @apiDescription This method saves the push notification and put it to the queue.
     * @apiGroup Sending
     *
     * @apiBody {string="send"} action API method
     * @apiBody {string} title Title of push notification
     * @apiBody {string} message Message of push notification
     * @apiBody {int} country_id Country ID
     *
     * @apiParamExample {json} Request-Example:
     * {"action":"send","title":"Hello","message":"World","country_id":4}
     *
     * @apiSuccessExample {json} Success:
     * {"success":true,"result":{"notification_id":123}}
     *
     * @apiErrorExample {json} Failed:
     * {"success":false,"result":null}
     */
    public function sendByCountryId(string $title, string $message, int $country_id): ?array
    {
        // TODO create a push notification in the database and return notification ID:

        $notification_id = $this->notificationService->createNewNotification($title, $message, $country_id);

        $this->notificationService->addMessagesToQueue($country_id, $notification_id);

        return [
            'notification_id' => $notification_id
        ];
    }

    /**
     * @param int $notification_id
     * @return array|null
     * @api {post} / Get details
     *
     * @apiVersion 0.1.0
     * @apiName details
     * @apiDescription This method returns all details by notification ID.
     * @apiGroup Information
     *
     * @apiBody {string="details"} action API method
     * @apiBody {int} notification_id Notification ID
     *
     * @apiParamExample {json} Request-Example:
     * {"action":"details","notification_id":123}
     *
     * @apiSuccessExample {json} Success:
     * {"success":true,"result":{"id":123,"title":"Hello","message":"World","sent":90000,"failed":10000,"in_progress":100000,"in_queue":123456}}
     *
     * @apiErrorExample {json} Notification not found:
     * {"success":false,"result":null}
     */
    public function details(int $notification_id): ?array
    {
        // TODO return an array like:
        //        return [
        //            'id' => 123,
        //            'title' => 'Hello',
        //            'message' => 'World',
        //            'sent' => 90000,
        //            'failed' => 10000,
        //            'in_progress' => 100000,
        //            'in_queue' => 123456,
        //        ];

        $notification = $this->notificationService->getNotificationInfo($notification_id);

        $statistics = $this->notificationService->getStatistics($notification);

        return array_merge($notification, $statistics);
    }

    /**
     * @throws \Exception
     * @api {post} / Sending by CRON
     *
     * @apiVersion 0.1.0
     * @apiName cron
     * @apiDescription This method sends the push notifications from queue.
     * @apiGroup Sending
     *
     * @apiBody {string="cron"} action API method
     *
     * @apiParamExample {json} Request-Example:
     * {"action":"cron"}
     *
     * @apiSuccessExample {json} Success and sent:
     * {"success":true,"result":[{"notification_id":123,"title":"Hello","message":"World","sent":50000,"failed":10000},{"notification_id":124,"title":"New","message":"World","sent":20000,"failed":20000}]}
     *
     * @apiSuccessExample {json} Success, no notifications in the queue:
     * {"success":true,"result":[]}
     */
    public function cron(): array
    {
        // TODO send push notifications to 100000k devices from queue and use PushNotification::send() to send
        //        return [
        //            [
        //                'notification_id' => 123,
        //                'title' => 'Hello',
        //                'message' => 'World',
        //                'sent' => 50000,
        //                'failed' => 10000,
        //            ],
        //            [
        //                'notification_id' => 124,
        //                'title' => 'New',
        //                'message' => 'World',
        //                'sent' => 20000,
        //                'failed' => 20000,
        //            ],
        //        ];

        $messages = $this->notificationService->getMessagesForDelivery();
        return $this->notificationService->sendMessageToDevices($messages);
    }
}