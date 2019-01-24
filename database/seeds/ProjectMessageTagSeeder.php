<?php

use Illuminate\Database\Seeder;

use App\Models\ProjectMessageTag;

class ProjectMessageTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = [
            [
                'name' => 'UPDATE',
                'tag_format' => 'UPDATE',
                'mesg' => 'Message may include promotional and non-promotional content and will be delivered only to those users who are inside the <a href="https://developers.facebook.com/docs/messenger-platform/policy-overview#24hours_window">24-hour standard messaging window</a> in accordance with the \'24+1 policy\'.',
                'notice' => null,
                'is_primary' => true
            ],
            [
                'name' => 'SUBSCRIPTION',
                'tag_format' => 'NON_​PROMOTIONAL_​SUBSCRIPTION',
                'mesg' => 'Non-promotional message under the News, Productivity, and Personal Trackers categories described in the Messenger Platform\'s subscription messaging policy. An example of a bot utilizing subscription messaging is TechCrunch.',
                'notice' => null,
                'is_primary' => true
            ],
            [
                'name' => 'SHIPPING',
                'tag_format' => 'SHIPPING_​UPDATE',
                'mesg' => 'Provide a shipping status notification for a product that has already been purchased. For example, when the product is shipped, in-transit, delivered, or delayed.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'RESERVATION',
                'tag_format' => 'RESERVATION_​UPDATE',
                'mesg' => 'Confirm updates to an existing reservation. For example, when there is a change in itinerary, location, or a cancellation (such as when a hotel booking is canceled, a car rental pick-up time changes, or a room upgrade is confirmed).',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'ISSUE',
                'tag_format' => 'ISSUE_​RESOLUTION',
                'mesg' => 'Respond to a customer service issue surfaced in a Messenger conversation after a transaction has taken place. This tag is intended for use cases where the business requires more than 24 hours to resolve an issue and needs to give someone a status update and/or gather additional information.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'APPOINTMENT',
                'tag_format' => 'APPOINTMENT_​UPDATE',
                'mesg' => 'Provide updates about an existing appointment. For example, when there is a change in time, a location update or a cancellation (such as when a spa treatment is canceled, a real estate agent needs to meet you at a new location or a dental office proposes a new appointment time).',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'GAME',
                'tag_format' => 'GAME_​EVENT',
                'mesg' => 'Provide an update on user progression, a global event in a game or a live sporting event. For example, when a person’s crops are ready to be collected, their building is finished, their daily tournament is about to start or their favorite soccer team is about to play.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'TRANSPORTATION',
                'tag_format' => 'TRANSPORTATION_​UPDATE',
                'mesg' => 'Confirm updates to an existing reservation. For example, when there is a change in status of any flight, train or ferry reservation (such as “ride canceled”, “trip started” or “ferry arrived”).',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'FEATURE',
                'tag_format' => 'FEATURE_​FUNCTIONALITY_​UPDATE',
                'mesg' => 'Provide an update on new features or functionality that become available in a bot. For example, announcing the ability to talk to a live agent in a bot, or that the bot has a new skill.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'TICKET',
                'tag_format' => 'TICKET_​UPDATE',
                'mesg' => 'Notify the message recipient of updates or reminders pertaining to an event for which the person has already confirmed attendance. For example, when you want to send a message about a change in time, a location update, a cancellation or a reminder for an upcoming event (such as when a concert is canceled, the venue has changed or a refund opportunity is available).',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'ACCOUNT',
                'tag_format' => 'ACCOUNT_​UPDATE',
                'mesg' => 'Confirm updates to a user\'s account setting. For example, when there is a change in account settings and preferences of a user profile, notification of a password change, or membership expiration.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'PAYMENT',
                'tag_format' => 'PAYMENT_​UPDATE',
                'mesg' => 'Provide payment updates to existing transactions. For example, it can be used to send a receipt, out-of-stock, auction ended, refund, or a status change in an existing purchase transaction.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'PERSONAL FINANCE',
                'tag_format' => 'PERSONAL_​FINANCE_​UPDATE',
                'mesg' => 'Confirm a user\'s financial activity. For example, it can be used to send notifications on bill pay reminders, scheduled payments, receipts of payment, transfer of funds, or other transactional activities in financial services.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'PAIRING',
                'tag_format' => 'PAIRING_​UPDATE',
                'mesg' => 'Notify the message recipient that a pairing has been identified based on the recipient\'s prior request. Examples: Match has been confirmed in a dating app. User has confirmed an open parking spot for someone who previously requested one.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'APPLICATION',
                'tag_format' => 'APPLICATION_​UPDATE',
                'mesg' => 'Notify the message recipient of an update on the status of their application. Examples: Application is being reviewed. Application has been rejected.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'EVENT REMINDER',
                'tag_format' => 'CONFIRMED_​EVENT_​REMINDER',
                'mesg' => 'Send the message recipient reminders of a scheduled event for which a person is going to attend. Examples: Upcoming classes or events that a person has signed up for. Confirmation of attendance to an accepted event or appointment.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'ALERT',
                'tag_format' => 'COMMUNITY_​ALERT',
                'mesg' => 'Notify the message recipient of utility alerts, or safety checks in your community. Examples: Request a safety check. Notify of an emergency or utility alerts.',
                'notice' => null,
                'is_primary' => false
            ],
            [
                'name' => 'BUSINESS_​PRODUCTIVITY',
                'tag_format' => 'BUSINESS_​PRODUCTIVITY',
                'mesg' => 'The business_productivity tag may only be used to send non-promotional messages to help people manage the productivity of their businesses or related activities. Examples: Notifications on services or products that a business has subscribed to or purchased from a service provider; Reminders or alerts on upcoming invoices or service maintenance; Reports on performance, metrics, or recommended actions for the business.',
                'notice' => null,
                'is_primary' => false
            ],
        ];

        foreach($tags as $tag) {
            ProjectMessageTag::create($tag);
        }
    }
}
