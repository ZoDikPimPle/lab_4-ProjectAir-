-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 16 2025 г., 20:15
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `ProjectAir`
--

-- --------------------------------------------------------

--
-- Структура таблицы `aircraft_types`
--

CREATE TABLE `aircraft_types` (
  `type_code` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `max_capacity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `aircraft_types`
--

INSERT INTO `aircraft_types` (`type_code`, `name`, `max_capacity`) VALUES
('AT001', 'Сухой Суперджет 100', 108),
('AT002', 'Ил-96', 300),
('AT003', 'Ту-204', 210),
('AT004', 'Як-42', 120),
('AT005', 'Ан-148', 85);

-- --------------------------------------------------------

--
-- Структура таблицы `airports`
--

CREATE TABLE `airports` (
  `airport_code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `utc_offset` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `airports`
--

INSERT INTO `airports` (`airport_code`, `name`, `city`, `utc_offset`) VALUES
('DME', 'Домодедово', 'Москва', 3),
('KZN', 'Казань', 'Казань', 3),
('LED', 'Пулково', 'Санкт-Петербург', 3),
('ROV', 'Платов', 'Ростов-на-Дону', 3),
('SVO', 'Шереметьево', 'Москва', 3);

-- --------------------------------------------------------

--
-- Структура таблицы `baggage`
--

CREATE TABLE `baggage` (
  `baggage_id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `weight` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `baggage`
--

INSERT INTO `baggage` (`baggage_id`, `ticket_id`, `weight`) VALUES
(1, 1, '23.50'),
(2, 2, '20.00'),
(3, 3, '25.00'),
(4, 4, '18.50'),
(5, 5, '22.00');

-- --------------------------------------------------------

--
-- Структура таблицы `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int NOT NULL,
  `user_id` int NOT NULL,
  `flight_number` varchar(10) NOT NULL,
  `booking_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `flight_number`, `booking_date`, `status`) VALUES
(1, 1, 'FL001', '2025-04-27 20:44:42', 'confirmed'),
(2, 1, 'FL002', '2025-04-28 08:01:10', 'confirmed'),
(3, 1, 'FL001', '2025-05-11 17:18:57', 'confirmed');

-- --------------------------------------------------------

--
-- Структура таблицы `flights`
--

CREATE TABLE `flights` (
  `flight_number` varchar(10) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `departure_airport` varchar(10) NOT NULL,
  `arrival_airport` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL,
  `aircraft_type_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `flights`
--

INSERT INTO `flights` (`flight_number`, `departure_time`, `arrival_time`, `departure_airport`, `arrival_airport`, `status`, `aircraft_type_code`) VALUES
('6', '2025-03-30 02:58:00', '2025-03-31 23:58:00', 'SVO', 'SVO', 'Cancelled', 'AT003'),
('FL001', '2023-10-01 08:00:00', '2023-10-01 10:00:00', 'SVO', 'LED', 'Cancelled', 'AT002'),
('FL002', '2023-10-01 09:00:00', '2023-10-01 11:00:00', 'DME', 'SVO', 'Cancelled', 'AT002'),
('FL003', '2023-10-01 10:00:00', '2023-10-01 12:00:00', 'LED', 'SVO', 'Scheduled', 'AT003'),
('FL004', '2023-10-01 11:00:00', '2023-10-01 13:00:00', 'SVO', 'SVO', 'Cancelled', 'AT001'),
('FL005', '2023-10-01 12:00:00', '2023-10-01 14:00:00', 'SVO', 'DME', 'Cancelled', 'AT001'),
('FL007', '2025-03-31 14:03:00', '2025-03-31 15:00:00', 'SVO', 'LED', 'Cancelled', 'AT003');

-- --------------------------------------------------------

--
-- Структура таблицы `passengers`
--

CREATE TABLE `passengers` (
  `passenger_id` int NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `flight_number` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `passengers`
--

INSERT INTO `passengers` (`passenger_id`, `full_name`, `flight_number`) VALUES
(1, 'Иван Иванов', 'FL001'),
(2, 'Мария Петрова', 'FL002'),
(3, 'Анна Сидорова', 'FL003'),
(4, 'Дмитрий Кузнецов', 'FL004'),
(5, 'Елена Васильева', 'FL005'),
(6, 'Filippov Nikita', 'FL001'),
(7, 'Fil Nik', 'FL002'),
(8, '', 'FL001');

-- --------------------------------------------------------

--
-- Структура таблицы `passenger_baggage`
--

CREATE TABLE `passenger_baggage` (
  `passenger_id` int NOT NULL,
  `baggage_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `status_flight`
--

CREATE TABLE `status_flight` (
  `status_code` varchar(10) NOT NULL,
  `status_description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `status_flight`
--

INSERT INTO `status_flight` (`status_code`, `status_description`) VALUES
('Cancelled', 'Отменен'),
('Completed', 'Завершен'),
('Delayed', 'Задержан'),
('Scheduled', 'Запланирован');

-- --------------------------------------------------------

--
-- Структура таблицы `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int NOT NULL,
  `passenger_id` int NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `passenger_id`, `seat_number`, `price`) VALUES
(1, 1, '1A', '5000.00'),
(2, 2, '2B', '5500.00'),
(3, 3, '3C', '6000.00'),
(4, 4, '4D', '6500.00'),
(5, 5, '5E', '7000.00'),
(6, 6, '3A', '6300.00'),
(7, 7, '1A', '6300.00'),
(8, 8, '2B', '6300.00');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guest','user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`, `created_at`) VALUES
(1, '1234@mail.ru', '1234', 'admin', '2025-04-27 17:29:07'),
(6, 'admin@mail.ru', '1234', 'admin', '2025-05-25 19:24:43'),
(7, '12345678@mail.ru', '$2y$10$bBoV9pL2lvU85Nr0Yd6y5.w0Yk20N2w3GY4lqxxDnUTJNDCWlZ06a', 'user', '2025-05-26 10:14:43');

-- --------------------------------------------------------

--
-- Структура таблицы `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` int NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `passport_series` varchar(10) DEFAULT NULL,
  `passport_number` varchar(20) DEFAULT NULL,
  `passport_issued_by` varchar(255) DEFAULT NULL,
  `passport_issue_date` date DEFAULT NULL,
  `birth_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `full_name`, `phone`, `passport_series`, `passport_number`, `passport_issued_by`, `passport_issue_date`, `birth_date`) VALUES
(7, 'Филиппов Никита Ильич', '+7911111111', '2222', '333333', 'им', '2023-05-26', '2002-11-22');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `aircraft_types`
--
ALTER TABLE `aircraft_types`
  ADD PRIMARY KEY (`type_code`);

--
-- Индексы таблицы `airports`
--
ALTER TABLE `airports`
  ADD PRIMARY KEY (`airport_code`);

--
-- Индексы таблицы `baggage`
--
ALTER TABLE `baggage`
  ADD PRIMARY KEY (`baggage_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Индексы таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `flight_number` (`flight_number`);

--
-- Индексы таблицы `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`flight_number`),
  ADD KEY `departure_airport` (`departure_airport`),
  ADD KEY `arrival_airport` (`arrival_airport`),
  ADD KEY `aircraft_type_code` (`aircraft_type_code`),
  ADD KEY `fk_status` (`status`);

--
-- Индексы таблицы `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`passenger_id`),
  ADD KEY `flight_number` (`flight_number`);

--
-- Индексы таблицы `passenger_baggage`
--
ALTER TABLE `passenger_baggage`
  ADD PRIMARY KEY (`passenger_id`,`baggage_id`),
  ADD KEY `baggage_id` (`baggage_id`);

--
-- Индексы таблицы `status_flight`
--
ALTER TABLE `status_flight`
  ADD PRIMARY KEY (`status_code`);

--
-- Индексы таблицы `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `passenger_id` (`passenger_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `baggage`
--
ALTER TABLE `baggage`
  MODIFY `baggage_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `passengers`
--
ALTER TABLE `passengers`
  MODIFY `passenger_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `baggage`
--
ALTER TABLE `baggage`
  ADD CONSTRAINT `baggage_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`);

--
-- Ограничения внешнего ключа таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`flight_number`) REFERENCES `flights` (`flight_number`);

