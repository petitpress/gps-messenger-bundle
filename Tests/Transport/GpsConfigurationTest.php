<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Tests\Transport;

use PetitPress\GpsMessengerBundle\Transport\GpsConfiguration;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationInterface;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolver;
use PetitPress\GpsMessengerBundle\Transport\GpsConfigurationResolverInterface;
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
     * @dataProvider dataProvider
     */
    public function testResolve(string $dsn, array $options, GpsConfigurationInterface $expectedConfiguration): void
    {
        $configuration = $this->gpsConfigurationResolver->resolve($dsn, $options);
        $this->assertEquals($expectedConfiguration, $configuration);
    }

    public static function dataProvider(): array
    {
        return [
            'Empty default' => [
                'dsn' => 'gps://default',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
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
                    'something',
                    [],
                    [],
                    [],
                    ['maxMessages' => GpsConfigurationResolverInterface::DEFAULT_MAX_MESSAGES_PULL, 'returnImmediately' => false]
                ),
            ],
            'Custom topic/subscription name configured through dsn #2 (deprecated queue[name])' => [
                'dsn' => 'gps://default?topic[name]=topic_name&queue[name]=subscription_name',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'topic_name',
                    'subscription_name',
                    [],
                    [],
                    [],
                    ['maxMessages' => GpsConfigurationResolverInterface::DEFAULT_MAX_MESSAGES_PULL, 'returnImmediately' => false]
                ),
            ],
            'Custom topic/subscription name configured through dsn #3' => [
                'dsn' => 'gps://default?topic[name]=topic_name&topic[options][labels][]=label_topic1&subscription[name]=subscription_name&subscription[options][labels][]=label_subscription1&subscription[options][enableMessageOrdering]=1&subscription[options][ackDeadlineSeconds]=100&max_messages_pull=5&client_config[apiEndpoint]=https://europe-west3-pubsub.googleapis.com',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    'topic_name',
                    'subscription_name',
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
                    'something',
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
                    'subscription_name',
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
                    ],
                    'client_config' => [
                        'apiEndpoint' => 'https://europe-west3-pubsub.googleapis.com',
                    ],
                    'max_messages_pull' => 5,
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    'topic_name1',
                    'subscription_name',
                    ['apiEndpoint' => 'https://europe-west3-pubsub.googleapis.com'],
                    ['labels' => ['label_topic1']],
                    ['labels' => ['label_subscription1'], 'enableMessageOrdering' => true, 'ackDeadlineSeconds' => 100],
                    ['maxMessages' => 5, 'returnImmediately' => false]
                ),
            ],
            'Custom subscription pull options configured through dsn #1 (deprecated max_messages_pull)' => [
                'dsn' => 'gps://default?max_messages_pull=5',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    [],
                    [],
                    [],
                    ['maxMessages' => 5, 'returnImmediately' => false]
                ),
            ],
            'Custom subscription pull options configured through dsn #2' => [
                'dsn' => 'gps://default?subscription[pull][maxMessages]=5',
                'options' => [],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
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
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    [],
                    [],
                    [],
                    ['maxMessages' => 5, 'returnImmediately' => true]
                ),
            ],
            'Custom subscription pull options configured through options #2' => [
                'dsn' => 'gps://default',
                'options' => [
                    'max_messages_pull' => 5,
                    'subscription' => [
                        'pull' => [
                            'returnImmediately' => true,
                        ]
                    ],
                ],
                'expectedConfiguration' => new GpsConfiguration(
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    GpsConfigurationResolverInterface::DEFAULT_TOPIC_NAME,
                    [],
                    [],
                    [],
                    ['maxMessages' => 5, 'returnImmediately' => true]
                ),
            ],
        ];
    }
}
