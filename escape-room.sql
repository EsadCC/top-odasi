-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2026 at 01:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `escape-room`
--

-- --------------------------------------------------------

--
-- Table structure for table `puzzles`
--

CREATE TABLE `puzzles` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` varchar(255) NOT NULL,
  `hint` text DEFAULT NULL,
  `roomId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `puzzles`
--

INSERT INTO `puzzles` (`id`, `question`, `answer`, `hint`, `roomId`) VALUES
(13, 'Op de keldermuur krassen 13 strepen. Elke dag verdwijnt er één. Na 6 dagen, hoeveel strepen zijn er nog?', '7', 'Trek 6 af van 13.', 1),
(14, 'Een oud hangslot heeft een 3-cijferige code.', '463', 'Dood = 4 letters, 3×2=6, 10-7=3.', 1),
(15, 'Er ligt een dagboek open op de tafel. De laatste zin luidt: \"Ik ben begraven op de dag na vrijdag, drie dagen voor dinsdag.\" Op welke dag werd hij begraven?', 'zaterdag', 'Drie dagen voor dinsdag is zaterdag.', 1),
(16, 'De lamp knippert in een patroon: 2 keer, pauze, 4 keer, pauze, 6 keer... Hoeveel keer knippert hij daarna?', '8', 'De reeks gaat +2 omhoog.', 1),
(17, 'Op het whiteboard staat een formule: X = 9 × 9, Y = X − 31, Z = Y + 6. Wat is Z?', '56', '9×9=81, 81−31=50, 50+6=56.', 2),
(18, 'Er staan 6 injectiespuiten op een rek, genummerd 1 t/m 6. Alleen de spuiten met een priemgetal zijn veilig. Welke nummers zijn dat?', '2, 3, 5', 'Priemgetallen zijn alleen deelbaar door 1 en zichzelf.', 2),
(19, 'Een gecodeerd etiket op de kluis: elk woord is gespiegeld. Het staat er: \"TOOD SI ROOD\". Wat is de boodschap?', 'DOOD IS ROOD', 'Schrijf elk woord achterstevoren.', 2),
(20, 'De nooduitgang vraagt een code: \"Neem het aantal maanden in een jaar, vermenigvuldig met 5, trek er 10 van af.\"', '50', '12 × 5 = 60, 60 − 10 = 50.', 2),
(21, 'Op een grafsteen staat: \"Ik ben een getal. Tel mij drie keer op bij mezelf en je krijgt 48. Wat ben ik?\"', '12', '3 × x = 48.', 3),
(22, 'De cijferreeks op het kerkhofhek: 3, 9, 27, 81... Wat is het volgende getal?', '243', 'Elk getal wordt vermenigvuldigd met 3.', 3),
(23, 'Er staan 7 zwarte kaarsen in een patroon: aan, uit, aan, aan, uit... Welke staat er op positie 14: aan of uit?', 'aan', 'Het patroon is 3 lang: aan, aan, uit.', 3),
(24, 'Het grafkruis heeft een slot met 3 cijfers.', '784', '7²÷7 = 7, 16÷2 = 8, een ruit heeft 4 zijden.', 3);

-- --------------------------------------------------------

--
-- Table structure for table `riddles`
--

CREATE TABLE `riddles` (
  `id` int(11) NOT NULL,
  `riddle` text NOT NULL,
  `answer` varchar(255) NOT NULL,
  `hint` varchar(255) DEFAULT NULL,
  `roomId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riddles`
--

INSERT INTO `riddles` (`id`, `riddle`, `answer`, `hint`, `roomId`) VALUES
(1, 'Op de keldermuur krassen 13 strepen. Elke dag verdwijnt er één. Na 6 dagen, hoeveel strepen zijn er nog?', '7', 'Trek 6 af van 13.', 1),
(2, 'Een oud hangslot heeft een code: letters in DOOD, dubbel van 3, 10-7.', '463', '4 6 3', 1),
(3, 'De lamp knippert: 2, 4, 6...', '8', '+2', 1),
(4, 'X = 9 × 9, Y = X − 31, Z = Y + 6. Wat is Z?', '56', '81 - 31 + 6', 2),
(5, 'Welke injectiespuiten zijn priemgetallen tussen 1 en 6?', '2,3,5', 'Priemgetallen', 2),
(6, 'TOOD SI ROOD', 'DOOD IS ROOD', 'Achterstevoren lezen', 2),
(7, '3, 9, 27, 81...', '243', 'x3', 3),
(8, 'Drie keer hetzelfde getal is 48.', '12', '48 / 3', 3),
(9, '7²÷7, helft van 16, zijden van een ruit.', '784', '7 8 4', 3);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `theme` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `description`, `theme`) VALUES
(1, 'De Verlaten Kelder', 'Een donkere, vochtige kelder vol geheimen. De muren fluisteren namen van de verdwenen bewoners.', 'Horror'),
(2, 'De Operatiekamer', 'Een verlaten ziekenhuis waar de dokter nooit is gestopt. Jij bent zijn volgende patiënt.', 'Horror'),
(3, 'Het Kerkhof', 'De doden rusten hier niet. Los de raadsels op voor middernacht... of sluit je je bij hen aan.', 'Horror');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `finish_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `team_name`, `owner_id`, `finish_time`, `created_at`) VALUES
(1, 'Team Brainwave', 2, NULL, '2026-05-28 11:26:06');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`team_id`, `user_id`) VALUES
(1, 2),
(1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('player','admin') NOT NULL DEFAULT 'player',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@escaperoom.nl', '$2y$10$', 'admin', '2026-05-28 11:26:06'),
(2, 'speler1', 'speler1@example.com', '$2y$10$', 'player', '2026-05-28 11:26:06'),
(3, 'speler2', 'speler2@example.com', '$2y$10$', 'player', '2026-05-28 11:26:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `puzzles`
--
ALTER TABLE `puzzles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_puzzles_room` (`roomId`);

--
-- Indexes for table `riddles`
--
ALTER TABLE `riddles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roomId` (`roomId`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_teams_owner` (`owner_id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`team_id`,`user_id`),
  ADD KEY `fk_tm_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `puzzles`
--
ALTER TABLE `puzzles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `riddles`
--
ALTER TABLE `riddles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `puzzles`
--
ALTER TABLE `puzzles`
  ADD CONSTRAINT `fk_puzzles_room` FOREIGN KEY (`roomId`) REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `riddles`
--
ALTER TABLE `riddles`
  ADD CONSTRAINT `riddles_ibfk_1` FOREIGN KEY (`roomId`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_teams_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `fk_tm_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
