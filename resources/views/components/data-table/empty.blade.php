@props(['colspan' => 3, 'title' => 'No records found', 'message' => 'Try adjusting your search or filters.'])
<tr>
    <td colspan="{{ $colspan }}">
        <x-ui.empty-state :title="$title" :message="$message">{{ $slot }}</x-ui.empty-state>
    </td>
</tr>
