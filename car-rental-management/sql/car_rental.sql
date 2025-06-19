-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 05 Jun 2025 pada 06.24
-- Versi server: 8.4.3
-- Versi PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `car_rental`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cars`
--

CREATE TABLE `cars` (
  `id` int NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int NOT NULL,
  `type_id` int DEFAULT NULL,
  `availability` tinyint(1) DEFAULT '1',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `cars`
--

INSERT INTO `cars` (`id`, `make`, `model`, `year`, `type_id`, `availability`, `image`) VALUES
(1, 'Toyota', 'Avanza', 2022, 1, 1, 'avanza.jpg'),
(2, 'Honda', 'Jazz', 2021, 3, 0, 'jazz.jpg'),
(3, 'Suzuki', 'Ertiga', 2020, 1, 1, 'ertiga.jpg'),
(4, 'Ferari', 'Amvibi', 2024, 3, 0, 'ferari.jpg'),
(6, 'Mobil Civic', 'Turbo', 2020, 1, 1, 'mobil-civic-turbo.jpg'),
(8, 'Mobil Civic', 'Type R', 2021, 1, 0, 'mobil-civic-tipe-r.jpg'),
(9, 'BMW', 'Sport', 2021, 4, 1, 'BMW.jpg'),
(10, 'Ferrari', 'Spiderxiii', 2023, 4, 1, 'ferrari-spider8.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `rental_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rentals`
--

CREATE TABLE `rentals` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `car_id` int DEFAULT NULL,
  `rental_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `rentals`
--

INSERT INTO `rentals` (`id`, `user_id`, `car_id`, `rental_date`, `return_date`, `payment_proof`, `payment_status`, `payment_date`) VALUES
(1, 5, 9, '2025-06-03 15:29:27', '2025-06-10 15:29:27', 'payment_1748939367_Flow Chart.png', 'rejected', '2025-06-03 15:29:27'),
(2, 5, 4, '2025-06-03 15:29:56', '2025-06-10 15:29:56', 'payment_1748939396_ERD Api.png', 'approved', '2025-06-03 15:29:56'),
(3, 5, 2, '2025-06-03 16:42:37', '2025-06-10 16:42:37', 'payment_1748943757_ERD Api.png', 'approved', '2025-06-03 16:42:37'),
(4, 13, 1, '2025-06-04 23:36:39', '2025-06-11 23:36:39', 'payment_1749054999_ERD Api.png', 'rejected', '2025-06-04 23:36:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','borrower') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(4, 'admin', '$2y$10$CBYYNe.clLwcPwXRQsgpHOPZUyVMx35tpYkgsZ304ujZskpViV.cu', 'admin'),
(5, 'peminjam', '$2y$10$M83W52QhVey3ISJO9xF8q.1XsGNjUvNyHWafNwiINrHraGQSKbbaW', 'borrower'),
(11, 'peminjam1', '$2y$10$Dmh6WLSEgk/B9G6BPNX9ge33ep8h0yMX3dR2U/pyqh69XjBimPAb2', 'borrower'),
(12, 'senza', '$2y$10$UIpUwOgT/S5DRsvC0qFgaeZoFG5ue1QIOBW9kv7x9EbGy07LOvflu', 'borrower'),
(13, 'naila', '$2y$10$bkr7keWBMT/xN2mpO9nebODzbsVhTVlwe3LUhXbtORpMSaWZB8BQy', 'borrower');

-- --------------------------------------------------------

--
-- Struktur dari tabel `vehicle_types`
--

CREATE TABLE `vehicle_types` (
  `id` int NOT NULL,
  `type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `vehicle_types`
--

INSERT INTO `vehicle_types` (`id`, `type_name`) VALUES
(1, 'SUV'),
(2, 'Sedan'),
(3, 'Hatchback'),
(4, 'Supercar');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indeks untuk tabel `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `vehicle_types`
--
ALTER TABLE `vehicle_types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `vehicle_types`
--
ALTER TABLE `vehicle_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `vehicle_types` (`id`);

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`);

--
-- Ketidakleluasaan untuk tabel `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
