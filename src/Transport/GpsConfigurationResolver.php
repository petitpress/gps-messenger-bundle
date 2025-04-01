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
        self::BOOL_NORMALIZER_KEY => ['enableMessageOrdering', 'retainAckedMessages', 'enableExactlyOnceDelivery'],
    ];
    private const NORMALIZABLE_SUBSCRIPTION_PULL_OPTIONS = [
        self::INT_NORMALIZER_KEY => ['maxMessages'],
        self::BOOL_NORMALIZER_KEY => ['returnImmediately'],
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
        $optionsResolver
            ->setDefault('client_config', [])
            ->setDefault('topic', function (OptionsResolver $topicResolver): void {
                $topicResolver
                    ->setDefault('name', self::DEFAULT_TOPIC_NAME)
                    ->setDefault('createIfNotExist', true)
                    ->setDefault('options', [])
                    ->setAllowedTypes('name', 'string')
                    ->setAllowedTypes('createIfNotExist', 'bool')
                    ->setAllowedTypes('options', 'array')
                ;
            })
            ->setDefault(
                'subscription',
                function (OptionsResolver $resolver, Options $parentOptions) use ($subscriptionOptionsNormalizer, $subscriptionPullOptionsNormalizer): void {
                    $resolver
                        ->setDefault('name', $parentOptions['topic']['name'])
                        ->setDefault('createIfNotExist', true)
                        ->setDefault('options', [])
                        ->setDefault(
                            'pull',
                            function (OptionsResolver $pullResolver): void {
                                $pullResolver
                                    ->setDefault('maxMessages', self::DEFAULT_MAX_MESSAGES_PULL)
                                    ->setDefault('returnImmediately', false)
                                    ->setDefined('timeoutMillis')
                                    ->setAllowedTypes('timeoutMillis', 'int')
                                ;
                            }
                        )
                        ->setAllowedTypes('name', 'string')
                        ->setAllowedTypes('createIfNotExist', 'bool')
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
            $resolvedOptions['topic']['createIfNotExist'],
            $resolvedOptions['subscription']['name'],
            $resolvedOptions['subscription']['createIfNotExist'],
            $resolvedOptions['client_config'],
            $resolvedOptions['topic']['options'],
            $resolvedOptions['subscription']['options'],
            $resolvedOptions['subscription']['pull']
        );
    }

    /**
     * @param array<string, mixed>  $options
     *
     * @return array<int|string, mixed>
     */
    private function getMergedOptions(string $dsn, array $options): array
    {
        $dnsOptions = [];
        $parsedDnsOptions = parse_url($dsn);

        $dsnQueryOptions = $parsedDnsOptions['query'] ?? null;
        if ($dsnQueryOptions !== null && $dsnQueryOptions !== '') {
            parse_str($dsnQueryOptions, $dnsOptions);
        }

        $dnsPathOption = $parsedDnsOptions['path'] ?? null;
        if ($dnsPathOption !== null && $dnsPathOption !== '') {
            if (! isset($dnsOptions['topic']) || ! is_array($dnsOptions['topic'])) {
                $dnsOptions['topic'] = [];
            }
            $dnsOptions['topic']['name'] = substr($dnsPathOption, 1);
        }

        if (isset($dnsOptions['topic']['createIfNotExist'])) {
            $dnsOptions['topic']['createIfNotExist'] = $this->toBool($dnsOptions['topic']['createIfNotExist'], true);
        }

        if (isset($dnsOptions['subscription']['createIfNotExist'])) {
            $dnsOptions['subscription']['createIfNotExist'] = $this->toBool($dnsOptions['subscription']['createIfNotExist'], true);
        }

        return array_merge($dnsOptions, $options);
    }

    private function toBool(string $value, bool $default): bool
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $result ?? $default;
    }
}
