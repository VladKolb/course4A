<?php
require_once __DIR__ . '/../vendor/autoload.php';  

use PHPUnit\Framework\TestCase;

class ValidatePizzaDataTest extends TestCase
{
    public function testDynamicDataGrowth()
    {
        $baseName = 'Ма';
        $baseDescription = 'Вкус';
        $basePrice = 100;

        $pizzaData = [];

        for ($i = 1; $i <= 10; $i++) {
          
            $name = $baseName . str_repeat('рита', $i);
            $description = $baseDescription . str_repeat('ная пицца', $i);
            $price = $basePrice + ($i * 100);

            $pizzaData[] = [
                'name' => $name,
                'description' => $description,
                'price' => $price
            ];

            $result = validatePizzaData($name, $description, $price);

            echo "Проверенная пицца: ";
            echo "Name = {$pizzaData[$i - 1]['name']}, ";
            echo "Description = {$pizzaData[$i - 1]['description']}, ";
            echo "Price = {$pizzaData[$i - 1]['price']}\n";

            if ($price > 0 && $price < 10000 && !empty($name) && !empty($description)) {
                $this->assertNull($result, "Failed on valid data: Name = $name, Description = $description, Price = $price");
            } else {
                $this->assertSame(
                    'Все поля обязательны для заполнения, цена должна быть больше 0 и меньше 10000.',
                    $result,
                    "Failed on invalid data: Name = $name, Description = $description, Price = $price"
                );
            }
        }
    }

    public function testEmptyName()
    {
        $baseName = ''; 
        $baseDescription = 'Вкусная пицца';
        $basePrice = 500;

        $pizzaData = [];

        for ($i = 1; $i <= 10; $i++) {
            $name = str_repeat('рита', $i);
            
            $description = $baseDescription . str_repeat(' пицца', $i);
            $price = $basePrice + ($i * 100);

            $pizzaData[] = [
                'name' => $name,
                'description' => $description,
                'price' => $price
            ];

            
            $result = validatePizzaData($name, $description, $price);
            
            echo "Проверенная пицца: ";
            echo "Name = {$pizzaData[$i - 1]['name']}, ";
            echo "Description = {$pizzaData[$i - 1]['description']}, ";
            echo "Price = {$pizzaData[$i - 1]['price']}\n";

            if ($name === '') {
                // Если имя пустое, проверяем правильность ошибки
                $this->assertSame(
                    'Все поля обязательны для заполнения, цена должна быть больше 0 и меньше 10000.',
                    $result,
                    "Failed on empty name with data: Name = $name, Description = $description, Price = $price"
                );
            } else {
                $this->assertNull($result, "Failed on valid data: Name = $name, Description = $description, Price = $price");
            }
        }
    }

    // Тест для пустого описания с наращиванием данных
    public function testEmptyDescription()
    {
        $baseName = 'Маргарита';
        $baseDescription = ''; // Начальное значение пустое
        $basePrice = 500;

        // Массив, который будет хранить наращиваемые данные
        $pizzaData = [];

        for ($i = 1; $i <= 10; $i++) {
            // Используем наращивающееся имя
            $name = $baseName . str_repeat('рита', $i);
            // Наращиваем описание
            $description = str_repeat(' пицца', $i);
            $price = $basePrice + ($i * 100);

            // Добавляем текущие данные в массив
            $pizzaData[] = [
                'name' => $name,
                'description' => $description,
                'price' => $price
            ];

            // Выполняем проверку данных
            $result = validatePizzaData($name, $description, $price);

            // Выводим проверенные данные в терминал
            echo "Проверенная пицца: ";
            echo "Name = {$pizzaData[$i - 1]['name']}, ";
            echo "Description = {$pizzaData[$i - 1]['description']}, ";
            echo "Price = {$pizzaData[$i - 1]['price']}\n";

            if ($description === '') {
                // Если описание пустое, проверяем правильность ошибки
                $this->assertSame(
                    'Все поля обязательны для заполнения, цена должна быть больше 0 и меньше 10000.',
                    $result,
                    "Failed on empty description with data: Name = $name, Description = $description, Price = $price"
                );
            } else {
                $this->assertNull($result, "Failed on valid data: Name = $name, Description = $description, Price = $price");
            }
        }
    }

    // Тест для невалидной цены (меньше или равна 0) с наращиванием данных
    public function testInvalidPriceLow()
    {
        $baseName = 'Маргарита';
        $baseDescription = 'Вкусная пицца';
        $basePrice = 0; // Начальная цена = 0

        // Массив, который будет хранить наращиваемые данные
        $pizzaData = [];

        for ($i = 1; $i <= 10; $i++) {
            // Используем наращивающееся имя
            $name = $baseName . str_repeat('рита', $i);
            // Наращиваем описание
            $description = $baseDescription . str_repeat(' пицца', $i);
            $price = $basePrice + ($i * 100);  // Увеличиваем цену с каждой итерацией

            // Добавляем текущие данные в массив
            $pizzaData[] = [
                'name' => $name,
                'description' => $description,
                'price' => $price
            ];

            // Выполняем проверку данных
            $result = validatePizzaData($name, $description, $price);

            // Выводим проверенные данные в терминал
            echo "Проверенная пицца: ";
            echo "Name = {$pizzaData[$i - 1]['name']}, ";
            echo "Description = {$pizzaData[$i - 1]['description']}, ";
            echo "Price = {$pizzaData[$i - 1]['price']}\n";

            if ($price <= 0) {
                // Если цена невалидна (<= 0), проверяем правильность ошибки
                $this->assertSame(
                    'Все поля обязательны для заполнения, цена должна быть больше 0 и меньше 10000.',
                    $result,
                    "Failed on invalid price low with data: Name = $name, Description = $description, Price = $price"
                );
            } else {
                $this->assertNull($result, "Failed on valid data: Name = $name, Description = $description, Price = $price");
            }
        }
    }

    // Тест для невалидной цены (больше 9999) с наращиванием данных
    public function testInvalidPriceHigh()
    {
        $baseName = 'Маргарита';
        $baseDescription = 'Вкусная пицца';
        $basePrice = 10000; // Начальная цена = 10000

        // Массив, который будет хранить наращиваемые данные
        $pizzaData = [];

        for ($i = 1; $i <= 10; $i++) {
            // Используем наращивающееся имя
            $name = $baseName . str_repeat('рита', $i);
            // Наращиваем описание
            $description = $baseDescription . str_repeat(' пицца', $i);
            $price = $basePrice + ($i * 100);  // Увеличиваем цену с каждой итерацией

            // Добавляем текущие данные в массив
            $pizzaData[] = [
                'name' => $name,
                'description' => $description,
                'price' => $price
            ];

            // Выполняем проверку данных
            $result = validatePizzaData($name, $description, $price);

            // Выводим проверенные данные в терминал
            echo "Проверенная пицца: ";
            echo "Name = {$pizzaData[$i - 1]['name']}, ";
            echo "Description = {$pizzaData[$i - 1]['description']}, ";
            echo "Price = {$pizzaData[$i - 1]['price']}\n";

            if ($price >= 10000) {
                // Если цена невалидна (>= 10000), проверяем правильность ошибки
                $this->assertSame(
                    'Все поля обязательны для заполнения, цена должна быть больше 0 и меньше 10000.',
                    $result,
                    "Failed on invalid price high with data: Name = $name, Description = $description, Price = $price"
                );
            } else {
                $this->assertNull($result, "Failed on valid data: Name = $name, Description = $description, Price = $price");
            }
        }
    }
}
