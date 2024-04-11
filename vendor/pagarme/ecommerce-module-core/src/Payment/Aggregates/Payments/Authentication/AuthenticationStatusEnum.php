<?php

namespace Pagarme\Core\Payment\Aggregates\Payments\Authentication;

abstract class AuthenticationStatusEnum
{
    const TRANSACTION_ACCEPTED = 'Y';

    const TRANSACTION_NOT_AUTHENTICATED = 'N';

    const CHALLENGE_REQUEST = 'C';

    const AUTHENTICATION_UNAVAILABLE = 'U';

    const AUTHENTICATION_ATTEMPT = 'A';

    const AUTHENTICATION_DENIED_BY_ISSUER = 'R';

    const JUST_INFORMATION = 'I';

    /**
     * @return array
     */
    public static function doesNotNeedToUseAntifraudStatuses()
    {
        return [
          self::TRANSACTION_ACCEPTED,
          self::AUTHENTICATION_ATTEMPT,
        ];
    }

    /**
     * @return array
     */
    public static function needToUseAntifraudStatuses()
    {
        return [
          self::TRANSACTION_NOT_AUTHENTICATED,
          self::CHALLENGE_REQUEST,
          self::AUTHENTICATION_UNAVAILABLE,
          self::AUTHENTICATION_DENIED_BY_ISSUER,
          self::JUST_INFORMATION,
        ];
    }

    /**
     * @param string|null $status
     * @return string
     */
    public static function statusMessage(string $status = null)
    {
        if (empty($status)) {
            return '';
        }

        $message = $status;
        switch ($status) {
            case self::TRANSACTION_ACCEPTED:
                $message .= ' - Transaction approved and authenticated by the Issuer';
                break;
            case self::AUTHENTICATION_ATTEMPT:
                $message .= ' - Transaction approved and authenticated by Brand';
                break;
            case self::JUST_INFORMATION:
                $message .= ' - Only data sent to the Brand and Issuer';
                break;
            case self::AUTHENTICATION_UNAVAILABLE:
                $message .= ' - Authentication unavailable';
                break;
            case self::TRANSACTION_NOT_AUTHENTICATED:
                $message .= ' - Transaction rejected by the Issuer';
                break;
            case self::AUTHENTICATION_DENIED_BY_ISSUER:
                $message .= ' - Transaction rejected by Issuer (post-challenge)';
                break;
        }

        return $message;
    }
}
