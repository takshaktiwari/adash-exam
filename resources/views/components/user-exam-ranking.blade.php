<div class="card mb-4 shadow-sm">
    <div class="card-header">
        <h5 class="my-2">
            Ranking among all users - {{ $rank }}
        </h5>
    </div>
    <div class="card-body p-0" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
        <table class="table ranking_table mb-0">
            <thead>
                <th>Rank</th>
                <th>User</th>
                <th>Marks</th>
            </thead>
            <tbody>
                @foreach ($reports as $key => $item)
                    <tr @class(['active' => $item->user_id == $userId])>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->marks }}</th>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@push('styles')
    <style>
        tr.active{
            background-color: #9cff75;
        }
    </style>
@endpush
