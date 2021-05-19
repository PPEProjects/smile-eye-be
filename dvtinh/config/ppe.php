<?php
return [
    'id'                  => env('APP_ID', 'ppe'),
    'frontend_url'        => env('FRONTEND_URL'),
    'app_version'         => env('APP_VERSION', 1),
    'common'              => [
        'per_page'  => 10,
        'jwt_token' => env('JWT_TOKEN', 'sohead-secret'),
        'ssl'       => env('JWT_SSL'),
    ],
    'core_db_connections' => env('DB_CORE_CONNECTION', 'ppe_core'),
    'platform'            => [
        'shopify' => [
            'scopes'           => [
                'read_products',
                'read_content',
                'write_content',
                'read_themes',
                'write_themes',
            ],
            'next_scope'           => [
                'read_products',
                'read_content',
                'write_content',
                'read_themes',
                'write_themes',
            ],
            'callback_url'            => env('APP_URL').'/api/platform/auth-handle/shopify',
            'api_key'                 => env('SPF_API_KEY'),
            'api_secret'              => env('SPF_SECRET_KEY'),
            'charge'                  => false,
            'charge_success_callback' => env('FRONTEND_URL'),
            'on_boarding'             => [
                'step_on_boarding' => false,
                'sidebar' => false
            ],
            'app_plan'                => [
                'test' => [
                    'name'       => 'test',
                    'price'      => 10,
                    'trial_days' => 0,
                    'test'       => true
                ],
            ],
            'webhook'                 => [
                [
                    'address' => env('APP_URL_WEBHOOK').'/api/shopify/webhook',
                    'topic'   => 'app/uninstalled',
                ],
                [
                    'address' => env('APP_URL_WEBHOOK').'/api/shopify/webhook',
                    'topic'   => 'shop/update',
                ],
                [
                    'address' => env('APP_URL_WEBHOOK').'/api/shopify/webhook',
                    'topic'   => 'themes/publish',
                ]
            ],
            'api_version'             => '2020-07',
        ],
    ],
    'shopify'             => [
        'scope'           => [
            'read_products',
            'write_content',
            'write_themes',
        ],
        'next_scope'           => [
            'read_products',
            'write_content',
            'write_themes',
        ],
        'redirect_url'            => env('APP_URL').'/api/shopify/auth-handle',
        'spf_api_key'             => env('SPF_API_KEY'),
        'spf_secret_key'          => env('SPF_SECRET_KEY'),
        'charge'                  => false,
        'charge_success_callback' => env('FRONTEND_URL'),
        'on_boarding'             => [
            'step_on_boarding' => false,
            'sidebar' => false
        ],
        'app_plan'                => [
            'test' => [
                'name'       => 'test',
                'price'      => 10,
                'trial_days' => 0,
                'test'       => true
            ],
        ],
    ],
    'social'              => [
        'action'   => [
            'auth'    => 'authentication',
            're_auth' => 're-authentication'
        ],
        'facebook' => [
            'auth'           => [
                'key'    => env('FACEBOOK_KEY'),
                'secret' => env('FACEBOOK_SECRET')
            ],
            'base_graph_url' => 'https://graph.facebook.com/',
            'graph_version'  => 'v7.0',
            'base_url'       => 'https://www.facebook.com/',
            'callback_url'   => env('APP_URL').'/api/social/auth-handle',
            'permissions'    => [
                'email',
                'public_profile',
                'pages_messaging',
                'pages_messaging_subscriptions',
                'manage_pages',
                'pages_show_list',
                'publish_pages',
            ],
            'fields'         => [
                'email',
                'name',
                'picture'
            ],
            'identify'       => 'facebook',
            'social_type'    => [
                'facebook' => 'page',
                'user'     => 'user'
            ],
            'reconnect_code' => [10, 190],
            'webhook'        => [
                'item'     => [
                    'comment'  => 'comment',
                    'reaction' => 'reaction'
                ],
                'comment'  => [
                    'verb' => [
                        'add'    => 'add',
                        'edited' => 'edited',
                        'remove' => 'remove'
                    ]
                ],
                'reaction' => [
                    'verb' => [
                        'add'    => 'add',
                        'edited' => 'edit',
                        'remove' => 'remove'
                    ],
                    'type' => [
                        'wow'   => 'wow',
                        'like'  => 'like',
                        'love'  => 'love',
                        'haha'  => 'haha',
                        'sad'   => 'sad',
                        'angry' => 'angry'
                    ]
                ]

            ]
        ],
        'twitter'  => [
            'social_name'                 => 'twitter',
            'identify'                    => 'twitter',
            'api_version'                 => '1.1',
            'url'                         => [
                'base'           => 'https://api.twitter.com/',
                'authorize'      => 'https://api.twitter.com/oauth/authorize',
                'search_hashtag' => 'https://twitter.com/i/search/typeahead.json?filters=true&result_type=topics&src=COMPOSE&count=20&q=%23',
            ],
            'callback_url'                => env('APP_URL').'/api/social/auth-handle',
            'media'                       => [
                'media_category' => [
                    'tweet' => [
                        'tweet_video' => 'tweet_video',
                        'tweet_image' => 'tweet_image',
                        'tweet_gif'   => 'tweet_gif',
                    ],
                    'dm'    => [
                        'dm_video' => 'dm_video',
                        'dm_image' => 'dm_image',
                        'dm_gif'   => 'dm_gif',
                    ],
                ],
                'type_image'     => [
                    'png',
                    'jpeg',
                    'jpg',
                ],
                'type_gif'       => [
                    'gif',
                ],
                'type_video'     => [
                    'mp4',
                ],
            ],
            'consumer_api_key'            => env('TWITTER_CONSUMER_API_KEY'),
            'consumer_api_api_secret_key' => env('TWITTER_CONSUMER_API_API_SECRET_KEY'),
            'access_token'                => env('TWITTER_ACCESS_TOKEN'),
            'access_token_secret'         => env('TWITTER_ACCESS_TOKEN_SECRET'),
            'errors_code_account'         => [64, 89, 93, 99, 220, 326],
            'app_name'                    => [
                'dev'        => 'test.login.bcd',
                'production' => 'test.login.bcd',
                'display'    => 'Social Publish'
            ]
        ],
        'google'   => [
            'client_id'     => env('GOOGLE_KEY'),
            'client_secret' => env('GOOGLE_SECRET'),
            'callback_url'  => env('APP_URL').'/api/social/auth-handle',
            'scopes'        => [
                'openid',
                'profile',
                'email',
            ],
            'status'        => [
                'pending'     => 'PENDING',
                'approved'    => 'APPROVED',
                'disapproved' => 'DISAPPROVED',
                'errors'      => 'ERRORS'
            ]
        ],
        'instagram_basic' => [
            'client_id'     => env('INSTAGRAM_KEY'),
            'client_secret' => env('INSTAGRAM_SECRET'),
            'callback_url'  => env('APP_URL').'/api/social/auth-handle',
            'scopes'        => [
                'user_profile',
                'user_media'
            ]
        ]
    ],
    'admin' => [
        'url' => env('ADMIN_URL'),
        'jwt_secret_key' => env('ADMIN_JWT_SECRET_KEY'),
    ],
    'chat_session_key' => 'HKjDjpX7rp',
    'app_env' => env('APP_ENV'),
    'debug_token' => env('DEBUG_TOKEN', 'socialhead!')
];
