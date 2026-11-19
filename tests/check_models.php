<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Model/Product.php';
require_once __DIR__ . '/../src/Model/Category.php';
require_once __DIR__ . '/../src/Service/CatalogService.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$catalog = new CatalogService(
    __DIR__ . '/../data/products.json',
    __DIR__ . '/../data/categories.json'
);

check(count($catalog->getProducts()) === 12, 'Должно загрузиться 12 товаров.');
check(count($catalog->getCategories()) === 3, 'Должно загрузиться 3 категории.');

$accessories = $catalog->filterByCategory(1);
check(count($accessories) === 4, 'В категории 1 должно быть 4 товара.');
check(abs($catalog->averagePrice($accessories) - 1330.25) < 0.001, 'Средняя цена посчитана неверно.');

$product = $catalog->findProductById(1);
check($product !== null, 'Товар с ID 1 должен быть найден.');
check($product->isActive(), 'Товар 1 должен быть активным.');
check($product->belongsToCategory(2), 'Товар 1 должен принадлежать категории 2.');
check($product->isWithinPriceRange(400, 500), 'Цена товара 1 должна входить в диапазон.');

// Сверяем обязательные параметры конструкторов с таблицей задания.
$file = fopen(__DIR__ . '/../data/class_requirements.csv', 'r');
fgetcsv($file, 0, ';', '"', '');
while (($row = fgetcsv($file, 0, ';', '"', '')) !== false) {
    [$class, $property, $type, $required] = $row;
    $found = false;
    $constructor = (new ReflectionClass($class))->getConstructor();
    foreach ($constructor->getParameters() as $parameter) {
        if ($parameter->getName() === $property) {
            $found = (string) $parameter->getType() === $type
                && $parameter->isOptional() === ($required !== 'yes');
            break;
        }
    }
    check($found, "Не совпадает параметр $class::$property.");
}
fclose($file);

$withoutStatus = new Product(100, 'Тестовый товар', 50, 1);
check(!$withoutStatus->hasStatus(), 'Статус товара может отсутствовать.');

$constructorRejectedBadId = false;
try {
    new Product(0, 'Тестовый товар', 100, 1, true);
} catch (InvalidArgumentException) {
    $constructorRejectedBadId = true;
}
check($constructorRejectedBadId, 'Конструктор должен отклонять ID 0.');

echo "Проверки пройдены.\n";
