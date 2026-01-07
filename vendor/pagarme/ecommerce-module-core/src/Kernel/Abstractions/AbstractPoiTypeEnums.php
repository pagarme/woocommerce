<?php

namespace Pagarme\Core\Kernel\Abstractions;

abstract class AbstractPoiTypeEnums
{
    const POS = 'pos';

    const TEF = 'tef';

    const LINK = 'link';

    const TAP_ON_PHONE = 'taponphone';

    const WHATSAPP_PAY = 'whatsapppay';

    const ECOMMERCE = 'ecommerce';

    const MICRO_POS = 'microPOS';

    const MANUAL_ENTRY = 'manualEntry';

    public static function getPoiTypes(): array
    {
        return [
            self::POS,
            self::TEF,
            self::LINK,
            self::TAP_ON_PHONE,
            self::WHATSAPP_PAY,
            self::ECOMMERCE,
            self::MICRO_POS,
            self::MANUAL_ENTRY,
        ];
    }

    public static function isValidPoiType(string $type): bool
    {
        return in_array(strtolower($type), strtolower(self::getPoiTypes()));
    }
}
