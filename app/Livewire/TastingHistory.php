<?php

namespace App\Livewire;

use App\Models\Beer;
use App\Models\TastingLog;
use App\Models\UserBeerCount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TastingHistory extends Component
{
    public $beerId;
    public $userBeerCount;
    public $beer;

    // Modal state
    public $showAddModal = false;
    public $quantity = 1;
    public $note = '';

    // Success message
    public $successMessage = '';

    protected $rules = [
        'quantity' => 'required|integer|min:1|max:99',
        'note' => 'nullable|string|max:150',
    ];

    public function mount($beerId)
    {
        $this->beerId = $beerId;
        $this->beer = Beer::findOrFail($beerId);
        
        $this->userBeerCount = UserBeerCount::where('user_id', Auth::id())
            ->where('beer_id', $this->beer->id)
            ->firstOrFail();
    }

    public function getGroupedLogs()
    {
        return $this->userBeerCount->tastingLogs()
            ->orderBy('tasted_at', 'desc')
            ->get()
            ->groupBy(function ($log) {
                return $log->tasted_at->timezone('Asia/Taipei')->format('Y-m-d');
            })->map(function ($logs) {
                // 計算淨數量：新增數 - 刪除數
                $addCount = $logs->whereIn('action', ['add', 'increment', 'initial'])->count();
                $deleteCount = $logs->whereIn('action', ['delete', 'decrement'])->count();
                $netTotal = $addCount - $deleteCount;

                return [
                    'date' => $logs->first()->tasted_at->timezone('Asia/Taipei'),
                    'total_daily' => max(0, $netTotal), // 確保不會是負數
                    'logs' => $logs
                ];
            })->filter(function ($group) {
                // 過濾掉淨數量為 0 的日期（例如：新增1個又刪除1個）
                return $group['total_daily'] > 0;
            });
    }

    /**
     * Open the add record modal
     */
    public function openAddModal()
    {
        $this->resetAddForm();
        $this->showAddModal = true;
    }

    /**
     * Close the add record modal
     */
    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetAddForm();
    }

    /**
     * Reset the add form to default values
     */
    public function resetAddForm()
    {
        $this->quantity = 1;
        $this->note = '';
        $this->resetValidation();
    }

    /**
     * Increase quantity (max 99)
     */
    public function increaseQuantity()
    {
        if ($this->quantity < 99) {
            $this->quantity++;
        }
    }

    /**
     * Decrease quantity (min 1)
     */
    public function decreaseQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    /**
     * Save the new tasting record
     */
    public function saveRecord()
    {
        $this->validate();

        DB::transaction(function () {
            // Update user beer count
            $this->userBeerCount->count += $this->quantity;
            $this->userBeerCount->last_tasted_at = now();
            $this->userBeerCount->save();

            // Create tasting log(s)
            // For simplicity, create one log entry per quantity.
            // Alternatively, we could add a 'quantity' field to TastingLog.
            // Using the existing pattern: each log = 1 unit
            for ($i = 0; $i < $this->quantity; $i++) {
                TastingLog::create([
                    'user_beer_count_id' => $this->userBeerCount->id,
                    'action' => 'add',
                    'tasted_at' => now(),
                    'note' => $i === 0 ? $this->note : null, // Only first log gets the note
                ]);
            }
        });

        // Refresh user beer count to get updated data
        $this->userBeerCount = $this->userBeerCount->fresh();

        // Close modal and show success message
        $this->closeAddModal();
        $this->successMessage = __('Record added successfully!');

        // Clear success message after 3 seconds
        $this->dispatch('clear-success-message');
        $this->dispatch('count-updated', count: $this->userBeerCount->count);
    }

    /**
     * Add count for a specific date (quick action)
     */
    public function addForDate(string $date)
    {
        // 使用傳入的日期，設定為當天的當前時間
        $tastedAt = \Carbon\Carbon::parse($date, 'Asia/Taipei')->setTimeFromTimeString(now('Asia/Taipei')->format('H:i:s'));

        DB::transaction(function () use ($tastedAt) {
            $this->userBeerCount->count += 1;
            $this->userBeerCount->last_tasted_at = now();
            $this->userBeerCount->save();

            TastingLog::create([
                'user_beer_count_id' => $this->userBeerCount->id,
                'action' => 'add',
                'tasted_at' => $tastedAt,
                'note' => null,
            ]);
        });

        // Refresh user beer count to get updated data
        $this->userBeerCount = $this->userBeerCount->fresh();
        $this->successMessage = __('Added 1 unit!');
        $this->dispatch('clear-success-message');
        $this->dispatch('count-updated', count: $this->userBeerCount->count);
    }

    /**
     * Delete count for a specific date (quick action)
     */
    public function deleteForDate(string $date)
    {
        // 移除 count <= 0 的檢查，以允許刪除卡片上的紀錄 (即使總數不一致)

        // 使用傳入的日期，設定為當天的當前時間
        $tastedAt = \Carbon\Carbon::parse($date, 'Asia/Taipei')->setTimeFromTimeString(now('Asia/Taipei')->format('H:i:s'));

        DB::transaction(function () use ($tastedAt) {
            // 確保數量不會低於 0
            $this->userBeerCount->count = max(0, $this->userBeerCount->count - 1);
            $this->userBeerCount->last_tasted_at = now();
            $this->userBeerCount->save();

            TastingLog::create([
                'user_beer_count_id' => $this->userBeerCount->id,
                'action' => 'delete',
                'tasted_at' => $tastedAt,
                'note' => null,
            ]);
        });

        // Refresh user beer count to get updated data
        $this->userBeerCount = $this->userBeerCount->fresh();
        $this->successMessage = __('Removed 1 unit!');
        $this->dispatch('clear-success-message');
        $this->dispatch('count-updated', count: $this->userBeerCount->count);
    }

    /**
     * Clear the success message
     */
    public function clearSuccessMessage()
    {
        $this->successMessage = '';
    }

    public function render()
    {
        return view('livewire.tasting-history', [
            'groupedLogs' => $this->getGroupedLogs(),
        ]);
    }
}