--
-- Ограничения внешнего ключа таблицы `flights`
--
ALTER TABLE `flights`
  ADD CONSTRAINT `fk_status` FOREIGN KEY (`status`) REFERENCES `status_flight` (`status_code`),
  ADD CONSTRAINT `flights_ibfk_1` FOREIGN KEY (`departure_airport`) REFERENCES `airports` (`airport_code`),
  ADD CONSTRAINT `flights_ibfk_2` FOREIGN KEY (`arrival_airport`) REFERENCES `airports` (`airport_code`),
  ADD CONSTRAINT `flights_ibfk_3` FOREIGN KEY (`aircraft_type_code`) REFERENCES `aircraft_types` (`type_code`);

--
-- Ограничения внешнего ключа таблицы `passengers`
--
ALTER TABLE `passengers`
  ADD CONSTRAINT `passengers_ibfk_1` FOREIGN KEY (`flight_number`) REFERENCES `flights` (`flight_number`);

--
-- Ограничения внешнего ключа таблицы `passenger_baggage`
--
ALTER TABLE `passenger_baggage`
  ADD CONSTRAINT `passenger_baggage_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`passenger_id`),
  ADD CONSTRAINT `passenger_baggage_ibfk_2` FOREIGN KEY (`baggage_id`) REFERENCES `baggage` (`baggage_id`);

--
-- Ограничения внешнего ключа таблицы `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`passenger_id`);

--
-- Ограничения внешнего ключа таблицы `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
