<?php
/**
 * Campus event information component
 *
 * @package lincolntech
 */
 $page_id = get_query_var('page_override_id');
 if (empty($page_id)) {
       $page_id = get_the_ID();
 }
 $component = get_field('component_campus_event_information', $page_id);
 if ($component !== false) {
    $items = get_field('events', $component);
    $timezone = new DateTimeZone(get_field('events_time_zone', $component));
    $headline_event_group = get_field('headline_event_group', $component);
    $description_event_group = get_field('description_event_group', $component);
    $additional_description_event_group = get_field('additional_description_event_group', $component);
    $upcoming_events = [];
    $content = [];
    if (!empty($items)) {
        $now = new DateTime();
        foreach ($items as $item) {
            $event = [
                'headline' => $item['headline'],
                'description' => $item['description_content'],
                'start' => new DateTime($item['date'] . 'T' . $item['start_time'], $timezone),
                'end' => new DateTime($item['date'] . 'T' . $item['end_time'], $timezone),
            ];

            // Kepp only upcoming single events
            if ($item['event_type'] === 'Single') {
                if ($event['end'] > $now) {
                    $upcoming_events[] = $event;
                }
                continue;
            }

            // Calculate the upcoming weekly reoccurring event
            if ($item['event_type'] === 'Reoccurring' && $item['schedule'] == 'Weekly') {
                list($event_start_hour, $event_start_minutes, $event_start_seconds) = explode(':', $item['start_time']);
                list($event_end_hour, $event_end_minutes, $event_end_seconds) = explode(':', $item['end_time']);
                foreach ($item['weekly_schedule'] as $day) {
                    // Get the next upcoming weekly event
                    $dt = new DateTimeImmutable($day, $timezone);
                    $weekly_event_ends_at = $dt->setTime($event_end_hour, $event_end_minutes, $event_end_seconds);
                    if ($weekly_event_ends_at < $now) {
                        $dt = new DateTimeImmutable("Next $day", $timezone);
                        $weekly_event_ends_at = $dt->setTime($event_end_hour, $event_end_minutes, $event_end_seconds);
                    }

                    // Add the correct next event to $upcoming_events
                    $event['start'] = $dt->setTime($event_start_hour, $event_start_minutes, $event_start_seconds);
                    $event['end'] = $weekly_event_ends_at;
                    $upcoming_events[] = $event;
                }
            }
        }

        if (!empty($upcoming_events)) {
            // Reorder by asc starting date the $upcoming_events
            usort($upcoming_events, function ($a, $b) {
                return $a['start']->diff($b['start'])->invert;
            });

            // Format the $upcoming_events output
            foreach ($upcoming_events as $event) {
                $content[] = '<h3>' . $event['headline'] . '</h3>'
                    . '<p>' . $event['description'] . '</p>'
                    . '<ul>'
                        . '<li>' . $event['start']->format('l, F jS, Y') . '</li>'
                        . '<li>' . $event['start']->format('g:i a') . ' to ' . $event['end']->format('g:i a T') . '</li>'
                    . '</ul>';
            }
        }
    }

    // Finally output all the events
    echo '<h2 class="section-heading--center-left">' . $headline_event_group . '</h2>'
        . '<p>' . $description_event_group . '</p>'
        . implode("\n", $content)
        . '<p>' . $additional_description_event_group . '</p>';
}
