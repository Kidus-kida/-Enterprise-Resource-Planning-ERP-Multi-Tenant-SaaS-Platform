<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
    <div class="max-w-4xl mx-auto p-6 bg-white shadow-md rounded-lg">
        @if (session()->has('success'))
            <div class="bg-green-200 text-green-800 p-3 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Name</label>
                    <input type="text" wire:model.defer="staff_name" class="input" />
                    @error('staff_name') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Department</label>
                    <input type="text" wire:model.defer="department" class="input" />
                    @error('department') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Employment Date</label>
                    <input type="date" wire:model.defer="employment_date" class="input" />
                    @error('employment_date') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Job Title</label>
                    <input type="text" wire:model.defer="job_title" class="input" />
                    @error('job_title') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Evaluation From</label>
                    <input type="date" wire:model.defer="evaluation_period_start" class="input" />
                    @error('evaluation_period_start') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Evaluation To</label>
                    <input type="date" wire:model.defer="evaluation_period_end" class="input" />
                    @error('evaluation_period_end') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr class="my-4">

            <h2 class="font-bold text-lg mb-2">Performance Ratings (1 to 5)</h2>

            @foreach ($ratings as $key => $value)
                <div class="flex items-center justify-between py-2 border-b">
                    <label class="capitalize">{{ str_replace('_', ' ', $key) }}</label>
                    <select wire:model.defer="ratings.{{ $key }}" class="input w-24">
                        <option value="">--</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    @error("ratings.$key") <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            @endforeach

            <button type="submit" class="mt-6 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Submit Evaluation
            </button>
        </form>
    </div>

    <style>
        .input {
            @apply border border-gray-300 rounded p-2 w-full;
        }
    </style>

</div>