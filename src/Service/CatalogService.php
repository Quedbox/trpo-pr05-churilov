<?php
declare(strict_types=1);

final class CatalogService
{
    /** @var Product[] */
    private array $products = [];

    /** @var array<int, Category> */
    private array $categories = [];

    public function __construct(string $productsPath, string $categoriesPath)
    {
        foreach ($this->readJson($categoriesPath) as $row) {
            $category = new Category((int) $row['id'], (string) $row['name']);
            $this->categories[$category->getId()] = $category;
        }

        foreach ($this->readJson($productsPath) as $row) {
            $product = new Product(
                (int) $row['id'],
                (string) $row['name'],
                (float) $row['price'],
                (int) $row['category_id'],
                $row['active'] ?? null
            );

            if (!isset($this->categories[$product->getCategoryId()])) {
                throw new InvalidArgumentException('У товара указана неизвестная категория.');
            }

            $this->products[] = $product;
        }
    }

    private function readJson(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Не удалось открыть файл каталога: ' . basename($path));
        }
        $json = file_get_contents($path);
        if ($json === false) {
            throw new RuntimeException('Не удалось прочитать файл: ' . $path);
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new RuntimeException('В файле ожидался JSON-массив: ' . $path);
        }

        return $data;
    }

    /** @return Product[] */
    public function getProducts(): array
    {
        return $this->products;
    }

    /** @return Category[] */
    public function getCategories(): array
    {
        return array_values($this->categories);
    }

    public function hasCategory(int $categoryId): bool
    {
        return isset($this->categories[$categoryId]);
    }

    public function getCategoryName(int $categoryId): string
    {
        if (!isset($this->categories[$categoryId])) {
            return 'Неизвестная категория';
        }

        return $this->categories[$categoryId]->getName();
    }

    /** @return Product[] */
    public function filterByCategory(?int $categoryId): array
    {
        if ($categoryId === null) {
            return $this->products;
        }

        return array_values(array_filter(
            $this->products,
            static fn (Product $product): bool => $product->belongsToCategory($categoryId)
        ));
    }

    /** @param Product[] $products */
    public function averagePrice(array $products): float
    {
        if ($products === []) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ($products as $product) {
            $sum += $product->getPrice();
        }

        return $sum / count($products);
    }

    public function findProductById(int $id): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->hasId($id)) {
                return $product;
            }
        }

        return null;
    }
}
