<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OnboardingModal extends Component
{
    public bool $show = false;

    public function mount()
    {
        // 檢查用戶是否已完成導覽
        $user = Auth::user();
        if ($user && is_null($user->onboarding_completed_at)) {
            $this->show = true;
        }
    }

    public function startOnboarding()
    {
        $this->show = false;
        $this->dispatch('start-onboarding-tour');
    }

    public function skipOnboarding()
    {
        // 記錄使用者已跳過導覽,避免重複顯示
        $user = Auth::user();
        if ($user) {
            $user->update(['onboarding_completed_at' => now()]);
        }
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.onboarding-modal');
    }
}
