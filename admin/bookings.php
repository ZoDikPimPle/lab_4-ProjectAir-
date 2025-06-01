<?php
require_once 'db.php';
include 'header.php';

// Получаем список бронирований с пользователями и рейсами
$stmt = $pdo->query("
    SELECT b.booking_id, u.email, b.flight_number, b.booking_date, b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    ORDER BY b.booking_date DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Возможные статусы бронирования
$statuses = ['pending', 'confirmed', 'cancelled'];
?>

<h2>Бронирования</h2>
<table border="1" cellpadding="5" cellspacing="0" id="bookingsTable">
    <thead>
    <tr>
        <th>ID брони</th>
        <th>Пользователь (email)</th>
        <th>Номер рейса</th>
        <th>Дата брони</th>
        <th>Статус</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($bookings as $b): ?>
        <tr data-booking-id="<?= htmlspecialchars($b['booking_id']) ?>">
            <td><?= htmlspecialchars($b['booking_id']) ?></td>
            <td><?= htmlspecialchars($b['email']) ?></td>
            <td><?= htmlspecialchars($b['flight_number']) ?></td>
            <td><?= htmlspecialchars($b['booking_date']) ?></td>
            <td class="editable-select" data-field="status" data-original="<?= htmlspecialchars($b['status']) ?>">
                <?= htmlspecialchars($b['status']) ?>
            </td>
            <td>
                <button class="save-btn" style="display:none;">Сохранить</button>
                <button class="cancel-btn" style="display:none;">Отмена</button>
                <button class="edit-btn">Редактировать</button>
                <a href="delete_booking.php?id=<?= urlencode($b['booking_id']) ?>" onclick="return confirm('Удалить бронирование?');">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('bookingsTable');
        const statusOptions = `<?php foreach($statuses as $status): ?>
        <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></option>
    <?php endforeach; ?>`;

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

            // Обрабатываем редактируемые ячейки
            row.querySelectorAll('.editable-select').forEach(cell => {
                const originalValue = cell.getAttribute('data-original');
                cell.innerHTML = `<select>${statusOptions}</select>`;
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
            row.querySelectorAll('.editable-select').forEach(cell => {
                const originalValue = cell.getAttribute('data-original');
                cell.textContent = originalValue;
            });
        }

        // Сохранение изменений
        function saveChanges(row) {
            const bookingId = row.getAttribute('data-booking-id');
            const data = {
                booking_id: bookingId
            };

            // Собираем данные из редактируемых полей
            row.querySelectorAll('.editable-select').forEach(cell => {
                const field = cell.getAttribute('data-field');
                data[field] = cell.querySelector('select').value;
            });

            // Отправляем данные на сервер
            fetch('update_booking.php', {
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
                        row.querySelectorAll('.editable-select').forEach(cell => {
                            const field = cell.getAttribute('data-field');
                            const newValue = data[field];
                            cell.setAttribute('data-original', newValue);
                            cell.textContent = newValue;
                        });
                        cancelEdit(row);
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
</script>

<?php include 'footer.php'; ?>
