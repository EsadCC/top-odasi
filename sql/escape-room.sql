-- =============================================
-- The Dark House — Horror Escape Room
-- Database dump
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Table: rooms
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rooms` (`id`, `name`, `description`) VALUES
(1, 'De Verlaten Kelder',       'Een donkere, vochtige kelder vol geheimen. De muren fluisteren namen van de verdwenen bewoners.'),
(2, 'De Bloedige Operatiekamer','Een verlaten ziekenhuis waar de dokter nooit is gestopt. Jij bent zijn volgende patiënt.'),
(3, 'Het Vervloekte Kerkhof',   'De doden rusten hier niet. Los de raadsels op voor middernacht... of sluit je je bij hen aan.');

-- --------------------------------------------------------
-- Table: riddles
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `riddles` (
  `id`     INT AUTO_INCREMENT PRIMARY KEY,
  `riddle` TEXT NOT NULL,
  `answer` VARCHAR(255) NOT NULL,
  `hint`   VARCHAR(255) DEFAULT NULL,
  `roomId` INT NOT NULL,
  KEY `fk_riddles_room` (`roomId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Room 1 — De Verlaten Kelder
INSERT INTO `riddles` (`riddle`, `answer`, `hint`, `roomId`) VALUES
('Op de keldermuur krassen 13 strepen. Elke dag verdwijnt er één. Na 6 dagen, hoeveel strepen zijn er nog?',
 '7', 'Trek 6 af van 13.', 1),

('Een oud hangslot heeft een 3-cijferige code. Aanwijzing op de vloer: "Het eerste cijfer is het aantal letters in DOOD. Het tweede is het dubbele van 3. Het derde is 10 min 7."',
 '467', 'Dood = 4 letters, 3×2=6, 10-7=3.', 1),

('Er ligt een dagboek open op de tafel. De laatste zin luidt: "Ik ben begraven op de dag na vrijdag, drie dagen voor dinsdag." Op welke dag werd hij begraven?',
 'zaterdag', 'Drie dagen voor dinsdag is zaterdag. De dag na vrijdag is ook zaterdag.', 1),

('De lamp knippert in een patroon: 2 keer, pauze, 4 keer, pauze, 6 keer, pauze... Hoeveel keer knippert hij daarna?',
 '8', 'De reeks gaat +2 omhoog: 2, 4, 6, ...?', 1);

-- Room 2 — De Bloedige Operatiekamer
INSERT INTO `riddles` (`riddle`, `answer`, `hint`, `roomId`) VALUES
('Op het whiteboard staat een formule: X = 9 × 9 | Y = X − 31 | Z = Y + 6. Wat is Z?',
 '56', '9×9=81, 81−31=50, 50+6=56.', 2),

('Er staan 6 injectiespuiten op een rek, genummerd 1 t/m 6. Alleen de spuiten met een priemgetal zijn veilig. Welke nummers zijn dat?',
 '2, 3, 5', 'Priemgetallen zijn alleen deelbaar door 1 en zichzelf.', 2),

('Een gecodeerd etiket op de kluis: elk woord is gespiegeld. Het staat er: "TOOD SI ROOD". Wat is de boodschap?',
 'DOOD IS ROOD', 'Schrijf elk woord achterstevoren.', 2),

('De nooduitgang vraagt een 4-cijferige code: "Neem het aantal maanden in een jaar, vermenigvuldig met 5, trek er 10 van af."',
 '50', '12 × 5 = 60, 60 − 10 = 50.', 2);

-- Room 3 — Het Vervloekte Kerkhof
INSERT INTO `riddles` (`riddle`, `answer`, `hint`, `roomId`) VALUES
('Op een grafsteen staat: "Ik ben een getal. Tel mij drie keer op bij mezelf en je krijgt 48. Wat ben ik?"',
 '12', '3 × x = 48. Deel 48 door 3.', 3),

('De cijferreeks op het kerkhofhek: 3, 9, 27, 81, ... Wat is het volgende getal?',
 '243', 'Elk getal wordt vermenigvuldigd met 3.', 3),

('Er staan 7 zwarte kaarsen in een patroon: aan, uit, aan, aan, uit, aan, aan, uit... Welke staat er op positie 14: aan of uit?',
 'aan', 'Het patroon is 3 lang: aan, aan, uit. Positie 14 mod 3 = rest 2 = aan.', 3),

('Het grafkruis heeft een slot met 3 cijfers. Aanwijzing: "Eerste cijfer: 7² gedeeld door 7. Tweede: de helft van 16. Derde: het aantal zijden van een ruit."',
 '784', '7²÷7 = 7, 16÷2 = 8, een ruit heeft 4 zijden.', 3);

-- --------------------------------------------------------
-- Table: teams
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `teams` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `team_name`   VARCHAR(100) NOT NULL,
  `password`    VARCHAR(255) NOT NULL DEFAULT '',
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: team_members
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `team_members` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `team_id`     INT NOT NULL,
  `member_name` VARCHAR(100) NOT NULL,
  KEY `fk_tm_team` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: reviews
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `team_name`  VARCHAR(100) NOT NULL,
  `room_id`    INT NOT NULL,
  `rating`     TINYINT NOT NULL COMMENT '1-5 sterren',
  `difficulty` TINYINT NOT NULL COMMENT '1-5 moeilijkheid',
  `feedback`   TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Foreign keys
-- --------------------------------------------------------
ALTER TABLE `riddles`
  ADD CONSTRAINT `fk_riddles_room`
  FOREIGN KEY (`roomId`) REFERENCES `rooms` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `team_members`
  ADD CONSTRAINT `fk_tm_team`
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
