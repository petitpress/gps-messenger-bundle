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
    /**
     * {@inheritdoc}
     */
    public function resolve(string $dsn, array $options): GpsConfigurationInterface
    {
        // not relevant options for transport itself
        unset($options['transport_name'], $options['serializer']);

        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefault('max_messages_pull', self::DEFAULT_MAX_MESSAGES_PULL)
            ->setDefault('topic', static function (OptionsResolver $topicResolver): void {
                $topicResolver
                    ->setDefault('name', self::DEFAULT_TOPIC_NAME)
                    ->setAllowedTypes('name', 'string')
                ;
            })
            ->setDefault('queue', static function (OptionsResolver $queueResolver, Options $parentOptions): void {
                $queueResolver
                    ->setDefault('name', $parentOptions['topic']['name'])
                    ->setAllowedTypes('name', 'string')
                ;
            })
            ->setNormalizer('max_messages_pull', static function (Options $options, $value): ?int {
                return ((int) filter_var($value, FILTER_SANITIZE_NUMBER_INT)) ?: null;
            })
            ->setAllowedTypes('max_messages_pull', ['int', 'string'])
        ;

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

        $resolvedOptions = $optionsResolver->resolve(array_merge($dnsOptions, $options));

        return new GpsConfiguration(
            $resolvedOptions['topic']['name'],
            $resolvedOptions['queue']['name'],
            $resolvedOptions['max_messages_pull'],
        );
    }
}