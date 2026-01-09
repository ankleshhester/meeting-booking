<div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-white/10">
    <table class="w-full text-sm bg-white border-separate border-spacing-0 dark:bg-gray-900">
        <thead class="bg-gray-100 dark:bg-white/5">
            <tr>
                <th class="px-4 py-3 text-left font-semibold whitespace-nowrap
                           border-b border-r border-gray-300
                           text-gray-700 dark:border-white/10 dark:text-gray-200">
                    Attendee Email
                </th>

                <th class="px-4 py-3 text-right font-semibold whitespace-nowrap
                           border-b border-r border-gray-300
                           text-gray-700 dark:border-white/10 dark:text-gray-200">
                    Cost / Hr
                </th>

                <th class="px-4 py-3 text-center font-semibold whitespace-nowrap
                           border-b border-r border-gray-300
                           text-gray-700 dark:border-white/10 dark:text-gray-200">
                    Hours
                </th>

                <th class="px-4 py-3 text-right font-semibold whitespace-nowrap
                           border-b border-gray-300
                           text-gray-700 dark:border-white/10 dark:text-gray-200">
                    Total Cost
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse($attendees as $attendee)
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="px-4 py-3 whitespace-nowrap
                               border-b border-r border-gray-200
                               text-gray-600 dark:border-white/5 dark:text-gray-400">
                        {{ $attendee->email }}
                    </td>

                    @php
                        $hourlyCost = $attendee->ctc ? $attendee->ctc / 2500 : null;
                    @endphp

                    <td class="px-4 py-3 text-right whitespace-nowrap
                            border-b border-r border-gray-200
                            text-gray-600 dark:border-white/5 dark:text-gray-400">
                        ₹{{ $hourlyCost !== null ? number_format($hourlyCost, 2) : 'N/A' }}
                    </td>

                    <td class="px-4 py-3 text-center whitespace-nowrap font-mono
                               border-b border-r border-gray-200
                               text-gray-600 dark:border-white/5 dark:text-gray-400">
                        {{ number_format($attendee->hours, 2) }}
                    </td>

                    <td class="px-4 py-3 text-right whitespace-nowrap
                               border-b border-gray-200
                               dark:border-white/5">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                                     bg-primary-50 text-primary-700
                                     dark:bg-primary-500/10 dark:text-primary-400">
                            ₹{{ number_format($attendee->individual_cost, 2) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        No attendee cost data found.
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if($attendees->count() > 0)
            <tfoot class="bg-gray-100 dark:bg-white/5">
                <tr>
                    <td colspan="3"
                        class="px-4 py-3 text-right font-semibold
                               border-t border-gray-300
                               text-gray-700 dark:border-white/10 dark:text-gray-200">
                        Grand Total
                    </td>

                    <td
                        class="px-4 py-3 text-right font-bold
                               border-t border-gray-300
                               text-primary-600 dark:border-white/10 dark:text-primary-400">
                        ₹{{ number_format($attendees->sum('individual_cost'), 2) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
