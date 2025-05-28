<?php
require_once 'db.php';
include 'header.php';

// Получаем список пользователей с учетом фильтров
$where = [];
$params = [];

// Проверяем фильтры из GET-запроса
foreach (['user_id', 'email', 'role'] as $field) {
    if (!empty($_GET[$field])) {
        $where[] = "u.$field LIKE ?";
        $params[] = '%' . $_GET[$field] . '%';
    }
}

// Фильтры по дате
if (!empty($_GET['created_at_from'])) {
    $where[] = "u.created_at >= ?";
    $params[] = $_GET['created_at_from'];
}
if (!empty($_GET['created_at_to'])) {
    $where[] = "u.created_at <= ?";
    $params[] = $_GET['created_at_to'];
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT u.user_id, u.email, u.role, u.created_at,
           up.full_name, up.phone, up.passport_series, up.passport_number
    FROM users u
    LEFT JOIN user_profiles up ON u.user_id = up.user_id
    $whereClause
    ORDER BY u.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roles = ['guest', 'user', 'admin'];
?>

    <h2>Пользователи</h2>

    <!-- Форма фильтрации -->
    <form method="get" id="filterForm">
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th><input type="text" name="user_id" placeholder="ID пользователя" value="<?=htmlspecialchars($_GET['user_id'] ?? '')?>"></th>
                <th><input type="text" name="email" placeholder="Email" value="<?=htmlspecialchars($_GET['email'] ?? '')?>"></th>
                <th>
                    <select name="role">
                        <option value="">Все роли</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?=htmlspecialchars($role)?>" <?=(!empty($_GET['role']) && $_GET['role'] === $role ? 'selected' : '')?>>
                                <?=htmlspecialchars($role)?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </th>
                <th>
                    От: <input type="date" name="created_at_from" value="<?=htmlspecialchars($_GET['created_at_from'] ?? '')?>"><br>
                    До: <input type="date" name="created_at_to" value="<?=htmlspecialchars($_GET['created_at_to'] ?? '')?>">
                </th>
                <th>
                    <button type="submit">Фильтровать</button>
                    <button type="button" onclick="resetFilters()">Сбросить</button>
                </th>
            </tr>
    </form>
    </table>

    <!-- Таблица с данными -->
    <table border="1" cellpadding="5" cellspacing="0" id="usersTable">
        <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Роль</th>
            <th>Имя</th>
            <th>Телефон</th>
            <th>Паспорт</th>
            <th>Дата регистрации</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($users as $user): ?>
            <tr data-user="<?=htmlspecialchars($user['user_id'])?>">
                <td><?=htmlspecialchars($user['user_id'])?></td>
                <td class="editable" data-field="email" data-original="<?=htmlspecialchars($user['email'])?>">
                    <?=htmlspecialchars($user['email'])?>
                </td>
                <td class="editable-select" data-field="role" data-original="<?=htmlspecialchars($user['role'])?>">
                    <?=htmlspecialchars($user['role'])?>
                </td>
                <td class="editable" data-field="full_name" data-original="<?=htmlspecialchars($user['full_name'] ?? '')?>">
                    <?=htmlspecialchars($user['full_name'] ?? '')?>
                </td>
                <td class="editable" data-field="phone" data-original="<?=htmlspecialchars($user['phone'] ?? '')?>">
                    <?=htmlspecialchars($user['phone'] ?? '')?>
                </td>
                <td>
                    <?=htmlspecialchars(($user['passport_series'] ?? '') . ' ' . ($user['passport_number'] ?? ''))?>
                </td>
                <td><?=htmlspecialchars($user['created_at'])?></td>
                <td>
                    <button class="save-btn" style="display:none;">Сохранить</button>
                    <button class="cancel-btn" style="display:none;">Отмена</button>
                    <button class="edit-btn">Редактировать</button>
                    <a href="delete_user.php?user_id=<?=urlencode($user['user_id'])?>" onclick="return confirm('Удалить пользователя?');">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('usersTable');

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
                    cell.innerHTML = `<input type="text" value="${originalValue}">`;
                });

                row.querySelectorAll('.editable-select').forEach(cell => {
                    const field = cell.getAttribute('data-field');
                    const originalValue = cell.getAttribute('data-original');

                    let options = '';
                    if (field === 'role') {
                        options = `<?php foreach($roles as $role): ?>
                        <option value="<?=htmlspecialchars($role)?>"><?=htmlspecialchars($role)?></option>
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
                        cell.textContent = originalValue;
                    }
                });
            }

            // Сохранение изменений
            function saveChanges(row) {
                const userId = row.getAttribute('data-user');
                const data = {
                    user_id: userId
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
                fetch('update_user.php', {
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
                            row.querySelectorAll('.editable, .editable-select').forEach(cell => {
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
                if (input.type === 'text' || input.type === 'date') {
                    input.value = '';
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                }
            });

            // Отправка формы после сброса
            form.submit();
        }
    </script>

<?php include 'footer.php'; ?><?php
