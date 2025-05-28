<?php
require_once 'db.php';
include 'header.php';

function formatRussianDate($datetime) {
    if (empty($datetime)) return '';
    $date = new DateTime($datetime);
    return $date->format('d.m.Y H:i');
}

// Получаем список рейсов с информацией из связанных таблиц с учетом фильтров
$where = [];
$params = [];

// Проверяем фильтры из GET-запроса
foreach (['flight_number', 'departure_city', 'arrival_city', 'status', 'aircraft_name'] as $field) {
    if (!empty($_GET[$field])) {
        $where[] = ($field === 'departure_city' ? 'da.city' :
                ($field === 'arrival_city' ? 'aa.city' :
                    ($field === 'aircraft_name' ? 'at.name' : "f.$field"))) . " LIKE ?";
        $params[] = '%' . $_GET[$field] . '%';
    }
}

// Фильтры по дате
if (!empty($_GET['departure_time_from'])) {
    $where[] = "f.departure_time >= ?";
    $params[] = $_GET['departure_time_from'];
}
if (!empty($_GET['departure_time_to'])) {
    $where[] = "f.departure_time <= ?";
    $params[] = $_GET['departure_time_to'];
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT f.flight_number, f.departure_time, f.arrival_time,
           da.city AS departure_city, aa.city AS arrival_city,
           f.status, at.name AS aircraft_name,
           da.airport_code AS departure_code, 
           aa.airport_code AS arrival_code,
           at.type_code AS aircraft_code
    FROM flights f
    JOIN airports da ON f.departure_airport = da.airport_code
    JOIN airports aa ON f.arrival_airport = aa.airport_code
    JOIN aircraft_types at ON f.aircraft_type_code = at.type_code
    $whereClause
    ORDER BY f.departure_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем списки для выпадающих меню
$airports = $pdo->query("SELECT airport_code, city FROM airports")->fetchAll(PDO::FETCH_ASSOC);
$aircrafts = $pdo->query("SELECT type_code, name FROM aircraft_types")->fetchAll(PDO::FETCH_ASSOC);
$statuses = ['Scheduled', 'Departed', 'Arrived', 'Cancelled', 'Delayed'];
?>

    <h2>Рейсы</h2>
    <a href="edit.php">Добавить новый рейс</a>


    <div style="margin: 10px 0;">
        <button onclick="massUpdateStatus('Cancelled')" class="mass-action-btn">Отменить все рейсы</button>
        <button onclick="massUpdateStatus('Delayed')" class="mass-action-btn">Задержать все рейсы</button>
    </div>

    <style>
        .mass-action-btn {
            padding: 8px 15px;
            margin-right: 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .mass-action-btn:hover {
            opacity: 0.8;
        }
    </style>

    <!-- Форма фильтрации -->
    <form method="get" id="filterForm">
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th><input type="text" name="flight_number" placeholder="Номер рейса" value="<?=htmlspecialchars($_GET['flight_number'] ?? '')?>"></th>
                <th>
                    От: <input type="datetime-local" name="departure_time_from" value="<?=htmlspecialchars($_GET['departure_time_from'] ?? '')?>"><br>
                    До: <input type="datetime-local" name="departure_time_to" value="<?=htmlspecialchars($_GET['departure_time_to'] ?? '')?>">
                </th>
                <th><input type="datetime-local" name="arrival_time" placeholder="Прибытие" value="<?=htmlspecialchars($_GET['arrival_time'] ?? '')?>"></th>
                <th>
                    <select name="departure_city">
                        <option value="">Все города</option>
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?=htmlspecialchars($airport['city'])?>" <?=(!empty($_GET['departure_city']) && $_GET['departure_city'] === $airport['city'] ? 'selected' : '')?>>
                                <?=htmlspecialchars($airport['city'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </th>
                <th>
                    <select name="arrival_city">
                        <option value="">Все города</option>
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?=htmlspecialchars($airport['city'])?>" <?=(!empty($_GET['arrival_city']) && $_GET['arrival_city'] === $airport['city'] ? 'selected' : '')?>>
                                <?=htmlspecialchars($airport['city'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </th>
                <th>
                    <select name="status">
                        <option value="">Все статусы</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?=htmlspecialchars($status)?>" <?=(!empty($_GET['status']) && $_GET['status'] === $status ? 'selected' : '')?>>
                                <?=htmlspecialchars($status)?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </th>
                <th>
                    <select name="aircraft_name">
                        <option value="">Все самолёты</option>
                        <?php foreach ($aircrafts as $aircraft): ?>
                            <option value="<?=htmlspecialchars($aircraft['name'])?>" <?=(!empty($_GET['aircraft_name']) && $_GET['aircraft_name'] === $aircraft['name'] ? 'selected' : '')?>>
                                <?=htmlspecialchars($aircraft['name'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </th>
                <th>
                    <button type="submit">Фильтровать</button>
                    <button type="button" onclick="resetFilters()">Сбросить</button>
                </th>
            </tr>
        </table>
    </form>


    <!-- Таблица с данными -->
    <table border="1" cellpadding="5" cellspacing="0" id="flightsTable">
        <thead>
        <tr>
            <th>Номер рейса</th>
            <th>Вылет</th>
            <th>Прибытие</th>
            <th>Город вылета</th>
            <th>Город прибытия</th>
            <th>Статус</th>
            <th>Тип самолёта</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($flights as $flight): ?>
            <tr data-flight="<?=htmlspecialchars($flight['flight_number'])?>">
                <td><?=htmlspecialchars($flight['flight_number'])?></td>
                <td class="editable" data-field="departure_time" data-original="<?=htmlspecialchars($flight['departure_time'])?>">
                    <?=htmlspecialchars(formatRussianDate($flight['departure_time']))?>
                </td>
                <td class="editable" data-field="arrival_time" data-original="<?=htmlspecialchars($flight['arrival_time'])?>">
                    <?=htmlspecialchars(formatRussianDate($flight['arrival_time']))?>
                </td>
                <td class="editable-select" data-field="departure_airport" data-original="<?=htmlspecialchars($flight['departure_code'])?>">
                    <?=htmlspecialchars($flight['departure_city'])?>
                </td>
                <td class="editable-select" data-field="arrival_airport" data-original="<?=htmlspecialchars($flight['arrival_code'])?>">
                    <?=htmlspecialchars($flight['arrival_city'])?>
                </td>
                <td class="editable-select" data-field="status" data-original="<?=htmlspecialchars($flight['status'])?>">
                    <?=htmlspecialchars($flight['status'])?>
                </td>
                <td class="editable-select" data-field="aircraft_type_code" data-original="<?=htmlspecialchars($flight['aircraft_code'])?>">
                    <?=htmlspecialchars($flight['aircraft_name'])?>
                </td>
                <td>
                    <button class="save-btn" style="display:none;">Сохранить</button>
                    <button class="cancel-btn" style="display:none;">Отмена</button>
                    <button class="edit-btn">Редактировать</button>
                    <a href="delete_flight.php?flight_number=<?=urlencode($flight['flight_number'])?>" onclick="return confirm('Удалить рейс?');">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <script>


        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('flightsTable');

            table.addEventListener('click', function(e) {
                const target = e.target;

                // Обработка кнопки редактирования
                if (target.classList.contains('edit-btn')) {
                    const row = target.closest('tr');
                    enableEditMode(row);
                }

                // Обработка кнопки сохранения
                if (target.classList.contains('save-btn')) {
                    const row = target.closest('tr');
                    saveChanges(row);
                }

                // Обработка кнопки отмены
                if (target.classList.contains('cancel-btn')) {
                    const row = target.closest('tr');
                    cancelEdit(row);
                }
            });

            // Включение режима редактирования
            function enableEditMode(row) {
                // Показать кнопки сохранения/отмены
                row.querySelector('.save-btn').style.display = 'inline-block';
                row.querySelector('.cancel-btn').style.display = 'inline-block';
                row.querySelector('.edit-btn').style.display = 'none';

                // Обрабатываем все редактируемые ячейки
                row.querySelectorAll('.editable').forEach(cell => {
                    const originalValue = cell.getAttribute('data-original');
                    cell.innerHTML = `<input type="datetime-local" value="${originalValue}">`;
                });

                row.querySelectorAll('.editable-select').forEach(cell => {
                    const field = cell.getAttribute('data-field');
                    const originalValue = cell.getAttribute('data-original');

                    let options = '';
                    if (field === 'status') {
                        options = `<?php foreach($statuses as $status): ?>
                    <option value="<?=htmlspecialchars($status)?>"><?=htmlspecialchars($status)?></option>
                <?php endforeach; ?>`;
                    } else if (field === 'departure_airport' || field === 'arrival_airport') {
                        options = `<?php foreach($airports as $airport): ?>
                    <option value="<?=htmlspecialchars($airport['airport_code'])?>"><?=htmlspecialchars($airport['city'])?></option>
                <?php endforeach; ?>`;
                    } else if (field === 'aircraft_type_code') {
                        options = `<?php foreach($aircrafts as $aircraft): ?>
                    <option value="<?=htmlspecialchars($aircraft['type_code'])?>"><?=htmlspecialchars($aircraft['name'])?></option>
                <?php endforeach; ?>`;
                    }

                    cell.innerHTML = `<select>${options}</select>`;
                    cell.querySelector('select').value = originalValue;
                });
            }

            // Отмена редактирования
            function cancelEdit(row) {
                // Скрыть кнопки сохранения/отмены
                row.querySelector('.save-btn').style.display = 'none';
                row.querySelector('.cancel-btn').style.display = 'none';
                row.querySelector('.edit-btn').style.display = 'inline-block';

                // Восстановить оригинальные значения
                row.querySelectorAll('.editable, .editable-select').forEach(cell => {
                    const originalValue = cell.getAttribute('data-original');
                    const field = cell.getAttribute('data-field');

                    if (cell.classList.contains('editable')) {
                        cell.textContent = originalValue;
                    } else {
                        // Для select нужно найти соответствующее название
                        let displayValue = originalValue;
                        if (field === 'status') {
                            displayValue = originalValue;
                        } else if (field === 'departure_airport' || field === 'arrival_airport') {
                            displayValue = '<?php
                                $airportsMap = [];
                                foreach($airports as $a) $airportsMap[$a['airport_code']] = $a['city'];
                                echo json_encode($airportsMap);
                                ?>'[originalValue];
                        } else if (field === 'aircraft_type_code') {
                            displayValue = '<?php
                                $aircraftsMap = [];
                                foreach($aircrafts as $a) $aircraftsMap[$a['type_code']] = $a['name'];
                                echo json_encode($aircraftsMap);
                                ?>'[originalValue];
                        }

                        cell.textContent = displayValue;
                    }
                });
            }

            // Сохранение изменений
            function saveChanges(row) {
                const flightNumber = row.getAttribute('data-flight');
                const data = {
                    flight_number: flightNumber
                };

                // Собираем данные из редактируемых полей
                row.querySelectorAll('.editable, .editable-select').forEach(cell => {
                    const field = cell.getAttribute('data-field');
                    if (cell.classList.contains('editable')) {
                        data[field] = cell.querySelector('input').value;
                    } else {
                        data[field] = cell.querySelector('select').value;
                    }
                });

                // Отправляем данные на сервер
                fetch('update_flight.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Обновляем оригинальные значения
                            row.querySelectorAll('.editable').forEach(cell => {
                                const field = cell.getAttribute('data-field');
                                cell.setAttribute('data-original', data[field]);
                            });

                            row.querySelectorAll('.editable-select').forEach(cell => {
                                const field = cell.getAttribute('data-field');
                                cell.setAttribute('data-original', data[field]);
                            });

                            // Выходим из режима редактирования
                            cancelEdit(row);

                            // Обновляем отображаемые значения
                            location.reload(); // Или можно обновить только измененные ячейки
                        } else {
                            alert('Ошибка при сохранении: ' + (result.message || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Произошла ошибка при сохранении');
                    });
            }
        });

        function resetFilters() {
            // Сброс значений всех полей фильтрации
            const form = document.getElementById('filterForm');
            const inputs = form.querySelectorAll('input, select');

            inputs.forEach(input => {
                if (input.type === 'text' || input.type === 'datetime-local') {
                    input.value = '';
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                }
            });

            // Отправка формы после сброса
            form.submit();
        }

        function massUpdateStatus(newStatus) {
            if (!confirm(`Вы уверены, что хотите ${newStatus === 'Cancelled' ? 'отменить' : 'задержать'} ВСЕ рейсы?`)) {
                return;
            }

            // Получаем все номера рейсов из таблицы
            const flightNumbers = Array.from(document.querySelectorAll('tr[data-flight]')).map(
                row => row.getAttribute('data-flight')
            );

            if (flightNumbers.length === 0) {
                alert('Нет рейсов для обновления');
                return;
            }

            fetch('mass_update_flights.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    flight_numbers: flightNumbers,
                    new_status: newStatus
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Статус ${flightNumbers.length} рейсов успешно обновлен на "${newStatus}"`);
                        location.reload(); // Обновляем страницу
                    } else {
                        alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при обновлении статусов');
                });
        }
    </script>

<?php include 'footer.php'; ?>