<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')

            <main class="admin-main">
                <header class="admin-header">
                    <div>
                        <h2>Activity Logs</h2>
                        <p>Audit administrative events, operational changes, and transactional history.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme">
                            <span id="themeToggleIcon" aria-hidden="true">◐</span>
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="ad-btn ad-btn-primary">Back to Dashboard</a>
                    </div>
                </header>

                <section class="ad-card">
                    <h3 class="ad-panel-title">Audit Trail</h3>
                    <div class="ad-table-wrap">
                        <table class="ad-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at?->format('M d, Y h:i A') }}</td>
                                        <td>{{ optional($log->user)->name ?? 'System' }}</td>
                                        <td>
                                            <span class="ad-status {{ $log->action === 'create' ? 'success' : ($log->action === 'delete' ? 'pending' : 'info') }}">
                                                {{ ucfirst($log->action) }}
                                            </span>
                                        </td>
                                        <td>{{ $log->entity_type ?? '-' }} {{ $log->entity_id ? '#'.$log->entity_id : '' }}</td>
                                        <td>{{ $log->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">No activity logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:0.8rem;">
                        {{ $logs->links() }}
                    </div>
                </section>
            </main>
        </div>
    </section>

    @include('admin.partials.theme-script')
</x-app-layout>
