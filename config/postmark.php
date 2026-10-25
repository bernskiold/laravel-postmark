<?php

return [

    'webhooks' => [

        /*
         * The HTTP Basic Auth credentials of the webhook in Postmark. If you
         * do not set the two values, the endpoint refuses all requests.
         */
        'username' => env('POSTMARK_WEBHOOK_USERNAME'),
        'password' => env('POSTMARK_WEBHOOK_PASSWORD'),

        /*
         * The queue connection and the queue for the webhook job. Set null
         * to use the default connection and queue of the application.
         */
        'connection' => env('POSTMARK_WEBHOOK_QUEUE_CONNECTION'),
        'queue' => env('POSTMARK_WEBHOOK_QUEUE'),

    ],

    /*
     * The log channel for webhook messages. Set null to use the default
     * channel of the application.
     */
    'log_channel' => env('POSTMARK_LOG_CHANNEL'),

];
