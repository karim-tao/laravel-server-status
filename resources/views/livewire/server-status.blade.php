@use('Illuminate\Support\Number')

<div class="server-status" wire:poll.5s="refresh">
    <style>
        .server-status *, .server-status *::before, .server-status *::after { box-sizing: border-box; }
        .server-status dl, .server-status dd { margin: 0; }
        .server-status .grid { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1.25rem; }
        .server-status .card { overflow: hidden; padding: 1.25rem 1rem; background-color: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1); }
        .server-status dt { overflow: hidden; font-size: 0.875rem; line-height: 1.25rem; font-weight: 500; color: #6b7280; text-overflow: ellipsis; white-space: nowrap; }
        .server-status .value { margin-top: 0.25rem; font-size: 1.875rem; line-height: 2.25rem; font-weight: 600; color: #111827; }
        .server-status .detail { margin-top: 0.25rem; font-size: 0.875rem; line-height: 1.25rem; color: #6b7280; }
        .server-status .footer { margin-top: 1rem; font-size: 0.75rem; line-height: 1rem; color: #9ca3af; }
        @media (min-width: 640px) {
            .server-status .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .server-status .card { padding: 1.5rem; }
        }
        @media (min-width: 1024px) {
            .server-status .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
    </style>

    <dl class="grid">
        <div class="card">
            <dt>CPU</dt>
            <dd class="value">{{ $cpu }}%</dd>
            <dd class="detail">
                @foreach ($load as $index => $value)
                    {{ [1, 5, 15][$index] }} min {{ $value }}% &middot;
                @endforeach
                {{ $cores }} cores
            </dd>
        </div>

        <div class="card">
            <dt>Memory</dt>
            <dd class="value">{{ round($memory_used / $memory_total * 100) }}%</dd>
            <dd class="detail">{{ Number::fileSize($memory_used, 1) }} of {{ Number::fileSize($memory_total, 1) }}</dd>
        </div>

        <div class="card">
            <dt>Disk</dt>
            <dd class="value">{{ round($disk_used / $disk_total * 100) }}%</dd>
            <dd class="detail">{{ Number::fileSize($disk_used, 1) }} of {{ Number::fileSize($disk_total, 1) }}</dd>
        </div>

        <div class="card">
            <dt>Uptime</dt>
            <dd class="value">{{ intdiv($uptime, 86400) }}d {{ intdiv($uptime % 86400, 3600) }}h {{ intdiv($uptime % 3600, 60) }}m</dd>
            <dd class="detail">Since {{ now()->subSeconds($uptime)->format('d/m/Y H:i') }}</dd>
        </div>

        <div class="card">
            <dt>PHP processes</dt>
            <dd class="value">{{ $processes }}</dd>
            <dd class="detail">Process {{ $process }}</dd>
        </div>
    </dl>

    <p class="footer">Refreshed every 5 seconds</p>
</div>
