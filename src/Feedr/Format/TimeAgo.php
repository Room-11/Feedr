<?php
/**
 * Displays the time since the provided timestamp (DateTime object)
 *
 * PHP version 5.4
 *
 * @category   Feedr
 * @package    Format
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace Feedr\Format;

/**
 * Displays the time since the provided timestamp (DateTime object)
 *
 * @category   Feedr
 * @package    Format
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class TimeAgo
{
    /**
     * @var array List of possible units to parse
     */
    private $units = [
        60        => ['%s second ago', '%d seconds ago'],
        3600      => ['%d minute ago', '%d minutes ago'],
        86400     => ['%d hour ago',   '%d hours ago'],
        604800    => ['yesterday',     '%d days ago'],
        2678400   => ['%d week ago',   '%d weeks ago'],
        31536000  => ['%d month ago',  '%d months ago'],
    ];

    /**
     * Calculates the time since the timestamp
     *
     * @param \DateTime $timestamp The timestamp from which to calculate the time since
     *
     * @return string The time since the timestamp
     */
    public function calculate(\DateTime $timestamp)
    {
        $difference = (new \DateTime('now'))->getTimestamp() - $timestamp->getTimestamp();

        $lastUnit = 1;

        foreach ($this->units as $numeric => $texts) {
            if ($difference < $numeric) {

                $amount = (int) ceil($difference / $lastUnit);

                return $this->renderTextualDate($amount, $texts);
            }

            $lastUnit = $numeric;
        }

        return $this->renderTextualDate((int) ceil($difference / $lastUnit), ['%d year ago', '%d years ago']);
    }

    /**
     * Renders the textual date based on the unit texts and the amount
     *
     * @param int   $amount The amount of units
     * @param array $texts  The textual representations of the units (singlular and plural)
     *
     * @return string The textual represenation of the date
     */
    private function renderTextualDate($amount, $texts)
    {
        if ($amount === 0 || $amount === 1) {
            return sprintf($texts[0], 1);
        } else {
            return sprintf($texts[1], $amount);
        }
    }
}
