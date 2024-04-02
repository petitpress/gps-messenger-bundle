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
        self::BOOL_NORMALIZER_KEY => ['enableMessageOrdering', 'retainAckedMessages', 'enableExactlyOnceDelivery', 'createIfNotExist'],
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

        $mergedOptions = $this->getMergedOptions($dsn, $options);
        $resolvedOptions = [];

        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefault('name', self::DEFAULT_TOPIC_NAME)
            ->setDefault('createIfNotExist', true)
            ->setDefault('options', [])
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('options', 'array');
        $resolvedOptions['topic'] = $optionsResolver->resolve($mergedOptions['topic'] ?? []);

        $resolvedOptions['subscription'] = $this->resolveSubscription(
            $mergedOptions,
            $subscriptionOptionsNormalizer
        );

        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefault('client_config', [])
            ->setNormalizer('client_config', static function (Options $options, $value): array {
                if (isset($value['keyFile']) && is_string($value['keyFile'])) {
                    $value['keyFile'] = json_decode(base64_decode($value['keyFile']), true, 512, JSON_THROW_ON_ERROR);
                }

                $value['suppressKeyFileNotice'] = true;

                return $value;
            })
            ->setDefault('max_messages_pull', self::DEFAULT_MAX_MESSAGES_PULL)
            ->setDefault('topic', [])
            ->setDefault('subscription', [])
            ->setNormalizer('max_messages_pull', static function (Options $options, $value): ?int {
                return ((int) filter_var($value, FILTER_SANITIZE_NUMBER_INT)) ?: null;
            })
            ->setAllowedTypes('max_messages_pull', ['int', 'string'])
            ->setAllowedTypes('topic', 'array')
            ->setAllowedTypes('subscription', 'array')
        ;

        $resolvedOptions = array_merge($optionsResolver->resolve($mergedOptions), $resolvedOptions);
        return new GpsConfiguration(
            $resolvedOptions['topic']['name'],
            $resolvedOptions['topic']['createIfNotExist'],
            $resolvedOptions['subscription']['name'],
            $resolvedOptions['subscription']['createIfNotExist'] ?? true,
            $resolvedOptions['max_messages_pull'],
            $resolvedOptions['client_config'],
            $resolvedOptions['topic']['options'],
            $resolvedOptions['subscription']['options'],
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

       if (isset($dnsOptions['topic']['createIfNotExist'])) {
            $dnsOptions['topic']['createIfNotExist'] = $this->toBool($dnsOptions['topic']['createIfNotExist'], true);
       }

        if (isset($dnsOptions['subscription']['createIfNotExist'])) {
            $dnsOptions['subscription']['createIfNotExist'] = $this->toBool($dnsOptions['subscription']['createIfNotExist'], true);
        }

        return array_merge($dnsOptions, $options);
    }

    private function resolveSubscription(array $mergedOptions, \Closure $subscriptionOptionsNormalizer): array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefault('name', $mergedOptions['topic']['name'] ?? self::DEFAULT_TOPIC_NAME)
            ->setDefault('createIfNotExist', true)
            ->setDefault('options', [])
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('options', 'array')
            ->setNormalizer('options', $subscriptionOptionsNormalizer);

        return $optionsResolver->resolve($mergedOptions['subscription'] ?? []);
    }

    private function toBool(string $value, bool $default): bool
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $result ?? $default;
    }
}
