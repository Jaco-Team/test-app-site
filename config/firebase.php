<?php

declare(strict_types=1);

return [
    /*
     * ------------------------------------------------------------------------
     * Default Firebase project
     * ------------------------------------------------------------------------
     */

    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
     * ------------------------------------------------------------------------
     * Firebase project configurations
     * ------------------------------------------------------------------------
     */

    'projects' => [
        'app' => [

            /*
             * ------------------------------------------------------------------------
             * Credentials / Service Account
             * ------------------------------------------------------------------------
             *
             * In order to access a Firebase project and its related services using a
             * server SDK, requests must be authenticated. For server-to-server
             * communication this is done with a Service Account.
             *
             * If you don't already have generated a Service Account, you can do so by
             * following the instructions from the official documentation pages at
             *
             * https://firebase.google.com/docs/admin/setup#initialize_the_sdk
             *
             * Once you have downloaded the Service Account JSON file, you can use it
             * to configure the package.
             *
             * If you don't provide credentials, the Firebase Admin SDK will try to
             * auto-discover them
             *
             * - by checking the environment variable FIREBASE_CREDENTIALS
             * - by checking the environment variable GOOGLE_APPLICATION_CREDENTIALS
             * - by trying to find Google's well known file
             * - by checking if the application is running on GCE/GCP
             *
             * If no credentials file can be found, an exception will be thrown the
             * first time you try to access a component of the Firebase Admin SDK.
             *
             */

            //'credentials' => env('FIREBASE_CREDENTIALS', env('GOOGLE_APPLICATION_CREDENTIALS')),
            'credentials' => [
                "type" => "service_account",
                "project_id" => "jaco-app-d3d16",
                "private_key_id" => "b7b66c7b04c6dbac37f954d13aec0a9d86654f01",
                "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDoWkcJe1QmTa/a\n70tOCsqz15lQSH9RYbjPqw3hE+Nmf1d9J/ddkOqtiqv/5s94hUm8bQky1B/6HbRq\n8FDRAmEKImudHKjtn074O+HoHmB6nioqgJ22b86VX1pLRQ4MKvmw66dMIszUbIf4\nowmAXYhdnTjTEcfj7eQ/Veu7A455IW+aVVJPLMn2kxUgs2KQ6uDthlUI5RpSAVII\njo1peVxPZhFZaZdh64aSzlTIV2t5GfNHfM1/Dt33fewOhQBU/SJvQpvGG2o8fITK\nGj7KJk9Z0agAMAAn5jsS/tT8XJuOmQyhSLk2/JF1PF45Hkv3H6gUYpF5LR/LztBT\nr1UpUrrjAgMBAAECggEAESILB+t0pohm9bVcb6pDDSTSNMXLwZP7nTCHSvf89iNg\nr6VhS6BydY56pVl/JetqJv9BROK4YuiK07HZu7e5sRYdqk2hNThgidRckZGf2prM\nECtAteGjsYcA8PjsEsCefcm6odYVFXrVQeO/wRvkgMalmIbAnxF9GaB7y+P0vwCw\nsrvXE0URS+WQcl7t25YNYLLs/oHhRQc/VYGpDdRCX7QTV+MyzVmEBZ6pTw6+bOlt\nLquDyt4h8pxBSv1T1s/p+Mi4ZbSdR+2F6ZYgdlJj65FZIgMfP9WZqCjcpE1P9UYl\not785eSASR2H7hTE5lAI2sY1qOVEbsGa938LYK88RQKBgQD0De40WQSQh3kGXF31\nG8T9UtCDvY20YdZBvPjsoH30W3YOsaxFF5mTIqkR4+tN2DYa0a4+YKnYLCpdrnuQ\nSesqiaEG8Um5KaoRO5Z+u+LRAWzm3SRzxvYF112PHHW4y25aagBJn4N0tQjZrnfh\np1mtEuiUMQNHTT2w2JfE9Lz1/QKBgQDzubh4QELXMcO3oIeHi/we19p3Pa48R0i8\nm7xFFiZHPjXvNyqLjakZ3S8lFRpuAl8IegIGosF5Ijh2+VAJFGFcwA/db+ATNPK4\nrsz1Z2a8P5ZT/XIzCgqW+9+9lrGAA1cKavQBrPWsOfkjJErgSCYDwbfXPZKIXnZV\nHL5cVMLaXwKBgCFTSaSiRypJJXCF6lqO6S5CQbDLkG8CMRSW7lK5c5mLZ6qH/mMM\n3u6le/qyaa4eiPzOhwGDh426MirKqZrBTThoxLFC/3MmSk/CGEHD/CvYCvvXVKPu\nliekCeWB2F1cgfwcn19uRbAJDGVVGo+Yn3kvrMyXiMASvwrH+KlvVeItAoGARM6b\n26cYt7Qe8wpo+MV2aia6UIujUvU0/bphzodQSzCu/VfvtPJLWTfuhWON0Jzuw2/e\nfo0YXeBhXIVKKSYNzqeSUwuwkPuTwrvmtSl6AY9aG2AkC3C8SJ/XLKkMd31550EG\nnip8OSEsytUGJod34/uesS963PD+K00wGNT9JbUCgYAiD5NDfjzAyKZFSyW5m8h/\n4vCaQuqW2NW/PAviAuYv+SCjwk9e6kGSVmBIRo9WIsbostLTQxuv1bRhwXWHpFK+\nPVzkiI6Vr6pqvly+Eb7SMODPz76qH61uDFnVqLdJY6HZfFDfQ+KHE4t3kgNYFfG7\nvICxd+nEKiO76zmNuzbs2A==\n-----END PRIVATE KEY-----\n",
                "client_email" => "firebase-adminsdk-qlvem@jaco-app-d3d16.iam.gserviceaccount.com",
                "client_id" => "105586885670647344231",
                "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
                "token_uri" => "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-qlvem%40jaco-app-d3d16.iam.gserviceaccount.com",
                "universe_domain" => "googleapis.com"
            ],
            /*
             * ------------------------------------------------------------------------
             * Firebase Auth Component
             * ------------------------------------------------------------------------
             */

            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firestore Component
             * ------------------------------------------------------------------------
             */

            'firestore' => [

                /*
                 * If you want to access a Firestore database other than the default database,
                 * enter its name here.
                 *
                 * By default, the Firestore client will connect to the `(default)` database.
                 *
                 * https://firebase.google.com/docs/firestore/manage-databases
                 */

                // 'database' => env('FIREBASE_FIRESTORE_DATABASE'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Realtime Database
             * ------------------------------------------------------------------------
             */

            'database' => [

                /*
                 * In most of the cases the project ID defined in the credentials file
                 * determines the URL of your project's Realtime Database. If the
                 * connection to the Realtime Database fails, you can override
                 * its URL with the value you see at
                 *
                 * https://console.firebase.google.com/u/1/project/_/database
                 *
                 * Please make sure that you use a full URL like, for example,
                 * https://my-project-id.firebaseio.com
                 */

                'url' => env('FIREBASE_DATABASE_URL'),

                /*
                 * As a best practice, a service should have access to only the resources it needs.
                 * To get more fine-grained control over the resources a Firebase app instance can access,
                 * use a unique identifier in your Security Rules to represent your service.
                 *
                 * https://firebase.google.com/docs/database/admin/start#authenticate-with-limited-privileges
                 */

                // 'auth_variable_override' => [
                //     'uid' => 'my-service-worker'
                // ],

            ],

            'dynamic_links' => [

                /*
                 * Dynamic links can be built with any URL prefix registered on
                 *
                 * https://console.firebase.google.com/u/1/project/_/durablelinks/links/
                 *
                 * You can define one of those domains as the default for new Dynamic
                 * Links created within your project.
                 *
                 * The value must be a valid domain, for example,
                 * https://example.page.link
                 */

                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Cloud Storage
             * ------------------------------------------------------------------------
             */

            'storage' => [

                /*
                 * Your project's default storage bucket usually uses the project ID
                 * as its name. If you have multiple storage buckets and want to
                 * use another one as the default for your application, you can
                 * override it here.
                 */

                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),

            ],

            /*
             * ------------------------------------------------------------------------
             * Caching
             * ------------------------------------------------------------------------
             *
             * The Firebase Admin SDK can cache some data returned from the Firebase
             * API, for example Google's public keys used to verify ID tokens.
             *
             */

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
             * ------------------------------------------------------------------------
             * Logging
             * ------------------------------------------------------------------------
             *
             * Enable logging of HTTP interaction for insights and/or debugging.
             *
             * Log channels are defined in config/logging.php
             *
             * Successful HTTP messages are logged with the log level 'info'.
             * Failed HTTP messages are logged with the log level 'notice'.
             *
             * Note: Using the same channel for simple and debug logs will result in
             * two entries per request and response.
             */

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * HTTP Client Options
             * ------------------------------------------------------------------------
             *
             * Behavior of the HTTP Client performing the API requests
             */

            'http_client_options' => [

                /*
                 * Use a proxy that all API requests should be passed through.
                 * (default: none)
                 */

                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),

                /*
                 * Set the maximum amount of seconds (float) that can pass before
                 * a request is considered timed out
                 *
                 * The default time out can be reviewed at
                 * https://github.com/kreait/firebase-php/blob/6.x/src/Firebase/Http/HttpClientOptions.php
                 */

                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT'),

                'guzzle_middlewares' => [],
            ],
        ],
    ],
];
