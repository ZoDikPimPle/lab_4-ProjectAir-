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

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Используем LEFT JOIN для получения данных из таблицы user_profiles
$sql = "SELECT u.*, up.full_name, up.phone, up.passport_series, up.passport_number, up.passport_issued_by, up.passport_issue_date, up.birth_date FROM users u LEFT JOIN user_profiles up ON u.user_id = up.user_id $whereClause ORDER BY u.user_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Роли для фильтра и редактирования
$roles = ['guest', 'user', 'admin'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Пользователи</title>
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
</head>
<body>
<h2>Пользователи</h2>
<a href="edit_user.php">Добавить нового пользователя</a>

<!-- Форма фильтрации -->
<form method="get" id="filterForm">
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th><input type="text" name="user_id" placeholder="ID" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>"></th>
            <th><input type="text" name="email" placeholder="Email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>"></th>
            <th>
                <select name="role">
                    <option value="">Все роли</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>" <?= (!empty($_GET['role']) && $_GET['role'] === $role ? 'selected' : '') ?>>
                            <?= htmlspecialchars($role) ?>
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

<!-- Таблица с пользователями -->
<table border="1" cellpadding="5" cellspacing="0" id="usersTable">
    <thead>
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>ФИО</th>
        <th>Телефон</th>
        <th>Серия паспорта</th>
        <th>Номер паспорта</th>
        <th>Дата рождения</th>
        <th>Роль</th>
        <th>Дата создания</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr data-user="<?= htmlspecialchars($user['user_id']) ?>">
            <td><?= htmlspecialchars($user['user_id']) ?></td>
            <td class="editable" data-field="email" data-original="<?= htmlspecialchars($user['email']) ?>">
                <?= htmlspecialchars($user['email']) ?>
            </td>
            <td class="editable" data-field="full_name" data-original="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                <?= htmlspecialchars($user['full_name'] ?? '') ?>
            </td>
            <td class="editable" data-field="phone" data-original="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                <?= htmlspecialchars($user['phone'] ?? '') ?>
            </td>
            <td class="editable" data-field="passport_series" data-original="<?= htmlspecialchars($user['passport_series'] ?? '') ?>">
                <?= htmlspecialchars($user['passport_series'] ?? '') ?>
            </td>
            <td class="editable" data-field="passport_number" data-original="<?= htmlspecialchars($user['passport_number'] ?? '') ?>">
                <?= htmlspecialchars($user['passport_number'] ?? '') ?>
            </td>
            <td class="editable" data-field="birth_date" data-original="<?= htmlspecialchars($user['birth_date'] ? date('d.m.Y', strtotime($user['birth_date'])) : '') ?>">
                <?= htmlspecialchars($user['birth_date'] ? date('d.m.Y', strtotime($user['birth_date'])) : '') ?>
            </td>
            <td class="editable-select" data-field="role" data-original="<?= htmlspecialchars($user['role']) ?>">
                <?= htmlspecialchars($user['role']) ?>
            </td>
            <td><?= htmlspecialchars($user['created_at']) ?></td>
            <td>
                <button class="save-btn" style="display:none;">Сохранить</button>
                <button class="cancel-btn" style="display:none;">Отмена</button>
                <button class="edit-btn">Редактировать</button>
                <a href="delete_user.php?user_id=<?= urlencode($user['user_id']) ?>" onclick="return confirm('Удалить пользователя?');">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('usersTable');
        const roleOptions = `<?php foreach($roles as $role): ?>
            <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option>
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

            // Обрабатываем все редактируемые ячейки
            row.querySelectorAll('.editable').forEach(cell => {
                const field = cell.getAttribute('data-field');
                const originalValue = cell.getAttribute('data-original');

                if (field === 'birth_date') {
                    cell.innerHTML = `<input type="text" value="${originalValue}" placeholder="дд.мм.гггг">`;
                } else {
                    cell.innerHTML = `<input type="text" value="${originalValue}">`;
                }
            });

            row.querySelectorAll('.editable-select').forEach(cell => {
                const originalValue = cell.getAttribute('data-original');
                cell.innerHTML = `<select>${roleOptions}</select>`;
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
                cell.textContent = originalValue;
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

    function resetFilters() {
        // Сброс значений всех полей фильтрации
        const form = document.getElementById('filterForm');
        const inputs = form.querySelectorAll('input, select');

        inputs.forEach(input => {
            if (input.type === 'text') {
                input.value = '';
            } else if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            }
        });

        // Отправка формы после сброса
        form.submit();
    }

    function massUpdateRole(newRole) {
        if (!confirm(`Вы уверены, что хотите изменить роль ВСЕХ пользователей на "${newRole}"?`)) {
            return;
        }

        // Получаем все ID пользователей из таблицы
        const userIds = Array.from(document.querySelectorAll('tr[data-user]')).map(
            row => row.getAttribute('data-user')
        );

        if (userIds.length === 0) {
            alert('Нет пользователей для обновления');
            return;
        }

        fetch('mass_update_users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_ids: userIds,
                new_role: newRole
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Роль ${userIds.length} пользователей успешно обновлена на "${newRole}"`);
                    location.reload(); // Обновляем страницу
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при обновлении ролей');
            });
    }
</script>

<?php include 'footer.php'; ?>
