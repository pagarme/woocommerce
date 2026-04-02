<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class PoiType extends AbstractValueObject
{
    const POS = 'Pos';
    const TEF = 'Tef';
    const LINK = 'Link';
    const TAP_ON_PHONE = 'TapOnPhone';
    const WHATSAPP_PAY = 'WhatsappPay';
    const ECOMMERCE = 'Ecommerce';
    const MICRO_POS = 'MicroPos';
    const MANUAL_ENTRY = 'ManualEntry';

    const DEFAULT = self::ECOMMERCE;

    /**
     * @var string
     */
    private $type;

    private function __construct(string $type)
    {
        $this->type = $type;
    }

    public static function pos(): self
    {
        return new self(self::POS);
    }

    public static function tef(): self
    {
        return new self(self::TEF);
    }

    public static function link(): self
    {
        return new self(self::LINK);
    }

    public static function tapOnPhone(): self
    {
        return new self(self::TAP_ON_PHONE);
    }

    public static function whatsappPay(): self
    {
        return new self(self::WHATSAPP_PAY);
    }

    public static function ecommerce(): self
    {
        return new self(self::ECOMMERCE);
    }

    public static function microPos(): self
    {
        return new self(self::MICRO_POS);
    }

    public static function manualEntry(): self
    {
        return new self(self::MANUAL_ENTRY);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public static function getAll(): array
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

    public static function isValid(string $type): bool
    {
        return self::normalize($type) !== null;
    }

    /**
     * Normalize a POI type to its canonical constant value.
     *
     * Returns the properly-cased POI type string if the input matches
     * (case-insensitively) one of the allowed types, or null otherwise.
     *
     * @param string|null $type The POI type to normalize.
     * @return string|null
     */
    protected static function normalize(string $type)
    {
        foreach (self::getAll() as $poiType) {
            if (strcasecmp($type, $poiType) === 0) {
                return $poiType;
            }
        }

        return null;
    }

    /**
     * @param $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->type === $object->type;
    }

    /**
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->type;
    }
}
