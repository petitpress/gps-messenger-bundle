<?php

namespace PetitPress\GpsMessengerBundle\DependencyInjection;

trigger_deprecation('petitpress/gps-messenger-bundle', '1.4', 'The class "%s" is deprecated, use "%s" instead.', __NAMESPACE__.'\GpsMessengerExtension', PetitPressGpsMessengerExtension::class);

class_alias(
    PetitPressGpsMessengerExtension::class,
    __NAMESPACE__.'\GpsMessengerExtension'
);

if (false) {
    class GpsMessengerExtension
    {
    }
}
