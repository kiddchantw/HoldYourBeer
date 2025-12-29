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
                return [
                    'date' => $logs->first()->tasted_at->timezone('Asia/Taipei'),
                    'total_daily' => $logs->whereIn('action', ['add', 'increment', 'initial'])->count(),
                    'logs' => $logs
                ];
            })->filter(function ($group) {
                // 過濾掉 total_daily 為 0 的日期（例如：新增1個又刪除1個）
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
    }

    /**
     * Add count for a specific date (quick action)
     */
    public function addForDate(string $date)
    {
        DB::transaction(function () {
            $this->userBeerCount->count += 1;
            $this->userBeerCount->last_tasted_at = now();
            $this->userBeerCount->save();

            TastingLog::create([
                'user_beer_count_id' => $this->userBeerCount->id,
                'action' => 'add',
                'tasted_at' => now(),
                'note' => null,
            ]);
        });

        // Refresh user beer count to get updated data
        $this->userBeerCount = $this->userBeerCount->fresh();
        $this->successMessage = __('Added 1 unit!');
        $this->dispatch('clear-success-message');
    }

    /**
     * Delete count for a specific date (quick action)
     */
    public function deleteForDate(string $date)
    {
        if ($this->userBeerCount->count <= 0) {
            return;
        }

        DB::transaction(function () {
            $this->userBeerCount->count -= 1;
            $this->userBeerCount->last_tasted_at = now();
            $this->userBeerCount->save();

            TastingLog::create([
                'user_beer_count_id' => $this->userBeerCount->id,
                'action' => 'delete',
                'tasted_at' => now(),
                'note' => null,
            ]);
        });

        // Refresh user beer count to get updated data
        $this->userBeerCount = $this->userBeerCount->fresh();
        $this->successMessage = __('Removed 1 unit!');
        $this->dispatch('clear-success-message');
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
