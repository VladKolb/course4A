<?php
require_once __DIR__ . '/../vendor/autoload.php';  

use PHPUnit\Framework\TestCase;

class PizzaDatabaseTest extends TestCase
{
    private $mockConn;

    protected function setUp(): void
    {
        $this->mockConn = $this->createMock(mysqli::class); 
    }

    public function testAddPizzaToDatabaseSuccess()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $pizzaData = [];

        $baseName = 'Маргарита';
        $baseDescription = 'Вкусная пицца';
        $basePrice = 500.0;

        $mockStmt = $this->createMock(mysqli_stmt::class);

        $this->mockConn->expects($this->exactly(10))  
                        ->method('prepare')
                        ->with("INSERT INTO pizza (name, description, price) VALUES (?, ?, ?)")
                        ->willReturn($mockStmt);

        $mockStmt->expects($this->exactly(10))  
                  ->method('bind_param')
                  ->with(
                      'ssd',
                      $this->callback(function($name) {
                          return strpos($name, 'Маргарита') === 0;  
                      }),
                      $this->callback(function($description) {
                          return strpos($description, 'Вкусная пицца') === 0;  
                      }),
                      $this->greaterThanOrEqual(0)  
                  )
                  ->willReturn(true);

        $mockStmt->expects($this->exactly(10))  // Мы ожидаем, что execute() будет вызван 10 раз
                  ->method('execute')
                  ->willReturn(true);

        $mockStmt->expects($this->exactly(10))  // Мы ожидаем, что close() будет вызван 10 раз
                  ->method('close')
                  ->willReturn(true);


        for ($i = 1; $i <= 10; $i++) {
            
            $name = $baseName; 
            $description = $baseDescription . str_repeat(' вкусная', $i); 
            $price = $basePrice + ($i * 50); 

            $pizzaData[] = [
                'name' => $name,
                'description' => $description,
                'price' => $price
            ];

            
            $result = addPizzaToDatabase($this->mockConn, $name, $description, $price);

            echo "Проверенная пицца: ";
            echo "Name = {$pizzaData[$i - 1]['name']}, ";
            echo "Description = {$pizzaData[$i - 1]['description']}, ";
            echo "Price = {$pizzaData[$i - 1]['price']}\n";

            $this->assertTrue($result, "Failed to add pizza: Name = $name, Description = $description, Price = $price");
        }
    }

    // Тест для ошибки при добавлении пиццы в базу данных
    public function testAddPizzaToDatabaseFailure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Массив для хранения данных, которые будут наращиваться
        $pizzaData = [];

        // Начальные базовые данные
        $baseName = 'Маргарита';
        $baseDescription = 'Вкусная пицца';
        $basePrice = 500.0;

        // Мокаем метод prepare, чтобы он мог быть вызван несколько раз
        $mockStmt = $this->createMock(mysqli_stmt::class);

        // Мокаем метод prepare объекта $conn, чтобы он мог быть вызван несколько раз
        $this->mockConn->expects($this->exactly(10))  // Мы ожидаем, что prepare будет вызван 10 раз (по одному для каждой пиццы)
                        ->method('prepare')
                        ->with("INSERT INTO pizza (name, description, price) VALUES (?, ?, ?)")
                        ->willReturn($mockStmt);

        // Устанавливаем ожидания для bind_param(), execute() и close()
        $mockStmt->expects($this->exactly(10))  // Мы ожидаем, что bind_param будет вызываться 10 раз
                  ->method('bind_param')
                  ->with(
                      'ssd',
                      $this->callback(function($name) {
                          return strpos($name, 'Маргарита') === 0;
                      }),
                      $this->callback(function($description) {
                          return strpos($description, 'Вкусная пицца') === 0;
                      }),
                      $this->greaterThanOrEqual(0)
                  )
                  ->willReturn(true);

        $mockStmt->expects($this->exactly(10))  // Мы ожидаем, что execute() будет вызван 10 раз
                  ->method('execute')
                  ->willReturn(true);

        $mockStmt->expects($this->exactly(10))  // Мы ожидаем, что close() будет вызван 10 раз
                  ->method('close')
                  ->willReturn(true);

        // Цикл наращивания данных для пиццы
        for ($i = 1; $i <= 10; $i++) {
            // Наращиваем данные с каждым шагом
            $name = $baseName; // имя не изменяется
            $description = $baseDescription . str_repeat(' вкусная', $i); // описание увеличивается
            $price = $basePrice + ($i * 50); // цена увеличивается

            // Добавляем текущие данные в массив
            $pizzaData[] = [
                'name' => $name,
                'description' => $description,
                'price' => $price
            ];

            // Мокаем метод prepare, чтобы он бросал исключение
            $this->mockConn->expects($this->once())
                            ->method('prepare')
                            ->willThrowException(new mysqli_sql_exception('Ошибка подключения'));

            // Выполняем тестируемую функцию
            $result = addPizzaToDatabase($this->mockConn, $name, $description, $price);

            // Выводим проверенные данные в терминал
            echo "Проверенная пицца: ";
            echo "Name = {$pizzaData[$i - 1]['name']}, ";
            echo "Description = {$pizzaData[$i - 1]['description']}, ";
            echo "Price = {$pizzaData[$i - 1]['price']}\n";

            // Проверяем, что функция возвращает false при ошибке
            $this->assertFalse($result, "Failed to add pizza (error expected): Name = $name, Description = $description, Price = $price");
        }
    }
}
