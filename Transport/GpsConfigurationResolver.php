<?php

declare(strict_types=1);

namespace PetitPress\GpsMessengerBundle\Transport;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ronald Marfoldi <ronald.marfoldi@petitpress.sk>
 */
final class GpsConfigurationResolver implements GpsConfigurationResolverInterface
{
    private const INT_NORMALIZER_KEY = 'int';
    private const BOOL_NORMALIZER_KEY = 'bool';
    private const NORMALIZABLE_SUBSCRIPTION_OPTIONS = [
        self::INT_NORMALIZER_KEY => ['ackDeadlineSeconds', 'maxDeliveryAttempts'],
        self::BOOL_NORMALIZER_KEY => ['enableMessageOrdering', 'retainAckedMessages', 'enableExactlyOnceDelivery', 'enableCreation'],
    ];
    private const NORMALIZABLE_SUBSCRIPTION_PULL_OPTIONS = [
        self::INT_NORMALIZER_KEY => ['maxMessages'],
        self::BOOL_NORMALIZER_KEY => ['returnImmediately', 'enableCreation'],
    ];

    /**
     * {@inheritdoc}
     */
    public function resolve(string $dsn, array $options): GpsConfigurationInterface
    {
        // not relevant options for transport itself
        unset($options['transport_name'], $options['serializer']);

        $subscriptionOptionsNormalizer = static function (Options $options, $data) {
            foreach ($data ?? [] as $optionName => $optionValue) {
                switch ($optionName) {
                    case \in_array($optionName, self::NORMALIZABLE_SUBSCRIPTION_OPTIONS[self::INT_NORMALIZER_KEY], true):
                        $data[$optionName] = (int) filter_var($optionValue, FILTER_SANITIZE_NUMBER_INT);
                        break;
                    case \in_array($optionName, self::NORMALIZABLE_SUBSCRIPTION_OPTIONS[self::BOOL_NORMALIZER_KEY], true):
                        $data[$optionName] = filter_var($optionValue, FILTER_VALIDATE_BOOLEAN);
                        break;
                }
            }

            return $data;
        };

        $subscriptionPullOptionsNormalizer = static function (Options $options, $data) {
            foreach ($data ?? [] as $optionName => $optionValue) {
                switch ($optionName) {
                    case \in_array($optionName, self::NORMALIZABLE_SUBSCRIPTION_PULL_OPTIONS[self::INT_NORMALIZER_KEY], true):
                        $data[$optionName] = (int) filter_var($optionValue, FILTER_SANITIZE_NUMBER_INT);
                        break;
                    case \in_array($optionName, self::NORMALIZABLE_SUBSCRIPTION_PULL_OPTIONS[self::BOOL_NORMALIZER_KEY], true):
                        $data[$optionName] = filter_var($optionValue, FILTER_VALIDATE_BOOLEAN);
                        break;
                }
            }

            return $data;
        };

        $mergedOptions = $this->getMergedOptions($dsn, $options);

        $optionsResolver = new OptionsResolver();
        if (isset($mergedOptions['queue'])) {
            $optionsResolver
                ->setDefault(
                    'queue',
                    function (OptionsResolver $resolver, Options $parentOptions) use ($subscriptionOptionsNormalizer): void {
                        $resolver
                            ->setDefault('name', $parentOptions['topic']['name'])
                            ->setDefault('options', [])
                            ->setAllowedTypes('name', 'string')
                            ->setAllowedTypes('options', 'array')
                            ->setNormalizer('options', $subscriptionOptionsNormalizer)
                        ;
                    }
                )
                ->setDeprecated(
                    'queue',
                    'petitpress/gps-messenger-bundle',
                    '1.3.0',
                    'The option "queue" is deprecated, use option "subscription" instead.'
                )
            ;
        }

        if (isset($mergedOptions['max_messages_pull'])) {
            $optionsResolver
                ->setDefault('max_messages_pull', self::DEFAULT_MAX_MESSAGES_PULL)
                ->setNormalizer('max_messages_pull', static function (Options $options, $value): ?int {
                    return ((int) filter_var($value, FILTER_SANITIZE_NUMBER_INT)) ?: null;
                })
                ->setAllowedTypes('max_messages_pull', ['int', 'string'])
                ->setDeprecated(
                    'max_messages_pull',
                    'petitpress/gps-messenger-bundle',
                    '1.6.0',
                    'The option "max_messages_pull" is deprecated, use option "subscription.pull.maxMessages" instead.'
                )
            ;
        }

        $optionsResolver
            ->setDefault('client_config', [])
            ->setDefault('topic', function (OptionsResolver $topicResolver): void {
                $topicResolver
                    ->setDefault('name', self::DEFAULT_TOPIC_NAME)
                    ->setDefault('enableCreation', true)
                    ->setDefault('options', [])
                    ->setAllowedTypes('name', 'string')
                    ->setAllowedTypes('options', 'array')
                ;
            })
            ->setDefault(
                'subscription',
                function (OptionsResolver $resolver, Options $parentOptions) use ($subscriptionOptionsNormalizer, $subscriptionPullOptionsNormalizer): void {
                    if ($parentOptions->offsetExists('queue')) {
                        $resolver
                            ->setDefault('name', $parentOptions['queue']['name'])
                            ->setDefault('options', $parentOptions['queue']['options'])
                            ->setDefault(
                                'pull',
                                function (OptionsResolver $pullResolver) use ($parentOptions): void {
                                    $pullResolver
                                        ->setDefault('maxMessages', $parentOptions->offsetExists('max_messages_pull') ? $parentOptions['max_messages_pull'] : self::DEFAULT_MAX_MESSAGES_PULL)
                                        ->setDefault('returnImmediately', false)
                                    ;
                                }
                            )
                            ->setAllowedTypes('name', 'string')
                            ->setAllowedTypes('options', 'array')
                            ->setAllowedTypes('pull', 'array')
                            ->setNormalizer('pull', $subscriptionPullOptionsNormalizer)
                        ;

                        return;
                    }

                    $resolver
                        ->setDefault('name', $parentOptions['topic']['name'])
                        ->setDefault('enableCreation', true)
                        ->setDefault('options', [])
                        ->setDefault(
                            'pull',
                            function (OptionsResolver $pullResolver) use ($parentOptions): void {
                                $pullResolver
                                    ->setDefault('maxMessages', $parentOptions->offsetExists('max_messages_pull') ? $parentOptions['max_messages_pull'] : self::DEFAULT_MAX_MESSAGES_PULL)
                                    ->setDefault('returnImmediately', false)
                                ;
                            }
                        )
                        ->setAllowedTypes('name', 'string')
                        ->setAllowedTypes('options', 'array')
                        ->setAllowedTypes('pull', 'array')
                        ->setNormalizer('options', $subscriptionOptionsNormalizer)
                        ->setNormalizer('pull', $subscriptionPullOptionsNormalizer)
                    ;
                }
            )
            ->setAllowedTypes('client_config', 'array')
        ;

        $resolvedOptions = $optionsResolver->resolve($mergedOptions);

        return new GpsConfiguration(
            $resolvedOptions['topic']['name'],
            $resolvedOptions['topic']['enableCreation'],
            $resolvedOptions['subscription']['name'],
            $resolvedOptions['subscription']['enableCreation'],
            $resolvedOptions['client_config'],
            $resolvedOptions['topic']['options'],
            $resolvedOptions['subscription']['options'],
            $resolvedOptions['subscription']['pull']
        );
    }

    private function getMergedOptions(string $dsn, array $options): array
    {
        $dnsOptions = [];
        $parsedDnsOptions = parse_url($dsn);

        $dsnQueryOptions = $parsedDnsOptions['query'] ?? null;
        if ($dsnQueryOptions) {
            parse_str($dsnQueryOptions, $dnsOptions);
        }

        $dnsPathOption = $parsedDnsOptions['path'] ?? null;
        if ($dnsPathOption) {
            $dnsOptions['topic']['name'] = substr($dnsPathOption, 1);
        }

        return array_merge($dnsOptions, $options);
    }
}
