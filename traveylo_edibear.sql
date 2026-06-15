-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 04, 2026 at 01:47 PM
-- Server version: 10.6.25-MariaDB
-- PHP Version: 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `traveylo_edibear`
--

-- --------------------------------------------------------

--
-- Table structure for Brave Heart challenge categories
--

CREATE TABLE `braveheart_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Table structure for Brave Heart challenge events
--

CREATE TABLE `braveheart_events` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `main_image` varchar(100) DEFAULT NULL,
  `application_file` varchar(100) DEFAULT NULL,
  `deadline_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Table structure for Brave Heart challenge winners
--

CREATE TABLE `braveheart_winners` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `image` varchar(100) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Table structure for table `ad1_descriptions`
--

CREATE TABLE `ad1_descriptions` (
  `id` int(11) NOT NULL,
  `ad1_id` int(11) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ad1_details`
--

CREATE TABLE `ad1_details` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `adlink` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ad1_details`
--

INSERT INTO `ad1_details` (`id`, `tag`, `title`, `description`, `image`, `video`, `video_status`, `status`, `timestamp`, `adlink`) VALUES
(7, '', '', '', '7.jpg', '', 0, 0, '2023-12-16 11:32:26', 'https://instagram.com/eventsravishing/?igshid=OGQ5');

-- --------------------------------------------------------

--
-- Table structure for table `ad2_descriptions`
--

CREATE TABLE `ad2_descriptions` (
  `id` int(11) NOT NULL,
  `ad2_id` int(11) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ad2_details`
--

CREATE TABLE `ad2_details` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `adlink` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ad2_details`
--

INSERT INTO `ad2_details` (`id`, `tag`, `title`, `description`, `image`, `video`, `video_status`, `status`, `timestamp`, `adlink`) VALUES
(7, '', '', '', '7.jpg', '', 0, 0, '2023-12-16 08:51:42', 'https://traveylo.com/');

-- --------------------------------------------------------

--
-- Table structure for table `blog_descriptions`
--

CREATE TABLE `blog_descriptions` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `blog_descriptions`
--

INSERT INTO `blog_descriptions` (`id`, `blog_id`, `description`, `image_01`, `image_02`) VALUES
(14, 15, 'Step 01 <br>\r\nIn the first step we should cut a square in brown paper. So take dark brown paper, mark 21 cm distance (Picture 01) and then mark the same distance on the other side (Picture 02) & cut the square. <br><br>Hurrah you have nicely done the first step. \r\n', '15-14-1.jpg', '15-14-2.jpg'),
(16, 15, '', '15-16-1.jpg', '15-16-2.jpg'),
(17, 15, 'Step 02<br>\r\nIn the second step we need three small circles for nose and eyes (you can use one and two rupee coins for draw them) & a rectangle for tongue. Letâ€™s cut these four shapes. Draw circles on the light brown paper using a pencil & cut them. (Picture 03, 04) When cutting the rectangle we can use a ruler (use light pink Paper) (Picture 05.) after that on one edge you have to cut it a curved shape (Picture 06). <br> <br>Yah! We have done our all the basic items & now we can create the cute dog face.    ', '15-17-1.jpg', '15-17-2.jpg'),
(18, 15, '', '15-18-1.jpg', '15-18-2.jpg'),
(19, 15, 'Step 03<br>\r\nIn this step take the brown square you cut earlier & fold it diagonally in half to make a triangle. (Picture 07). Then fold the left & right corners down. (Picture 08). Be careful with this step, you have to fold both sides to the same size. <br><br> Yah! you have made dog ears.   ', '15-19-1.jpg', '15-19-2.jpg'),
(20, 15, '', '15-20-1.jpg', '15-20-2.jpg'),
(21, 15, 'Step 04 <br>\r\nLetâ€™s make dogâ€™s nose & mouth in this fourth step. Fold the bottom corner up. Folding it to the limit of the dog ears crossing. Make sure you are only folding one sheet of paper. (Picture 09 ) Then get the glue & the big circle shape you cut before & paste it on your folded corner (Picture 10). <br><br>\r\nNow letâ€™s make the eyes, get the light brown circles you cut earlier and paste them like in pictures 11 & 12. Be careful in this step you have to paste circles with the same distance.\r\n', '15-21-1.jpg', '15-21-2.jpg'),
(22, 15, '', '15-22-1.jpg', '15-22-2.jpg'),
(23, 15, 'Step 05 <br>\r\nAfter pasting it take the rectangle pink shape you cut earlier and paste it under the bottom corner you folded. (Picture 11) Then get the black pen and colour the eyes, draw nose holes & tongue. \r\nHurrah! Here is your cute dog face. <br><br>\r\nGreat! You did superb work. You can try this with different colours and different styles. <br><br>\r\nlet\'s meet another activity\r\n', '15-23-1.jpg', '15-23-2.jpg'),
(24, 17, 'Step 01 <br>In the first step, take the A4 paper and fold it in half lengthwise. Now the paper looks like a long rectangle. Crease the fold with your fingernail before unfolding the paper again. (Picture 1)', '17-24-1.jpg', '17-24-2.jpg'),
(25, 17, 'Step 02<br>In the second step, open the paper again and fold the top corners to the previous crease line like a triangle. (Picture 02) Press along the fold with your fingernail so the fold stays in place and crease them tightly. (Picture 03) Remember, when you fold, keep the corners balanced.', '17-25-1.jpg', '17-25-2.jpg'),
(26, 17, 'Step 03<br> In this step, you should fold the top angled edges toward the crease again like a long triangle and press along the fold with your fingernail so the fold stays in place. (Picture 04)', '17-26-1.jpg', '17-26-2.jpg'),
(27, 17, 'Step 04<br> In the fourth step, fold the paper along the center line and crease it tightly. (Picture 05)', '17-27-1.jpg', '17-27-2.jpg'),
(28, 17, 'Step 05<br> In the last step, we are going to make wings. So fold the angled sides to the center line again and crease it tightly, then unfold them slightly again so the plane is flat on top. (Picture 06) Be careful to make sure both wings are balanced.', '17-28-1.jpg', '17-28-2.jpg'),
(29, 17, 'Hoorah! You are done. Now your plane is ready to fly.', '17-29-1.jpg', '17-29-2.jpg'),
(30, 17, '', '17-30-1.jpg', '17-30-2.jpg'),
(31, 18, 'Step 01 <br> First, we need to draw a tree sketch on your white paper. Take the white paper and draw a tree with a trunk and three branches spreading out in the middle of the paper (Picture 01). <br> Remember, make the tree a bit fat because it\'s easier to paste our paper pieces. Well done on drawing a beautiful tree!', '18-31-1.jpg', '18-31-2.jpg'),
(32, 18, 'Step 2: <br> Now, we need small paper pieces. Grab your brown paper and tear it into small pieces (Picture 02). Remember, don\'t tear them too small, as it can be tricky to paste them onto your tree. ', '18-32-1.jpg', '18-32-2.jpg'),
(33, 18, 'Now comes the most fun step! Are you ready? Take the white paper with the beautiful tree drawing. Keep the torn pieces close. Then, get the glue and carefully apply it on the trunk. Remember not to apply glue all over the trunk and other areas. paste the pieces carefully onto the trunk, making sure not to go outside the lines (Picture 04). We\'ve just finished our third step.', '18-33-1.jpg', '18-33-2.jpg'),
(34, 18, 'Step 4 <br> In this step take green paper and tear it into small pieces. Then, get the glue and carefully apply it on the leaves area. Remember we should add some red pieces mixed with green on branches & paste some of them on the floor like fallen leaves. (Picture 05) <br><br> Let it dry for 5â€“10 minutes.\r\n<br><br> Hurrah! We\'ve almost finished our beautiful tree. Great job, kiddos! ', '18-34-1.jpg', '18-34-2.jpg'),
(35, 19, 'Hello little buddies, today, I\'m going to show you how to make an adorable dog face using paper. But before we start remember to be careful when using scissors. Tell your mom to stay close to you. Alright, let\'s go! <br> <br>\r\nBefore starting we need the following materials, <br> <br>\r\n01.	Three Colour A4 Papers (Light Brown, Light pink, Dark Brown) <br> \r\n02.	Scissors  <br> \r\n03.	Glue <br> \r\n04.	Black Pen <br> \r\n05.	Pencil <br> \r\n06.	Ruler <br> \r\n', '', ''),
(36, 20, 'Hello little buddies, today, I\'m going to show you how to make an adorable dog face using paper. But before we start remember to be careful when using scissors. Tell your mom to stay close to you. Alright, let\'s go! <br> <br>\r\nBefore starting we need the following materials, <br> <br>\r\n01.	Three Colour A4 Papers (Light Brown, Light pink, Dark Brown) <br> \r\n02.	Scissors  <br> \r\n03.	Glue <br> \r\n04.	Black Pen <br> \r\n05.	Pencil <br> \r\n06.	Ruler <br> \r\n', '', ''),
(37, 21, 'Hello little buddies, today, I\'m going to show you how to make an adorable dog face using paper. But before we start remember to be careful when using scissors. Tell your mom to stay close to you. Alright, let\'s go! <br> <br>\r\nBefore starting we need the following materials, <br> <br>\r\n01.	Three Colour A4 Papers (Light Brown, Light pink, Dark Brown) <br> \r\n02.	Scissors  <br> \r\n03.	Glue <br> \r\n04.	Black Pen <br> \r\n05.	Pencil <br> \r\n06.	Ruler <br> \r\n', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `blog_details`
--

CREATE TABLE `blog_details` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `blog_details`
--

INSERT INTO `blog_details` (`id`, `tag`, `title`, `description`, `image`, `video`, `video_status`, `status`, `timestamp`) VALUES
(15, 'ORIGAMI ', 'CUTE DOG FACE', 'Hello little buddies, today, I\'m going to show you how to make an adorable dog face using paper. But before we start remember to be careful when using scissors. Tell your mom to stay close to you. Alright, let\'s go! <br> <br>\r\nBefore starting we need the following materials, <br> <br>\r\n01.	Three Colour A4 Papers (Light Brown, Light pink, Dark Brown) <br> \r\n02.	Scissors  <br> \r\n03.	Glue <br> \r\n04.	Black Pen <br> \r\n05.	Pencil <br> \r\n06.	Ruler <br> \r\n', '15.jpg', '.', 0, 0, '2023-06-19 06:42:52'),
(17, 'ORIGAMI', 'How to Make a Paper Airplane', 'Hi friends, Do you want to make an airplane using paper? <br><br>  I\'ll teach you step by step. It\'s really easy and a lot of fun. In just a few minutes, you\'ll be ready to take off your paper plane. For this, you can use normal A4 paper. Let\'s start.', '17.jpg', '.', 0, 0, '2024-02-13 02:12:54'),
(18, 'Hand Crafts ', 'COLLAGE PAPER TREE ', 'Hello kiddos! Do you want to make a collage tree with Edi? Awesome! Let\'s get started. Here\'s a step-by-step guide to making a colorful collage tree. <br><br>Before we begin, make sure you have these materials:<br><br>\r\n01. A4 colour papers (White, Brown, Red, Dark Green)<br>\r\n02. Glue<br>\r\n03. Pencil\r\n', '18.jpg', '.', 0, 0, '2024-02-15 12:24:23'),
(19, 'ORIGAMI ', 'CUTE DOG FACE ', 'Hello little buddies, today, I\'m going to show you how to make an adorable dog face using paper. But before we start remember to be careful when using scissors. Tell your mom to stay close to you. Alright, let\'s go! <br> <br>\r\nBefore starting we need the following materials, <br> <br>\r\n01.	Three Colour A4 Papers (Light Brown, Light pink, Dark Brown) <br> \r\n02.	Scissors  <br> \r\n03.	Glue <br> \r\n04.	Black Pen <br> \r\n05.	Pencil <br> \r\n06.	Ruler <br> \r\n', '19.jpg', '.', 0, 0, '2025-06-16 12:59:33'),
(20, 'ORIGAMI ', 'CUTE DOG FACE ', 'Hello little buddies, today, I\'m going to show you how to make an adorable dog face using paper. But before we start remember to be careful when using scissors. Tell your mom to stay close to you. Alright, let\'s go! <br> <br>\r\nBefore starting we need the following materials, <br> <br>\r\n01.	Three Colour A4 Papers (Light Brown, Light pink, Dark Brown) <br> \r\n02.	Scissors  <br> \r\n03.	Glue <br> \r\n04.	Black Pen <br> \r\n05.	Pencil <br> \r\n06.	Ruler <br> \r\n', '20.jpg', '.', 0, 0, '2025-06-16 13:00:15'),
(21, 'ORIGAMI ', 'CUTE DOG FACE ', 'Hello little buddies, today, I\'m going to show you how to make an adorable dog face using paper. But before we start remember to be careful when using scissors. Tell your mom to stay close to you. Alright, let\'s go! <br> <br>\r\nBefore starting we need the following materials, <br> <br>\r\n01.	Three Colour A4 Papers (Light Brown, Light pink, Dark Brown) <br> \r\n02.	Scissors  <br> \r\n03.	Glue <br> \r\n04.	Black Pen <br> \r\n05.	Pencil <br> \r\n06.	Ruler <br> \r\n', '21.jpg', '.', 0, 0, '2025-06-16 13:00:52');

-- --------------------------------------------------------

--
-- Table structure for table `books_descriptions`
--

CREATE TABLE `books_descriptions` (
  `id` int(11) NOT NULL,
  `books_id` int(11) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `books_details`
--

CREATE TABLE `books_details` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `pdfupload` varchar(20) NOT NULL,
  `download_count` int(11) NOT NULL,
  `language_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `main_cat_id` int(11) DEFAULT NULL,
  `sub_cat_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `books_details`
--

INSERT INTO `books_details` (`id`, `tag`, `title`, `description`, `image`, `video`, `video_status`, `status`, `timestamp`, `pdfupload`, `download_count`, `language_id`, `grade_id`, `main_cat_id`, `sub_cat_id`) VALUES
(4, 'NUMBERS ', 'Addition', 'English Addition Worksheet', '4.JPG', '', 0, 1, '2024-01-05 04:35:55', 'Addition_01.pdf', 13, 1, 1, 2, 2),
(6, 'NUMBERS ', 'Addition - Sinhala', 'Sinhala Addition Worksheet', '6.JPG', '', 0, 1, '2024-01-26 01:38:56', 'Addition.pdf', 16, 1, 2, 2, 2),
(7, 'SHAPES ', 'SHAPES PRACTICE', 'Tracing Practice Worksheet', '7.JPG', '', 0, 1, '2024-01-27 05:10:13', 'TRACING PRACTICE.pdf', 31, 1, 2, 2, 9),
(8, 'NUMBERS ', 'Subtraction', 'English Subtraction Worksheet', '8.jpg', '', 0, 1, '2024-01-29 05:04:19', '8.pdf', 9, 1, 2, 2, 2),
(9, 'NUMBERS ', 'Division', 'English division worksheet', '9.jpg', '', 0, 1, '2024-02-12 04:56:40', 'Division.pdf', 5, 1, 3, 2, 5),
(10, 'NUMBERS ', 'Division - Sinhala ', 'Sinhala division worksheet', '10.jpg', '', 0, 1, '2024-02-12 04:58:28', 'Division Sinhala.pdf', 7, 1, 3, 2, 5),
(11, 'SHAPES ', 'SHAPES PRACTICE', 'Tracing Practice Worksheet', '11.JPG', '', 0, 1, '2024-02-14 01:45:13', 'Tracing Practice.pdf', 39, 1, 2, 2, 9),
(12, 'NUMBERS ', 'Number Zero ', 'Number 0 Tracing Worksheets', '12.jpg', '', 0, 1, '2024-02-16 02:18:42', 'Zero - Sinhala.pdf', 11, NULL, NULL, NULL, NULL),
(13, 'NUMBERS ', 'Number One', 'Number 1 Tracing Worksheets', '13.jpg', '', 0, 1, '2024-02-16 02:27:17', 'One - Sinhala.pdf', 19, NULL, NULL, NULL, NULL),
(14, 'NUMBERS ', 'Number Two', 'Number 2 Tracing Worksheets', '14.jpg', '', 0, 1, '2024-02-16 02:42:35', 'Two - Sinhala.pdf', 17, NULL, NULL, NULL, NULL),
(15, 'NUMBERS ', 'Number Three', 'Number 3 Tracing Worksheets', '15.jpg', '', 0, 1, '2024-02-16 02:50:57', 'Three - Sinhala.pdf', 15, NULL, NULL, NULL, NULL),
(16, 'NUMBERS ', 'Number Four ', 'Number 4 Tracing Worksheets', '16.jpg', '', 0, 1, '2024-02-16 02:55:20', '16.pdf', 18, NULL, NULL, NULL, NULL),
(17, 'NUMBERS ', 'Number Five ', 'Number 5 Tracing Worksheets', '17.jpg', '', 0, 1, '2024-02-17 13:15:35', 'Five - Sinhala.pdf', 15, NULL, NULL, NULL, NULL),
(18, 'NUMBERS ', 'Number Six', 'Number 6 Tracing Worksheets', '18.jpg', '', 0, 1, '2024-02-17 13:20:17', 'Six - Sinhala.pdf', 13, NULL, NULL, NULL, NULL),
(19, 'NUMBERS ', 'Number Seven', 'Number 7 Tracing Worksheets', '19.jpg', '', 0, 1, '2024-02-17 13:22:24', 'Seven - Sinhala.pdf', 16, NULL, NULL, NULL, NULL),
(20, 'NUMBERS ', 'Number Eight ', 'Number 8 Tracing Worksheets', '20.jpg', '', 0, 1, '2024-02-17 13:24:23', 'Eight - Sinhala.pdf', 15, NULL, NULL, NULL, NULL),
(21, 'NUMBERS ', 'Number Nine', 'Number 9 Tracing Worksheets', '21.jpg', '', 0, 1, '2024-02-17 13:26:09', 'Nine - Sinhala.pdf', 13, NULL, NULL, NULL, NULL),
(22, 'NUMBERS ', 'Number Ten', 'Number 10 Tracing Worksheets', '22.jpg', '', 0, 1, '2024-02-17 13:27:52', 'Ten - Sinhala.pdf', 18, NULL, NULL, NULL, NULL),
(23, 'ENGLISH', 'Capital Letter A', 'Capital Letter A Tracing Worksheet', '23.jpg', '', 0, 1, '2024-02-17 13:33:58', '23.pdf', 27, NULL, NULL, NULL, NULL),
(24, 'ENGLISH', 'Capital Letter B', 'Capital Letter B Tracing Worksheet', '24.JPG', '', 0, 1, '2024-02-17 13:36:28', 'Letter - B.pdf', 18, NULL, NULL, NULL, NULL),
(25, 'ENGLISH', 'Capital Letter C', 'Capital Letter C Tracing Worksheet', '25.JPG', '', 0, 1, '2024-02-18 01:34:16', 'Letter - C.pdf', 17, NULL, NULL, NULL, NULL),
(26, 'ENGLISH', 'Capital Letter D', 'Capital Letter D Tracing Worksheet', '26.JPG', '', 0, 1, '2024-02-18 01:37:54', 'Letter - D.pdf', 18, NULL, NULL, NULL, NULL),
(27, 'ENGLISH', 'Capital Letter E', 'Capital Letter E Tracing Worksheet', '27.JPG', '', 0, 1, '2024-02-18 01:39:53', 'Letter - E.pdf', 20, NULL, NULL, NULL, NULL),
(28, 'ENGLISH', 'Capital Letter F', 'Capital Letter F Tracing Worksheet\r\n\r\n', '28.JPG', '', 0, 1, '2024-02-18 01:41:36', 'Letter - F.pdf', 18, NULL, NULL, NULL, NULL),
(29, 'ENGLISH', 'Capital Letter G', 'Capital Letter G Tracing Worksheet', '29.JPG', '', 0, 1, '2024-02-18 01:43:40', 'Letter - G.pdf', 18, NULL, NULL, NULL, NULL),
(30, 'ENGLISH', 'Capital Letter H', 'Capital Letter H Tracing Worksheet', '30.JPG', '', 0, 1, '2024-02-18 01:46:28', 'Letter - H.pdf', 13, NULL, NULL, NULL, NULL),
(31, 'ENGLISH', 'Capital Letter I', 'Capital Letter I Tracing Worksheet', '31.JPG', '', 0, 1, '2024-02-18 07:47:54', 'Letter - I.pdf', 15, NULL, NULL, NULL, NULL),
(32, 'ENGLISH', 'Capital Letter J', 'Capital Letter J Tracing Worksheet\r\n\r\n', '32.JPG', '', 0, 1, '2024-02-18 07:50:29', 'Letter -J.pdf', 15, NULL, NULL, NULL, NULL),
(33, 'ENGLISH', 'Capital Letter K', 'Capital Letter K Tracing Worksheet', '33.JPG', '', 0, 1, '2024-02-18 07:52:46', 'Letter -K.pdf', 14, NULL, NULL, NULL, NULL),
(34, 'ENGLISH', 'Capital Letter L', 'Capital Letter L Tracing Worksheet', '34.JPG', '', 0, 1, '2024-02-18 07:59:09', 'Letter - L.pdf', 17, NULL, NULL, NULL, NULL),
(35, 'ENGLISH', 'Capital Letter M', 'Capital Letter M Tracing Worksheet', '35.JPG', '', 0, 1, '2024-02-18 08:04:22', 'Letter - M.pdf', 20, NULL, NULL, NULL, NULL),
(36, 'ENGLISH', 'Capital Letter N', 'Capital Letter N Tracing Worksheet', '36.JPG', '', 0, 1, '2024-02-18 08:06:35', 'Letter - N.pdf', 18, NULL, NULL, NULL, NULL),
(37, 'ENGLISH', 'Capital Letter O', 'Capital Letter O Tracing Worksheet', '37.JPG', '', 0, 1, '2024-02-18 08:08:46', 'Letter - O.pdf', 14, NULL, NULL, NULL, NULL),
(38, 'ENGLISH', 'Capital Letter P', 'Capital Letter P Tracing Worksheet', '38.JPG', '', 0, 1, '2024-02-18 10:04:18', 'Letter - P.pdf', 8, NULL, NULL, NULL, NULL),
(39, 'ENGLISH', 'Capital Letter Q', 'Capital Letter Q Tracing Worksheet', '39.JPG', '', 0, 1, '2024-02-18 10:05:46', 'Letter - Q.pdf', 15, NULL, NULL, NULL, NULL),
(40, 'ENGLISH', 'Capital Letter R', 'Capital Letter R Tracing Worksheet', '40.JPG', '', 0, 1, '2024-02-18 10:07:49', 'Letter - R.pdf', 12, NULL, NULL, NULL, NULL),
(41, 'ENGLISH', 'Capital Letter S', 'Capital Letter S Tracing Worksheet', '41.JPG', '', 0, 1, '2024-02-18 10:09:57', 'Letter - S.pdf', 18, NULL, NULL, NULL, NULL),
(42, 'ENGLISH', 'Capital Letter T', 'Capital Letter T Tracing Worksheet', '42.JPG', '', 0, 1, '2024-02-18 10:12:24', 'Letter - T.pdf', 20, NULL, NULL, NULL, NULL),
(43, 'ENGLISH', 'Capital Letter U', 'Capital Letter U Tracing Worksheet', '43.JPG', '', 0, 1, '2024-02-18 10:13:55', 'Letter -U.pdf', 16, NULL, NULL, NULL, NULL),
(44, 'ENGLISH', 'Capital Letter V', 'Capital Letter V Tracing Worksheet', '44.JPG', '', 0, 1, '2024-02-18 10:15:51', 'Letter - V.pdf', 18, NULL, NULL, NULL, NULL),
(45, 'ENGLISH', 'Capital Letter W', 'Capital Letter W Tracing Worksheet', '45.JPG', '', 0, 1, '2024-02-18 10:17:37', 'Letter - W.pdf', 15, NULL, NULL, NULL, NULL),
(46, 'ENGLISH', 'Capital Letter X', 'Capital Letter X Tracing Worksheet', '46.JPG', '', 0, 1, '2024-02-18 10:19:07', 'Letter - X.pdf', 17, NULL, NULL, NULL, NULL),
(47, 'ENGLISH', 'Capital Letter Y', 'Capital Letter Y Tracing Worksheet\r\n\r\n', '47.JPG', '', 0, 1, '2024-02-18 10:20:21', 'Letter - Y.pdf', 18, NULL, NULL, NULL, NULL),
(48, 'ENGLISH', 'Capital Letter Z', 'Capital Letter Z Tracing Worksheet\r\n\r\n', '48.JPG', '', 0, 1, '2024-02-18 10:21:39', 'Letter - Z.pdf', 19, NULL, NULL, NULL, NULL),
(49, 'SINHALA', 'Sinhala Letter - à¶…', 'Sinhala Letter à¶… Tracing Worksheet', '49.jpg', '', 0, 1, '2024-02-20 12:29:18', '49.pdf', 208, NULL, NULL, NULL, NULL),
(50, 'SINHALA', 'Sinhala Letter - à¶†', 'Sinhala Letter à¶† Tracing Worksheet', '50.jpg', '', 0, 1, '2024-02-20 12:33:01', '50.pdf', 106, NULL, NULL, NULL, NULL),
(51, 'SINHALA', 'Sinhala Letter - à¶‡', 'Sinhala Letter à¶‡ Tracing Worksheet', '51.jpg', '', 0, 1, '2024-02-20 12:35:35', '51.pdf', 95, NULL, NULL, NULL, NULL),
(52, 'SINHALA', 'Sinhala Letter - à¶ˆ', 'Sinhala Letter à¶ˆ Tracing Worksheet', '52.jpg', '', 0, 1, '2024-02-20 12:40:50', '52.pdf', 100, NULL, NULL, NULL, NULL),
(53, 'SINHALA', 'Sinhala Letter - à¶‰', 'Sinhala Letter à¶‰ Tracing Worksheet', '53.jpg', '', 0, 1, '2024-02-20 12:47:34', '53.pdf', 93, NULL, NULL, NULL, NULL),
(54, 'SINHALA', 'Sinhala Letter - à¶Š', 'Sinhala Letter à¶Š Tracing Worksheet', '54.jpg', '', 0, 1, '2024-02-20 13:10:42', '54.pdf', 83, NULL, NULL, NULL, NULL),
(55, 'SINHALA', 'Sinhala Letter - à¶‹', 'Sinhala Letter à¶‹ Tracing Worksheet', '55.jpg', '', 0, 1, '2024-02-20 13:14:49', '55.pdf', 111, NULL, NULL, NULL, NULL),
(56, 'NUMBERS ', 'MULTIPLICATION', 'English Multiplication worksheet ', '56.jpg', '', 0, 1, '2024-02-21 02:24:36', '56.pdf', 7, NULL, NULL, NULL, NULL),
(57, 'NUMBERS ', 'MULTIPLICATION - Sin.', 'Sinhala Multiplication worksheet', '57.jpg', '', 0, 1, '2024-02-21 09:59:44', '57.pdf', 7, NULL, NULL, NULL, NULL),
(58, 'SINHALA', 'Sinhala Letter - à¶Œ', 'Sinhala Letter à¶Œ Tracing Worksheet', '58.jpg', '', 0, 1, '2024-02-21 10:56:31', '58.pdf', 96, NULL, NULL, NULL, NULL),
(59, 'SINHALA', 'Sinhala Letter - à¶‘', 'Sinhala Letter à¶‘ Tracing Worksheet', '59.jpg', '', 0, 1, '2024-02-21 11:00:52', '59.pdf', 92, NULL, NULL, NULL, NULL),
(60, 'SINHALA', 'Sinhala Letter - à¶’', 'Sinhala Letter à¶’ Tracing Worksheet', '60.jpg', '', 0, 1, '2024-02-21 11:04:36', '60.pdf', 88, NULL, NULL, NULL, NULL),
(61, 'SINHALA', 'Sinhala Letter - à¶”', 'Sinhala Letter à¶” Tracing Worksheet', '61.jpg', '', 0, 1, '2024-02-27 02:10:27', '61.pdf', 84, NULL, NULL, NULL, NULL),
(62, 'SINHALA', 'Sinhala Letter - à¶•', 'Sinhala Letter à¶• Tracing Worksheet', '62.jpg', '', 0, 1, '2024-02-27 02:18:46', '62.pdf', 86, NULL, NULL, NULL, NULL),
(63, 'SINHALA', 'Sinhala Letter - à¶š', 'Sinhala Letter à¶š Tracing Worksheet', '63.jpg', '', 0, 1, '2024-02-27 02:21:48', '63.pdf', 94, NULL, NULL, NULL, NULL),
(64, 'SINHALA', 'Sinhala Letter - à¶œ', 'Sinhala Letter à¶œ Tracing Worksheet', '64.jpg', '', 0, 1, '2024-02-27 02:24:20', '64.pdf', 131, NULL, NULL, NULL, NULL),
(65, 'SINHALA', 'Sinhala Letter - à¶ ', 'Sinhala Letter à¶  Tracing Worksheet', '65.jpg', '', 0, 1, '2024-02-27 02:26:46', '65.pdf', 77, NULL, NULL, NULL, NULL),
(66, 'SINHALA', 'Sinhala Letter - à¶¢', 'Sinhala Letter à¶¢ Tracing Worksheet', '66.JPG', '', 0, 1, '2024-03-02 02:27:41', '66.pdf', 99, NULL, NULL, NULL, NULL),
(67, 'SINHALA', 'Sinhala Letter - à¶§', 'Sinhala Letter à¶§ Tracing Worksheet', '67.jpg', '', 0, 1, '2024-03-02 02:34:36', '67.pdf', 152, NULL, NULL, NULL, NULL),
(68, 'SINHALA', 'Sinhala Letter - à¶©', 'Sinhala Letter à¶© Tracing Worksheet', '68.jpg', '', 0, 1, '2024-03-02 02:39:04', '68.pdf', 107, NULL, NULL, NULL, NULL),
(69, 'SINHALA', 'Sinhala Letter - à¶«', 'Sinhala Letter à¶« Tracing Worksheet', '69.jpg', '', 0, 1, '2024-03-02 02:45:21', '69.pdf', 85, NULL, NULL, NULL, NULL),
(70, 'SINHALA', 'Sinhala Letter - à¶­', 'Sinhala Letter à¶­ Tracing Worksheet', '70.jpg', '', 0, 1, '2024-03-02 02:47:46', '70.pdf', 104, NULL, NULL, NULL, NULL),
(71, 'SINHALA', 'Sinhala Letter - à¶¯', 'Sinhala Letter à¶¯ Tracing Worksheet', '71.jpg', '', 0, 1, '2024-03-03 02:41:13', '71.pdf', 113, NULL, NULL, NULL, NULL),
(72, 'SINHALA', 'Sinhala Letter - à¶±', 'Sinhala Letter à¶± Tracing Worksheet', '72.jpg', '', 0, 1, '2024-03-03 02:43:54', '72.pdf', 117, NULL, NULL, NULL, NULL),
(73, 'SINHALA', 'Sinhala Letter - à¶´', 'Sinhala Letter à¶´ Tracing Worksheet', '73.jpg', '', 0, 1, '2024-03-03 02:49:25', '73.pdf', 136, NULL, NULL, NULL, NULL),
(74, 'SINHALA', 'Sinhala Letter - à¶¶', 'Sinhala Letter à¶¶ Tracing Worksheet', '74.jpg', '', 0, 1, '2024-03-03 02:51:29', '74.pdf', 105, NULL, NULL, NULL, NULL),
(75, 'SINHALA', 'Sinhala Letter - à¶¸', 'Sinhala Letter à¶¸ Tracing Worksheet', '75.jpg', '', 0, 1, '2024-03-03 02:56:42', '75.pdf', 128, NULL, NULL, NULL, NULL),
(76, 'SINHALA', 'Sinhala Letter - à¶º', 'Sinhala Letter à¶º Tracing Worksheet', '76.jpg', '', 0, 1, '2024-03-03 03:03:43', '76.pdf', 159, NULL, NULL, NULL, NULL),
(77, 'SINHALA', 'Sinhala Letter - à¶»', 'Sinhala Letter à¶» Tracing Worksheet', '77.jpg', '', 0, 1, '2024-03-03 03:05:43', '77.pdf', 171, NULL, NULL, NULL, NULL),
(78, 'SINHALA', 'Sinhala Letter - à¶½', 'Sinhala Letter à¶½ Tracing Worksheet', '78.jpg', '', 0, 1, '2024-03-03 03:08:20', '78.pdf', 136, NULL, NULL, NULL, NULL),
(79, 'SINHALA', 'Sinhala Letter - à·€', 'Sinhala Letter à·€ Tracing Worksheet\r\n\r\n', '79.jpg', '', 0, 1, '2024-03-03 03:10:45', '79.pdf', 133, NULL, NULL, NULL, NULL),
(80, 'SINHALA', 'Sinhala Letter - à·ƒ', 'Sinhala Letter à·ƒ Tracing Worksheet', '80.jpg', '', 0, 1, '2024-03-03 03:12:42', '80.pdf', 191, NULL, NULL, NULL, NULL),
(81, 'SINHALA', 'Sinhala Letter - à·„', 'Sinhala Letter à·„ Tracing Worksheet', '81.jpg', '', 0, 1, '2024-03-03 03:14:40', '81.pdf', 186, NULL, NULL, NULL, NULL),
(82, 'SINHALA', 'Sinhala Letter - à·…', 'Sinhala Letter à·… Tracing Worksheet', '82.jpg', '', 0, 1, '2024-03-03 03:16:26', '82.pdf', 312, NULL, NULL, NULL, NULL),
(83, 'TAMIL ', 'Tamil Letter - à®…', 'Tamil Letter à®… Tracing Worksheet', '83.jpg', '', 0, 1, '2024-03-03 11:18:24', '83.pdf', 6, NULL, NULL, NULL, NULL),
(84, 'TAMIL ', 'Tamil Letter - à®†', 'Tamil Letter à®† Tracing Worksheet', '84.jpg', '', 0, 1, '2024-03-04 02:55:22', '84.pdf', 8, NULL, NULL, NULL, NULL),
(85, 'TAMIL ', 'Tamil Letter - à®‡', 'Tamil Letter à®‡ Tracing Worksheet', '85.jpg', '', 0, 1, '2024-03-06 02:41:31', '85.pdf', 7, NULL, NULL, NULL, NULL),
(86, 'TAMIL ', 'Tamil Letter - à®ˆ', 'Tamil Letter à®ˆ Tracing Worksheet', '86.jpg', '', 0, 1, '2024-03-07 09:06:59', '86.pdf', 3, NULL, NULL, NULL, NULL),
(87, 'TAMIL ', 'Tamil Letter - à®‰', 'Tamil Letter à®‰ Tracing Worksheet', '87.JPG', '', 0, 1, '2024-03-08 01:43:15', '87.pdf', 3, NULL, NULL, NULL, NULL),
(88, 'TAMIL ', 'TAMIL LETTER - à®Š', 'Tamil Letter à®Š Tracing Worksheet', '88.jpg', '', 0, 1, '2024-04-26 01:29:41', '88.pdf', 4, NULL, NULL, NULL, NULL),
(89, 'TAMIL ', 'Tamil Letter - à®Ž', 'Tamil Letter à®Ž Tracing Worksheet', '89.jpg', '', 0, 1, '2024-04-26 01:30:55', '89.pdf', 3, NULL, NULL, NULL, NULL),
(90, 'TAMIL ', 'Tamil Letter - à®', 'Tamil Letter à® Tracing Worksheet', '90.jpg', '', 0, 1, '2024-04-26 01:31:57', '90.pdf', 3, NULL, NULL, NULL, NULL),
(91, 'TAMIL ', 'Tamil Letter - à®', 'Tamil Letter à® Tracing Worksheet', '91.jpg', '', 0, 1, '2024-05-02 03:25:42', '91.pdf', 3, NULL, NULL, NULL, NULL),
(93, 'TAMIL ', 'Tamil Letter - à®’', 'Tamil Letter à®’ Tracing Worksheet', '93.jpg', '', 0, 1, '2024-05-05 02:53:56', '93.pdf', 3, NULL, NULL, NULL, NULL),
(94, 'TAMIL ', 'Tamil Letter - à®“', 'Tamil Letter à®“ Tracing Worksheet', '94.jpg', '', 0, 1, '2024-05-11 08:40:56', '94.pdf', 4, NULL, NULL, NULL, NULL),
(95, 'TAMIL ', 'Tamil Letter - à®”', 'Tamil Letter à®” Tracing Worksheet', '95.jpg', '', 0, 1, '2024-05-16 03:19:49', '95.pdf', 4, NULL, NULL, NULL, NULL),
(96, 'TAMIL ', 'Tamil Letter - à®•', 'Tamil Letter à®• Tracing Worksheet', '96.jpg', '', 0, 1, '2024-05-17 02:57:20', '96.pdf', 6, NULL, NULL, NULL, NULL),
(97, 'TAMIL ', 'Tamil Letter - à®š', 'Tamil Letter à®š Tracing Worksheet', '97.jpg', '', 0, 1, '2024-05-19 02:41:35', '97.pdf', 5, NULL, NULL, NULL, NULL),
(98, 'TAMIL ', 'Tamil Letter - à®Ÿ', 'Tamil Letter à®Ÿ Tracing Worksheet', '98.jpg', '', 0, 1, '2024-05-27 03:19:37', '98.pdf', 6, NULL, NULL, NULL, NULL),
(99, 'TAMIL ', 'Tamil Letter - à®£', 'Tamil Letter à®£ Tracing Worksheet', '99.jpg', '', 0, 1, '2024-05-28 02:58:38', '99.pdf', 6, NULL, NULL, NULL, NULL),
(100, 'TAMIL ', 'Tamil Letter - à®¤', 'Tamil Letter à®¤ Tracing Worksheet', '100.jpg', '', 0, 1, '2024-05-29 04:06:27', '100.pdf', 3, NULL, NULL, NULL, NULL),
(101, 'TAMIL ', 'Tamil Letter - à®¨', 'Tamil Letter à®¨ Tracing Worksheet', '101.jpg', '', 0, 1, '2024-06-04 02:37:32', '101.pdf', 5, NULL, NULL, NULL, NULL),
(102, 'TAMIL ', 'Tamil Letter - à®ª', 'Tamil Letter à®ª Tracing Worksheet', '102.jpg', '', 0, 1, '2024-06-04 02:38:30', '102.pdf', 4, NULL, NULL, NULL, NULL),
(103, 'TAMIL ', 'Tamil Letter - à®®', 'Tamil Letter à®® Tracing Worksheet', '103.jpg', '', 0, 1, '2024-06-04 02:39:28', '103.pdf', 3, NULL, NULL, NULL, NULL),
(104, 'TAMIL ', 'Tamil Letter - à®¯', 'Tamil Letter à®¯ Tracing Worksheet', '104.jpg', '', 0, 1, '2024-06-04 02:40:18', '104.pdf', 4, NULL, NULL, NULL, NULL),
(105, 'TAMIL ', 'Tamil Letter - à®°', 'Tamil Letter à®° Tracing Worksheet', '105.jpg', '', 0, 1, '2024-06-04 02:41:00', '105.pdf', 5, NULL, NULL, NULL, NULL),
(106, 'TAMIL ', 'Tamil Letter - à®²', 'Tamil Letter à®² Tracing Worksheet', '106.jpg', '', 0, 1, '2024-06-04 02:42:18', '106.pdf', 6, NULL, NULL, NULL, NULL),
(107, 'TAMIL ', 'Tamil Letter - à®µ', 'Tamil Letter à®µ Tracing Worksheet', '107.jpg', '', 0, 1, '2024-06-04 02:43:17', '107.pdf', 6, NULL, NULL, NULL, NULL),
(108, 'TAMIL ', 'Tamil Letter - à®´', 'Tamil Letter à®´ Tracing Worksheet', '108.jpg', '', 0, 1, '2024-06-04 02:44:04', '108.pdf', 4, NULL, NULL, NULL, NULL),
(109, 'TAMIL ', 'Tamil Letter - à®³', 'Tamil Letter à®³ Tracing Worksheet', '109.jpg', '', 0, 1, '2024-06-04 02:44:47', '109.pdf', 3, NULL, NULL, NULL, NULL),
(110, 'TAMIL ', 'Tamil Letter - à®±', 'Tamil Letter à®± Tracing Worksheet', '110.jpg', '', 0, 1, '2024-06-04 02:45:57', '110.pdf', 3, NULL, NULL, NULL, NULL),
(111, 'TAMIL ', 'Tamil Letter - à®©', 'Tamil Letter à®© Tracing Worksheet', '111.jpg', '', 0, 1, '2024-06-04 02:46:54', '111.pdf', 10, NULL, NULL, NULL, NULL),
(112, 'NUMBERS ', 'Subtraction - Sin. ', 'Sinhala Subtraction Worksheet', '112.jpg', '', 0, 1, '2025-02-12 07:18:45', '112.pdf', 17, NULL, NULL, NULL, NULL),
(113, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 1 Tracing Worksheets', '113.JPG', '', 0, 1, '2025-06-12 09:27:11', '113.pdf', 7, NULL, NULL, NULL, NULL),
(114, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 2 Tracing Worksheets', '114.JPG', '', 0, 1, '2025-06-12 13:46:22', '114.pdf', 8, NULL, NULL, NULL, NULL),
(115, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 3 Tracing Worksheets', '115.JPG', '', 0, 1, '2025-06-12 13:47:53', '115.pdf', 6, NULL, NULL, NULL, NULL),
(116, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 4 Tracing Worksheets', '116.JPG', '', 0, 1, '2025-06-12 13:49:23', '116.pdf', 7, NULL, NULL, NULL, NULL),
(117, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 5 Tracing Worksheets', '117.JPG', '', 0, 1, '2025-06-12 13:50:51', '117.pdf', 6, NULL, NULL, NULL, NULL),
(118, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 6 Tracing Worksheets', '118.JPG', '', 0, 1, '2025-06-12 13:52:21', '118.pdf', 6, NULL, NULL, NULL, NULL),
(119, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 7 Tracing Worksheets', '119.JPG', '', 0, 1, '2025-06-12 13:53:45', '119.pdf', 7, NULL, NULL, NULL, NULL),
(120, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 8 Tracing Worksheets', '120.JPG', '', 0, 1, '2025-06-12 13:55:49', '120.pdf', 6, NULL, NULL, NULL, NULL),
(121, 'NUMBERS ', 'NUMBERS TRACING ', 'Number 9 Tracing Worksheets', '121.JPG', '', 0, 1, '2025-06-12 13:57:10', '121.pdf', 9, NULL, NULL, NULL, NULL),
(122, 'TIME', 'TELLING THE TIME', 'Telling the time - twelve to three ', '122.jpg', '', 0, 1, '2025-06-13 09:31:06', 'Twelve to Three.pdf', 6, NULL, NULL, NULL, NULL),
(123, 'TIME', 'TELLING THE TIME', 'Telling the time - four to seven', '123.jpg', '', 0, 1, '2025-06-13 09:34:11', 'Four to Seven.pdf', 8, NULL, NULL, NULL, NULL),
(124, 'TIME', 'TELLING THE TIME', 'Telling the time - eight to eleven ', '124.jpg', '', 0, 1, '2025-06-13 09:37:04', 'Eight to Eleven.pdf', 10, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `discounted_price` decimal(10,2) DEFAULT 0.00,
  `age_group` varchar(50) DEFAULT NULL,
  `description` text,
  `language` varchar(50) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `session_id` varchar(128) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(32) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `address_line` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `district` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `payment_method` enum('cod','bank_transfer','card') NOT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `order_status` varchar(50) NOT NULL DEFAULT 'Order Placed',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carousel`
--

CREATE TABLE `carousel` (
  `id` int(11) NOT NULL,
  `type` varchar(5) NOT NULL COMMENT 'img or video',
  `text1` varchar(100) DEFAULT NULL,
  `text2` varchar(100) DEFAULT NULL,
  `src` varchar(100) NOT NULL,
  `display_order` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `carousel`
--

INSERT INTO `carousel` (`id`, `type`, `text1`, `text2`, `src`, `display_order`, `status`) VALUES
(24, 'img', '', '', 'MainBanner.webp', 4, 1),
(25, 'img', 'Welcome to ', 'edibear', 'sample.webp', 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `title`) VALUES
(1, 'LKG'),
(2, 'UKG'),
(3, 'Grade-1'),
(4, 'Grade-2'),
(5, 'Grade-3'),
(6, 'Grade-4'),
(7, 'Grade-5');

-- --------------------------------------------------------

--
-- Table structure for table `homework_descriptions`
--

CREATE TABLE `homework_descriptions` (
  `id` int(11) NOT NULL,
  `homework_id` int(11) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `homework_details`
--

CREATE TABLE `homework_details` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `pdfupload` varchar(20) NOT NULL,
  `download_count` int(11) NOT NULL,
  `language_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `main_cat_id` int(11) DEFAULT NULL,
  `sub_cat_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `homework_details`
--

INSERT INTO `homework_details` (`id`, `tag`, `title`, `description`, `image`, `video`, `video_status`, `status`, `timestamp`, `pdfupload`, `download_count`, `language_id`, `grade_id`, `main_cat_id`, `sub_cat_id`) VALUES
(4, 'SINHALA WORDS &amp; PICTURES', 'FLOWERS', 'Flowers names in Sinhala', '4.jpg', '', 0, 1, '2023-10-07 03:48:20', '4.pdf', 62, 1, 1, 3, NULL),
(5, 'SINHALA WORDS &amp; PICTURES', 'FRUITS', 'Fruits names in Sinhala', '5.JPG', '', 0, 1, '2023-10-08 04:54:17', '5.pdf', 54, 1, 2, 3, NULL),
(6, 'SINHALA WORDS &amp; PICTURES', 'VEGETABLES ', 'Vegetables names in Sinhala', '6.jpg', '', 0, 1, '2023-10-09 13:01:14', '6.pdf', 54, 1, 1, 3, NULL),
(7, 'SINHALA WORDS &amp; PICTURES', 'ANIMALS', 'Animals names in Sinhala', '7.JPG', '', 0, 1, '2023-10-10 02:57:01', '7.pdf', 53, 1, 3, 3, NULL),
(8, 'SINHALA WORDS &amp; PICTURES', 'VEHICLES ', 'Vehicles names in Sinhala', '8.jpg', '', 0, 1, '2023-10-12 02:01:02', '8.pdf', 54, 1, 5, 3, NULL),
(9, 'SINHALA WORDS &amp; PICTURES', 'BIRDS ', 'Birds names in Sinhala\r\n\r\n', '9.JPG', '', 0, 1, '2023-10-13 01:34:02', '9.pdf', 42, 1, 3, 3, NULL),
(10, 'SINHALA WORDS &amp; PICTURES', 'INSECTS ', 'Insects names in Sinhala\r\n\r\n', '10.jpg', '', 0, 1, '2023-10-17 03:44:14', '10.pdf', 40, 1, 4, 3, NULL),
(11, 'SINHALA WORDS &amp; PICTURES', 'PARTS OF THE BODY ', 'Parts of the body in Sinhala', '11.jpg', '', 0, 1, '2023-10-19 03:16:43', '11.pdf', 49, NULL, NULL, NULL, NULL),
(12, 'SINHALA WORDS &amp; PICTURES', 'MUSIC INSTRUMENTS ', 'Music instruments names in Sinhala', '12.jpg', '', 0, 1, '2023-10-24 02:45:08', '12.pdf', 40, NULL, NULL, NULL, NULL),
(13, 'SINHALA WORDS &amp; PICTURES', 'THINGS MADE OF PLASTIC ', 'Things made of plastic in Sinhala', '13.jpg', '', 0, 1, '2024-01-07 08:22:25', '13.pdf', 42, NULL, NULL, NULL, NULL),
(14, 'SINHALA WORDS &amp; PICTURES', 'THINGS MADE OF METAL', 'Things made of metal in Sinhala', '14.jpg', '', 0, 1, '2024-01-09 13:20:03', '14.pdf', 33, NULL, NULL, NULL, NULL),
(15, 'SINHALA WORDS &amp; PICTURES', 'THINGS MADE OF RUBBER', 'Things made of rubber in Sinhala', '15.jpg', '', 0, 1, '2024-01-11 03:34:33', '15.pdf', 47, NULL, NULL, NULL, NULL),
(16, 'SINHALA WORDS &amp; PICTURES', 'THINGS MADE OF WOOD', 'Things made of wood in Sinhala ', '16.jpg', '', 0, 1, '2024-01-12 05:23:11', '16.pdf', 58, NULL, NULL, NULL, NULL),
(17, 'ENGLISH WORDS &amp; PICTURES', 'SHAPES', 'Shapes names in English', '17.jpg', '', 0, 1, '2024-01-26 03:29:25', '17.pdf', 15, NULL, NULL, NULL, NULL),
(18, 'SINHALA WORDS &amp; PICTURES', 'SHAPES ', 'Shapes names in Sinhala', '18.jpg', '', 0, 1, '2024-01-26 03:53:06', '18.pdf', 74, NULL, NULL, NULL, NULL),
(19, 'ENGLISH WORDS &amp; PICTURES', 'FLOWERS', 'Flowers names in English', '19.jpg', '', 0, 1, '2024-01-31 02:22:50', '19.pdf', 13, NULL, NULL, NULL, NULL),
(20, 'ENGLISH WORDS &amp; PICTURES', 'FRUITS', 'Fruits names in English', '20.jpg', '', 0, 1, '2024-02-01 01:56:02', '20.pdf', 14, NULL, NULL, NULL, NULL),
(21, 'ENGLISH WORDS &amp; PICTURES', 'VEGETABLES ', 'Vegetables names in English', '21.jpg', '', 0, 1, '2024-02-03 02:17:32', '21.pdf', 15, NULL, NULL, NULL, NULL),
(22, 'ENGLISH WORDS &amp; PICTURES', 'ANIMALS ', 'Animal names in English', '22.jpg', '', 0, 1, '2024-02-03 02:40:11', '22.pdf', 11, NULL, NULL, NULL, NULL),
(23, 'ENGLISH WORDS &amp; PICTURES', 'BIRDS ', 'Birds names in English', '23.jpg', '', 0, 1, '2024-02-07 01:59:36', '23.pdf', 11, NULL, NULL, NULL, NULL),
(24, 'ENGLISH WORDS &amp; PICTURES', 'VEHICLES ', 'Vehicle names in English', '24.jpg', '', 0, 1, '2024-02-10 01:32:51', '24.pdf', 14, NULL, NULL, NULL, NULL),
(25, 'ENGLISH WORDS &amp; PICTURES', 'FARM ANIMALS ', 'Farm animals names in English', '25.jpg', '', 0, 1, '2024-02-12 05:02:15', '25.pdf', 12, NULL, NULL, NULL, NULL),
(26, 'SINHALA WORDS &amp; PICTURES', 'FARM ANIMALS ', 'Farm animals names in Sinhala', '26.jpg', '', 0, 1, '2024-02-12 05:04:26', '26.pdf', 49, NULL, NULL, NULL, NULL),
(28, 'ENGLISH WORDS &amp; PICTURES', 'HOME APPLIANCES ', 'Home Appliances in English', '28.jpg', '', 0, 1, '2024-02-21 09:51:47', '28.pdf', 10, NULL, NULL, NULL, NULL),
(29, 'SINHALA WORDS &amp; PICTURES', 'HOME APPLIANCES ', 'Home appliance names in Sinhala', '29.jpg', '', 0, 1, '2024-02-21 09:53:54', '29.pdf', 38, NULL, NULL, NULL, NULL),
(30, 'ENGLISH WORDS &amp; PICTURES', 'MAMMALS ', 'Mammals names in English', '30.jpg', '', 0, 1, '2024-02-28 02:33:37', '30.pdf', 16, NULL, NULL, NULL, NULL),
(31, 'SINHALA WORDS &amp; PICTURES', 'MAMMALS ', 'Mammal names in Sinhala', '31.jpg', '', 0, 1, '2024-02-28 02:35:06', '31.pdf', 59, NULL, NULL, NULL, NULL),
(32, 'ENGLISH WORDS &amp; PICTURES', 'KITCHEN UTENSILS ', 'Kitchen utensils names in English', '32.jpg', '', 0, 1, '2024-02-29 03:05:56', '32.pdf', 10, NULL, NULL, NULL, NULL),
(33, 'SINHALA WORDS &amp; PICTURES', 'KITCHEN UTENSILS', 'Kitchen utensils names in Sinhala ', '33.JPG', '', 0, 1, '2024-02-29 03:14:34', '33.pdf', 58, NULL, NULL, NULL, NULL),
(34, 'ENGLISH WORDS &amp; PICTURES', 'SPORTS', 'Sports names in English', '34.JPG', '', 0, 1, '2024-02-29 03:33:35', '34.pdf', 12, NULL, NULL, NULL, NULL),
(35, 'SINHALA WORDS &amp; PICTURES', 'SPORTS', 'Sports names in Sinhala', '35.JPG', '', 0, 1, '2024-02-29 03:34:18', '35.pdf', 54, NULL, NULL, NULL, NULL),
(36, 'ENGLISH ALPHABET', 'FLASHCARD - A,B,C &amp; D', 'Flashcards for Letters A,B,C & D ', '36.jpg', '', 0, 1, '2024-06-06 04:27:26', '36.pdf', 9, NULL, NULL, NULL, NULL),
(37, 'ENGLISH ALPHABET', 'FLASHCARD - E,F,G &amp; H', 'Flashcards for Letters E,F,G & H', '37.jpg', '', 0, 1, '2024-06-06 04:32:34', '37.pdf', 14, NULL, NULL, NULL, NULL),
(38, 'ENGLISH ALPHABET', 'FLASHCARD - I,J,K &amp; L', 'Flashcards for Letters I,J,K & L ', '38.jpg', '', 0, 1, '2024-06-06 04:33:53', '38.pdf', 5, NULL, NULL, NULL, NULL),
(39, 'ENGLISH ALPHABET', 'FLASHCARD - M,N,O &amp; P', 'Flashcards for Letters M,N,O & P ', '39.jpg', '', 0, 1, '2024-06-06 04:34:41', '39.pdf', 7, NULL, NULL, NULL, NULL),
(40, 'ENGLISH ALPHABET', 'FLASHCARD - Q,R,S &amp; T', 'Flashcards for Letters Q,R,S & T', '40.jpg', '', 0, 1, '2024-06-06 04:35:34', '40.pdf', 7, NULL, NULL, NULL, NULL),
(41, 'ENGLISH ALPHABET', 'FLASHCARD - U,V,W &amp; X', 'Flashcards for Letters U,V,W & X', '41.jpg', '', 0, 1, '2024-06-06 04:36:14', '41.pdf', 8, NULL, NULL, NULL, NULL),
(42, 'ENGLISH ALPHABET', 'FLASHCARD - Y &amp; Z  ', 'Flashcards for Letters Y & Z', '42.jpg', '', 0, 1, '2024-06-06 04:37:08', '42.pdf', 7, NULL, NULL, NULL, NULL),
(43, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶… Flashcard', '43.jpg', '', 0, 1, '2024-06-07 02:35:11', '43.pdf', 186, NULL, NULL, NULL, NULL),
(44, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶† Flashcard', '44.jpg', '', 0, 1, '2024-06-07 02:39:06', '44.pdf', 135, NULL, NULL, NULL, NULL),
(45, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶‡ Flashcard', '45.jpg', '', 0, 1, '2024-06-07 02:39:59', '45.pdf', 147, NULL, NULL, NULL, NULL),
(46, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶ˆ Flashcard', '46.jpg', '', 0, 1, '2024-06-12 04:13:12', '46.pdf', 150, NULL, NULL, NULL, NULL),
(47, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶‰ Flashcard', '47.jpg', '', 0, 1, '2024-06-12 04:15:15', '47.pdf', 137, NULL, NULL, NULL, NULL),
(48, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶Š Flashcard', '48.jpg', '', 0, 1, '2024-06-12 04:17:08', '48.pdf', 159, NULL, NULL, NULL, NULL),
(49, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶‹ Flashcard', '49.jpg', '', 0, 1, '2024-06-12 04:18:09', '49.pdf', 204, NULL, NULL, NULL, NULL),
(50, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶Œ Flashcard', '50.jpg', '', 0, 1, '2024-06-12 04:19:03', '50.pdf', 140, NULL, NULL, NULL, NULL),
(51, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶‘ Flashcard', '51.jpg', '', 0, 1, '2024-06-12 04:20:28', '51.pdf', 174, NULL, NULL, NULL, NULL),
(62, 'ENGLISH ALPHABET', 'LETTER A FLASHCARD ', 'lashcard for Letter A', '62.jpg', '', 0, 1, '2024-06-19 12:05:56', '62.pdf', 12, NULL, NULL, NULL, NULL),
(63, 'ENGLISH ALPHABET', 'LETTER B FLASHCARD ', 'Flashcard for Letter B', '63.JPG', '', 0, 1, '2024-06-19 12:19:13', 'Flash Card B.pdf', 11, NULL, NULL, NULL, NULL),
(64, 'ENGLISH ALPHABET', 'LETTER C FLASHCARD ', 'Flashcard for Letter C', '64.JPG', '', 0, 1, '2024-06-19 12:21:00', 'Flash Card C.pdf', 12, NULL, NULL, NULL, NULL),
(65, 'ENGLISH ALPHABET', 'LETTER D FLASHCARD ', 'Flashcard for Letter D', '65.JPG', '', 0, 1, '2024-06-19 12:25:41', 'Flash Card D.pdf', 11, NULL, NULL, NULL, NULL),
(66, 'ENGLISH ALPHABET', 'LETTER E FLASHCARD ', 'Flashcard for Letter E', '66.JPG', '', 0, 1, '2024-06-19 12:26:56', 'Flash Card E.pdf', 9, NULL, NULL, NULL, NULL),
(67, 'ENGLISH ALPHABET', 'LETTER F FLASH CARD ', 'Flashcard for Letter F', '67.JPG', '', 0, 1, '2024-06-19 12:28:20', 'Flash Card F.pdf', 10, NULL, NULL, NULL, NULL),
(68, 'ENGLISH ALPHABET', 'LETTER G FLASHCARD ', 'Flashcard for Letter G', '68.JPG', '', 0, 1, '2024-06-19 12:29:10', 'Flash Card G.pdf', 9, NULL, NULL, NULL, NULL),
(69, 'ENGLISH ALPHABET', 'LETTER H FLASHCARD ', 'Flashcard for Letter H', '69.JPG', '', 0, 1, '2024-06-19 12:31:01', '69.pdf', 9, NULL, NULL, NULL, NULL),
(70, 'ENGLISH ALPHABET', 'LETTER I FLASHCARD ', 'Flashcard for Letter I', '70.JPG', '', 0, 1, '2024-06-19 12:35:47', 'Flash Card I.pdf', 10, NULL, NULL, NULL, NULL),
(71, 'ENGLISH ALPHABET', 'LETTER J FLASHCARD ', 'Flashcard for Letter J', '71.JPG', '', 0, 1, '2024-06-19 12:36:32', 'Flash Card J.pdf', 8, NULL, NULL, NULL, NULL),
(72, 'ENGLISH ALPHABET', 'LETTER K FLASHCARD ', 'Flashcard for Letter K', '72.JPG', '', 0, 1, '2024-06-19 12:38:04', 'Flash Card K.pdf', 8, NULL, NULL, NULL, NULL),
(73, 'ENGLISH ALPHABET', 'LETTER L FLASHCARD ', 'Flashcard for Letter L', '73.JPG', '', 0, 1, '2024-06-19 12:39:42', 'Flash Card L.pdf', 8, NULL, NULL, NULL, NULL),
(74, 'ENGLISH ALPHABET', 'LETTER M FLASHCARD  ', 'Flashcard for Letter M', '74.JPG', '', 0, 1, '2024-06-19 12:40:58', '74.pdf', 10, NULL, NULL, NULL, NULL),
(75, 'ENGLISH ALPHABET', 'LETTER N FLASHCARD ', 'Flashcard for Letter N', '75.JPG', '', 0, 1, '2024-06-19 12:41:13', '75.pdf', 9, NULL, NULL, NULL, NULL),
(76, 'ENGLISH ALPHABET', 'LETTER O FLASHCARD ', 'Flashcard for Letter O', '76.JPG', '', 0, 1, '2024-06-19 12:45:12', 'Flash Card O.pdf', 9, NULL, NULL, NULL, NULL),
(77, 'ENGLISH ALPHABET', 'LETTER P FLASHCARD ', 'Flashcard for Letter P', '77.JPG', '', 0, 1, '2024-06-19 12:45:59', 'Flash Card P.pdf', 8, NULL, NULL, NULL, NULL),
(78, 'ENGLISH ALPHABET', 'LETTER Q FLASHCARD ', 'Flashcard for Letter Q', '78.JPG', '', 0, 1, '2024-06-19 12:55:44', 'Flash Card Q.pdf', 10, NULL, NULL, NULL, NULL),
(79, 'ENGLISH ALPHABET', 'LETTER R FLASHCARD ', 'Flashcard for Letter R', '79.JPG', '', 0, 1, '2024-06-19 12:58:12', 'Flash Card R.pdf', 9, NULL, NULL, NULL, NULL),
(80, 'ENGLISH ALPHABET', 'LETTER S FLASHCARD ', 'Flashcard for Letter S', '80.JPG', '', 0, 1, '2024-06-19 12:59:02', 'Flash Card S.pdf', 9, NULL, NULL, NULL, NULL),
(81, 'ENGLISH ALPHABET', 'LETTER T FLASHCARD ', 'Flashcard for Letter T', '81.JPG', '', 0, 1, '2024-06-19 13:00:39', 'Flash Card T.pdf', 10, NULL, NULL, NULL, NULL),
(82, 'ENGLISH ALPHABET', 'LETTER U FLASHCARD ', 'Flashcard for Letter U', '82.JPG', '', 0, 1, '2024-06-19 13:01:28', 'Flash Card U.pdf', 8, NULL, NULL, NULL, NULL),
(83, 'ENGLISH ALPHABET', 'LETTER V FLASHCARD ', 'Flashcard for Letter V', '83.JPG', '', 0, 1, '2024-06-19 13:02:46', 'Flash Card V.pdf', 12, NULL, NULL, NULL, NULL),
(84, 'ENGLISH ALPHABET', 'LETTER W FLASHCARD ', 'Flashcard for Letter W', '84.JPG', '', 0, 1, '2024-06-19 13:03:34', 'Flash Card W.pdf', 8, NULL, NULL, NULL, NULL),
(85, 'ENGLISH ALPHABET', 'LETTER X FLASHCARD ', 'Flashcard for Letter X', '85.JPG', '', 0, 1, '2024-06-19 13:04:13', 'Flash Card X.pdf', 7, NULL, NULL, NULL, NULL),
(86, 'ENGLISH ALPHABET', ' LETTER Y FLASHCARD', 'Flashcard for Letter Y', '86.JPG', '', 0, 1, '2024-06-19 13:05:00', 'Flash Card Y.pdf', 9, NULL, NULL, NULL, NULL),
(87, 'ENGLISH ALPHABET', 'LETTER Z FLASHCARD ', 'Flashcard for Letter Z', '87.JPG', '', 0, 1, '2024-06-19 13:05:50', '87.pdf', 12, NULL, NULL, NULL, NULL),
(88, 'ENGLISH ALPHABET', 'FLASH CARD - A &amp; B ', 'Flashcards for Letters A & B ', '88.JPG', '', 0, 1, '2024-06-29 08:57:07', 'Fash Card A to B.pdf', 9, NULL, NULL, NULL, NULL),
(89, 'ENGLISH ALPHABET', 'FLASH CARD - C &amp; D ', 'Flashcards for Letters C & D\r\n\r\n', '89.JPG', '', 0, 1, '2024-06-29 08:58:24', 'Fash Card C &  D.pdf', 13, NULL, NULL, NULL, NULL),
(90, 'ENGLISH ALPHABET', 'FLASH CARD - E &amp; F', 'Flashcards for Letters E & F\r\n\r\n', '90.JPG', '', 0, 1, '2024-06-29 08:59:39', 'Fash Card E & F.pdf', 12, NULL, NULL, NULL, NULL),
(91, 'ENGLISH ALPHABET', 'FLASH CARD - G &amp; H  ', 'Flashcards for Letters G & H \r\n\r\n', '91.JPG', '', 0, 1, '2024-06-29 09:01:27', 'Fash Card G & H.pdf', 10, NULL, NULL, NULL, NULL),
(92, 'ENGLISH ALPHABET', 'FLASH CARD - I &amp; J', 'Flashcards for Letters I & J \r\n\r\n', '92.JPG', '', 0, 1, '2024-06-29 09:02:49', 'Fash Card I & J.pdf', 9, NULL, NULL, NULL, NULL),
(93, 'ENGLISH ALPHABET', 'FLASH CARD - K &amp; L ', 'Flashcards for Letters K & L\r\n\r\n', '93.JPG', '', 0, 1, '2024-06-29 09:03:37', 'Fash Card K & L.pdf', 10, NULL, NULL, NULL, NULL),
(94, 'ENGLISH ALPHABET', 'FLASH CARD - M &amp; N ', 'Flashcards for Letters M & N \r\n\r\n', '94.JPG', '', 0, 1, '2024-06-29 09:04:51', 'Fash Card M & N.pdf', 10, NULL, NULL, NULL, NULL),
(95, 'ENGLISH ALPHABET', 'FLASH CARD - O &amp; P ', 'Flashcards for Letters O & P \r\n\r\n', '95.JPG', '', 0, 1, '2024-06-29 09:05:49', 'Fash Card O & P.pdf', 11, NULL, NULL, NULL, NULL),
(96, 'ENGLISH ALPHABET', 'FLASH CARD - Q &amp; R ', 'Flashcards for Letters Q & R \r\n\r\n', '96.JPG', '', 0, 1, '2024-06-29 09:06:43', 'Fash Card Q & R.pdf', 9, NULL, NULL, NULL, NULL),
(97, 'ENGLISH ALPHABET', 'FLASH CARD - S &amp; T ', 'Flashcards for Letters S & T \r\n\r\n', '97.JPG', '', 0, 1, '2024-06-29 09:07:25', 'Fash Card S & T.pdf', 11, NULL, NULL, NULL, NULL),
(98, 'ENGLISH ALPHABET', 'FLASH CARD - U &amp; V ', 'Flashcards for Letters U & V \r\n\r\n', '98.JPG', '', 0, 1, '2024-06-29 09:08:11', 'Fash Card U & V.pdf', 10, NULL, NULL, NULL, NULL),
(99, 'ENGLISH ALPHABET', 'FLASH CARD - W &amp; X ', 'Flashcards for Letters W & X \r\n\r\n', '99.JPG', '', 0, 1, '2024-06-29 09:08:58', 'Fash Card W & X.pdf', 10, NULL, NULL, NULL, NULL),
(100, 'ENGLISH ALPHABET', 'FLASHCARD - Y &amp; Z  ', 'Flashcards for Letters Y & Z\r\n\r\n', '100.JPG', '', 0, 1, '2024-06-29 09:09:45', '100.pdf', 16, NULL, NULL, NULL, NULL),
(101, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶’ Flashcard', '101.jpg', '', 0, 1, '2024-07-02 09:25:03', '101.pdf', 213, NULL, NULL, NULL, NULL),
(102, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶” Flashcard\r\n\r\n', '102.jpg', '', 0, 1, '2024-07-02 09:29:50', '102.pdf', 194, NULL, NULL, NULL, NULL),
(103, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶• Flashcard', '103.jpg', '', 0, 1, '2024-07-02 09:32:01', '103.pdf', 174, NULL, NULL, NULL, NULL),
(104, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶š Flashcard', '104.jpg', '', 0, 1, '2024-07-02 09:33:54', '104.pdf', 176, NULL, NULL, NULL, NULL),
(105, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶œ Flashcard', '105.jpg', '', 0, 1, '2024-07-02 09:35:07', '105.pdf', 234, NULL, NULL, NULL, NULL),
(106, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶  Flashcard', '106.jpg', '', 0, 1, '2024-07-02 09:36:40', '106.pdf', 217, NULL, NULL, NULL, NULL),
(107, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶¢ Flashcard\r\n\r\n', '107.jpg', '', 0, 1, '2024-07-02 09:38:00', '107.pdf', 232, NULL, NULL, NULL, NULL),
(108, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶§ Flashcard', '108.jpg', '', 0, 1, '2024-07-02 09:43:17', '108.pdf', 573, NULL, NULL, NULL, NULL),
(109, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶© Flashcard\r\n\r\n', '109.jpg', '', 0, 1, '2024-07-02 09:45:05', '109.pdf', 386, NULL, NULL, NULL, NULL),
(110, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶« Flashcard', '110.jpg', '', 0, 1, '2024-07-02 09:46:12', '110.pdf', 156, NULL, NULL, NULL, NULL),
(111, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶­ Flashcard', '111.jpg', '', 0, 1, '2024-07-02 09:47:45', '111.pdf', 155, NULL, NULL, NULL, NULL),
(112, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶¯ Flashcard', '112.jpg', '', 0, 1, '2024-07-02 09:48:46', '112.pdf', 221, NULL, NULL, NULL, NULL),
(113, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶± Flashcard', '113.jpg', '', 0, 1, '2024-07-02 09:49:41', '113.pdf', 171, NULL, NULL, NULL, NULL),
(114, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶´ Flashcard', '114.jpg', '', 0, 1, '2024-07-02 09:50:40', '114.pdf', 185, NULL, NULL, NULL, NULL),
(115, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶¶ Flashcard', '115.jpg', '', 0, 1, '2024-07-02 09:51:44', '115.pdf', 180, NULL, NULL, NULL, NULL),
(116, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶¸ Flashcard', '116.jpg', '', 0, 1, '2024-07-02 09:52:49', '116.pdf', 209, NULL, NULL, NULL, NULL),
(117, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶¹ Flashcard', '117.jpg', '', 0, 1, '2024-07-02 09:55:27', '117.pdf', 175, NULL, NULL, NULL, NULL),
(118, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶º Flashcard', '118.jpg', '', 0, 1, '2024-07-02 09:57:21', '118.pdf', 248, NULL, NULL, NULL, NULL),
(119, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶» Flashcard', '119.jpg', '', 0, 1, '2024-07-02 09:59:00', '119.pdf', 265, NULL, NULL, NULL, NULL),
(120, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶½ Flashcard', '120.jpg', '', 0, 1, '2024-07-02 10:00:48', '120.pdf', 258, NULL, NULL, NULL, NULL),
(121, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à·€ Flashcard', '121.jpg', '', 0, 1, '2024-07-02 10:01:37', '121.pdf', 232, NULL, NULL, NULL, NULL),
(122, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à·ƒ Flashcard', '122.jpg', '', 0, 1, '2024-07-02 10:07:00', '122.pdf', 192, NULL, NULL, NULL, NULL),
(123, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à·„ Flashcard', '123.jpg', '', 0, 1, '2024-07-02 10:07:57', '123.pdf', 232, NULL, NULL, NULL, NULL),
(124, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à·… Flashcard', '124.jpg', '', 0, 1, '2024-07-02 10:08:44', '124.pdf', 184, NULL, NULL, NULL, NULL),
(125, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶Ÿ Flashcard', '125.jpg', '', 0, 1, '2024-07-02 10:12:26', '125.pdf', 84, NULL, NULL, NULL, NULL),
(126, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶¬ Flashcard\r\n\r\n', '126.jpg', '', 0, 1, '2024-07-02 10:14:02', '126.pdf', 103, NULL, NULL, NULL, NULL),
(127, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶µ Flashcard\r\n\r\n', '127.jpg', '', 0, 1, '2024-07-02 10:15:14', '127.pdf', 78, NULL, NULL, NULL, NULL),
(128, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à¶“ Flashcard', '128.jpg', '', 0, 1, '2024-07-02 10:16:20', '128.pdf', 60, NULL, NULL, NULL, NULL),
(129, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letter à·† Flashcard', '129.jpg', '', 0, 1, '2024-07-02 10:18:45', '129.pdf', 51, NULL, NULL, NULL, NULL),
(130, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letters à¶… & à¶†', '130.jpg', '', 0, 1, '2024-07-04 08:43:49', '130.pdf', 175, NULL, NULL, NULL, NULL),
(131, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letters à¶‡ & à¶ˆ', '131.jpg', '', 0, 1, '2024-07-04 08:44:32', '131.pdf', 127, NULL, NULL, NULL, NULL),
(132, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letters à¶‰ & à¶Š', '132.jpg', '', 0, 1, '2024-07-04 08:45:07', '132.pdf', 132, NULL, NULL, NULL, NULL),
(133, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letters à¶‹ & à¶Œ', '133.jpg', '', 0, 1, '2025-03-09 04:02:36', '133.pdf', 110, NULL, NULL, NULL, NULL),
(134, 'SINHALA ALPHABET', 'SINHALA FLASHCARD', 'Sinhala Letters  à¶‘ & à¶’ ', '134.jpg', '', 0, 1, '2025-03-09 04:10:44', '134.pdf', 191, NULL, NULL, NULL, NULL),
(135, 'ENGLISH WORDS &amp; PICTURES', 'THINGS MADE OF WOOD', 'Things made of wood in English ', '135.JPG', '', 0, 1, '2025-03-26 03:06:00', '135.pdf', 17, NULL, NULL, NULL, NULL),
(136, 'ENGLISH WORDS &amp; PICTURES', 'THINGS MADE OF RUBBER', 'Things made of rubber in English', '136.jpg', '', 0, 1, '2025-03-30 03:39:42', '136.pdf', 14, NULL, NULL, NULL, NULL),
(137, 'ENGLISH WORDS &amp; PICTURES', 'THINGS MADE OF METAL', 'Things made of metal in English', '137.JPG', '', 0, 1, '2025-03-30 03:58:57', '137.pdf', 17, NULL, NULL, NULL, NULL),
(138, 'ENGLISH WORDS &amp; PICTURES', 'THINGS MADE OF PLASTIC ', 'Things made of plastic in English\r\n\r\n', '138.JPG', '', 0, 1, '2025-03-30 04:15:10', '138.pdf', 21, NULL, NULL, NULL, NULL),
(139, 'ENGLISH WORDS &amp; PICTURES', 'MUSIC INSTRUMENTS ', 'Music instruments in English ', '139.JPG', '', 0, 1, '2025-03-30 04:43:34', '139.pdf', 27, NULL, NULL, NULL, NULL),
(140, 'ENGLISH WORDS &amp; PICTURES', 'PARTS OF THE BODY ', 'Parts of the body in English', '140.JPG', '', 0, 1, '2025-03-30 05:07:37', '140.pdf', 27, NULL, NULL, NULL, NULL),
(141, 'ENGLISH WORDS &amp; PICTURES', 'INSECTS ', 'Insects names in English ', '141.JPG', '', 0, 1, '2025-03-30 07:35:47', 'Insects.pdf', 31, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `title`) VALUES
(1, 'Sinhala'),
(2, 'Tamil'),
(3, 'English');

-- --------------------------------------------------------

--
-- Table structure for table `main_category`
--

CREATE TABLE `main_category` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `main_category`
--

INSERT INTO `main_category` (`id`, `title`, `description`) VALUES
(1, 'Leisure Activities', 'Leisure Activities'),
(2, 'Books & Papers', 'Books & Papers'),
(3, 'Study Packs', 'Study Packs');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
  `email_addr` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pdf_descriptions`
--

CREATE TABLE `pdf_descriptions` (
  `id` int(11) NOT NULL,
  `pdf_id` int(11) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pdf_details`
--

CREATE TABLE `pdf_details` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `pdfupload` varchar(100) NOT NULL,
  `download_count` int(11) NOT NULL,
  `language_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `main_cat_id` int(11) DEFAULT NULL,
  `sub_cat_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pdf_details`
--

INSERT INTO `pdf_details` (`id`, `tag`, `title`, `description`, `image`, `video`, `video_status`, `status`, `timestamp`, `pdfupload`, `download_count`, `language_id`, `grade_id`, `main_cat_id`, `sub_cat_id`) VALUES
(2, 'COLOURING ', 'Rat ', 'Rat coloring page', '2.JPG', '', 0, 1, '2023-09-19 02:14:44', 'Rat_01.pdf', 9, 3, 3, 1, 1),
(3, 'COLOURING ', 'Rabbit ', 'Rabbit coloring page', '3.JPG', '', 0, 1, '2023-09-22 02:25:30', 'Rabbit_01.pdf', 18, 3, 2, 1, 1),
(4, 'COLOURING ', 'Lion', 'Lion coloring page', '4.JPG', '', 0, 1, '2023-09-22 02:30:07', 'Lion_01.pdf', 8, 3, 1, 1, 1),
(5, 'COLOURING ', 'Giraffe', 'Giraffe coloring pages ', '5.jpg', '', 0, 1, '2023-09-22 02:33:09', 'Giraffe_01.pdf', 8, 3, 1, 1, 1),
(6, 'COLOURING ', 'Elephant', 'Elephant coloring page ', '6.JPG', '', 0, 1, '2023-09-22 02:35:58', 'Elephant-_01.pdf', 13, 3, 1, 1, 1),
(7, 'COLOURING ', 'Deer', 'Deer coloring page', '7.JPG', '', 0, 1, '2023-09-22 02:38:17', 'Deer_01.pdf', 11, 3, 1, 1, 1),
(8, 'COLOURING ', 'Dinosaur', 'Dinosaur coloring page ', '8.JPG', '', 0, 1, '2023-09-22 02:43:30', '8.pdf', 14, NULL, NULL, NULL, NULL),
(9, 'COLOURING ', 'Cat', 'Cat coloring page', '9.JPG', '', 0, 1, '2023-10-05 02:19:02', 'Cat_01.pdf', 12, NULL, NULL, NULL, NULL),
(10, 'COLOURING ', 'Dog ', 'Dog coloring page', '10.JPG', '', 0, 1, '2023-10-05 02:30:13', 'Dog_01.pdf', 13, NULL, NULL, NULL, NULL),
(11, 'COLOURING ', 'Hen ', 'Hen coloring page', '11.JPG', '', 0, 1, '2023-11-28 13:44:16', 'Hen_01.pdf', 17, NULL, NULL, NULL, NULL),
(12, 'COLOURING ', 'Owl ', 'Owl coloring page', '12.JPG', '', 0, 1, '2023-12-06 02:08:15', 'Owl_01.pdf', 11, NULL, NULL, NULL, NULL),
(13, 'COLOURING ', 'Dragon', 'Dragon coloring page', '13.JPG', '', 0, 1, '2024-01-01 01:34:59', 'Dragon_01.pdf', 11, NULL, NULL, NULL, NULL),
(14, 'COLOURING ', 'Flower Garden ', 'Flower Garden with bee', '14.JPG', '', 0, 1, '2024-01-17 13:00:37', 'Flower Garden.pdf', 13, NULL, NULL, NULL, NULL),
(15, 'DOT-TO-DOT', 'CAT', 'Cat dot-to-dot worksheet', '15.jpg', '', 0, 1, '2024-01-30 02:31:13', 'Cat dot to dot worksheet.pdf', 5, NULL, NULL, NULL, NULL),
(16, 'COLOURING ', 'FROG', 'Frog coloring page', '16.JPG', '', 0, 1, '2024-02-07 01:55:55', 'Frog.pdf', 11, NULL, NULL, NULL, NULL),
(17, 'COLOURING ', 'PENGUIN', 'Penguin coloring page', '17.JPG', '', 0, 1, '2024-02-07 01:57:12', 'Penguin.pdf', 11, NULL, NULL, NULL, NULL),
(18, 'COLOURING ', 'PIG ', 'Pig coloring page', '18.JPG', '', 0, 1, '2024-02-08 14:12:02', 'pig.pdf', 9, NULL, NULL, NULL, NULL),
(19, 'COLOURING ', 'COW ', 'Cow coloring page', '19.JPG', '', 0, 1, '2024-02-08 14:12:35', 'Cow.pdf', 11, NULL, NULL, NULL, NULL),
(20, 'COLOURING ', 'Flower - 01', 'Flower coloring sheet', '20.JPG', '', 0, 1, '2024-02-12 11:13:15', 'Flower coloring sheet.pdf', 14, NULL, NULL, NULL, NULL),
(21, 'DOT-TO-DOT', 'Lion ', 'Lion dot-to-dot worksheet', '21.jpg', '', 0, 1, '2024-02-14 01:29:22', 'Lion dot to dot worksheet.pdf', 12, NULL, NULL, NULL, NULL),
(22, 'COLOURING ', 'BUTTERFLY ', 'Butterfly coloring sheet', '22.JPG', '', 0, 1, '2024-02-15 03:42:45', 'Butterfly.pdf', 18, NULL, NULL, NULL, NULL),
(23, 'DOT-TO-DOT', 'Fish ', 'Fish dot-to-dot worksheet', '23.jpg', '', 0, 1, '2024-02-17 13:06:58', 'Fish dot-to-dot worksheet.pdf', 10, NULL, NULL, NULL, NULL),
(24, 'DOT-TO-DOT', 'Dinosaur', 'Dinosaur dot-to-dot worksheet', '24.jpg', '', 0, 1, '2024-02-17 13:10:37', 'Dinosaur dot-to-dot worksheet.pdf', 10, NULL, NULL, NULL, NULL),
(25, 'MATCHING ', 'Matching Games 01 ', 'Printable Matching Games for Kids', '25.jpg', '', 0, 1, '2024-02-20 13:55:57', 'matching games for kids 01.pdf', 18, NULL, NULL, NULL, NULL),
(26, 'MATCHING ', 'Matching Games 02', 'Printable Matching Games for Kids', '26.jpg', '', 0, 1, '2024-02-27 02:00:17', 'Matching games for kids 02.pdf', 16, NULL, NULL, NULL, NULL),
(27, 'COLOURING ', 'TURTLE ', 'Turtle coloring page', '27.JPG', '', 0, 1, '2024-03-06 02:53:15', 'Turtle Colouring Page.pdf', 19, NULL, NULL, NULL, NULL),
(28, 'COLOURING ', 'SUNFLOWERS ', 'Sunflowers Colouring Page ', '28.JPG', '', 0, 1, '2025-02-12 02:57:10', 'Sunflowers & sun.pdf', 18, NULL, NULL, NULL, NULL),
(29, 'DOT-TO-DOT', 'UNICORN ', 'Unicorn dot-to-dot worksheet', '29.jpg', '', 0, 1, '2025-02-12 03:13:52', 'Unicorn dot to dot worksheet.pdf', 7, NULL, NULL, NULL, NULL),
(30, 'DOT-TO-DOT', 'TEDDY BEAR', 'Teddy dot-to-dot worksheet', '30.jpg', '', 0, 1, '2025-02-12 03:15:29', 'Teddy dot to dot worksheet.pdf', 7, NULL, NULL, NULL, NULL),
(31, 'DOT-TO-DOT', 'PENGUIN', 'Penguin dot-to-dot worksheet', '31.jpg', '', 0, 1, '2025-02-12 03:17:56', 'Penguin dot to dot worksheet.pdf', 7, NULL, NULL, NULL, NULL),
(32, 'DOT-TO-DOT', 'FOX', 'Fox dot-to-dot worksheet', '32.jpg', '', 0, 1, '2025-02-12 03:19:05', 'Fox dot to dot worksheet.pdf', 6, NULL, NULL, NULL, NULL),
(33, 'DOT-TO-DOT', 'KOALA', 'Koala dot-to-dot worksheet', '33.jpg', '', 0, 1, '2025-02-12 03:20:22', 'Koala dot to dot worksheet.pdf', 9, NULL, NULL, NULL, NULL),
(34, 'DOT-TO-DOT', 'BEAR', 'Bear dot-to-dot worksheet', '34.jpg', '', 0, 1, '2025-02-12 03:21:37', 'Bear dot to dot worksheet.pdf', 7, NULL, NULL, NULL, NULL),
(35, 'DOT-TO-DOT', 'CAT', 'Cat dot-to-dot worksheet', '35.jpg', '', 0, 1, '2025-02-12 03:22:58', 'Cat dot to dot worksheet.pdf', 9, NULL, NULL, NULL, NULL),
(36, 'DOT-TO-DOT', 'HEN', 'Hen dot-to-dot worksheet', '36.jpg', '', 0, 1, '2025-02-12 03:24:18', 'Hen dot to dot worksheet.pdf', 17, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sub_category`
--

CREATE TABLE `sub_category` (
  `id` int(11) NOT NULL,
  `main_cat_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `sub_category`
--

INSERT INTO `sub_category` (`id`, `main_cat_id`, `title`, `description`) VALUES
(1, 1, 'Animals', 'Animals'),
(2, 1, 'Fruits', 'Fruits'),
(3, 1, 'Vegitable', 'Vegitable'),
(4, 1, 'Vehicle', 'Vehicle'),
(5, 2, 'Numbers', 'Numbers'),
(6, 2, 'Addition', 'Addition'),
(7, 2, 'Subtraction', 'Subtraction'),
(8, 2, 'Time', 'Time'),
(9, 2, 'Shapes', 'Shapes');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ratings` int(11) NOT NULL,
  `one_word` varchar(50) NOT NULL,
  `review` varchar(500) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `name`, `ratings`, `one_word`, `review`, `status`, `timestamp`) VALUES
(1, 8, '', 5, 'Superb ', 'Edibear\'s worksheets and study materials are really helpful for my kid\'s education.', 1, '2024-02-20 02:01:15'),
(2, 9, '', 5, 'Really Helpful ', 'Sinhala worksheets are very important. We cannot find this type of worksheet anywhere on the internet. Thatâ€™s why I love ediber.', 1, '2024-02-20 02:14:16'),
(3, 10, '', 5, 'Great Work ', 'As a school teacher, Iâ€™m busy with school kids, and I donâ€™t have much time to create worksheets for them. So I need helpful resources like ediber to save time.', 1, '2024-02-20 02:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials_images`
--

CREATE TABLE `testimonials_images` (
  `testimonial_id` int(11) NOT NULL,
  `image` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tourists`
--

CREATE TABLE `tourists` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(20) DEFAULT NULL,
  `password` varchar(1000) NOT NULL,
  `email` varchar(50) NOT NULL,
  `country` varchar(20) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `delete_status` int(11) DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tourists`
--

INSERT INTO `tourists` (`id`, `username`, `name`, `profile_pic`, `password`, `email`, `country`, `status`, `delete_status`, `timestamp`) VALUES
(1, 'kcranasinghe', '\r\n', '1.jpg', 'pass$2y$10$YlJ7CJAFdezhKp7qdFkJluB/ld.bzEITAOVURZNtu4HyYOnoDiXrK', 'kcranasinghe13@gmail.coms', 'Ethiopia', 0, 1, '2022-11-30 22:24:29');

-- --------------------------------------------------------

--
-- Table structure for table `tour_day_details`
--

CREATE TABLE `tour_day_details` (
  `id` int(11) NOT NULL,
  `tour_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `accommodation` varchar(50) NOT NULL,
  `room` varchar(20) NOT NULL,
  `meal_plan` varchar(20) NOT NULL,
  `travel_time` varchar(50) NOT NULL,
  `image_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tour_day_details`
--

INSERT INTO `tour_day_details` (`id`, `tour_id`, `title`, `description`, `accommodation`, `room`, `meal_plan`, `travel_time`, `image_name`) VALUES
(1, 2, 'day 1', 'day 1 des', 'acc', 'room', 'm plan', 't time', '2-day1.jpg'),
(4, 2, 'Day 2', 'Desc', '', '', '', '', '2-day4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tour_details`
--

CREATE TABLE `tour_details` (
  `id` int(11) NOT NULL,
  `no` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `image_name` varchar(20) DEFAULT NULL,
  `duration` varchar(50) NOT NULL,
  `tour_group` varchar(50) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `guide` varchar(50) NOT NULL,
  `pickup_drop` varchar(50) NOT NULL,
  `hotel_type` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `arrival_departure_location` varchar(100) NOT NULL,
  `depature_time` varchar(50) NOT NULL,
  `meal_plan` varchar(50) NOT NULL,
  `bed_room` varchar(50) NOT NULL,
  `services_included` text NOT NULL,
  `services_excluded` text NOT NULL,
  `map` varchar(1000) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tour_details`
--

INSERT INTO `tour_details` (`id`, `no`, `title`, `type`, `image_name`, `duration`, `tour_group`, `vehicle_type`, `guide`, `pickup_drop`, `hotel_type`, `description`, `arrival_departure_location`, `depature_time`, `meal_plan`, `bed_room`, `services_included`, `services_excluded`, `map`, `status`, `timestamp`) VALUES
(2, 'Tour 01', 'Test Title', 'Tour Type', '2.jpg', 'Duration', 'Small', 'Van / Car', 'Guide', 'Pick-up Drop', 'Hotel Type', 'Tour main description', 'Katunayake', 'Dep Time', 'meal', 'bed room', 'xx - yy', 'aa - yy', 'https://www.google.com/maps/embed?pb=!1m27!1m12!1m3!1d31682.96605585071!2d79.9908908630698!3d6.965514977934151!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m12!3e0!4m4!2s6.984048%2C80.033215!3m2!1d6.984048!2d80.033215!4m5!1s0x3ae2575eb8a8bd3d%3A0xa3d1f704f6a95a28!2sKaduwela%20Public%20Bus%20Stand%2C%20WXPM%2BG8C%2C%20Kaduwela!3m2!1d6.9363028!2d79.983274!5e0!3m2!1sen!2slk!4v1670001305765!5m2!1sen!2slk', 1, '2022-11-02 01:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `tour_sub_images`
--

CREATE TABLE `tour_sub_images` (
  `tour_id` int(11) NOT NULL,
  `image_name` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tour_sub_images`
--

INSERT INTO `tour_sub_images` (`tour_id`, `image_name`) VALUES
(2, '2-4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `login_email` varchar(100) NOT NULL,
  `password` varchar(10000) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `register_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `delete_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`id`, `first_name`, `last_name`, `login_email`, `password`, `mobile_number`, `register_timestamp`, `delete_status`) VALUES
(1, 'KC', 'Ranasinghe', 'udyanaasith@gmail.com', 'pass$2y$10$41BDw/jGUHW1OOnOulhWtuMN2lzQ.1i.UmVzMMN21NLxKBTmEA5ia', '+94703619957', '2022-08-05 03:26:26', 0),
(4, 'Asith', 'Udyana', 'testadmin@gmail.com', 'pass$2y$10$iA3mnPtEMDx2WtJDwPdDoOcBTuEA9.b53OifPBo4DsBQvIe92JU2i', '+94703619957', '2022-10-21 04:11:02', 1),
(5, 'Thilina ', 'Sampath ', 'tsranasingha@gmail.com', 'pass$2y$10$8D9VHZ96f9WmQuI712pAQ.xQygd0RKZIXfeT8WM49MfNXzmzMQvDq', '0724761762', '2023-09-19 02:08:52', 0),
(7, 'Test', 'Admin', 'test@gmail.com', 'pass$2y$10$L8zmqpx/1aqB0u1e.fwD2e7Ivv9NKeOHPsyjYw261iWmqCZZdHGhq', '', CURRENT_TIMESTAMP, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ad1_descriptions`
--
ALTER TABLE `ad1_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_blog_id` (`ad1_id`);

--
-- Indexes for table `ad1_details`
--
ALTER TABLE `ad1_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ad2_descriptions`
--
ALTER TABLE `ad2_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_blog_id` (`ad2_id`);

--
-- Indexes for table `ad2_details`
--
ALTER TABLE `ad2_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_descriptions`
--
ALTER TABLE `blog_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_blog_id` (`blog_id`);

--
-- Indexes for table `blog_details`
--
ALTER TABLE `blog_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books_descriptions`
--
ALTER TABLE `books_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index` (`books_id`);

--
-- Indexes for table `books_details`
--
ALTER TABLE `books_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_language` (`language_id`),
  ADD KEY `fk_grade` (`grade_id`),
  ADD KEY `fk_main_cat` (`main_cat_id`),
  ADD KEY `fk_sub_cat` (`sub_cat_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cart_product` (`product_id`),
  ADD KEY `fk_cart_user` (`user_id`);

--
-- Indexes for table `carousel`
--
ALTER TABLE `carousel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_category` (`category_id`),
  ADD KEY `fk_product_subcategory` (`sub_category_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homework_descriptions`
--
ALTER TABLE `homework_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index` (`homework_id`);

--
-- Indexes for table `homework_details`
--
ALTER TABLE `homework_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `main_category`
--
ALTER TABLE `main_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pdf_descriptions`
--
ALTER TABLE `pdf_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_pdf_id` (`pdf_id`);

--
-- Indexes for table `pdf_details`
--
ALTER TABLE `pdf_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sub_category`
--
ALTER TABLE `sub_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_main_category` (`main_cat_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_tourist_id` (`user_id`);

--
-- Indexes for table `testimonials_images`
--
ALTER TABLE `testimonials_images`
  ADD KEY `FK_testimonial_image_id` (`testimonial_id`);

--
-- Indexes for table `tourists`
--
ALTER TABLE `tourists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tour_day_details`
--
ALTER TABLE `tour_day_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK1_tour_id` (`tour_id`);

--
-- Indexes for table `tour_details`
--
ALTER TABLE `tour_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tour_sub_images`
--
ALTER TABLE `tour_sub_images`
  ADD KEY `FK_tour_id` (`tour_id`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ad1_descriptions`
--
ALTER TABLE `ad1_descriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad1_details`
--
ALTER TABLE `ad1_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ad2_descriptions`
--
ALTER TABLE `ad2_descriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad2_details`
--
ALTER TABLE `ad2_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `blog_descriptions`
--
ALTER TABLE `blog_descriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `blog_details`
--
ALTER TABLE `blog_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `books_descriptions`
--
ALTER TABLE `books_descriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `books_details`
--
ALTER TABLE `books_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carousel`
--
ALTER TABLE `carousel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `homework_descriptions`
--
ALTER TABLE `homework_descriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `homework_details`
--
ALTER TABLE `homework_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `pdf_descriptions`
--
ALTER TABLE `pdf_descriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pdf_details`
--
ALTER TABLE `pdf_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tourists`
--
ALTER TABLE `tourists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tour_day_details`
--
ALTER TABLE `tour_day_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tour_details`
--
ALTER TABLE `tour_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Indexes for table `braveheart_categories`
--
ALTER TABLE `braveheart_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `braveheart_events`
--
ALTER TABLE `braveheart_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `braveheart_winners`
--
ALTER TABLE `braveheart_winners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_braveheart_event` (`event_id`);

--
-- AUTO_INCREMENT for table `braveheart_categories`
--
ALTER TABLE `braveheart_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `braveheart_events`
--
ALTER TABLE `braveheart_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `braveheart_winners`
--
ALTER TABLE `braveheart_winners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `braveheart_winners`
--
ALTER TABLE `braveheart_winners`
  ADD CONSTRAINT `fk_braveheart_event` FOREIGN KEY (`event_id`) REFERENCES `braveheart_events` (`id`);

--
-- Constraints for table `ad1_descriptions`
--
ALTER TABLE `ad1_descriptions`
  ADD CONSTRAINT `FK_ad1_id` FOREIGN KEY (`ad1_id`) REFERENCES `ad1_details` (`id`);

--
-- Constraints for table `ad2_descriptions`
--
ALTER TABLE `ad2_descriptions`
  ADD CONSTRAINT `FK_ad2_id` FOREIGN KEY (`ad2_id`) REFERENCES `ad2_details` (`id`);

--
-- Constraints for table `blog_descriptions`
--
ALTER TABLE `blog_descriptions`
  ADD CONSTRAINT `FK_blog_id` FOREIGN KEY (`blog_id`) REFERENCES `blog_details` (`id`);

--
-- Constraints for table `books_descriptions`
--
ALTER TABLE `books_descriptions`
  ADD CONSTRAINT `FK_books_id` FOREIGN KEY (`books_id`) REFERENCES `books_details` (`id`);

--
-- Constraints for table `homework_descriptions`
--
ALTER TABLE `homework_descriptions`
  ADD CONSTRAINT `FK_homework_id` FOREIGN KEY (`homework_id`) REFERENCES `homework_details` (`id`);

--
-- Constraints for table `pdf_descriptions`
--
ALTER TABLE `pdf_descriptions`
  ADD CONSTRAINT `FK_pdf_id` FOREIGN KEY (`pdf_id`) REFERENCES `pdf_details` (`id`);

--
-- Constraints for table `books_details`
--
ALTER TABLE `books_details`
  ADD CONSTRAINT `fk_grade` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`),
  ADD CONSTRAINT `fk_language` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  ADD CONSTRAINT `fk_main_cat` FOREIGN KEY (`main_cat_id`) REFERENCES `main_category` (`id`),
  ADD CONSTRAINT `fk_sub_cat` FOREIGN KEY (`sub_cat_id`) REFERENCES `sub_category` (`id`);

--
-- Constraints for table `sub_category`
--
ALTER TABLE `sub_category`
  ADD CONSTRAINT `fk_main_category` FOREIGN KEY (`main_cat_id`) REFERENCES `main_category` (`id`);

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `FK_tourist_id` FOREIGN KEY (`user_id`) REFERENCES `tourists` (`id`);

--
-- Constraints for table `testimonials_images`
--
ALTER TABLE `testimonials_images`
  ADD CONSTRAINT `FK_testimonial_image_id` FOREIGN KEY (`testimonial_id`) REFERENCES `testimonials` (`id`);

--
-- Constraints for table `tour_day_details`
--
ALTER TABLE `tour_day_details`
  ADD CONSTRAINT `FK1_tour_id` FOREIGN KEY (`tour_id`) REFERENCES `tour_details` (`id`);

--
-- Constraints for table `tour_sub_images`
--
ALTER TABLE `tour_sub_images`
  ADD CONSTRAINT `FK_tour_id` FOREIGN KEY (`tour_id`) REFERENCES `tour_details` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `tourists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_subcategory` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_category` (`id`) ON DELETE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


















-- Worksheets taxonomy + products (MySQL 5.7+ / MariaDB 10.2+)
-- Create database if needed: CREATE DATABASE nursing_exam_support CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ws_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ws_subcategories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(191) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ws_sub_cat FOREIGN KEY (category_id) REFERENCES ws_categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ws_products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    subcategory_id INT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ws_prod_cat FOREIGN KEY (category_id) REFERENCES ws_categories (id) ON DELETE RESTRICT,
    CONSTRAINT fk_ws_prod_sub FOREIGN KEY (subcategory_id) REFERENCES ws_subcategories (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_ws_subcategories_category ON ws_subcategories (category_id);
CREATE INDEX idx_ws_products_category ON ws_products (category_id);
CREATE INDEX idx_ws_products_subcategory ON ws_products (subcategory_id);


-- ================================================================
-- MIGRATIONS (from sql/ directory)
-- ================================================================

-- --------------------------------------------------------
-- sql/add_orders_order_status.sql
-- --------------------------------------------------------
ALTER TABLE `orders`
  ADD COLUMN `order_status` varchar(50) NOT NULL DEFAULT 'Order Placed' AFTER `payment_status`;

-- --------------------------------------------------------
-- sql/add_product_subcategories.sql
-- --------------------------------------------------------
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `product_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_category_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_psc_product_category` (`product_category_id`),
  CONSTRAINT `fk_psc_product_categories`
    FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `products`
  ADD COLUMN `product_subcategory_id` int(11) DEFAULT NULL AFTER `sub_category_id`;

ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_product_subcategory`
    FOREIGN KEY (`product_subcategory_id`) REFERENCES `product_subcategories` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------
-- sql/add_products_weight.sql
-- --------------------------------------------------------
ALTER TABLE `products` ADD COLUMN `isbn` varchar(64) DEFAULT NULL;
ALTER TABLE `products` ADD COLUMN `weight` varchar(64) DEFAULT NULL;

-- --------------------------------------------------------
-- sql/add_test_admin_user.sql
-- --------------------------------------------------------
INSERT INTO `user_table` (`id`, `first_name`, `last_name`, `login_email`, `password`, `mobile_number`, `register_timestamp`, `delete_status`) VALUES (6, 'Test', 'Admin', 'test@gmail.com', 'pass$2y$10$L8zmqpx/1aqB0u1e.fwD2e7Ivv9NKeOHPsyjYw261iWmqCZZdHGhq', '', CURRENT_TIMESTAMP, 0);

-- --------------------------------------------------------
-- sql/add_tourist_password_reset.sql
-- --------------------------------------------------------
ALTER TABLE `tourists`
  ADD COLUMN `password_reset_token` varchar(64) DEFAULT NULL,
  ADD COLUMN `password_reset_expires` datetime DEFAULT NULL;

-- --------------------------------------------------------
-- sql/alter_tourists_signup_columns.sql
-- --------------------------------------------------------
ALTER TABLE `tourists`
  MODIFY `username` VARCHAR(191) NOT NULL,
  MODIFY `email` VARCHAR(191) NOT NULL,
  MODIFY `country` VARCHAR(100) NOT NULL;

-- --------------------------------------------------------
-- sql/clear_all_data.sql
-- --------------------------------------------------------
-- SET NAMES utf8mb4;
-- SET FOREIGN_KEY_CHECKS = 0;
-- TRUNCATE TABLE `cart`;
-- TRUNCATE TABLE `orders`;
-- TRUNCATE TABLE `products`;
-- TRUNCATE TABLE `product_subcategories`;
-- TRUNCATE TABLE `product_categories`;
-- TRUNCATE TABLE `ad1_descriptions`;
-- TRUNCATE TABLE `ad1_details`;
-- TRUNCATE TABLE `ad2_descriptions`;
-- TRUNCATE TABLE `ad2_details`;
-- TRUNCATE TABLE `blog_descriptions`;
-- TRUNCATE TABLE `blog_details`;
-- TRUNCATE TABLE `books_descriptions`;
-- TRUNCATE TABLE `books_details`;
-- TRUNCATE TABLE `homework_descriptions`;
-- TRUNCATE TABLE `homework_details`;
-- TRUNCATE TABLE `pdf_descriptions`;
-- TRUNCATE TABLE `pdf_details`;
-- TRUNCATE TABLE `carousel`;
-- TRUNCATE TABLE `grades`;
-- TRUNCATE TABLE `languages`;
-- TRUNCATE TABLE `main_category`;
-- TRUNCATE TABLE `sub_category`;
-- TRUNCATE TABLE `newsletter`;
-- TRUNCATE TABLE `testimonials`;
-- TRUNCATE TABLE `testimonials_images`;
-- TRUNCATE TABLE `tourists`;
-- TRUNCATE TABLE `tour_day_details`;
-- TRUNCATE TABLE `tour_sub_images`;
-- TRUNCATE TABLE `tour_details`;
-- TRUNCATE TABLE `braveheart_winners`;
-- TRUNCATE TABLE `braveheart_events`;
-- TRUNCATE TABLE `braveheart_categories`;
-- TRUNCATE TABLE `user_table`;
-- SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- sql/create_product_review.sql
-- --------------------------------------------------------
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `product_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 0,
  `review` text,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_review_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- sql/link_free_content_to_product_categories.sql
-- --------------------------------------------------------
ALTER TABLE `pdf_details`
  ADD COLUMN `product_category_id` int(11) DEFAULT NULL COMMENT 'shop category' AFTER `sub_cat_id`,
  ADD COLUMN `product_subcategory_id` int(11) DEFAULT NULL COMMENT 'shop subcategory' AFTER `product_category_id`;

ALTER TABLE `books_details`
  ADD COLUMN `product_category_id` int(11) DEFAULT NULL COMMENT 'shop category' AFTER `sub_cat_id`,
  ADD COLUMN `product_subcategory_id` int(11) DEFAULT NULL COMMENT 'shop subcategory' AFTER `product_category_id`;

ALTER TABLE `homework_details`
  ADD COLUMN `product_category_id` int(11) DEFAULT NULL COMMENT 'shop category' AFTER `sub_cat_id`,
  ADD COLUMN `product_subcategory_id` int(11) DEFAULT NULL COMMENT 'shop subcategory' AFTER `product_category_id`;

-- --------------------------------------------------------
-- sql/migration_blog_extra_media.sql
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_extra_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `media_type` varchar(20) NOT NULL DEFAULT 'image',
  `path` varchar(512) NOT NULL DEFAULT '',
  `caption` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_blog_extra_media_blog` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- sql/migration_blog_tag_triple.sql
-- --------------------------------------------------------
ALTER TABLE `blog_details`
  MODIFY COLUMN `tag` VARCHAR(255) NOT NULL;

-- --------------------------------------------------------
-- sql/migration_home_section_backgrounds.sql
-- --------------------------------------------------------
ALTER TABLE `carousel` MODIFY `type` VARCHAR(32) NOT NULL COMMENT 'img, video, main, explore_bg, testimonial_bg, footer_bg, ...';

-- --------------------------------------------------------
-- sql/migration_order_items.sql
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- sql/migration_product_gallery_and_options.sql
-- --------------------------------------------------------
ALTER TABLE `products`
  ADD COLUMN `gallery_images` TEXT NULL DEFAULT NULL COMMENT 'JSON: up to 3 filenames in img/products/' AFTER `image`;

ALTER TABLE `products`
  ADD COLUMN `options_extra` TEXT NULL DEFAULT NULL COMMENT 'JSON array of {k,v} from admin extra option rows' AFTER `description`;

-- --------------------------------------------------------
-- sql/migration_shipping_and_weight_kg.sql
-- --------------------------------------------------------
ALTER TABLE `products`
  ADD COLUMN `weight_kg` DECIMAL(12,4) NULL DEFAULT NULL COMMENT 'Unit weight in kg for shipping' AFTER `weight`;

CREATE TABLE IF NOT EXISTS `edi_shipping_weight_tiers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `max_weight_kg` DECIMAL(12,4) DEFAULT NULL COMMENT 'Cart total kg up to and including this; NULL = unlimited (catch-all)',
  `fee_lkr` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort_order`),
  KEY `idx_max` (`max_weight_kg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `edi_shipping_districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `fee_lkr` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Added on top of weight-tier fee; match is case-insensitive on name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `edi_shipping_weight_tiers` (`max_weight_kg`, `fee_lkr`, `sort_order`) VALUES
  (1.0000, 300.00, 10),
  (5.0000, 450.00, 20),
  (NULL, 650.00, 90);

INSERT IGNORE INTO `edi_shipping_districts` (`name`, `fee_lkr`) VALUES
  ('Colombo', 0.00),
  ('Gampaha', 50.00),
  ('Kandy', 100.00),
  ('Other / not listed', 0.00);

-- --------------------------------------------------------
-- sql/migration_user_table_admin_ui.sql
-- --------------------------------------------------------
ALTER TABLE `user_table`
  ADD COLUMN `admin_role` varchar(32) NOT NULL DEFAULT 'administrator' AFTER `mobile_number`;

ALTER TABLE `user_table`
  ADD COLUMN `city_country` varchar(100) NOT NULL DEFAULT '' AFTER `admin_role`;

ALTER TABLE `user_table`
  ADD COLUMN `admin_status` tinyint(1) NOT NULL DEFAULT 1 AFTER `city_country`;

ALTER TABLE `user_table`
  ADD COLUMN `profile_pic` varchar(255) NOT NULL DEFAULT 'default.jpg' AFTER `admin_status`;

-- --------------------------------------------------------
-- sql/seed_languages_grades.sql
-- --------------------------------------------------------
SET NAMES utf8mb4;

INSERT INTO `languages` (`id`, `title`) VALUES
  (1, 'Sinhala'),
  (2, 'English'),
  (3, 'Tamil')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `grades` (`id`, `title`) VALUES
  (1, 'Pre School'),
  (2, 'Grade 1'),
  (3, 'Grade 2'),
  (4, 'Grade 3'),
  (5, 'Grade 4'),
  (6, 'Grade 5')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- --------------------------------------------------------
-- sql/migration_bank_details.sql
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `edi_bank_details` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `account_number` VARCHAR(50)  NOT NULL DEFAULT '',
    `account_name`   VARCHAR(150) NOT NULL DEFAULT '',
    `bank_name`      VARCHAR(150) NOT NULL DEFAULT '',
    `branch_name`    VARCHAR(150) NOT NULL DEFAULT '',
    `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `edi_bank_details` (`account_number`, `account_name`, `bank_name`, `branch_name`)
SELECT '1000400531', 'EDIBEAR (PRIVATE) LIMITED', 'COMMERCIAL BANK', 'GAMPAHA BRANCH'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `edi_bank_details` LIMIT 1);

-- --------------------------------------------------------
-- sql/migration_vouchers.sql
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `edi_vouchers` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code`            VARCHAR(50)  NOT NULL,
  `description`     VARCHAR(255) NOT NULL DEFAULT '',
  `discount_type`   ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `min_order_total`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_uses`        INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
  `used_count`      INT UNSIGNED NOT NULL DEFAULT 0,
  `status`          TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive',
  `starts_at`       DATE DEFAULT NULL,
  `expires_at`      DATE DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_voucher_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER $$
DROP PROCEDURE IF EXISTS _edi_add_voucher_cols$$
CREATE PROCEDURE _edi_add_voucher_cols()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'voucher_code') THEN
    ALTER TABLE `orders` ADD COLUMN `voucher_code` VARCHAR(50) DEFAULT NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'voucher_discount') THEN
    ALTER TABLE `orders` ADD COLUMN `voucher_discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
  END IF;
END$$
DELIMITER ;
CALL _edi_add_voucher_cols();
DROP PROCEDURE IF EXISTS _edi_add_voucher_cols;
