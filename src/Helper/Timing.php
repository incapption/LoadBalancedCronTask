<?php

namespace Incapption\LoadBalancedCronTask\Helper;

use DateTime;
use Incapption\LoadBalancedCronTask\Exceptions\LoadBalancedCronTaskException;

class Timing
{
    /**
     * @var ?DateTime
     */
    private $dateTime;

    /**
     * @var array
     */
    private $current;

    public function __construct(?DateTime $dateTime)
    {
        $this->dateTime = $dateTime;

        $this->current = [
            'minute' => intval($this->dateTime->format('i')),
            'hour' => intval($this->dateTime->format('G')),
            'dayOfMonth' => intval($this->dateTime->format('j')),
            'monthNumberOfDays' => intval($this->dateTime->format('t')),
        ];
    }

    public function isEveryNthMinutes(int $minute): bool
    {
        if (is_int($minute) === false || $minute < 0 || $minute > 59) {
            throw new LoadBalancedCronTaskException('parameter must be an integer between 0 and 59.');
        }

        if ($this->current['minute'] % $minute === 0) {
            return true;
        }

        return false;
    }

    public function isHourAt(int $minute): bool
    {
        if (is_int($minute) === false || $minute < 0 || $minute > 59) {
            throw new LoadBalancedCronTaskException('parameter must be an integer between 0 and 59.');
        }

        if (($this->current['hour'] >= 0 && $this->current['hour'] <= 23) && $this->current['minute'] === $minute) {
            return true;
        }

        return false;
    }

    public function isDailyAt(string $time): bool
    {
        $scheduledHour = self::parseTimeInput($time)['hour'];
        $scheduledMinute = self::parseTimeInput($time)['minute'];

        if ($this->current['hour'] === $scheduledHour && $this->current['minute'] === $scheduledMinute) {
            return true;
        }

        return false;
    }

    private function parseTimeInput(string $time): array
    {
        $exceptionMsg = 'a specific time must be in the format of "15:34" => H:i';

        $chars = str_split($time);

        if (count($chars) !== 5 || strlen($time) !== 5) {
            throw new LoadBalancedCronTaskException($exceptionMsg);
        }

        $hour = intval($chars[0].$chars[1]);
        $minute = intval($chars[3].$chars[4]);

        if ($chars[2] !== ":" || ($hour < 0 || $hour > 23) || ($minute < 0 || $minute > 59)) {
            throw new LoadBalancedCronTaskException($exceptionMsg);
        }

        return [
            'hour' => $hour,
            'minute' => $minute,
        ];
    }

    public function isMonthOn(int $dayOfMonth, string $time): bool
    {
        if (is_int($dayOfMonth) === false || $dayOfMonth < 1 || $dayOfMonth > 31) {
            throw new LoadBalancedCronTaskException('first parameter must be an integer between 1 and 31.');
        }

        $scheduledHour = self::parseTimeInput($time)['hour'];
        $scheduledMinute = self::parseTimeInput($time)['minute'];

        if ($this->current['dayOfMonth'] === $dayOfMonth
            && $this->current['hour'] === $scheduledHour
            && $this->current['minute'] === $scheduledMinute) {
            return true;
        }

        return false;
    }

    public function isLastDayOfMonthAt(string $time): bool
    {
        $scheduledHour = self::parseTimeInput($time)['hour'];
        $scheduledMinute = self::parseTimeInput($time)['minute'];

        if ($this->current['dayOfMonth'] === $this->current['monthNumberOfDays']
            && $this->current['hour'] === $scheduledHour
            && $this->current['minute'] === $scheduledMinute) {
            return true;
        }

        return false;
    }

    public function isLastDayOfMonthOffsetAt(int $offsetDays, string $time): bool
    {
        $scheduledHour = self::parseTimeInput($time)['hour'];
        $scheduledMinute = self::parseTimeInput($time)['minute'];
        $offsetDays = intval($offsetDays);

        if ($this->current['dayOfMonth'] === ($this->current['monthNumberOfDays'] - $offsetDays)
            && $this->current['hour'] === $scheduledHour
            && $this->current['minute'] === $scheduledMinute) {
            return true;
        }

        return false;
    }

}