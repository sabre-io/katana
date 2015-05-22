<?php

/**
 * @license
 *
 * sabre/katana.
 * Copyright (C) 2015 fruux GmbH (https://fruux.com/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Sabre\Katana\CalDav\Schedule;

use Sabre\Katana\Configuration;
use Sabre\CalDAV as SabreCalDav;
use Sabre\VObject\ITip;
use Hoa\Mail;
use Hoa\Socket;
use Hoa\Stringbuffer;

/**
 * IMip plugin, with our own emails.
 *
 * @copyright Copyright (C) 2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license GNU Affero General Public License, Version 3.
 */
class IMipPlugin extends SabreCalDav\Schedule\IMipPlugin {

    protected $configuration = null;

    function __construct($senderEmail, Configuration $configuration) {

        parent::__construct($senderEmail);
        $this->configuration = $configuration;
    }

    function schedule(ITip\Message $itip) {

        // Not sending any emails if the system considers the update
        // insignificant.
        if (false === $itip->significantChange) {
            if (empty($itip->scheduleStatus)) {
                $itip->scheduleStatus = '1.0;We got the message, but it\'s not significant enough to warrant an email.';
            }

            return;
        }

        $deliveredLocally = '1.2' === $itip->getScheduleStatus();

        $senderName    = $itip->senderName;
        $recipientName = $itip->recipientName;

        // 7 is the length of `mailto:`.
        $senderEmail    = substr($itip->sender, 7);
        $recipientEmail = substr($itip->recipient, 7);

        $subject = 'sabre/katana iTIP message';
        $summary = (string)$itip->message->VEVENT->SUMMARY;

        switch (strtoupper($itip->method)) {
            case 'REPLY' :
                // In the case of a reply, we need to find the `PARTSTAT` from
                // the user.
                $partstat = (string)$itip->message->VEVENT->ATTENDEE['PARTSTAT'];

                switch (strtoupper($partstat)) {
                    case 'DECLINED':
                        $subject = $senderName . ' declined your invitation to "' . $summary . '"';
                        $action  = 'DECLINED';

                        break;

                    case 'ACCEPTED':
                        $subject = $senderName . ' accepted your invitation to "' . $summary . '"';
                        $action  = 'ACCEPTED';

                        break;

                    case 'TENTATIVE':
                        $subject = $senderName . ' tentatively accepted your invitation to "' . $summary . '"';
                        $action  = 'TENTATIVE';

                        break;

                    default:
                        $itip->scheduleStatus = '5.0;Email not deliered. We didn\'t understand this PARTSTAT.';

                        return;
                }

                break;

            case 'REQUEST':
                $subject = $senderName . ' invited you to "' . $summary . '"';
                $action  = 'REQUEST';

                break;

            case 'CANCEL':
                $subject = '"' . $summary . '" has been canceled.';
                $action  = 'CANCEL';

                break;

            default:
                $itip->scheduleStatus = '5.0;Email not delivered. Unsupported METHOD.';

                return;

        }

        $dateTime =
            isset($itip->message->VEVENT->DTSTART)
                ? $itip->message->VEVENT->DTSTART->getDateTime()
                : new \DateTime('now');

        $allDay =
            isset($itip->message->VEVENT->DTSTART) &&
            false === $itip->message->VEVENT->DTSTART->hasTime();

        $attendees = [];

        if (isset($itip->message->VEVENT->ATTENDEE)) {
            $_attendees = &$itip->message->VEVENT->ATTENDEE;

            for ($i = 0, $max = count($_attendees); $i < $max; ++$i) {
                $attendee    = $_attendees[$i];
                $attendees[] = [
                    'cn' =>
                        isset($attendee['CN'])
                            ? (string)$attendee['CN']
                            : (string)$attendee['EMAIL'],
                    'email' =>
                        isset($attendee['EMAIL'])
                            ? (string)$attendee['EMAIL']
                            : null,
                    'role' =>
                        isset($attendee['ROLE'])
                            ? (string)$attendee['ROLE']
                            : null
                ];
            }
        }

        $notEmpty = function($property, $else) use ($itip) {
            if (isset($itip->message->VEVENT->$property)) {
                $handle = (string)$itip->message->VEVENT->$property;

                if (!empty($handle)) {
                    return $handle;
                }
            }

            return $else;
        };

        $url         = $notEmpty('URL', false);
        $description = $notEmpty('DESCRIPTION', false);
        $location    = $notEmpty('LOCATION', false);

        $locationImage = false;
        $locationLink  = false;

        if (isset($itip->message->VEVENT->{'X-APPLE-STRUCTURED-LOCATION'})) {

            $match = preg_match(
                '/^(geo:)?(?<latitude>\-?\d+\.\d+),(?<longitude>\-?\d+\.\d+)$/',
                (string)$itip->message->VEVENT->{'X-APPLE-STRUCTURED-LOCATION'},
                $coordinates
            );

            if (0 !== $match) {
                $zoom   = 13;
                $width  = 500;
                $height = 300;

                $locationImage =
                    'http://api.tiles.mapbox.com/v4' .
                    '/mapbox.streets' .
                    '/pin-m-star+285A98' .
                    '(' . $coordinates['longitude'] .
                    ',' . $coordinates['latitude'] .
                    ')' .
                    '/' . $coordinates['longitude'] .
                    ',' . $coordinates['latitude'] .
                    ',' . $zoom .
                    '/' . $width . 'x' . $height . '.png' .
                    '?access_token=pk.eyJ1IjoiZHRvYnNjaGFsbCIsImEiOiIzMzdhNTRhNGNjOGFjOGQ4MDM5ZTJhNGZjYjNmNmE5OCJ9.7ZQOdfvoZW0XIbvjN54Wrg';

                $locationLink =
                    'http://www.openstreetmap.org' .
                    '/?mlat=' . $coordinates['latitude'] .
                    '&mlon=' . $coordinates['longitude'] .
                    '#map=' . $zoom .
                    '/' . $coordinates['latitude'] .
                    '/' . $coordinates['longitude'];
            }
        }

        $configuration = $this->getConfiguration()->mail;

        Mail\Message::setDefaultTransport(
            new Mail\Transport\Smtp(
                new Socket\Client('tcp://' . $configuration->transport),
                $configuration->username,
                $configuration->password
            )
        );

        $message            = new Mail\Message();
        $message['from']    = $senderName . ' (via sabre/katana) <' . $this->senderEmail . '>';
        // reply-to
        $message['to']      = $recipientName . '<' . $recipientEmail . '>';
        $message['subject'] = $subject;
        // return-path

        $textBody = function() {
            return 'hello!';
        };
        $htmlBody = function() use (
            $senderName,
            $summary,
            $action,
            $dateTime,
            $allDay,
            $attendees,
            $location,
            $locationImage,
            $locationLink,
            $url,
            $description
        ) {
            ob_start();

            require 'katana://views/caldav_scheduling.html';
            $out = ob_get_contents();
            ob_end_clean();

            return $out;
        };

        $message->addContent(
            new Mail\Content\Alternative([
                new Mail\Content\Text($textBody()),
                new Mail\Content\Html($htmlBody())
            ])
        );

        if (false === $deliveredLocally) {
            $bodyAsStream = new Stringbuffer\Read();
            $bodyAsStream->initializeWith($itip->message->serialize());

            $attachment = new Mail\Content\Attachment(
                $bodyAsStream,
                'event.ics',
                'text/calendar; charset=UTF-8'
            );

            $message->addContent($attachment);
        }

        $message->send();

        if (false === $deliveredLocally) {
            $itip->scheduleStatus = '1.1;Scheduling message is sent via iMip.';
        }
    }

    protected function getConfiguration() {

        return $this->configuration;
    }
}
