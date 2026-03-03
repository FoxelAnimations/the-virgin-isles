<?php

namespace App\Livewire\Admin;

use App\Models\Beacon;
use App\Models\BeaconScan;
use App\Models\BeaconType;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BeaconLogs extends Component
{
    use WithPagination;

    public string $filterKnown = '';
    public string $filterGuid = '';
    public string $filterTypeId = '';
    public string $filterStatus = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';

    protected $queryString = [
        'filterKnown' => ['except' => ''],
        'filterGuid' => ['except' => ''],
        'filterTypeId' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function updatedFilterKnown(): void { $this->resetPage(); }
    public function updatedFilterGuid(): void { $this->resetPage(); }
    public function updatedFilterTypeId(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterDateFrom(): void { $this->resetPage(); }
    public function updatedFilterDateTo(): void { $this->resetPage(); }

    public function deleteScan(int $scanId): void
    {
        BeaconScan::findOrFail($scanId)->delete();
        session()->flash('status', 'Log entry deleted.');
    }

    public function exportCsv(): StreamedResponse
    {
        $scans = $this->getFilteredQuery()->get();

        return response()->streamDownload(function () use ($scans) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Scanned At', 'GUID', 'Beacon Title', 'Known', 'Hashed IP',
                'User Agent', 'Referrer', 'Requested URL', 'Redirect Used',
                'UTM Source', 'UTM Medium', 'UTM Campaign', 'UTM Term', 'UTM Content',
                'Rate Limited', 'Device Type',
            ]);
            foreach ($scans as $scan) {
                fputcsv($handle, [
                    $scan->id,
                    $scan->scanned_at?->toIso8601String(),
                    $scan->guid,
                    $scan->beacon?->title ?? '(unknown)',
                    $scan->is_known ? 'Yes' : 'No',
                    $scan->hashed_ip,
                    $scan->user_agent,
                    $scan->referrer,
                    $scan->requested_url,
                    $scan->redirect_url_used,
                    $scan->utm_source,
                    $scan->utm_medium,
                    $scan->utm_campaign,
                    $scan->utm_term,
                    $scan->utm_content,
                    $scan->rate_limited ? 'Yes' : 'No',
                    $scan->device_type,
                ]);
            }
            fclose($handle);
        }, 'beacon-logs-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function getFilteredQuery()
    {
        $query = BeaconScan::with('beacon.type');

        if ($this->filterKnown === 'known') {
            $query->where('is_known', true);
        } elseif ($this->filterKnown === 'unknown') {
            $query->where('is_known', false);
        }

        if ($this->filterGuid) {
            $query->where('guid', 'like', "%{$this->filterGuid}%");
        }

        if ($this->filterDateFrom) {
            $query->where('scanned_at', '>=', $this->filterDateFrom . ' 00:00:00');
        }
        if ($this->filterDateTo) {
            $query->where('scanned_at', '<=', $this->filterDateTo . ' 23:59:59');
        }

        if ($this->filterTypeId || $this->filterStatus) {
            $query->whereHas('beacon', function ($q) {
                if ($this->filterTypeId) {
                    $q->where('type_id', $this->filterTypeId);
                }
                if ($this->filterStatus === 'online') {
                    $q->where('is_online', true)->where('is_out_of_action', false);
                } elseif ($this->filterStatus === 'offline') {
                    $q->where('is_online', false);
                } elseif ($this->filterStatus === 'out_of_action') {
                    $q->where('is_out_of_action', true);
                }
            });
        }

        return $query->orderByDesc('scanned_at');
    }

    public function render()
    {
        return view('livewire.admin.beacon-logs', [
            'scans' => $this->getFilteredQuery()->paginate(50),
            'types' => BeaconType::orderBy('name')->get(),
        ])->layout('layouts.admin');
    }
}
