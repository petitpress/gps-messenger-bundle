<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use PetitPress\GpsMessengerBundle\Transport\GpsConfiguration;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolver;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolverInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsConfigurationTest extends TestCase
{
    private GpsConfigurationResolver $gpsConfigurationResolver;

    protected function setUp(): void
    {
        $this->gpsConfigurationResolver = new GpsConfigurationResolver();
    }

    /**
     * @param array<string, mixed>                     $options
     */
    #[DataProvider('dataProvider')]
    public function testResolve(string $dsn, array $options, GpsConfigurationInterface $expectedConfiguration): void
    {
        $configuration = $this->gpsConfigurationResolver->resolve($dsn, $options);
        static::assertEquals($expectedConfiguration, $configuration);
    }

    /**
     * @return array<string, mixed>
     */
    public static function dataProvider(): array
    {
        return [
            'Empty default' => [
                'dsn' => 'gps://default',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => GpsConfigurationResolverInterface::DEFAULT_MAX_MESSAGES_PULL, 'returnImmediately' => false]
                ),
            ],
            'Custom topic/subscription name configured through dsn #1' => [
                'dsn' => 'gps://default/something',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'something',
                    true,
                    'something',
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => GpsConfigurationResolverInterface::DEFAULT_MAX_MESSAGES_PULL, 'returnImmediately' => false]
                ),
            ],
            'Custom topic/subscription name configured through dsn #2' => [
                'dsn' => 'gps://default?topic[name]=topic_name&topic[options][labels][]=label_topic1&subscription[name]=subscription_name&subscription[options][labels][]=label_subscription1&subscription[options][enableMessageOrdering]=1&subscription[options][ackDeadlineSeconds]=100&&subscription[pull][maxMessages]=5&client_config[apiEndpoint]=https://europe-west3-pubsub.googleapis.com',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'topic_name',
                    true,
                    'subscription_name',
                    true,
                    ['apiEndpoint' => 'https://europe-west3-pubsub.googleapis.com'],
                    ['labels' => ['label_topic1']],
                    ['labels' => ['label_subscription1'], 'enableMessageOrdering' => true, 'ackDeadlineSeconds' => 100],
                    ['maxMessages' => 5, 'returnImmediately' => false]
                ),
            ],
            'Custom topic/subscription name configured through options #1' => [
                'dsn' => 'gps://default',
                'options' => [
                    'topic' => ['name' => 'something'],
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    'something',
                    true,
                    'something',
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => GpsConfigurationResolverInterface::DEFAULT_MAX_MESSAGES_PULL, 'returnImmediately' => false]
                ),
            ],
            'Custom topic/subscription name configured through options #2' => [
                'dsn' => 'gps://default',
                'options' => [
                    'topic' => ['name' => 'topic_name'],
                    'subscription' => ['name' => 'subscription_name'],
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    'topic_name',
                    true,
                    'subscription_name',
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => GpsConfigurationResolverInterface::DEFAULT_MAX_MESSAGES_PULL, 'returnImmediately' => false]
                ),
            ],
            'Custom topic/subscription name configured through options #4' => [
                'dsn' => 'gps://default',
                'options' => [
                    'topic' => [
                        'name' => 'topic_name1',
                        'options' => [
                            'labels' => ['label_topic1'],
                        ],
                    ],
                    'subscription' => [
                        'name' => 'subscription_name',
                        'options' => [
                            'labels' => ['label_subscription1'],
                            'enableMessageOrdering' => true,
                            'ackDeadlineSeconds' => 100,
                        ],
                        'pull' => [
                            'maxMessages' => 5
                        ],
                    ],
                    'client_config' => [
                        'apiEndpoint' => 'https://europe-west3-pubsub.googleapis.com',
                    ],
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    'topic_name1',
                    true,
                    'subscription_name',
                    true,
                    ['apiEndpoint' => 'https://europe-west3-pubsub.googleapis.com'],
                    ['labels' => ['label_topic1']],
                    ['labels' => ['label_subscription1'], 'enableMessageOrdering' => true, 'ackDeadlineSeconds' => 100],
                    ['maxMessages' => 5, 'returnImmediately' => false]
                ),
            ],
            'Custom subscription pull options configured through dsn #1' => [
                'dsn' => 'gps://default?subscription[pull][maxMessages]=5',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => 5, 'returnImmediately' => false]
                ),
            ],
            'Custom subscription pull options configured through options #1' => [
                'dsn' => 'gps://default',
                'options' => [
                    'subscription' => [
                        'pull' => [
                            'maxMessages' => 5,
                            'returnImmediately' => true,
                        ]
                    ],
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => 5, 'returnImmediately' => true]
                ),
            ],
            'Custom subscription pull options configured through options #2' => [
                'dsn' => 'gps://default',
                'options' => [
                    'subscription' => [
                        'pull' => [
                            'returnImmediately' => true,
                            'maxMessages' => 5,
                        ]
                    ],
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => 5, 'returnImmediately' => true]
                ),
            ],
            'Subscription is not created' => [
                'dsn' => 'gps://default',
                'options' => [
                    'subscription' => [
                        'createIfNotExist' => false
                    ],
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    false,
                    [],
                    [],
                    [],
                    ['maxMessages' => 10, 'returnImmediately' => false]
                ),
            ],
            'Topic is not created' => [
                'dsn' => 'gps://default',
                'options' => [
                    'topic' => [
                        'createIfNotExist' => false
                    ],
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    false,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => 10, 'returnImmediately' => false]
                ),
            ],
            'DSN: Subscription is not created' => [
                'dsn' => 'gps://default?topic[name]=foo&subscription[name]=bar&subscription[createIfNotExist]=false',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'foo',
                    true,
                    'bar',
                    false,
                    [],
                    [],
                    [],
                    ['maxMessages' => 10, 'returnImmediately' => false]
                ),
            ],
            'DSN: Subscription is not created #2' => [
                'dsn' => 'gps://default?topic[name]=foo&topic[createIfNotExist]=true&subscription[name]=bar&subscription[createIfNotExist]=false',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'foo',
                    true,
                    'bar',
                    false,
                    [],
                    [],
                    [],
                    ['maxMessages' => 10, 'returnImmediately' => false]
                ),
            ],
            'DSN: Topic is not created' => [
                'dsn' => 'gps://default?topic[name]=foo&topic[createIfNotExist]=false&subscription[name]=bar&subscription[createIfNotExist]=true',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'foo',
                    false,
                    'bar',
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => 10, 'returnImmediately' => false]
                ),
            ],
            'DSN: Topic is not created #2' => [
                'dsn' => 'gps://default?topic[name]=foo&topic[createIfNotExist]=false&subscription[name]=bar',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'foo',
                    false,
                    'bar',
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => 10, 'returnImmediately' => false]
                ),
            ],
            'DSN: createIfNotExist contains invalid value' => [
                'dsn' => 'gps://default?topic[name]=foo&topic[createIfNotExist]=quux&subscription[name]=bar',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'foo',
                    true,
                    'bar',
                    true,
                    [],
                    [],
                    [],
                    ['maxMessages' => 10, 'returnImmediately' => false]
                ),
            ],
        ];
    }
}
