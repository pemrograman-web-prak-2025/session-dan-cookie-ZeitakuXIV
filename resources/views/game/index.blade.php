@extends('layouts.app')

@section('title', 'Toilet Tycoon - Dashboard')

@push('styles')
<style>
    body {
        background-color: #f5f5f5;
    }

    /* Stats Panel, fixed bottom left */
    .stats-panel {
        position: fixed;
        left: 20px;
        bottom: 20px;
        background: white;
        border-radius: 15px;
        padding: 15px 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        min-width: 500px;
        z-index: 100;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #666;
        font-weight: 500;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: bold;
        color: #333;
    }

    .stat-value.green {
        color: #10b981;
    }

    /* Settings Button */
    .settings-btn {
        position: fixed;
        top: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        z-index: 1000;
    }

    /* Toilet Card */
    .toilet-card {
        width: 100%;
        max-width: 280px;
        margin: 0 auto;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }

    .toilet-image-wrapper {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 15px;
    }

    .toilet-header {
        background: transparent;
        padding: 0;
        margin-bottom: 10px;
    }

    .toilet-image {
        height: 300px;
        width: 100%;
        background: #f5deb3;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .toilet-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .toilet-description {
        background: transparent;
        padding: 0;
    }

    /* Progress Bar */
    .progress-container {
        width: 100%;
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: #10b981;
        transition: width 0.1s linear;
    }

    /* Upgrade Button */
    .upgrade-btn {
        width: 100%;
        padding: 12px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .upgrade-btn.enabled {
        background: #10b981;
        color: white;
    }

    .upgrade-btn.enabled:hover {
        background: #059669;
        transform: translateY(-2px);
    }

    .upgrade-btn.disabled {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* Badge Colors */
    .bg-purple {
        background-color: #a78bfa !important;
        color: white !important;
    }

    .badge.bg-warning {
        background-color: #fef3c7 !important;
        color: #92400e !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-panel {
            left: 10px;
            bottom: 10px;
            min-width: 180px;
            padding: 12px 15px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Settings Button -->
    <button class="settings-btn btn btn-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" onclick="toggleSettings()">
        ⚙️
    </button>

    <!-- Stats Panel -->
    <div class="stats-panel">
        <div class="stat-item">
            <span class="stat-label">🚻 Available</span>
            <span class="stat-value" id="available-count">{{ $toilets->reject(fn($t) => $t->isOccupied())->count() }}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">💰 Balance</span>
            <span class="stat-value green" id="balance-display">Rp {{ number_format($user->balance, 0, ',', '.') }}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">👥 Served</span>
            <span class="stat-value" id="npcs-served">{{ number_format($npcsServed ?? 0) }}</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        @if($toilets->count() == 0)
        <!-- Welcome Screen -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card rounded-4 shadow-sm p-5 text-center">
                    <h2 class="mb-3">pungli toilet simulator</h2>
                    <p class="text-secondary mb-4">ayo bangun toilet pertamamu menuju mclaren!</p>
                    <button class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow" onclick="addToilet()">+ Add Toilet</button>
                </div>
            </div>
        </div>
        @else
        <!-- Toilet Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 justify-content-center mb-4">
            @foreach($toilets as $index => $toilet)
            <div class="col">
                <div class="toilet-card" data-toilet-id="{{ $toilet->id }}" data-level="{{ $toilet->level }}">
                    <!-- Header Outside -->
                    <div class="toilet-header d-flex justify-content-between align-items-center">
                        <span class="text-secondary small fw-semibold">Toilet #{{ $index + 1 }}</span>
                        <span class="small fw-semibold {{ $toilet->isOccupied() ? 'text-warning' : 'text-success' }}">
                            {{ $toilet->isOccupied() ? 'Occupied' : 'Available' }}
                        </span>
                    </div>

                    <!-- Image Card -->
                    <div class="toilet-image-wrapper">
                        <div class="toilet-image">
                            @if($toilet->isOccupied())
                                <img src="{{asset('storage/toilets/toilet-closed.jpg')}}" alt="Occupied">
                            @else
                                <img src="{{asset('storage/toilets/toilet-open.jpg')}}" alt="Available">
                            @endif
                        </div>
                    </div>

                    <!-- Description Outside -->
                    <div class="toilet-description">
                        @if($toilet->isOccupied())
                            @php $session = $toilet->activeSessions->first(); @endphp
                            <div class="fw-semibold mb-2 mt-3">{{ $session->npc_name }}</div>
                            <div class="progress-container rounded-pill mb-3" data-session-id="{{ $session->id }}" data-end-time="{{ $session->end_time }}">
                                <div class="progress-fill rounded-pill" style="width: 100%"></div>
                            </div>
                            <button class="upgrade-btn disabled rounded-3" disabled>Upgrade</button>
                            <div class="text-secondary small text-center mt-2">Rp {{ number_format(10000 * (4 ** ($toilet->level - 1)), 0, ',', '.') }} to upgrade</div>
                        @else
                            <div style="height: 20px;"></div>
                            <button class="upgrade-btn enabled rounded-3 mt-3" onclick="upgradeToilet({{ $toilet->id }})">Upgrade</button>
                            <div class="text-secondary small text-center mt-2">Rp {{ number_format(10000 * (4 ** ($toilet->level - 1)), 0, ',', '.') }} to upgrade</div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Add Toilet Section -->
        @if($toilets->count() < 4)
        <div class="row">
            <div class="col-12 text-center mt-4">
                @php
                    $toiletCount = $toilets->count();
                    $nextCost = $toiletCount == 0 ? 0 : 50000 * (4 ** ($toiletCount - 1));
                @endphp
                <button class="btn btn-primary rounded-3 px-4 py-3 fw-semibold shadow" onclick="addToilet()">
                    + Add Toilet
                    @if($nextCost > 0)
                        <small class="d-block mt-1">(Rp {{ number_format($nextCost, 0, ',', '.') }})</small>
                    @else
                        <small class="d-block mt-1">(FREE)</small>
                    @endif
                </button>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-danger" onclick="resetProgress()">Reset Progress</button>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-100">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let npcSpawnInterval;
    let progressUpdateInterval;
    let activeSessions = {};
    let npcsServedCount = {{ $npcsServed ?? 0 }};

    document.addEventListener('DOMContentLoaded', function() {
        loadActiveSessions();
        startNPCSpawning();
        startProgressUpdates();
        updateStats();
    });

    function loadActiveSessions() {
        axios.get('{{ route("sessions.active") }}')
            .then(response => {
                response.data.forEach(session => {
                    activeSessions[session.id] = session;
                });
            })
            .catch(error => {});
    }

    function startNPCSpawning() {
        function scheduleNextSpawn() {
            const delay = Math.random() * 5000 + 3000;
            npcSpawnInterval = setTimeout(() => {
                spawnNPC();
                scheduleNextSpawn();
            }, delay);
        }
        scheduleNextSpawn();
    }

    function spawnNPC() {
        const availableToilets = Array.from(document.querySelectorAll('.toilet-card')).filter(card => {
            return card.querySelector('.text-success') !== null;
        });

        if (availableToilets.length === 0) return;

        const randomCard = availableToilets[Math.floor(Math.random() * availableToilets.length)];
        const toiletId = randomCard.dataset.toiletId;

        axios.post('{{ route("sessions.create") }}', {
            toilet_id: toiletId
        })
        .then(response => {
            const session = response.data.session;
            activeSessions[session.id] = session;
            updateToiletCard(toiletId, session);
            updateStats();
            showToast(`${session.npc_name} is using the toilet!`, 'info');
        })
        .catch(error => {});
    }

    function updateToiletCard(toiletId, session) {
        const card = document.querySelector(`[data-toilet-id="${toiletId}"]`);
        if (!card) return;

        const toiletNumber = card.querySelector('span').textContent;
        const level = card.dataset.level;
        const upgradeCost = 10000 * Math.pow(4, level - 1);

        const badgeClass = session.service_type == 'pee' ? 'bg-warning text-dark' : (session.service_type == 'poop' ? 'bg-purple' : 'bg-info');

        card.innerHTML = `
            <div class="toilet-header d-flex justify-content-between align-items-center">
                <span class="text-secondary small fw-semibold">${toiletNumber}</span>
                <span class="small fw-semibold text-warning">Occupied</span>
            </div>
            <div class="toilet-image-wrapper">
                <div class="toilet-image">
                <img src="{{asset('storage/toilets/toilet-closed.jpg')}}" alt="Available">
                </div>
            </div>
            <div class="toilet-description">
                <div class="fw-semibold mb-2 mt-3">${session.npc_name}</div>
                <div class="progress-container rounded-pill mb-3" data-session-id="${session.id}" data-end-time="${session.end_time}">
                    <div class="progress-fill rounded-pill" style="width: 100%"></div>
                </div>
                <button class="upgrade-btn disabled rounded-3" disabled>Upgrade</button>
                <div class="text-secondary small text-center mt-2">Rp ${new Intl.NumberFormat('id-ID').format(upgradeCost)} to upgrade</div>
            </div>
        `;
    }

    function startProgressUpdates() {
        progressUpdateInterval = setInterval(() => {
            const progressBars = document.querySelectorAll('.progress-container');

            progressBars.forEach(bar => {
                const sessionId = bar.dataset.sessionId;
                const endTime = new Date(bar.dataset.endTime);
                const now = new Date();
                const session = activeSessions[sessionId];

                if (!session) return;

                const startTime = new Date(session.start_time);
                const totalDuration = endTime - startTime;
                const elapsed = now - startTime;
                const remaining = Math.max(0, 100 - (elapsed / totalDuration * 100));

                const progressFill = bar.querySelector('.progress-fill');
                if (progressFill) {
                    progressFill.style.width = remaining + '%';
                }

                if (remaining <= 0 && session.is_active) {
                    completeSession(sessionId);
                }
            });
        }, 100);
    }

    function completeSession(sessionId) {
        axios.patch(`/sessions/${sessionId}/end`)
            .then(response => {
                const session = response.data.session;
                const balance = response.data.balance;

                updateBalance(balance);
                npcsServedCount++;
                updateStats();

                const toiletId = session.toilet_id;
                const card = document.querySelector(`[data-toilet-id="${toiletId}"]`);

                if (card) {
                    const toiletNumber = card.querySelector('span').textContent;
                    const level = card.dataset.level;
                    const upgradeCost = 10000 * Math.pow(4, level - 1);

                    card.innerHTML = `
                        <div class="toilet-header d-flex justify-content-between align-items-center">
                            <span class="text-secondary small fw-semibold">${toiletNumber}</span>
                            <span class="small fw-semibold text-success">Available</span>
                        </div>
                        <div class="toilet-image-wrapper">
                            <div class="toilet-image">
                                <img src="{{asset('storage/toilets/toilet-open.jpg')}}" alt="Available">
                            </div>
                        </div>
                        <div class="toilet-description">
                            <div style="height: 20px;"></div>
                            <button class="upgrade-btn enabled rounded-3 mt-3" onclick="upgradeToilet(${toiletId})">Upgrade</button>
                            <div class="text-secondary small text-center mt-2">Rp ${new Intl.NumberFormat('id-ID').format(upgradeCost)} to upgrade</div>
                        </div>
                    `;
                }

                delete activeSessions[sessionId];
                updateStats();
                showToast(`+Rp ${new Intl.NumberFormat('id-ID').format(session.price)}`, 'success');
            })
            .catch(error => {});
    }

    function addToilet() {
        // Calculate the cost for the next toilet
        const toiletCount = document.querySelectorAll('.toilet-card').length;
        const cost = toiletCount === 0 ? 0 : 50000 * Math.pow(4, toiletCount - 1);
        
        let message = 'Add a new toilet';
        if (cost > 0) {
            message += ` for Rp ${new Intl.NumberFormat('id-ID').format(cost)}?`;
        } else {
            message += ' (FREE)?';
        }
        
        if (!confirm(message)) return;

        axios.post('{{ route("toilets.store") }}')
            .then(response => {
                showToast('Toilet added successfully!', 'success');
                updateBalance(response.data.balance);
                location.reload();
            })
            .catch(error => {
                const message = error.response?.data?.error || 'Failed to add toilet';
                showToast(message, 'danger');
            });
    }

    function upgradeToilet(toiletId) {
        // Get toilet level from data attribute
        const card = document.querySelector(`[data-toilet-id="${toiletId}"]`);
        const level = parseInt(card.dataset.level);
        const cost = 10000 * Math.pow(4, level - 1);
        
        if (!confirm(`Upgrade this toilet for Rp ${new Intl.NumberFormat('id-ID').format(cost)}?`)) return;

        axios.post(`/toilets/${toiletId}/upgrade`)
            .then(response => {
                showToast('Toilet upgraded successfully!', 'success');
                updateBalance(response.data.balance);
                location.reload();
            })
            .catch(error => {
                const message = error.response?.data?.error || 'Failed to upgrade toilet';
                showToast(message, 'danger');
            });
    }

    function resetProgress() {
        if (!confirm('Reset all progress? This cannot be undone!')) return;

        axios.delete('{{ route("reset-progress") }}')
            .then(response => {
                showToast('Progress reset successfully', 'success');
                location.reload();
            })
            .catch(error => {
                const message = error.response?.data?.error || 'Failed to reset progress';
                showToast(message, 'danger');
            });
    }

    function updateStats() {
        // Count available toilets (those with green "Available" status)
        const availableCount = document.querySelectorAll('.toilet-header .text-success').length;
        document.getElementById('available-count').textContent = availableCount;

        // Update served count
        document.getElementById('npcs-served').textContent = new Intl.NumberFormat().format(npcsServedCount);
    }

    function updateBalance(newBalance) {
        const balanceEl = document.getElementById('balance-display');
        if (balanceEl) {
            balanceEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(newBalance);
        }
    }

    function toggleSettings() {
        const modal = new bootstrap.Modal(document.getElementById('settingsModal'));
        modal.show();
    }

    window.addEventListener('beforeunload', () => {
        clearTimeout(npcSpawnInterval);
        clearInterval(progressUpdateInterval);
    });
</script>
@endpush
