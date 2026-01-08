<div class="p-4">
    <table class="w-full text-left divide-y divide-gray-200 dark:divide-white/5">
        <thead>
            <tr class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                <th class="py-3">Attendee Email</th>
                <th class="py-2">Cost (Hr)</th>
                <th class="py-2">Hours</th>
                <th class="py-2 text-right">Cost</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
            @foreach($attendees as $attendee)
                <tr class="text-sm">
                    <td class="py-3">{{ $attendee->email }}</td>
                    <td class="py-2">₹{{ number_format($attendee->cost_per_hour, 2) }}</td>
                    <td class="py-2">{{ number_format($attendee->hours, 2) }}</td>
                    <td class="py-2 text-right font-medium text-primary-600">
                        ₹{{ number_format($attendee->individual_cost, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
