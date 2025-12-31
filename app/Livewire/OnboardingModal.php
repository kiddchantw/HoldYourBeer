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
        // 不更新 onboarding_completed_at
        // 只關閉 Modal，下次登入還會再問
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.onboarding-modal');
    }
}
