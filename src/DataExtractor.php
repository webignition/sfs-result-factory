<?php

namespace webignition\SfsResultFactory;

class DataExtractor
{
    const FIELD_FREQUENCY = 'frequency';
    const FIELD_APPEARS = 'appears';
    const FIELD_LAST_SEEN = 'lastseen';
    const FIELD_CONFIDENCE = 'confidence';
    const FIELD_DELEGATED = 'delegated';
    const FIELD_COUNTRY = 'country';
    const FIELD_ASN = 'asn';
    const FIELD_TOR_EXIT = 'torexit';
    const IS_BLACKLISTED_THRESHOLD_IN_MINUTES = 10;

    private $isBlacklistedThresholdInMinutes = self::IS_BLACKLISTED_THRESHOLD_IN_MINUTES;

    public function __construct(?int $isBlacklistedThresholdInMinutes = self::IS_BLACKLISTED_THRESHOLD_IN_MINUTES)
    {
        $this->isBlacklistedThresholdInMinutes = $isBlacklistedThresholdInMinutes;
    }

    public function getFrequency(array $data): ?int
    {
        return $this->getNonNegativeIntegerValue($data, self::FIELD_FREQUENCY);
    }

    public function getAppears(array $data): ?bool
    {
        return $this->getNullableBooleanValue($data, self::FIELD_APPEARS);
    }

    public function getLastSeen(array $data): ?\DateTime
    {
        $lastSeen = $data[self::FIELD_LAST_SEEN] ?? null;
        if (null === $lastSeen) {
            return null;
        }

        try {
            $lastSeenDateTime = new \DateTime($lastSeen);
            $lastSeenDateTime->setTimezone(new \DateTimeZone('UTC'));

            return $lastSeenDateTime;
        } catch (\Exception $e) {
        }

        return null;
    }

    public function getIsBlacklisted(array $data): bool
    {
        $lastSeen = $this->getLastSeen($data);
        if (null === $lastSeen) {
            return false;
        }

        $frequency = $this->getFrequency($data);
        if ($frequency !== 255) {
            return false;
        }

        $threshold = new \DateTime('-' . $this->isBlacklistedThresholdInMinutes . ' minute');
        $threshold->setTimezone(new \DateTimeZone('UTC'));

        return $lastSeen > $threshold;
    }

    public function getConfidence(array $data): ?float
    {
        $confidence = $data[self::FIELD_CONFIDENCE] ?? null;
        if (null === $confidence) {
            return null;
        }

        $confidence = filter_var($confidence, FILTER_VALIDATE_FLOAT);
        if (false === $confidence) {
            return null;
        }

        return $confidence;
    }

    public function getDelegatedCountryCode(array $data): ?string
    {
        return $this->getNonEmptyStringValue($data, self::FIELD_DELEGATED);
    }

    public function getCountryCode(array $data): ?string
    {
        return $this->getNonEmptyStringValue($data, self::FIELD_COUNTRY);
    }

    public function getAsn(array $data): ?int
    {
        return $this->getNonNegativeIntegerValue($data, self::FIELD_ASN);
    }

    public function getIsTorExit(array $data): ?bool
    {
        return $this->getNullableBooleanValue($data, self::FIELD_TOR_EXIT);
    }

    private function getNonNegativeIntegerValue(array $data, string $field): ?int
    {
        $value = $data[$field] ?? null;
        if (!is_int($value)) {
            return null;
        }

        return $value >=0 ? $value : null;
    }

    private function getNonEmptyStringValue(array $data, string $field): ?string
    {
        $value = $data[$field] ?? null;
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return empty($value) ? null : $value;
    }

    private function getNullableBooleanValue(array $data, string $field): ?bool
    {
        $value = $this->getNonNegativeIntegerValue($data, $field);
        if ($value !== 0 && $value !== 1) {
            return null;
        }

        return (bool) $value;
    }
}
