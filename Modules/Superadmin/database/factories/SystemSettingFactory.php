<?php

namespace Modules\Superadmin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Superadmin\Models\SystemSetting;

class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    public function definition(): array
    {
        return [
            'category' => $this->faker->randomElement(['general', 'appearance', 'email', 'security']),
            'section' => $this->faker->word,
            'key' => $this->faker->unique()->slug(3, '.'),
            'value' => $this->faker->word,
            'type' => 'string',
            'input_type' => 'text',
            'label' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'options' => null,
            'validation_rules' => null,
            'default_value' => null,
            'depends_on' => null,
            'is_public' => false,
            'is_editable' => true,
            'is_sensitive' => false,
            'is_system' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the setting is sensitive.
     */
    public function sensitive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_sensitive' => true,
            'input_type' => 'password',
        ]);
    }

    /**
     * Indicate that the setting is not editable.
     */
    public function readonly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_editable' => false,
        ]);
    }

    /**
     * Indicate that the setting is a system setting.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
            'is_editable' => false,
        ]);
    }

    /**
     * Indicate that the setting is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Set the type to integer.
     */
    public function integer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'integer',
            'input_type' => 'number',
            'value' => (string) $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * Set the type to boolean.
     */
    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'boolean',
            'input_type' => 'checkbox',
            'value' => $this->faker->boolean ? '1' : '0',
        ]);
    }

    /**
     * Set the type to json.
     */
    public function json(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'json',
            'input_type' => 'textarea',
            'value' => json_encode(['key' => 'value']),
        ]);
    }

    /**
     * Set validation rules.
     */
    public function withValidation(string $rules): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_rules' => $rules,
        ]);
    }

    /**
     * Set a dependency.
     */
    public function dependsOn(string $key, string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'depends_on' => "{$key}:{$value}",
        ]);
    }
}
