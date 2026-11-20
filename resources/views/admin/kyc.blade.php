<section class="dashboard-progress mb-3 p-4 syndicate-kyc-panel">
    <style>
        .syndicate-kyc-panel .admin-table { width: 100%; border-collapse: collapse; }
        .syndicate-kyc-panel .admin-table th,
        .syndicate-kyc-panel .admin-table td { border-bottom: 1px solid #e5eaf1; padding: 14px 10px; text-align: left; vertical-align: top; }
        .syndicate-kyc-panel .admin-table th { color: #5f7184; font-size: 13px; text-transform: uppercase; }
        .syndicate-kyc-panel .status { border-radius: 999px; display: inline-block; font-size: 12px; font-weight: 700; padding: 5px 10px; }
        .syndicate-kyc-panel .status-under_review { background: #fff1c2; color: #795900; }
        .syndicate-kyc-panel .status-approved { background: #d5f8ea; color: #08794f; }
        .syndicate-kyc-panel .status-rejected { background: #ffe0e0; color: #a32929; }
        .syndicate-kyc-panel .admin-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .syndicate-kyc-panel .admin-actions form { margin: 0; }
        .syndicate-kyc-panel .admin-actions input { border: 1px solid #dbe3ed; border-radius: 6px; padding: 7px; width: 180px; }
        .syndicate-kyc-panel .admin-actions button,
        .syndicate-kyc-panel .admin-actions a { border: 0; border-radius: 7px; cursor: pointer; display: inline-block; font-size: 13px; font-weight: 700; padding: 8px 12px; text-decoration: none; }
        .syndicate-kyc-panel .approve { background: #00e497; color: #073d32; }
        .syndicate-kyc-panel .reject { background: #ffe0e0; color: #a32929; }
        .syndicate-kyc-panel .download { background: #e8f0ff; color: #016df4; }
        @media (max-width: 800px) { .syndicate-kyc-panel { overflow-x: auto; } .syndicate-kyc-panel .admin-table { min-width: 760px; } }
    </style>
    <h3 class="dashboard-progress-title">KYC review</h3>
    <div class="progress mt-3" style="height: 10px;">
        <div class="progress-bar bg-green" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="mt-3">
            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif
            <p>Review submitted CNIC documents and decide whether each user is approved.</p>
            <table class="admin-table">
                <thead>
                <tr><th>User</th><th>Status</th><th>Submitted</th><th>Documents and decision</th></tr>
                </thead>
                <tbody>
                @forelse ($submissions as $submission)
                    <tr>
                        <td><strong>{{ $submission->user->name }}</strong><br>{{ $submission->user->email }}</td>
                        <td><span class="status status-{{ $submission->status }}">{{ str_replace('_', ' ', ucfirst($submission->status)) }}</span><br>{{ $submission->admin_note }}</td>
                        <td>{{ optional($submission->submitted_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="admin-actions">
                                <a class="download" href="{{ route('admin.kyc.download', [$submission, 'front']) }}">Front</a>
                                <a class="download" href="{{ route('admin.kyc.download', [$submission, 'back']) }}">Back</a>
                                @if ($submission->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.kyc.approve', $submission) }}">@csrf<button class="approve" type="submit">Approve</button></form>
                                @endif
                                @if ($submission->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.kyc.reject', $submission) }}">
                                        @csrf
                                        <input type="text" name="admin_note" placeholder="Reason if rejecting" required>
                                        <button class="reject" type="submit">Reject</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No KYC submissions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
    </div>
</section>
