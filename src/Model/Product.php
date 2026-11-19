<?php
declare(strict_types=1);

final class Product
{
    private int $id;
    private string $name;
    private float $price;
    private int $categoryId;
    private bool $active;

    public function __construct(
        int $id,
        string $name,
        float $price,
        int $categoryId,
        bool $active
    ) {
        $name = trim($name);

        if ($id < 1) {
            throw new InvalidArgumentException('ID товара должен быть положительным.');
        }
        if ($name === '') {
            throw new InvalidArgumentException('Название товара не должно быть пустым.');
        }
        if ($price < 0) {
            throw new InvalidArgumentException('Цена товара не может быть отрицательной.');
        }
        if ($categoryId < 1) {
            throw new InvalidArgumentException('ID категории должен быть положительным.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->categoryId = $categoryId;
        $this->active = $active;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function belongsToCategory(int $categoryId): bool
    {
        return $this->categoryId === $categoryId;
    }

    public function isWithinPriceRange(float $minimum, float $maximum): bool
    {
        return $minimum >= 0
            && $maximum >= $minimum
            && $this->price >= $minimum
            && $this->price <= $maximum;
    }

    public function hasId(int $id): bool
    {
        return $this->id === $id;
    }
}
