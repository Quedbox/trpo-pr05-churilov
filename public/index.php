<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Model/Product.php';
require_once __DIR__ . '/../src/Model/Category.php';
require_once __DIR__ . '/../src/Service/CatalogService.php';

function e(string|int|float $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$catalog = new CatalogService(
    __DIR__ . '/../data/products.json',
    __DIR__ . '/../data/categories.json'
);

$categoryValue = $_GET['category'] ?? '';
$selectedCategoryId = null;
$filterError = '';

if ($categoryValue !== '') {
    if (!is_string($categoryValue)) {
        $filterError = 'Некорректный фильтр. Показан весь каталог.';
    } else {
        $categoryId = filter_var($categoryValue, FILTER_VALIDATE_INT);
        if ($categoryId === false || !$catalog->hasCategory($categoryId)) {
            $filterError = 'Такой категории нет. Показан весь каталог.';
        } else {
            $selectedCategoryId = $categoryId;
        }
    }
}

$products = $catalog->filterByCategory($selectedCategoryId);
$averagePrice = $products === [] ? null : $catalog->averagePrice($products);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Каталог товаров</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1>Каталог товаров</h1>
            <p>Практическая работа № 5 · PHP ООП</p>
        </div>
    </header>

    <main class="container">
        <section class="filter-section" aria-labelledby="filter-title">
            <h2 id="filter-title">Фильтр по категории</h2>
            <form method="get" class="filter-form">
                <label for="category">Категория</label>
                <select id="category" name="category">
                    <option value="">Все категории</option>
                    <?php foreach ($catalog->getCategories() as $category): ?>
                        <option
                            value="<?= e($category->getId()) ?>"
                            <?= $selectedCategoryId === $category->getId() ? 'selected' : '' ?>
                        ><?= e($category->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Показать</button>
            </form>
            <?php if ($filterError !== ''): ?>
                <p class="error" role="alert"><?= e($filterError) ?></p>
            <?php endif; ?>
        </section>

        <section aria-labelledby="products-title">
            <div class="section-heading">
                <h2 id="products-title">Товары</h2>
                <p>Найдено: <?= count($products) ?></p>
            </div>
            <p class="average-price">
                Средняя цена:
                <?php if ($averagePrice === null): ?>
                    —
                <?php else: ?>
                    <?= e(number_format($averagePrice, 2, ',', ' ')) ?> ₽
                <?php endif; ?>
            </p>

            <?php if ($products === []): ?>
                <p class="empty">В этой категории пока нет товаров.</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <h3><?= e($product->getName()) ?></h3>
                            <p>Категория: <?= e($catalog->getCategoryName($product->getCategoryId())) ?></p>
                            <p>Цена: <?= e(number_format($product->getPrice(), 2, ',', ' ')) ?> ₽</p>
                            <p>
                                Статус:
                                <span class="<?= $product->isActive() ? 'active' : 'inactive' ?>">
                                    <?= $product->isActive() ? 'Активен' : 'Неактивен' ?>
                                </span>
                            </p>
                            <small>ID: <?= e($product->getId()) ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
