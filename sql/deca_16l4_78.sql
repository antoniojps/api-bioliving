-- phpMyAdmin SQL Dump
-- version 4.3.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 23-Jun-2017 às 11:51
-- Versão do servidor: 5.6.23-log
-- PHP Version: 5.6.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `deca_16l4_78`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `colaboradores`
--

CREATE TABLE IF NOT EXISTS `colaboradores` (
  `id_colaboradores` int(11) NOT NULL,
  `nome` varchar(45) DEFAULT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `image_src` varchar(255) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `colaboradores`
--

INSERT INTO `colaboradores` (`id_colaboradores`, `nome`, `descricao`, `image_src`) VALUES
(1, 'Dr. Rosa Pinho', 'Botânica e curadora do herbário do Departamento de Biologia da Universidade de Aveiro.\r\n', 'http://loremflickr.com/50/50'),
(2, 'Universidade de Aveiro', 'A melhor universidade do mundo capaz de produzir alunos com desempenhos extraordinários.\n', 'http://loremflickr.com/50/50');

-- --------------------------------------------------------

--
-- Estrutura da tabela `estatutos`
--

CREATE TABLE IF NOT EXISTS `estatutos` (
  `id_estatutos` int(11) NOT NULL,
  `nome_estatuto` varchar(45) DEFAULT NULL,
  `descricao` varchar(300) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `estatutos`
--

INSERT INTO `estatutos` (`id_estatutos`, `nome_estatuto`, `descricao`) VALUES
(1, 'admin', ' tem acesso à área de administração e a informações privilegiadas e seguras'),
(2, 'socio', 'para possíveis informações/páginas que apenas os sócios tenham acesso\n'),
(3, 'normal', 'conta registada, permite aceder a endpoints cujo ID da conta equivale ao parâmetro do query respectivo ao ID.\n'),
(4, 'colaborador', 'pode criar eventos');

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos`
--

CREATE TABLE IF NOT EXISTS `eventos` (
  `id_eventos` int(11) NOT NULL,
  `nome_evento` varchar(65) NOT NULL,
  `data_registo_evento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_evento` datetime DEFAULT NULL,
  `preco` decimal(9,2) DEFAULT NULL,
  `desconto_socio` int(3) DEFAULT NULL,
  `descricao_short` varchar(200) DEFAULT NULL,
  `descricao` varchar(6000) DEFAULT NULL,
  `max_participantes` int(6) DEFAULT NULL,
  `min_participantes` int(6) DEFAULT NULL,
  `idade_minima` int(2) DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `localizacao_localizacao` int(11) DEFAULT NULL,
  `tipo_evento_id_tipo_evento` int(11) DEFAULT NULL,
  `facebook_id` bigint(255) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `eventos`
--

INSERT INTO `eventos` (`id_eventos`, `nome_evento`, `data_registo_evento`, `data_evento`, `preco`, `desconto_socio`, `descricao_short`, `descricao`, `max_participantes`, `min_participantes`, `idade_minima`, `data_fim`, `ativo`, `localizacao_localizacao`, `tipo_evento_id_tipo_evento`, `facebook_id`) VALUES
(22, 'Passeio Botânico', '2017-06-22 03:31:49', '2017-06-30 08:28:00', '3.00', 34, ' Passeio Botanico à Pateira de Frossos com a Dra. Rosa Pinho, botânica e curadora do herbário do Departamento de Biologia da Universidade de Aveiro.', 'Agora que a Primavera fez florescer tantas plantas, venha conhecer a diversidade botânica da Pateira de Frossos, na companhia de uma botânica de renome. Qual o nome de cada planta? Quais se podem comer? Que propriedade medicinal têm? Deixe-se maravilhar pelo fascinante mundo das plantas.', NULL, 5, NULL, '2017-06-30 18:00:00', 1, 68, 11, 1769492436713853),
(23, 'Votação online Orçamento Participativo Albergaria-a-Velha', '2017-06-22 10:33:54', '2017-06-20 00:00:00', NULL, NULL, 'Durante o mês de Junho estão a votação 20 projetos para o Orçamento Participativo do Município de Albergaria-a-Velha, em http://op.cm-albergaria.pt/\n', 'A Associação BioLiving apresentou a proposta #6\n"Valorização turística e educativa da Pateira de Frossos"\n\nhttp://op.cm-albergaria.pt/op/op-albergaria-a-velha/projetos/5911f218090d460015bfb4e8\n\nQualquer cidadão português, maior de 18 anos pode votar e ajudar-nos a valorizar a Pateira de Frossos.\n\nBasta aceder a http://op.cm-albergaria.pt/ - orçamento participativo - projetos a votação - e solicitar o seu perfil de "OP Albergaria" indicando data de nascimento e nº de BI. Quando receber o email com a aprovação do seu perfil "OP Albergaria", pode votar em 3 projetos. Não se esqueça de votar no nº 6! ;)\n\nObrigado!', NULL, NULL, NULL, '2017-06-30 00:00:00', 1, 69, 13, 1926998964233458),
(24, 'LousadaBlitz', '2017-06-22 10:41:37', '2017-06-22 08:00:00', NULL, NULL, 'Ser biólogo por um dia!', 'Na companhia de especialistas de diversas áreas, venha conhecer a fauna e a flora do município de Lousada, e as técnicas aplicadas no seu estudo. As atividades do 1.º Lousada Blitz são dirigidas a miúdos e graúdos, num roteiro científico e lúdico de natureza, em todo o concelho, de manhã à noite. O dia começa cedo com a anilhagem científica de aves, e termina com os morcegos. Pelo meio os insetos, os anfíbios e répteis, os mamíferos e as plantas. Acompanhe todas as ações, ou só algumas, seja biólogo por um dia!', NULL, NULL, NULL, '2017-06-22 20:00:00', 1, 69, 10, 1351347394918864),
(25, 'BioLousada - FieldSketching', '2017-06-22 10:44:03', '2017-06-17 09:30:00', NULL, NULL, 'Desenhar a natureza é uma outra forma de vê-la, de se conectar com ela. ', 'Ao desenhar, observa detalhes que nunca antes tinha visto. E ao fazê-lo, sentirá emoções que também desconhecia. Se pensa que não sabe desenhar, esta oficina é exatamente para si, pois irá aprender as técnicas e truques para preparar soberbos esboços de campo. Apaixone-se pela natureza, desenhando-a, com a bióloga e ilustradora DNick Falcão\r\n\r\nNota: a BioLiving fornecerá as canetas necessárias, mas os participantes deverão levar caderno ou bloco de campo.\r\n\r\nwww.cm-lousada.pt/pt/biolousada', NULL, NULL, NULL, '2017-06-17 12:30:00', 1, 69, 4, 256158051525662);

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos_fotos`
--

CREATE TABLE IF NOT EXISTS `eventos_fotos` (
  `id_eventos_fotos` int(11) NOT NULL,
  `link_foto` varchar(255) NOT NULL,
  `eventos_id_eventos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos_has_eventos_infos`
--

CREATE TABLE IF NOT EXISTS `eventos_has_eventos_infos` (
  `eventos_id_eventos` int(11) NOT NULL,
  `eventos_infos_id_extras` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `eventos_has_eventos_infos`
--

INSERT INTO `eventos_has_eventos_infos` (`eventos_id_eventos`, `eventos_infos_id_extras`) VALUES
(25, 5),
(22, 6),
(22, 7),
(23, 7);

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos_has_tags`
--

CREATE TABLE IF NOT EXISTS `eventos_has_tags` (
  `eventos_id_eventos` int(11) NOT NULL,
  `tags_id_tags` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `eventos_has_tags`
--

INSERT INTO `eventos_has_tags` (`eventos_id_eventos`, `tags_id_tags`) VALUES
(22, 3),
(22, 4),
(22, 8),
(24, 8);

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos_infos`
--

CREATE TABLE IF NOT EXISTS `eventos_infos` (
  `id_extras` int(11) NOT NULL,
  `titulo` varchar(75) NOT NULL,
  `descricao` varchar(300) DEFAULT NULL,
  `icons_id_icons` int(11) DEFAULT NULL,
  `descricao_pequena` varchar(45) DEFAULT NULL,
  `icons_pequeno_icons_id` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `eventos_infos`
--

INSERT INTO `eventos_infos` (`id_extras`, `titulo`, `descricao`, `icons_id_icons`, `descricao_pequena`, `icons_pequeno_icons_id`) VALUES
(5, 'Oferta de canetas', 'Os participantes deverão levar caderno ou bloco de campo.', 431, NULL, NULL),
(6, 'Oferta de lanche', 'Sandes mista e sumo oferecidos', 156, NULL, NULL),
(7, 'Oferta de t-shirt', 'Ganha uma t-shirt da bioliving!', 274, 'Basta participar', 311);

-- --------------------------------------------------------

--
-- Estrutura da tabela `fotos_eventos`
--

CREATE TABLE IF NOT EXISTS `fotos_eventos` (
  `id_foto` int(11) NOT NULL,
  `codigo_foto` varchar(255) NOT NULL,
  `eventos_id_eventos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `icons`
--

CREATE TABLE IF NOT EXISTS `icons` (
  `id_icons` int(11) NOT NULL,
  `classe` varchar(45) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=678 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `icons`
--

INSERT INTO `icons` (`id_icons`, `classe`) VALUES
(2, 'fa-500px'),
(3, 'fa-address-book'),
(4, 'fa-address-book-o'),
(5, 'fa-address-card'),
(6, 'fa-address-card-o'),
(7, 'fa-adjust'),
(8, 'fa-adn'),
(9, 'fa-align-center'),
(10, 'fa-align-justify'),
(11, 'fa-align-left'),
(12, 'fa-align-right'),
(13, 'fa-amazon'),
(14, 'fa-ambulance'),
(15, 'fa-american-sign-language-interpreting'),
(16, 'fa-anchor'),
(17, 'fa-android'),
(18, 'fa-angellist'),
(19, 'fa-angle-double-down'),
(20, 'fa-angle-double-left'),
(21, 'fa-angle-double-right'),
(22, 'fa-angle-double-up'),
(23, 'fa-angle-down'),
(24, 'fa-angle-left'),
(25, 'fa-angle-right'),
(26, 'fa-angle-up'),
(27, 'fa-apple'),
(28, 'fa-archive'),
(29, 'fa-area-chart'),
(30, 'fa-arrow-circle-down'),
(31, 'fa-arrow-circle-left'),
(32, 'fa-arrow-circle-o-down'),
(33, 'fa-arrow-circle-o-left'),
(34, 'fa-arrow-circle-o-right'),
(35, 'fa-arrow-circle-o-up'),
(36, 'fa-arrow-circle-right'),
(37, 'fa-arrow-circle-up'),
(38, 'fa-arrow-down'),
(39, 'fa-arrow-left'),
(40, 'fa-arrow-right'),
(41, 'fa-arrow-up'),
(42, 'fa-arrows'),
(43, 'fa-arrows-alt'),
(44, 'fa-arrows-h'),
(45, 'fa-arrows-v'),
(46, 'fa-assistive-listening-systems'),
(47, 'fa-asterisk'),
(48, 'fa-at'),
(49, 'fa-audio-description'),
(50, 'fa-backward'),
(51, 'fa-balance-scale'),
(52, 'fa-ban'),
(53, 'fa-bandcamp'),
(54, 'fa-bar-chart'),
(55, 'fa-barcode'),
(56, 'fa-bars'),
(57, 'fa-bath'),
(58, 'fa-battery-empty'),
(59, 'fa-battery-full'),
(60, 'fa-battery-half'),
(61, 'fa-battery-quarter'),
(62, 'fa-battery-three-quarters'),
(63, 'fa-bed'),
(64, 'fa-beer'),
(65, 'fa-behance'),
(66, 'fa-behance-square'),
(67, 'fa-bell'),
(68, 'fa-bell-o'),
(69, 'fa-bell-slash'),
(70, 'fa-bell-slash-o'),
(71, 'fa-bicycle'),
(72, 'fa-binoculars'),
(73, 'fa-birthday-cake'),
(74, 'fa-bitbucket'),
(75, 'fa-bitbucket-square'),
(76, 'fa-black-tie'),
(77, 'fa-blind'),
(78, 'fa-bluetooth'),
(79, 'fa-bluetooth-b'),
(80, 'fa-bold'),
(81, 'fa-bolt'),
(82, 'fa-bomb'),
(83, 'fa-book'),
(84, 'fa-bookmark'),
(85, 'fa-bookmark-o'),
(86, 'fa-braille'),
(87, 'fa-briefcase'),
(88, 'fa-btc'),
(89, 'fa-bug'),
(90, 'fa-building'),
(91, 'fa-building-o'),
(92, 'fa-bullhorn'),
(93, 'fa-bullseye'),
(94, 'fa-bus'),
(95, 'fa-buysellads'),
(96, 'fa-calculator'),
(97, 'fa-calendar'),
(98, 'fa-calendar-check-o'),
(99, 'fa-calendar-minus-o'),
(100, 'fa-calendar-o'),
(101, 'fa-calendar-plus-o'),
(102, 'fa-calendar-times-o'),
(103, 'fa-camera'),
(104, 'fa-camera-retro'),
(105, 'fa-car'),
(106, 'fa-caret-down'),
(107, 'fa-caret-left'),
(108, 'fa-caret-right'),
(109, 'fa-caret-square-o-down'),
(110, 'fa-caret-square-o-left'),
(111, 'fa-caret-square-o-right'),
(112, 'fa-caret-square-o-up'),
(113, 'fa-caret-up'),
(114, 'fa-cart-arrow-down'),
(115, 'fa-cart-plus'),
(116, 'fa-cc'),
(117, 'fa-cc-amex'),
(118, 'fa-cc-diners-club'),
(119, 'fa-cc-discover'),
(120, 'fa-cc-jcb'),
(121, 'fa-cc-mastercard'),
(122, 'fa-cc-paypal'),
(123, 'fa-cc-stripe'),
(124, 'fa-cc-visa'),
(125, 'fa-certificate'),
(126, 'fa-chain-broken'),
(127, 'fa-check'),
(128, 'fa-check-circle'),
(129, 'fa-check-circle-o'),
(130, 'fa-check-square'),
(131, 'fa-check-square-o'),
(132, 'fa-chevron-circle-down'),
(133, 'fa-chevron-circle-left'),
(134, 'fa-chevron-circle-right'),
(135, 'fa-chevron-circle-up'),
(136, 'fa-chevron-down'),
(137, 'fa-chevron-left'),
(138, 'fa-chevron-right'),
(139, 'fa-chevron-up'),
(140, 'fa-child'),
(141, 'fa-chrome'),
(142, 'fa-circle'),
(143, 'fa-circle-o'),
(144, 'fa-circle-o-notch'),
(145, 'fa-circle-thin'),
(146, 'fa-clipboard'),
(147, 'fa-clock-o'),
(148, 'fa-clone'),
(149, 'fa-cloud'),
(150, 'fa-cloud-download'),
(151, 'fa-cloud-upload'),
(152, 'fa-code'),
(153, 'fa-code-fork'),
(154, 'fa-codepen'),
(155, 'fa-codiepie'),
(156, 'fa-coffee'),
(157, 'fa-cog'),
(158, 'fa-cogs'),
(159, 'fa-columns'),
(160, 'fa-comment'),
(161, 'fa-comment-o'),
(162, 'fa-commenting'),
(163, 'fa-commenting-o'),
(164, 'fa-comments'),
(165, 'fa-comments-o'),
(166, 'fa-compass'),
(167, 'fa-compress'),
(168, 'fa-connectdevelop'),
(169, 'fa-contao'),
(170, 'fa-copyright'),
(171, 'fa-creative-commons'),
(172, 'fa-credit-card'),
(173, 'fa-credit-card-alt'),
(174, 'fa-crop'),
(175, 'fa-crosshairs'),
(176, 'fa-css3'),
(177, 'fa-cube'),
(178, 'fa-cubes'),
(179, 'fa-cutlery'),
(180, 'fa-dashcube'),
(181, 'fa-database'),
(182, 'fa-deaf'),
(183, 'fa-delicious'),
(184, 'fa-desktop'),
(185, 'fa-deviantart'),
(186, 'fa-diamond'),
(187, 'fa-digg'),
(188, 'fa-dot-circle-o'),
(189, 'fa-download'),
(190, 'fa-dribbble'),
(191, 'fa-dropbox'),
(192, 'fa-drupal'),
(193, 'fa-edge'),
(194, 'fa-eercast'),
(195, 'fa-eject'),
(196, 'fa-ellipsis-h'),
(197, 'fa-ellipsis-v'),
(198, 'fa-empire'),
(199, 'fa-envelope'),
(200, 'fa-envelope-o'),
(201, 'fa-envelope-open'),
(202, 'fa-envelope-open-o'),
(203, 'fa-envelope-square'),
(204, 'fa-envira'),
(205, 'fa-eraser'),
(206, 'fa-etsy'),
(207, 'fa-eur'),
(208, 'fa-exchange'),
(209, 'fa-exclamation'),
(210, 'fa-exclamation-circle'),
(211, 'fa-exclamation-triangle'),
(212, 'fa-expand'),
(213, 'fa-expeditedssl'),
(214, 'fa-external-link'),
(215, 'fa-external-link-square'),
(216, 'fa-eye'),
(217, 'fa-eye-slash'),
(218, 'fa-eyedropper'),
(219, 'fa-facebook'),
(220, 'fa-facebook-official'),
(221, 'fa-facebook-square'),
(222, 'fa-fast-backward'),
(223, 'fa-fast-forward'),
(224, 'fa-fax'),
(225, 'fa-female'),
(226, 'fa-fighter-jet'),
(227, 'fa-file'),
(228, 'fa-file-archive-o'),
(229, 'fa-file-audio-o'),
(230, 'fa-file-code-o'),
(231, 'fa-file-excel-o'),
(232, 'fa-file-image-o'),
(233, 'fa-file-o'),
(234, 'fa-file-pdf-o'),
(235, 'fa-file-powerpoint-o'),
(236, 'fa-file-text'),
(237, 'fa-file-text-o'),
(238, 'fa-file-video-o'),
(239, 'fa-file-word-o'),
(240, 'fa-files-o'),
(241, 'fa-film'),
(242, 'fa-filter'),
(243, 'fa-fire'),
(244, 'fa-fire-extinguisher'),
(245, 'fa-firefox'),
(246, 'fa-first-order'),
(247, 'fa-flag'),
(248, 'fa-flag-checkered'),
(249, 'fa-flag-o'),
(250, 'fa-flask'),
(251, 'fa-flickr'),
(252, 'fa-floppy-o'),
(253, 'fa-folder'),
(254, 'fa-folder-o'),
(255, 'fa-folder-open'),
(256, 'fa-folder-open-o'),
(257, 'fa-font'),
(258, 'fa-font-awesome'),
(259, 'fa-fonticons'),
(260, 'fa-fort-awesome'),
(261, 'fa-forumbee'),
(262, 'fa-forward'),
(263, 'fa-foursquare'),
(264, 'fa-free-code-camp'),
(265, 'fa-frown-o'),
(266, 'fa-futbol-o'),
(267, 'fa-gamepad'),
(268, 'fa-gavel'),
(269, 'fa-gbp'),
(270, 'fa-genderless'),
(271, 'fa-get-pocket'),
(272, 'fa-gg'),
(273, 'fa-gg-circle'),
(274, 'fa-gift'),
(275, 'fa-git'),
(276, 'fa-git-square'),
(277, 'fa-github'),
(278, 'fa-github-alt'),
(279, 'fa-github-square'),
(280, 'fa-gitlab'),
(281, 'fa-glass'),
(282, 'fa-glide'),
(283, 'fa-glide-g'),
(284, 'fa-globe'),
(285, 'fa-google'),
(286, 'fa-google-plus'),
(287, 'fa-google-plus-official'),
(288, 'fa-google-plus-square'),
(289, 'fa-google-wallet'),
(290, 'fa-graduation-cap'),
(291, 'fa-gratipay'),
(292, 'fa-grav'),
(293, 'fa-h-square'),
(294, 'fa-hacker-news'),
(295, 'fa-hand-lizard-o'),
(296, 'fa-hand-o-down'),
(297, 'fa-hand-o-left'),
(298, 'fa-hand-o-right'),
(299, 'fa-hand-o-up'),
(300, 'fa-hand-paper-o'),
(301, 'fa-hand-peace-o'),
(302, 'fa-hand-pointer-o'),
(303, 'fa-hand-rock-o'),
(304, 'fa-hand-scissors-o'),
(305, 'fa-hand-spock-o'),
(306, 'fa-handshake-o'),
(307, 'fa-hashtag'),
(308, 'fa-hdd-o'),
(309, 'fa-header'),
(310, 'fa-headphones'),
(311, 'fa-heart'),
(312, 'fa-heart-o'),
(313, 'fa-heartbeat'),
(314, 'fa-history'),
(315, 'fa-home'),
(316, 'fa-hospital-o'),
(317, 'fa-hourglass'),
(318, 'fa-hourglass-end'),
(319, 'fa-hourglass-half'),
(320, 'fa-hourglass-o'),
(321, 'fa-hourglass-start'),
(322, 'fa-houzz'),
(323, 'fa-html5'),
(324, 'fa-i-cursor'),
(325, 'fa-id-badge'),
(326, 'fa-id-card'),
(327, 'fa-id-card-o'),
(328, 'fa-ils'),
(329, 'fa-imdb'),
(330, 'fa-inbox'),
(331, 'fa-indent'),
(332, 'fa-industry'),
(333, 'fa-info'),
(334, 'fa-info-circle'),
(335, 'fa-inr'),
(336, 'fa-instagram'),
(337, 'fa-internet-explorer'),
(338, 'fa-ioxhost'),
(339, 'fa-italic'),
(340, 'fa-joomla'),
(341, 'fa-jpy'),
(342, 'fa-jsfiddle'),
(343, 'fa-key'),
(344, 'fa-keyboard-o'),
(345, 'fa-krw'),
(346, 'fa-language'),
(347, 'fa-laptop'),
(348, 'fa-lastfm'),
(349, 'fa-lastfm-square'),
(350, 'fa-leaf'),
(351, 'fa-leanpub'),
(352, 'fa-lemon-o'),
(353, 'fa-level-down'),
(354, 'fa-level-up'),
(355, 'fa-life-ring'),
(356, 'fa-lightbulb-o'),
(357, 'fa-line-chart'),
(358, 'fa-link'),
(359, 'fa-linkedin'),
(360, 'fa-linkedin-square'),
(361, 'fa-linode'),
(362, 'fa-linux'),
(363, 'fa-list'),
(364, 'fa-list-alt'),
(365, 'fa-list-ol'),
(366, 'fa-list-ul'),
(367, 'fa-location-arrow'),
(368, 'fa-lock'),
(369, 'fa-long-arrow-down'),
(370, 'fa-long-arrow-left'),
(371, 'fa-long-arrow-right'),
(372, 'fa-long-arrow-up'),
(373, 'fa-low-vision'),
(374, 'fa-magic'),
(375, 'fa-magnet'),
(376, 'fa-male'),
(377, 'fa-map'),
(378, 'fa-map-marker'),
(379, 'fa-map-o'),
(380, 'fa-map-pin'),
(381, 'fa-map-signs'),
(382, 'fa-mars'),
(383, 'fa-mars-double'),
(384, 'fa-mars-stroke'),
(385, 'fa-mars-stroke-h'),
(386, 'fa-mars-stroke-v'),
(387, 'fa-maxcdn'),
(388, 'fa-meanpath'),
(389, 'fa-medium'),
(390, 'fa-medkit'),
(391, 'fa-meetup'),
(392, 'fa-meh-o'),
(393, 'fa-mercury'),
(394, 'fa-microchip'),
(395, 'fa-microphone'),
(396, 'fa-microphone-slash'),
(397, 'fa-minus'),
(398, 'fa-minus-circle'),
(399, 'fa-minus-square'),
(400, 'fa-minus-square-o'),
(401, 'fa-mixcloud'),
(402, 'fa-mobile'),
(403, 'fa-modx'),
(404, 'fa-money'),
(405, 'fa-moon-o'),
(406, 'fa-motorcycle'),
(407, 'fa-mouse-pointer'),
(408, 'fa-music'),
(409, 'fa-neuter'),
(410, 'fa-newspaper-o'),
(411, 'fa-object-group'),
(412, 'fa-object-ungroup'),
(413, 'fa-odnoklassniki'),
(414, 'fa-odnoklassniki-square'),
(415, 'fa-opencart'),
(416, 'fa-openid'),
(417, 'fa-opera'),
(418, 'fa-optin-monster'),
(419, 'fa-outdent'),
(420, 'fa-pagelines'),
(421, 'fa-paint-brush'),
(422, 'fa-paper-plane'),
(423, 'fa-paper-plane-o'),
(424, 'fa-paperclip'),
(425, 'fa-paragraph'),
(426, 'fa-pause'),
(427, 'fa-pause-circle'),
(428, 'fa-pause-circle-o'),
(429, 'fa-paw'),
(430, 'fa-paypal'),
(431, 'fa-pencil'),
(432, 'fa-pencil-square'),
(433, 'fa-pencil-square-o'),
(434, 'fa-percent'),
(435, 'fa-phone'),
(436, 'fa-phone-square'),
(437, 'fa-picture-o'),
(438, 'fa-pie-chart'),
(439, 'fa-pied-piper'),
(440, 'fa-pied-piper-alt'),
(441, 'fa-pied-piper-pp'),
(442, 'fa-pinterest'),
(443, 'fa-pinterest-p'),
(444, 'fa-pinterest-square'),
(445, 'fa-plane'),
(446, 'fa-play'),
(447, 'fa-play-circle'),
(448, 'fa-play-circle-o'),
(449, 'fa-plug'),
(450, 'fa-plus'),
(451, 'fa-plus-circle'),
(452, 'fa-plus-square'),
(453, 'fa-plus-square-o'),
(454, 'fa-podcast'),
(455, 'fa-power-off'),
(456, 'fa-print'),
(457, 'fa-product-hunt'),
(458, 'fa-puzzle-piece'),
(459, 'fa-qq'),
(460, 'fa-qrcode'),
(461, 'fa-question'),
(462, 'fa-question-circle'),
(463, 'fa-question-circle-o'),
(464, 'fa-quora'),
(465, 'fa-quote-left'),
(466, 'fa-quote-right'),
(467, 'fa-random'),
(468, 'fa-ravelry'),
(469, 'fa-rebel'),
(470, 'fa-recycle'),
(471, 'fa-reddit'),
(472, 'fa-reddit-alien'),
(473, 'fa-reddit-square'),
(474, 'fa-refresh'),
(475, 'fa-registered'),
(476, 'fa-renren'),
(477, 'fa-repeat'),
(478, 'fa-reply'),
(479, 'fa-reply-all'),
(480, 'fa-retweet'),
(481, 'fa-road'),
(482, 'fa-rocket'),
(483, 'fa-rss'),
(484, 'fa-rss-square'),
(485, 'fa-rub'),
(486, 'fa-safari'),
(487, 'fa-scissors'),
(488, 'fa-scribd'),
(489, 'fa-search'),
(490, 'fa-search-minus'),
(491, 'fa-search-plus'),
(492, 'fa-sellsy'),
(493, 'fa-server'),
(494, 'fa-share'),
(495, 'fa-share-alt'),
(496, 'fa-share-alt-square'),
(497, 'fa-share-square'),
(498, 'fa-share-square-o'),
(499, 'fa-shield'),
(500, 'fa-ship'),
(501, 'fa-shirtsinbulk'),
(502, 'fa-shopping-bag'),
(503, 'fa-shopping-basket'),
(504, 'fa-shopping-cart'),
(505, 'fa-shower'),
(506, 'fa-sign-in'),
(507, 'fa-sign-language'),
(508, 'fa-sign-out'),
(509, 'fa-signal'),
(510, 'fa-simplybuilt'),
(511, 'fa-sitemap'),
(512, 'fa-skyatlas'),
(513, 'fa-skype'),
(514, 'fa-slack'),
(515, 'fa-sliders'),
(516, 'fa-slideshare'),
(517, 'fa-smile-o'),
(518, 'fa-snapchat'),
(519, 'fa-snapchat-ghost'),
(520, 'fa-snapchat-square'),
(521, 'fa-snowflake-o'),
(522, 'fa-sort'),
(523, 'fa-sort-alpha-asc'),
(524, 'fa-sort-alpha-desc'),
(525, 'fa-sort-amount-asc'),
(526, 'fa-sort-amount-desc'),
(527, 'fa-sort-asc'),
(528, 'fa-sort-desc'),
(529, 'fa-sort-numeric-asc'),
(530, 'fa-sort-numeric-desc'),
(531, 'fa-soundcloud'),
(532, 'fa-space-shuttle'),
(533, 'fa-spinner'),
(534, 'fa-spoon'),
(535, 'fa-spotify'),
(536, 'fa-square'),
(537, 'fa-square-o'),
(538, 'fa-stack-exchange'),
(539, 'fa-stack-overflow'),
(540, 'fa-star'),
(541, 'fa-star-half'),
(542, 'fa-star-half-o'),
(543, 'fa-star-o'),
(544, 'fa-steam'),
(545, 'fa-steam-square'),
(546, 'fa-step-backward'),
(547, 'fa-step-forward'),
(548, 'fa-stethoscope'),
(549, 'fa-sticky-note'),
(550, 'fa-sticky-note-o'),
(551, 'fa-stop'),
(552, 'fa-stop-circle'),
(553, 'fa-stop-circle-o'),
(554, 'fa-street-view'),
(555, 'fa-strikethrough'),
(556, 'fa-stumbleupon'),
(557, 'fa-stumbleupon-circle'),
(558, 'fa-subscript'),
(559, 'fa-subway'),
(560, 'fa-suitcase'),
(561, 'fa-sun-o'),
(562, 'fa-superpowers'),
(563, 'fa-superscript'),
(564, 'fa-table'),
(565, 'fa-tablet'),
(566, 'fa-tachometer'),
(567, 'fa-tag'),
(568, 'fa-tags'),
(569, 'fa-tasks'),
(570, 'fa-taxi'),
(571, 'fa-telegram'),
(572, 'fa-television'),
(573, 'fa-tencent-weibo'),
(574, 'fa-terminal'),
(575, 'fa-text-height'),
(576, 'fa-text-width'),
(577, 'fa-th'),
(578, 'fa-th-large'),
(579, 'fa-th-list'),
(580, 'fa-themeisle'),
(581, 'fa-thermometer-empty'),
(582, 'fa-thermometer-full'),
(583, 'fa-thermometer-half'),
(584, 'fa-thermometer-quarter'),
(585, 'fa-thermometer-three-quarters'),
(586, 'fa-thumb-tack'),
(587, 'fa-thumbs-down'),
(588, 'fa-thumbs-o-down'),
(589, 'fa-thumbs-o-up'),
(590, 'fa-thumbs-up'),
(591, 'fa-ticket'),
(592, 'fa-times'),
(593, 'fa-times-circle'),
(594, 'fa-times-circle-o'),
(595, 'fa-tint'),
(596, 'fa-toggle-off'),
(597, 'fa-toggle-on'),
(598, 'fa-trademark'),
(599, 'fa-train'),
(600, 'fa-transgender'),
(601, 'fa-transgender-alt'),
(602, 'fa-trash'),
(603, 'fa-trash-o'),
(604, 'fa-tree'),
(605, 'fa-trello'),
(606, 'fa-tripadvisor'),
(607, 'fa-trophy'),
(608, 'fa-truck'),
(609, 'fa-try'),
(610, 'fa-tty'),
(611, 'fa-tumblr'),
(612, 'fa-tumblr-square'),
(613, 'fa-twitch'),
(614, 'fa-twitter'),
(615, 'fa-twitter-square'),
(616, 'fa-umbrella'),
(617, 'fa-underline'),
(618, 'fa-undo'),
(619, 'fa-universal-access'),
(620, 'fa-university'),
(621, 'fa-unlock'),
(622, 'fa-unlock-alt'),
(623, 'fa-upload'),
(624, 'fa-usb'),
(625, 'fa-usd'),
(626, 'fa-user'),
(627, 'fa-user-circle'),
(628, 'fa-user-circle-o'),
(629, 'fa-user-md'),
(630, 'fa-user-o'),
(631, 'fa-user-plus'),
(632, 'fa-user-secret'),
(633, 'fa-user-times'),
(634, 'fa-users'),
(635, 'fa-venus'),
(636, 'fa-venus-double'),
(637, 'fa-venus-mars'),
(638, 'fa-viacoin'),
(639, 'fa-viadeo'),
(640, 'fa-viadeo-square'),
(641, 'fa-video-camera'),
(642, 'fa-vimeo'),
(643, 'fa-vimeo-square'),
(644, 'fa-vine'),
(645, 'fa-vk'),
(646, 'fa-volume-control-phone'),
(647, 'fa-volume-down'),
(648, 'fa-volume-off'),
(649, 'fa-volume-up'),
(650, 'fa-weibo'),
(651, 'fa-weixin'),
(652, 'fa-whatsapp'),
(653, 'fa-wheelchair'),
(654, 'fa-wheelchair-alt'),
(655, 'fa-wifi'),
(656, 'fa-wikipedia-w'),
(657, 'fa-window-close'),
(658, 'fa-window-close-o'),
(659, 'fa-window-maximize'),
(660, 'fa-window-minimize'),
(661, 'fa-window-restore'),
(662, 'fa-windows'),
(663, 'fa-wordpress'),
(664, 'fa-wpbeginner'),
(665, 'fa-wpexplorer'),
(666, 'fa-wpforms'),
(667, 'fa-wrench'),
(668, 'fa-xing'),
(669, 'fa-xing-square'),
(670, 'fa-y-combinator'),
(671, 'fa-yahoo'),
(672, 'fa-yelp'),
(673, 'fa-yoast'),
(674, 'fa-youtube'),
(675, 'fa-youtube-play'),
(676, 'fa-youtube-square');

-- --------------------------------------------------------

--
-- Estrutura da tabela `interesses`
--

CREATE TABLE IF NOT EXISTS `interesses` (
  `eventos_id_eventos` int(11) NOT NULL,
  `utilizadores_id_utilizadores` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `interesses`
--

INSERT INTO `interesses` (`eventos_id_eventos`, `utilizadores_id_utilizadores`) VALUES
(22, 45),
(23, 45);

-- --------------------------------------------------------

--
-- Estrutura da tabela `localizacao`
--

CREATE TABLE IF NOT EXISTS `localizacao` (
  `localizacao` int(11) NOT NULL,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `nome` varchar(155) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `localizacao`
--

INSERT INTO `localizacao` (`localizacao`, `lat`, `lng`, `nome`) VALUES
(68, 40.660702, -8.561897, 'Pateira de Frossos'),
(69, 41.305199, -8.269640, 'Lousada'),
(70, 40.630302, -8.657506, 'Campus Universitário de Aveiro'),
(71, 40.512527, -8.311495, 'Anadia');

-- --------------------------------------------------------

--
-- Estrutura da tabela `participantes`
--

CREATE TABLE IF NOT EXISTS `participantes` (
  `eventos_id_eventos` int(11) NOT NULL,
  `utilizadores_id_utilizadores` int(11) NOT NULL,
  `data_inscricao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `participantes`
--

INSERT INTO `participantes` (`eventos_id_eventos`, `utilizadores_id_utilizadores`, `data_inscricao`) VALUES
(22, 44, '2017-06-23 01:00:27'),
(22, 45, '2017-06-22 10:52:26'),
(23, 44, '2017-06-22 19:06:05'),
(23, 45, '2017-06-22 10:52:26');

-- --------------------------------------------------------

--
-- Estrutura da tabela `participantes_colaboradores`
--

CREATE TABLE IF NOT EXISTS `participantes_colaboradores` (
  `eventos_id_eventos` int(11) NOT NULL,
  `colaboradores_id_colaboradores` int(11) NOT NULL,
  `tipo_colaborador` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `participantes_colaboradores`
--

INSERT INTO `participantes_colaboradores` (`eventos_id_eventos`, `colaboradores_id_colaboradores`, `tipo_colaborador`) VALUES
(22, 1, 'Instrutora'),
(22, 2, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `passwords_blacklist`
--

CREATE TABLE IF NOT EXISTS `passwords_blacklist` (
  `id_passwords` int(11) NOT NULL,
  `passwords` varchar(255) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `passwords_blacklist`
--

INSERT INTO `passwords_blacklist` (`id_passwords`, `passwords`) VALUES
(1, 'antonioamote'),
(2, '111111'),
(3, 'carolina'),
(4, 'madalena'),
(5, '12345'),
(6, 'carpediem'),
(7, 'madeira'),
(8, '123456'),
(9, 'casanova'),
(10, 'madrid'),
(11, '1234567'),
(12, 'cascais'),
(13, 'mafalda'),
(14, '12345678'),
(15, 'catarina'),
(16, 'margarida'),
(17, '123456789'),
(18, 'chocolate'),
(19, 'maria'),
(20, '131313'),
(21, 'claudia'),
(22, 'mariana'),
(23, '1qaz2wsx'),
(24, 'coimbra'),
(25, 'martinha'),
(26, '666666'),
(27, 'cristina'),
(28, 'matilde'),
(29, '696969'),
(30, 'duarte'),
(31, 'miguel'),
(32, 'afonso'),
(33, 'economia'),
(34, 'monica'),
(35, 'alexandra'),
(36, 'eduardo'),
(37, 'oliveira'),
(38, 'alexandre'),
(39, 'emprego'),
(40, 'papoila'),
(41, 'algarve'),
(42, 'estrela'),
(43, 'patricia'),
(44, 'almada'),
(45, 'estrelas'),
(46, 'pipoca'),
(47, 'aninhas'),
(48, 'fcporto'),
(49, 'portugal'),
(50, 'antonio'),
(51, 'felicidade'),
(52, 'qwerty'),
(53, 'baltazar'),
(54, 'ferrari'),
(55, 'raquel'),
(56, 'banana'),
(57, 'ferreira'),
(58, 'ricardo'),
(59, 'barcelona'),
(60, 'filipa'),
(61, 'ritinha'),
(62, 'beatriz'),
(63, 'filipe'),
(64, 'rodrigo'),
(65, 'belenenses'),
(66, 'francisca'),
(67, 'salvador'),
(68, 'benedita'),
(69, 'francisco'),
(70, 'sandra'),
(71, 'benfica'),
(72, 'frederico'),
(73, 'sebastiao'),
(74, 'benfica1'),
(75, 'golfinho'),
(76, 'slbenfica'),
(77, 'bernardo'),
(78, 'goncalo'),
(79, 'sporting'),
(80, 'borboleta'),
(81, 'helena'),
(82, 'sucesso'),
(83, 'brasil'),
(84, 'henrique'),
(85, 'sunshine'),
(86, 'briosa'),
(87, 'joaninha'),
(88, 'susana'),
(89, 'caralho'),
(90, 'liberdade'),
(91, 'teresa'),
(92, 'careca'),
(93, 'lisboa'),
(94, 'teresinha'),
(95, 'carlos'),
(96, 'londres'),
(97, 'tobias'),
(98, 'carlota'),
(99, 'lourenco'),
(100, 'vermelho'),
(101, 'segredo'),
(102, '********'),
(103, '23456'),
(104, '12345678912345678'),
(105, '111111'),
(106, '1234567890'),
(107, '1234567'),
(108, 'password'),
(109, '123123'),
(110, '987654321'),
(111, 'qwertyuiop'),
(112, 'mynoob'),
(113, '123321'),
(114, '666666'),
(115, 'Batcskd2w'),
(116, '7777777'),
(117, '1q2w3e4r'),
(118, '654321'),
(119, '555555'),
(120, '3rjs1la7qe'),
(121, 'google'),
(122, '1q2w3e4r5t'),
(123, '123qwe'),
(124, 'zxcvbnm'),
(125, '1q2w3e'),
(126, 'senha'),
(127, 'superman'),
(128, '666666'),
(129, 'password1'),
(130, 'asd123'),
(131, '696969'),
(132, 'qwerty123'),
(133, '12345678'),
(134, '123456789'),
(135, '1234567890'),
(136, 'fodasse'),
(137, 'bioliving'),
(138, 'bioliving123');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id_tags` int(11) NOT NULL,
  `tag_nome` varchar(45) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tags`
--

INSERT INTO `tags` (`id_tags`, `tag_nome`) VALUES
(3, 'passeiobotanico'),
(4, 'haconversa'),
(8, 'lousadablitz'),
(10, 'biolousada');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipo_evento`
--

CREATE TABLE IF NOT EXISTS `tipo_evento` (
  `id_tipo_evento` int(11) NOT NULL,
  `nome_tipo_evento` varchar(75) NOT NULL,
  `icons_id` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tipo_evento`
--

INSERT INTO `tipo_evento` (`id_tipo_evento`, `nome_tipo_evento`, `icons_id`) VALUES
(1, 'Há Conversa', 634),
(2, 'Cultural', 290),
(3, 'Voluntariado', 140),
(4, 'Field Sketching', 421),
(6, 'Palestra', 290),
(9, 'Plantação', 604),
(10, 'Oficina', 290),
(11, 'Passeio', 77),
(12, 'Passeio de Bicicleta', 71),
(13, 'Votação', 620);

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE IF NOT EXISTS `utilizadores` (
  `id_utilizadores` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `apelido` varchar(50) NOT NULL,
  `genero` int(1) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `data_registo_user` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` text,
  `sobre` text,
  `sobre_mini` varchar(30) DEFAULT NULL,
  `telemovel` int(9) DEFAULT NULL,
  `localizacao_id_localizacao` int(11) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `estatutos_id_estatutos` int(11) NOT NULL DEFAULT '3'
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id_utilizadores`, `nome`, `apelido`, `genero`, `data_nascimento`, `data_registo_user`, `email`, `password`, `foto`, `sobre`, `sobre_mini`, `telemovel`, `localizacao_id_localizacao`, `ativo`, `estatutos_id_estatutos`) VALUES
(19, 'Joao', 'Manel', 1, '1996-03-08', '2017-06-04 16:02:28', 'joaosilva2escola@gmail.com', '$2y$10$7/Tz3rgJG3HYpSbn7GwAu.cILhSiYu7gbXp7AbEjdfyppcBGNvQQi', NULL, 'Estudante', 'Web devloper', NULL, NULL, 1, 1),
(20, 'Antonio', 'Santos', NULL, '1996-06-14', '2017-06-04 20:07:29', 'manelito@gmail.com', '$2y$10$XgcKBeP0kn0Q8xkT27v7we5A.czipRsp/TfSChPILnDG.CQfDPuYy', NULL, NULL, NULL, NULL, NULL, 0, 1),
(22, 'Nome', 'Apelido', NULL, '1990-03-08', '2017-06-04 22:36:44', 'admin@ua.pt', '$2y$10$9Lu6Cp8Wikv3Fu06vlNvi.HOGd7I6bc6QKr2mu4EnnLpupubOJeJW', NULL, '', '', NULL, NULL, 1, 1),
(23, 'NomalNome', 'NormalApelido', NULL, '1990-12-03', '2017-06-04 22:38:43', 'normal@ua.pt', '$2y$10$MvvQKn0HBKDmIwCVtSxZF.FMDtGNO0xYOsVD8CXOzf4SRz9QJ9U5y', NULL, NULL, NULL, NULL, NULL, 1, 3),
(24, 'Diogo', 'Duarte', NULL, '2000-04-05', '2017-06-04 23:27:01', 'diogo@ua.pt', '$2y$10$MvvQKn0HBKDmIwCVtSxZF.FMDtGNO0xYOsVD8CXOzf4SRz9QJ9U5y', NULL, NULL, NULL, NULL, NULL, 1, 3),
(26, 'Igor', 'Sarcuza', NULL, '2000-06-04', '2017-06-04 23:28:32', 'igor@ua.pt', '$2y$10$MvvQKn0HBKDmIwCVtSxZF.FMDtGNO0xYOsVD8CXOzf4SRz9QJ9U5y', NULL, NULL, NULL, NULL, NULL, 1, 2),
(28, 'Diana', 'Rocha', NULL, '1996-05-19', '2017-06-05 10:35:55', 'dianarocha@ua.pt', '$2y$10$AMDSHxixdL/SGakwFapSOeSxYIu3YcT3HnL2ET8.8NdjGVoiGJJMW', NULL, NULL, NULL, NULL, NULL, 1, 3),
(29, 'Alberta', 'alforreca', 2, '1996-06-07', '2017-06-07 02:01:30', 'maria@bioliving.pt', '$2y$10$MvvQKn0HBKDmIwCVtSxZF.FMDtGNO0xYOsVD8CXOzf4SRz9QJ9U5y', 'https://pbs.twimg.com/profile_images/721089446448390146/sTS4_QU1.jpg', 'Maria. One name that can change your life forever. A Maria, is kind sweet and amazing. You think about her every time you wake up, and when you go to sleep she stays in your dreams. She so delicate, you won''t ever want to hurt her. But I have, yet she still treats me right. She forgives and loves. Maria is the true defintion of love. She makes me fall in love every time I talk to her. Although she doesn''t want to fall for me, at times it seems like we have the moments where she really loves me. She is truly an amazing person and I wish one day I can have her heart again.', 'Sweet Person', 914295785, NULL, 1, 3),
(43, 'Manelino', 'Santos', NULL, NULL, '2017-06-16 09:37:06', 'manelinoo@ua.pt', '$2y$10$PYverooN4J29vUFMjah7DukSLa37kQuWzvxrbOfAY8daWbzow9dlK', NULL, NULL, NULL, NULL, NULL, 1, 3),
(44, 'henrique', 'silva', NULL, NULL, '2017-06-16 23:37:24', 'joaohenrique@ua.pt', '$2y$10$jl2yfMhvvgSctVZ4/EPSweP0fUkvOHug8PrqpzWYDu.hzxM1QJuv6', NULL, NULL, NULL, NULL, NULL, 1, 3),
(45, 'Antonio', 'Santos', NULL, NULL, '2017-06-17 22:01:46', 'antoniojps@ua.pt', '$2y$10$pOlc6Q6sHs50duheIh08semKy0zKDKuhzVZ5QkTxLmCGJ7fODu3b.', NULL, NULL, NULL, NULL, 71, 1, 1),
(46, 'Utilizador', 'Normal', NULL, NULL, '2017-06-19 03:24:50', 'normal@normal.com', '$2y$10$ArtVCuN6vg/v7qi04QNz3ugZR21UUS.7boqM.o4utYQMKHOqmjdJS', NULL, NULL, NULL, NULL, NULL, 1, 3),
(47, 'henrique', 'silva', NULL, NULL, '2017-06-20 16:25:16', 'joaohenrique1@ua.pt', '$2y$10$IjzIWVheYjMV5RizL90QpeflnK9gYGJGU/MiEZWBvky8tV2sHYy1q', NULL, NULL, NULL, NULL, NULL, 1, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores_tokens`
--

CREATE TABLE IF NOT EXISTS `utilizadores_tokens` (
  `id` varchar(50) NOT NULL,
  `utilizadores_id_utilizadores` int(11) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `refresh_token` text CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `ativo` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `utilizadores_tokens`
--

INSERT INTO `utilizadores_tokens` (`id`, `utilizadores_id_utilizadores`, `data_criacao`, `refresh_token`, `ativo`) VALUES
('004c9b5d91efde95fc5529c5311730b3', 45, '2017-06-18 03:55:36', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkwMjEzOCwiaWF0IjoxNDk3NzU4MTM4LCJqdGkiOiIwMDRjOWI1ZDkxZWZkZTk1ZmM1NTI5YzUzMTE3MzBiMyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.Sko6gmK5-HCafnZzQVMMnocN7SwFLxFr4pck5ojOul0', 0),
('037568cbe413b1205b7b694537e70393', 45, '2017-06-19 04:44:03', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5MTQ0MiwiaWF0IjoxNDk3ODQ3NDQyLCJqdGkiOiIwMzc1NjhjYmU0MTNiMTIwNWI3YjY5NDUzN2U3MDM5MyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.xwvW4pTcicsqWjPl8DFNG4vn7az6Ket27RffvlufOVo', 0),
('0375b5541a70be704112dce66971a537', 45, '2017-06-19 05:36:26', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDU4NSwiaWF0IjoxNDk3ODUwNTg1LCJqdGkiOiIwMzc1YjU1NDFhNzBiZTcwNDExMmRjZTY2OTcxYTUzNyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.7cZv4V8bnF1hJ0kILpc9g9eId9aEnPpLuFj2DMvLUpE', 0),
('041b5f6c71e5df4913442680de3f6aa6', 45, '2017-06-18 08:53:26', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkyMDAwNywiaWF0IjoxNDk3Nzc2MDA3LCJqdGkiOiIwNDFiNWY2YzcxZTVkZjQ5MTM0NDI2ODBkZTNmNmFhNiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.6L_UxFaB2LRWB9MiLxuAppTME3On_7Ri6gCq7WHdjpY', 1),
('056f54b8df6bbfc2d7c3c50a4b4bde2f', 45, '2017-06-19 12:04:59', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzg5OCwiaWF0IjoxNDk3ODczODk4LCJqdGkiOiIwNTZmNTRiOGRmNmJiZmMyZDdjM2M1MGE0YjRiZGUyZiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.dWI7xnduEtkFstLn6N9PCsDAk4m4fxHisBTsiTuG3JA', 0),
('078d213bf52eabaf074041f6ddb120b4', 45, '2017-06-19 12:04:41', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzg4MSwiaWF0IjoxNDk3ODczODgxLCJqdGkiOiIwNzhkMjEzYmY1MmVhYmFmMDc0MDQxZjZkZGIxMjBiNCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.rtppoGZYHY80sFYBUZuftUYF1J-encoCwawzs_NaumI', 0),
('0b74f24ea3e5b669f11b3ac0e4f29f27', 45, '2017-06-18 08:49:44', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxOTc4NSwiaWF0IjoxNDk3Nzc1Nzg1LCJqdGkiOiIwYjc0ZjI0ZWEzZTViNjY5ZjExYjNhYzBlNGYyOWYyNyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.XNnptmT3RkwfR7JwKjKBklFu3rZnTdi7u9lq_9jGit4', 1),
('0bac8c4d1d772ff5b2b7382879d387fd', 46, '2017-06-19 03:24:50', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4NjY5MCwiaWF0IjoxNDk3ODQyNjkwLCJqdGkiOiIwYmFjOGM0ZDFkNzcyZmY1YjJiNzM4Mjg3OWQzODdmZCIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.tRouV1K0t5tSe7UrGWmxWYVAT8tgDoha3IgdVhUARiY', 0),
('0bef6a38bb1650effaef7f10924f36a3', 45, '2017-06-19 06:44:48', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5ODY4NywiaWF0IjoxNDk3ODU0Njg3LCJqdGkiOiIwYmVmNmEzOGJiMTY1MGVmZmFlZjdmMTA5MjRmMzZhMyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.lS1ZuGI-66CHIBvJpHB0nQ5KbsaJ96Rgne0v0_gSH8s', 0),
('0c8f4f6dab9fa071c564f22dde412505', 45, '2017-06-19 01:40:05', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4MDQwNSwiaWF0IjoxNDk3ODM2NDA1LCJqdGkiOiIwYzhmNGY2ZGFiOWZhMDcxYzU2NGYyMmRkZTQxMjUwNSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.Pvkl2s5V_KzId_g4JoFsD8dORo9-_oXyqgE3ohz-v8c', 0),
('0e38b0f3d61fc390d8324072f9c2ae0f', 45, '2017-06-21 13:30:54', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDE5NTg1NiwiaWF0IjoxNDk4MDUxODU2LCJqdGkiOiIwZTM4YjBmM2Q2MWZjMzkwZDgzMjQwNzJmOWMyYWUwZiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.ku9B6JwmMNs5VwFrFAg1d7_ua9vuGyMZs3r6Q7FDbd8', 0),
('0e3c60c3e503dad0c7b90c401b5053da', 45, '2017-06-19 02:40:27', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4NDAyNywiaWF0IjoxNDk3ODQwMDI3LCJqdGkiOiIwZTNjNjBjM2U1MDNkYWQwYzdiOTBjNDAxYjUwNTNkYSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.K7m5DkttH3Kr9rYv_idzUaWMZh7T-oUM1qG3NcbZx2w', 0),
('1505eb6bdaf6436fe66c98261d3d01bb', 46, '2017-06-21 15:02:09', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDIwMTMzMSwiaWF0IjoxNDk4MDU3MzMxLCJqdGkiOiIxNTA1ZWI2YmRhZjY0MzZmZTY2Yzk4MjYxZDNkMDFiYiIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.xoa9f8Qh8jeiL6r00wxQeo4eG2nAk4C7xDVeh3W6ME4', 0),
('166a38ad704e55e0d375d9a18f371099', 45, '2017-06-19 06:55:12', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTMxMSwiaWF0IjoxNDk3ODU1MzExLCJqdGkiOiIxNjZhMzhhZDcwNGU1NWUwZDM3NWQ5YTE4ZjM3MTA5OSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.8nVerTftyGkv8XzW77fhjQrw7pDvvDZ-B52vBl7Rsfs', 0),
('17fd9399e01b96bbf9b457c3affc0edb', 46, '2017-06-19 05:40:07', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDgwNiwiaWF0IjoxNDk3ODUwODA2LCJqdGkiOiIxN2ZkOTM5OWUwMWI5NmJiZjliNDU3YzNhZmZjMGVkYiIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.bC9xpcqUIoRwsrwwWkN_tqNKV2jfYA_L0tGjPO-T9ns', 0),
('1ae736e38e595e3b74b625dc24cdb650', 45, '2017-06-19 05:43:55', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NTAzNCwiaWF0IjoxNDk3ODUxMDM0LCJqdGkiOiIxYWU3MzZlMzhlNTk1ZTNiNzRiNjI1ZGMyNGNkYjY1MCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.UYdZIEGf2Df-Ocf7c0DZdWV4Q4jU7g74JsY4aQ9Iibg', 0),
('1ddb6113981d2cfc746c5048d17c0b6c', 45, '2017-06-18 08:33:48', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxODgyOSwiaWF0IjoxNDk3Nzc0ODI5LCJqdGkiOiIxZGRiNjExMzk4MWQyY2ZjNzQ2YzUwNDhkMTdjMGI2YyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.1RNRuaDX_t5vbB62kHH0ibYux5ieug5CxMkSXgrYako', 1),
('1de5b1e799f8737023f9fa339b36864d', 46, '2017-06-19 06:57:20', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTQ0MCwiaWF0IjoxNDk3ODU1NDQwLCJqdGkiOiIxZGU1YjFlNzk5Zjg3MzcwMjNmOWZhMzM5YjM2ODY0ZCIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.TMNZ67osNh5fV1SO2B-CNzAF_U0hhaPlJQL1GuoHlXk', 0),
('1eab62a960267fde118ee1da2610a197', 45, '2017-06-19 07:09:48', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAwMDE4NywiaWF0IjoxNDk3ODU2MTg3LCJqdGkiOiIxZWFiNjJhOTYwMjY3ZmRlMTE4ZWUxZGEyNjEwYTE5NyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.H6yFib9bCv8sPkMlrvXOFyK7EO2Fd9Wj8bbXSYtWR3U', 0),
('2078ca470ffc02182f4f6a05b136b5ee', 45, '2017-06-18 08:28:51', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxODUzMiwiaWF0IjoxNDk3Nzc0NTMyLCJqdGkiOiIyMDc4Y2E0NzBmZmMwMjE4MmY0ZjZhMDViMTM2YjVlZSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.mYUXakX4GjdjtrWmIsnSaictPUyyATnygg_E9YdD_NI', 1),
('222bb5fdf116a82f7771de3e18bfc5dd', 45, '2017-06-19 02:36:34', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4Mzc5NCwiaWF0IjoxNDk3ODM5Nzk0LCJqdGkiOiIyMjJiYjVmZGYxMTZhODJmNzc3MWRlM2UxOGJmYzVkZCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.XT44dng3OmCz9PVSsn1AUgOFwQsa1aoIZxXkOjRk2HY', 0),
('245d94ee7db660dca675743e9137b439', 44, '2017-06-16 23:37:24', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzgwMDI0MywiaWF0IjoxNDk3NjU2MjQzLCJqdGkiOiIyNDVkOTRlZTdkYjY2MGRjYTY3NTc0M2U5MTM3YjQzOSIsImlkVXRpbGl6YWRvciI6IjQ0Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.aD4V0ybZXVrzOMTsjTTptEyJdPHghlQ9AHifh5KkmU8', 1),
('25619067bc456c82732a3064af478338', 43, '2017-06-16 09:37:07', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzc0OTgyNywiaWF0IjoxNDk3NjA1ODI3LCJqdGkiOiIyNTYxOTA2N2JjNDU2YzgyNzMyYTMwNjRhZjQ3ODMzOCIsImlkVXRpbGl6YWRvciI6IjQzIiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.frdoQMvq1JOOlTZdSurTkNgyxh11pBg7uFXqO05U-d0', 0),
('26dc7244dcc531770f00944031f14e3d', 46, '2017-06-19 06:45:10', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5ODcwOSwiaWF0IjoxNDk3ODU0NzA5LCJqdGkiOiIyNmRjNzI0NGRjYzUzMTc3MGYwMDk0NDAzMWYxNGUzZCIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.Ir3WqWSQ-FLth86-PsSURSsacJXogdUiULWDytWsK8U', 0),
('2713c4abc5be98a65a4beecb249078ec', 45, '2017-06-19 05:55:48', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NTc0OCwiaWF0IjoxNDk3ODUxNzQ4LCJqdGkiOiIyNzEzYzRhYmM1YmU5OGE2NWE0YmVlY2IyNDkwNzhlYyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.RM-nilI2YNPLY43aN2VvN13QmdJIGeolHQcpNYDNQmQ', 0),
('2854521f1aee95dd5f84231415695e2e', 45, '2017-06-19 12:01:39', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzY5OSwiaWF0IjoxNDk3ODczNjk5LCJqdGkiOiIyODU0NTIxZjFhZWU5NWRkNWY4NDIzMTQxNTY5NWUyZSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.LPpVVoEeAxcn0XRt9bBXo6_h8eu8dyeImmgo9UDF93U', 0),
('32f4115cf1b065417f1c823812147364', 45, '2017-06-19 05:43:27', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NTAwNiwiaWF0IjoxNDk3ODUxMDA2LCJqdGkiOiIzMmY0MTE1Y2YxYjA2NTQxN2YxYzgyMzgxMjE0NzM2NCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.2NBR7fIMH37_aT1K-p76qszTILfTsqFMPH00eNZAyDY', 0),
('33f077932b5ee9283f9f31b4b0ff1fb8', 45, '2017-06-19 05:42:53', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDk3MiwiaWF0IjoxNDk3ODUwOTcyLCJqdGkiOiIzM2YwNzc5MzJiNWVlOTI4M2Y5ZjMxYjRiMGZmMWZiOCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.GK9pU0rtVgf4ZJwRPMBTo1Wg6l02N01TpN6m84nYClQ', 0),
('37995d2a0a2aa66328019e12f2a6c5fb', 45, '2017-06-18 08:22:50', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxODE3MSwiaWF0IjoxNDk3Nzc0MTcxLCJqdGkiOiIzNzk5NWQyYTBhMmFhNjYzMjgwMTllMTJmMmE2YzVmYiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.CUkXyp0tb0n5Gb0JlpGVcv5iJ3c_naa7JHX20GAIsg4', 1),
('385b9d39610cc50322d007d3425401fb', 45, '2017-06-19 12:00:54', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzY1NCwiaWF0IjoxNDk3ODczNjU0LCJqdGkiOiIzODViOWQzOTYxMGNjNTAzMjJkMDA3ZDM0MjU0MDFmYiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.yZue-ZNIHT9FzGW43MjUM8KTisShjoozsJSpPmVtO8s', 0),
('39f4540b32d9e3404b17508cb06211ed', 45, '2017-06-22 16:01:36', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDI5MTI5NywiaWF0IjoxNDk4MTQ3Mjk3LCJqdGkiOiIzOWY0NTQwYjMyZDllMzQwNGIxNzUwOGNiMDYyMTFlZCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.lMdoZKuFqNsqS-pbdMP8ai1Abvf5LkBbenuIfy4DInY', 0),
('3d77ea65310b058c1daec2e36abf2676', 46, '2017-06-19 12:15:19', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxODUxOSwiaWF0IjoxNDk3ODc0NTE5LCJqdGkiOiIzZDc3ZWE2NTMxMGIwNThjMWRhZWMyZTM2YWJmMjY3NiIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.0oTaoip5rsraOmlf2vh-IwkKWOOLvKQgQMCEOTBhk3s', 0),
('3e5ea4c55acb3cba67a75a3cfbd4ab1f', 45, '2017-06-18 08:36:05', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxODk2NiwiaWF0IjoxNDk3Nzc0OTY2LCJqdGkiOiIzZTVlYTRjNTVhY2IzY2JhNjdhNzVhM2NmYmQ0YWIxZiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.XAWO1oJfRRbacgkJnuuzzqTGUu_XXtzlSIWhKggUyb8', 1),
('4086bb6089399df97721561cee2f1e79', 45, '2017-06-22 12:43:06', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDI3OTM4OCwiaWF0IjoxNDk4MTM1Mzg4LCJqdGkiOiI0MDg2YmI2MDg5Mzk5ZGY5NzcyMTU2MWNlZTJmMWU3OSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.p-k4bG-jHR9CInoiAFvMEm75Pu1l-IZ7LmPF8XgEovU', 1),
('40b3e98e00a26f09ca7e8f8a9d38978c', 46, '2017-06-19 07:10:03', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAwMDIwMiwiaWF0IjoxNDk3ODU2MjAyLCJqdGkiOiI0MGIzZTk4ZTAwYTI2ZjA5Y2E3ZThmOGE5ZDM4OTc4YyIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.FtKgTeRxqwq_P8DpvpeZQke6Rg0cGgF0nVOKIEDRvms', 0),
('41348a894810c10426606dd944d2c727', 45, '2017-06-19 12:04:34', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzg3NCwiaWF0IjoxNDk3ODczODc0LCJqdGkiOiI0MTM0OGE4OTQ4MTBjMTA0MjY2MDZkZDk0NGQyYzcyNyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.PeMd-onKlftdpELFQoE6IZH2UWelXz7hEEsaZtguqLc', 0),
('4969737b57b5c6da495a957625be23ef', 45, '2017-06-20 18:33:54', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDEyNzYzNSwiaWF0IjoxNDk3OTgzNjM1LCJqdGkiOiI0OTY5NzM3YjU3YjVjNmRhNDk1YTk1NzYyNWJlMjNlZiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.2Sr0WBRXPbMoISHsQeLepemtwCTHks65TH95_VmHzEA', 0),
('4c11bb61a53b294457046b63ae4aee34', 45, '2017-06-18 08:19:23', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxNzk2NCwiaWF0IjoxNDk3NzczOTY0LCJqdGkiOiI0YzExYmI2MWE1M2IyOTQ0NTcwNDZiNjNhZTRhZWUzNCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.2Cz68hn86Q-dWa6SPLB9AN29CGv3-mgPcGLlsLWqsps', 1),
('4d77da8bf9a68ce1bf8f9dda907cd714', 46, '2017-06-21 16:07:20', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDIwNTI0MSwiaWF0IjoxNDk4MDYxMjQxLCJqdGkiOiI0ZDc3ZGE4YmY5YTY4Y2UxYmY4ZjlkZGE5MDdjZDcxNCIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.1O374ixP9RL00G4sJ-OcLDYMDRAzTI0bPmTZG7DJX7Q', 0),
('4dc9dd194b61964ee32d853897d29154', 45, '2017-06-23 07:26:15', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDM0Njc3NywiaWF0IjoxNDk4MjAyNzc3LCJqdGkiOiI0ZGM5ZGQxOTRiNjE5NjRlZTMyZDg1Mzg5N2QyOTE1NCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.cVyLmd724pam4ayXjrLfFjdTIoWrQnIxQ29bpNgW_Mo', 0),
('4eb150b1a6660fa93489cd985a960766', 45, '2017-06-18 08:19:45', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxNzk4NiwiaWF0IjoxNDk3NzczOTg2LCJqdGkiOiI0ZWIxNTBiMWE2NjYwZmE5MzQ4OWNkOTg1YTk2MDc2NiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.gNqarPDFmhGXTx8vxg1ErohNlcnyPpNtroBsz1E4aRE', 1),
('54ecbf47b94dda91e7d564ef06242231', 45, '2017-06-17 22:01:47', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzg4MDkwOCwiaWF0IjoxNDk3NzM2OTA4LCJqdGkiOiI1NGVjYmY0N2I5NGRkYTkxZTdkNTY0ZWYwNjI0MjIzMSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.hGUb_TZpK8zhG2el09NRsswZr6ggm9gsgLkxnbhIWY4', 0),
('5700808cf1d33f6413046cb3c5b29580', 45, '2017-06-19 05:30:45', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDI0NCwiaWF0IjoxNDk3ODUwMjQ0LCJqdGkiOiI1NzAwODA4Y2YxZDMzZjY0MTMwNDZjYjNjNWIyOTU4MCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.kfbJ_Kt0BTUVKhNx4NiK_GGckm05c_jW7X0_ucEjaDk', 0),
('59aeca4b56480d89ac5d96c49e9d7005', 45, '2017-06-18 01:45:52', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzg5NDM1NCwiaWF0IjoxNDk3NzUwMzU0LCJqdGkiOiI1OWFlY2E0YjU2NDgwZDg5YWM1ZDk2YzQ5ZTlkNzAwNSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.KUexKaKqk3CQSWb0ZeNLsT5JtXc0_pA6tkIQV6Wj6JU', 0),
('5a13b09e1e6e58e87e1869fb985b0970', 45, '2017-06-18 08:37:01', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxOTAyMiwiaWF0IjoxNDk3Nzc1MDIyLCJqdGkiOiI1YTEzYjA5ZTFlNmU1OGU4N2UxODY5ZmI5ODViMDk3MCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.HG8pxcWbQj61rBG0iJcQs_L4RdraOH1-1rZHW_UB4LA', 1),
('6136737636ec51acc1b79a2d59290fe2', 45, '2017-06-19 06:56:08', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTM2NywiaWF0IjoxNDk3ODU1MzY3LCJqdGkiOiI2MTM2NzM3NjM2ZWM1MWFjYzFiNzlhMmQ1OTI5MGZlMiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.c8lHm6BcoSz20eX7vio0BECVtizNg2sgeCvlgmse4N0', 0),
('625f22aeacaf761ab25b8f70f41a336b', 45, '2017-06-20 12:13:59', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDEwNDg0MCwiaWF0IjoxNDk3OTYwODQwLCJqdGkiOiI2MjVmMjJhZWFjYWY3NjFhYjI1YjhmNzBmNDFhMzM2YiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.g7CuCF4EA0fj7fklQhauHycTUxvvickFZRcsRvi8XmY', 0),
('62b124b3b7315dcd830b06f9ebff3fd6', 45, '2017-06-19 02:47:20', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4NDQzOSwiaWF0IjoxNDk3ODQwNDM5LCJqdGkiOiI2MmIxMjRiM2I3MzE1ZGNkODMwYjA2ZjllYmZmM2ZkNiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.dPtRgVCObQTMwHoCcubhVPHglFz2ThWtZ1wKPOoLaYo', 0),
('6337252af3231da1fd829f6125be4118', 45, '2017-06-18 08:37:09', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxOTAzMCwiaWF0IjoxNDk3Nzc1MDMwLCJqdGkiOiI2MzM3MjUyYWYzMjMxZGExZmQ4MjlmNjEyNWJlNDExOCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.5Lly7aoXdT-xyRVSL8gDaRnYfpjKf5niKosdOQ9LgnQ', 1),
('63783bf2eccb001746ab705e14c59d8b', 45, '2017-06-19 05:36:49', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDYwOCwiaWF0IjoxNDk3ODUwNjA4LCJqdGkiOiI2Mzc4M2JmMmVjY2IwMDE3NDZhYjcwNWUxNGM1OWQ4YiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.XIFdIOE9NvRVWaEFRbq1cnTt2HXAz1r1fntpR41deqc', 0),
('6406f59c5c4b7ff9046a164666790f44', 45, '2017-06-19 07:04:29', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTg2OCwiaWF0IjoxNDk3ODU1ODY4LCJqdGkiOiI2NDA2ZjU5YzVjNGI3ZmY5MDQ2YTE2NDY2Njc5MGY0NCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.RFxWLY64POZ_Oy2Gs7_W9QPvlHpTqA9XlriPIEOXHUI', 0),
('6772c742a4ec69bd27786a17835194f6', 45, '2017-06-19 02:19:47', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4Mjc4NiwiaWF0IjoxNDk3ODM4Nzg2LCJqdGkiOiI2NzcyYzc0MmE0ZWM2OWJkMjc3ODZhMTc4MzUxOTRmNiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.Md5uohS-Om4E9yvhUKd7d2RCYqBsQO9brKptlG9iu9M', 0),
('6785b537f337a2fe3a44a5285c5f94b0', 45, '2017-06-22 15:39:19', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDI4OTk2MCwiaWF0IjoxNDk4MTQ1OTYwLCJqdGkiOiI2Nzg1YjUzN2YzMzdhMmZlM2E0NGE1Mjg1YzVmOTRiMCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.oxCFYpOfkRjwkmjJc3MvvKNpiFb18NTNfZge45FZK2c', 0),
('6f255c4199cccf2e682204725d33fa57', 46, '2017-06-19 04:47:35', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5MTY1NCwiaWF0IjoxNDk3ODQ3NjU0LCJqdGkiOiI2ZjI1NWM0MTk5Y2NjZjJlNjgyMjA0NzI1ZDMzZmE1NyIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.EEU8_F2oeeWL21VcaupVuzZuymtdTBenhFd7HNJCm3k', 0),
('700edaf6adee22ad94cd42bfff0efd2f', 45, '2017-06-19 07:05:34', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTkzMywiaWF0IjoxNDk3ODU1OTMzLCJqdGkiOiI3MDBlZGFmNmFkZWUyMmFkOTRjZDQyYmZmZjBlZmQyZiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.vCyCmORNY_I0jG-OK55wmgHqz2iC-iuqga2SSy0ahuU', 0),
('70d6f006c6eef2a52de56e2dd51299d7', 45, '2017-06-19 11:58:04', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzQ4NCwiaWF0IjoxNDk3ODczNDg0LCJqdGkiOiI3MGQ2ZjAwNmM2ZWVmMmE1MmRlNTZlMmRkNTEyOTlkNyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.Gp11RQd1xbyGbuj5luirBLg7kjvEDS68oHrK1wB5K3w', 0),
('74c78bf9ade36cfc534bb3f9d9be16d3', 45, '2017-06-19 12:00:00', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzU5OSwiaWF0IjoxNDk3ODczNTk5LCJqdGkiOiI3NGM3OGJmOWFkZTM2Y2ZjNTM0YmIzZjlkOWJlMTZkMyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.pBBNWP4I-7ozbs961lb72V-QUFYGLq5WUACfe9drruQ', 0),
('75f426ab80f98b15b3a06e76fc7092e5', 46, '2017-06-19 05:41:19', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDg3OCwiaWF0IjoxNDk3ODUwODc4LCJqdGkiOiI3NWY0MjZhYjgwZjk4YjE1YjNhMDZlNzZmYzcwOTJlNSIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.r7r15Gz6sbXHUEUDZKWnwhNcmtWDtm4-ghd5ydmEbNk', 0),
('782f69ac434971b37f4cdd47b4a66085', 45, '2017-06-19 06:55:51', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTM1MCwiaWF0IjoxNDk3ODU1MzUwLCJqdGkiOiI3ODJmNjlhYzQzNDk3MWIzN2Y0Y2RkNDdiNGE2NjA4NSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.kI7jfo5yWcs11SFmdyiKZsepkRFukIYyFPZ1e9_wtqs', 0),
('7aa088dd58b5fa07521c5f115dc039a7', 46, '2017-06-19 05:31:48', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDMwOCwiaWF0IjoxNDk3ODUwMzA4LCJqdGkiOiI3YWEwODhkZDU4YjVmYTA3NTIxYzVmMTE1ZGMwMzlhNyIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.cjlgBI_eQm8oP4UXxS5w8wxeEI0AV2tLr1TjaT0NAq8', 0),
('7e30f6ebfc59a9934aa805b8bb0b2aae', 45, '2017-06-18 08:13:31', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxNzYxMiwiaWF0IjoxNDk3NzczNjEyLCJqdGkiOiI3ZTMwZjZlYmZjNTlhOTkzNGFhODA1YjhiYjBiMmFhZSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.FzkxHa3pYimYTuzlTo7VxLy_odrYo8T0hBQnvZCrj7A', 1),
('83420097f6e8475a8f3c804c73508843', 46, '2017-06-20 17:03:47', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDEyMjIyOSwiaWF0IjoxNDk3OTc4MjI5LCJqdGkiOiI4MzQyMDA5N2Y2ZTg0NzVhOGYzYzgwNGM3MzUwODg0MyIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.xHRSzf0sxAOPpBIoC2TOUGzV8ltbBjOsWrSoWHAAM_Y', 0),
('83e55bdf9631f9384c1d284ff18590b6', 44, '2017-06-22 16:38:14', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDI5MzQ5MSwiaWF0IjoxNDk4MTQ5NDkxLCJqdGkiOiI4M2U1NWJkZjk2MzFmOTM4NGMxZDI4NGZmMTg1OTBiNiIsImlkVXRpbGl6YWRvciI6IjQ0Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.KGK9Yiks8P4cb5VtPWqextTGtKYmfdvVuhbPK8c7zpI', 1),
('87638f7d1af575738d5b7a0bba19fa43', 45, '2017-06-20 12:14:13', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDEwNDg1NSwiaWF0IjoxNDk3OTYwODU1LCJqdGkiOiI4NzYzOGY3ZDFhZjU3NTczOGQ1YjdhMGJiYTE5ZmE0MyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.iWzINONPTj0wrp-iJPlHjB9Ghp04mAKrkxABbEV2GoU', 0),
('8aa058b3613f892593fb8824ca3718c5', 46, '2017-06-19 07:04:43', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTg4MiwiaWF0IjoxNDk3ODU1ODgyLCJqdGkiOiI4YWEwNThiMzYxM2Y4OTI1OTNmYjg4MjRjYTM3MThjNSIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.uAkfIP3Xpi6d7qXzxCdZZjShcm0oTTGJHCAhNYVMRU4', 0),
('8c25fd6295d0f35cdbde05fcd6eb8202', 45, '2017-06-19 06:48:52', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5ODkzMSwiaWF0IjoxNDk3ODU0OTMxLCJqdGkiOiI4YzI1ZmQ2Mjk1ZDBmMzVjZGJkZTA1ZmNkNmViODIwMiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.TpvkROKjMoHX135LGdH_MqIs5IEH9DKPJxRzzN6mPz8', 0),
('8ee6ff0cd9047bd78645ad35d04a7871', 46, '2017-06-19 03:25:10', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4NjcxMCwiaWF0IjoxNDk3ODQyNzEwLCJqdGkiOiI4ZWU2ZmYwY2Q5MDQ3YmQ3ODY0NWFkMzVkMDRhNzg3MSIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.xHaZyOjr31KTVnnycfbNTQC1QLQGRxXbLlQZnZDRNUg', 0),
('914e66f3b48ee90d0ed7f55a9b988ea9', 46, '2017-06-21 12:30:51', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDE5MjI1MywiaWF0IjoxNDk4MDQ4MjUzLCJqdGkiOiI5MTRlNjZmM2I0OGVlOTBkMGVkN2Y1NWE5Yjk4OGVhOSIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.RVsQvCuhtESOS_bKuIh3Saf7j6yKIWg5zWoZH7GvIWg', 0),
('91ff58edba001a1a1fd1aeb93fa8cf79', 45, '2017-06-18 02:01:08', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzg5NTI3MCwiaWF0IjoxNDk3NzUxMjcwLCJqdGkiOiI5MWZmNThlZGJhMDAxYTFhMWZkMWFlYjkzZmE4Y2Y3OSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.OGUjFlmhrDQgiZQ8bBReagsB2HkKSYMjfATYM0utOQQ', 0),
('9201cb6e3a9ec8b632fa55a7dfc3ca59', 46, '2017-06-19 05:46:12', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NTE3MiwiaWF0IjoxNDk3ODUxMTcyLCJqdGkiOiI5MjAxY2I2ZTNhOWVjOGI2MzJmYTU1YTdkZmMzY2E1OSIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.c7xc5EFRhbWyNdPkv5zzHK6s2fBUtdmOv9ydDS1SCvk', 0),
('929e1df63d3a9597521e03711707dee2', 45, '2017-06-19 06:53:45', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTIyNCwiaWF0IjoxNDk3ODU1MjI0LCJqdGkiOiI5MjllMWRmNjNkM2E5NTk3NTIxZTAzNzExNzA3ZGVlMiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.zYdqGtjNUh8yBH2mlsGGSj9BxBKWP6TdMZ2xkXECtSs', 0),
('956081ef9b6c0ef9fa30d27cc6dda8da', 45, '2017-06-18 08:49:47', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxOTc4OCwiaWF0IjoxNDk3Nzc1Nzg4LCJqdGkiOiI5NTYwODFlZjliNmMwZWY5ZmEzMGQyN2NjNmRkYThkYSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.wVFTqz3O3CFJpQgDWrCJeDcFgyjjc2hjr8x7qQtjNww', 1),
('95e37e1a9ae70839acfed9edef79a305', 45, '2017-06-21 10:18:00', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDE4NDI4MiwiaWF0IjoxNDk4MDQwMjgyLCJqdGkiOiI5NWUzN2UxYTlhZTcwODM5YWNmZWQ5ZWRlZjc5YTMwNSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.ljyD6cNwB0u6FmzcEbyAtmAOjW2sU4S3IaGbfHuYkd4', 0),
('96f3a8477a1c8b9d72455b280a5cc025', 45, '2017-06-18 08:37:03', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxOTAyNCwiaWF0IjoxNDk3Nzc1MDI0LCJqdGkiOiI5NmYzYTg0NzdhMWM4YjlkNzI0NTViMjgwYTVjYzAyNSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.TgtYOJ0rVvWVQD796FP_390pn0g4B3rAoMp2t_3iO0U', 1),
('972ae52d35a0f382aa5c0856d6438b45', 45, '2017-06-19 05:55:11', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NTcxMCwiaWF0IjoxNDk3ODUxNzEwLCJqdGkiOiI5NzJhZTUyZDM1YTBmMzgyYWE1YzA4NTZkNjQzOGI0NSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.23jjhK5N7lk8xl8GQ7oE4CEo6uwTNcMuUSThy-FPRIE', 0),
('973c906a53964c60f771cedbaaa2bc5a', 45, '2017-06-19 02:39:44', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4Mzk4MywiaWF0IjoxNDk3ODM5OTgzLCJqdGkiOiI5NzNjOTA2YTUzOTY0YzYwZjc3MWNlZGJhYWEyYmM1YSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.x8tvlwDnTfeUpaxVQMO4tF3tX0oe8h-aul5qdFIBnIU', 0),
('9bae0387dd3df41c951bdb00e5a30ad8', 45, '2017-06-19 06:57:06', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTQyNSwiaWF0IjoxNDk3ODU1NDI1LCJqdGkiOiI5YmFlMDM4N2RkM2RmNDFjOTUxYmRiMDBlNWEzMGFkOCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.N4dj6oYmmGO10wofkJoBwW7Zj4nom90B_lIPPAqXFPw', 0),
('9c2f9eca2990e0d74977931d83736473', 45, '2017-06-19 05:43:22', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NTAwMiwiaWF0IjoxNDk3ODUxMDAyLCJqdGkiOiI5YzJmOWVjYTI5OTBlMGQ3NDk3NzkzMWQ4MzczNjQ3MyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.zHHr9mLNQXQ76Go1qrDXAHcWYhHE0KWj8yBKexBsHlY', 0),
('9efc2211b6e7db89d32760bcf68bff65', 46, '2017-06-19 12:05:31', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzkzMSwiaWF0IjoxNDk3ODczOTMxLCJqdGkiOiI5ZWZjMjIxMWI2ZTdkYjg5ZDMyNzYwYmNmNjhiZmY2NSIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.4bvRSUaR8EpNEmIxnF1l_v53vCYf4-SxtV53oRAZHk8', 0),
('a2a4c3f35bd901d5d4e3a0f338b97636', 47, '2017-06-21 11:39:54', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDE4OTE5MywiaWF0IjoxNDk4MDQ1MTkzLCJqdGkiOiJhMmE0YzNmMzViZDkwMWQ1ZDRlM2EwZjMzOGI5NzYzNiIsImlkVXRpbGl6YWRvciI6IjQ3Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.XaTozIuBgJTPwVPVLhihDNrrBBaZxbTeOA4FUmzdCmg', 0),
('aa3f06e76c3e8e6d8b0590366e1921c2', 45, '2017-06-19 12:01:34', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzY5NCwiaWF0IjoxNDk3ODczNjk0LCJqdGkiOiJhYTNmMDZlNzZjM2U4ZTZkOGIwNTkwMzY2ZTE5MjFjMiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ._KCn0QIUbIWgNfMzOkIVfWck-H38uLIEv0DgvpXMgJY', 1),
('af5c4ffe979badce07e8e02f87230642', 45, '2017-06-19 05:39:51', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDc5MCwiaWF0IjoxNDk3ODUwNzkwLCJqdGkiOiJhZjVjNGZmZTk3OWJhZGNlMDdlOGUwMmY4NzIzMDY0MiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.7HX45ytvbbqj6qjSg3CQY7CqSANBmGaS9jDBRqvYL60', 0),
('b426e2ebe0e1a33383d41b181f97b420', 45, '2017-06-19 02:37:05', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4MzgyNSwiaWF0IjoxNDk3ODM5ODI1LCJqdGkiOiJiNDI2ZTJlYmUwZTFhMzMzODNkNDFiMTgxZjk3YjQyMCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.PB39RxLg1yxlYNpxwRSoFg2aOah4v2etJnul_R6yWPM', 0),
('bc8c25428e08e7a78f8bbe45b9d468df', 45, '2017-06-19 12:03:43', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzgyMywiaWF0IjoxNDk3ODczODIzLCJqdGkiOiJiYzhjMjU0MjhlMDhlN2E3OGY4YmJlNDViOWQ0NjhkZiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.g-Q4nbrjjWU-YX1polrDjwRFBL0s6hpsEPMZ4SWER5g', 0),
('bd3eed6e5fe1d408bddcd2ec33ff8b0a', 45, '2017-06-19 05:33:40', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDQyMCwiaWF0IjoxNDk3ODUwNDIwLCJqdGkiOiJiZDNlZWQ2ZTVmZTFkNDA4YmRkY2QyZWMzM2ZmOGIwYSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.ALQemXs6Df4yA4MdhpPvMRJYjyOYwrCa9KFNFwXTFWs', 0),
('be97d8c8b67bded0bb2b70bfa8599d1f', 45, '2017-06-18 08:19:42', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxNzk4MywiaWF0IjoxNDk3NzczOTgzLCJqdGkiOiJiZTk3ZDhjOGI2N2JkZWQwYmIyYjcwYmZhODU5OWQxZiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.A0bYGfPeN4ODAxgzKLItvJmzSFKv9Sb94YHouXnkZ4k', 1),
('c3c5491356f045eab807d3c128a84fb2', 45, '2017-06-19 02:38:20', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4Mzg5OSwiaWF0IjoxNDk3ODM5ODk5LCJqdGkiOiJjM2M1NDkxMzU2ZjA0NWVhYjgwN2QzYzEyOGE4NGZiMiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.LU_14guqwf4zIwh7dWNIEic9QRTuNWRnSANdee7OHe4', 0),
('c481ffc9979739f269f2f51d60ffc65d', 45, '2017-06-19 05:45:58', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NTE1OCwiaWF0IjoxNDk3ODUxMTU4LCJqdGkiOiJjNDgxZmZjOTk3OTczOWYyNjlmMmY1MWQ2MGZmYzY1ZCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.491KUiF7PXkh0srF6tlAO5g4__7ucu9nLr-uWsbi2ZY', 0),
('c68f6e90958919529adb920524bdb221', 45, '2017-06-19 12:03:21', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAxNzgwMSwiaWF0IjoxNDk3ODczODAxLCJqdGkiOiJjNjhmNmU5MDk1ODkxOTUyOWFkYjkyMDUyNGJkYjIyMSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.OqhgwUh7n2kKXmWuuFCl7nPl6VDXh3ENu1JVoGjaDcs', 0),
('c7bbd3cd047f56edd36d89c9d48fb963', 45, '2017-06-22 13:46:58', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDI4MzIyMCwiaWF0IjoxNDk4MTM5MjIwLCJqdGkiOiJjN2JiZDNjZDA0N2Y1NmVkZDM2ZDg5YzlkNDhmYjk2MyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.0NvBYLPoAgFmAKtc43oLVEPv6vcKcmBX_SiZJdl5Vu4', 1),
('c85c8cf37fcd3418bc1670bcea3e4eb8', 45, '2017-06-19 05:42:33', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDk1MiwiaWF0IjoxNDk3ODUwOTUyLCJqdGkiOiJjODVjOGNmMzdmY2QzNDE4YmMxNjcwYmNlYTNlNGViOCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.ORbL4OYz5UIXZMlEUDJlTrZTk-_c-hGpIo_st3B03XI', 0),
('c9287a408b742f0039bc3ba38debac98', 45, '2017-06-18 08:15:09', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxNzcxMCwiaWF0IjoxNDk3NzczNzEwLCJqdGkiOiJjOTI4N2E0MDhiNzQyZjAwMzliYzNiYTM4ZGViYWM5OCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.hLu8QOdcyNnfU2mwtyCtmh28cpC_oJwx3dXw4jvzQ7k', 1),
('c9c0a887d6e2025c9424772a89e61381', 45, '2017-06-18 08:31:17', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxODY3OCwiaWF0IjoxNDk3Nzc0Njc4LCJqdGkiOiJjOWMwYTg4N2Q2ZTIwMjVjOTQyNDc3MmE4OWU2MTM4MSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.vQNkAAnMOtjHXQILynGT3VwvF1-YIRO1obpgAIDuzN0', 1),
('cf547257302e37046c298ce9aceac111', 44, '2017-06-16 23:44:14', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzgwMDY1MywiaWF0IjoxNDk3NjU2NjUzLCJqdGkiOiJjZjU0NzI1NzMwMmUzNzA0NmMyOThjZTlhY2VhYzExMSIsImlkVXRpbGl6YWRvciI6IjQ0Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.q950tk0SVgnXrH-EmFNnLQc0VL0k-Tn-GHWTF9CcCc8', 0),
('d16f8f6e39e34f59a54689b4394952d8', 45, '2017-06-19 02:40:02', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk4NDAwMSwiaWF0IjoxNDk3ODQwMDAxLCJqdGkiOiJkMTZmOGY2ZTM5ZTM0ZjU5YTU0Njg5YjQzOTQ5NTJkOCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ._pWUZtYycOuH4Uo5mHYM4s-ogPOo4fJRLWVCd-DpmNo', 1),
('d21233badcf3e0ce2c27b79aa9dc7777', 45, '2017-06-18 08:15:28', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxNzcyOSwiaWF0IjoxNDk3NzczNzI5LCJqdGkiOiJkMjEyMzNiYWRjZjNlMGNlMmMyN2I3OWFhOWRjNzc3NyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.djnlqwMDhnBCjHMb0MklIddngkaorJwFiCHrlby4zyk', 1),
('d369d6f36053795a3941a3e93f0f889c', 45, '2017-06-19 07:12:03', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDAwMDMyMiwiaWF0IjoxNDk3ODU2MzIyLCJqdGkiOiJkMzY5ZDZmMzYwNTM3OTVhMzk0MWEzZTkzZjBmODg5YyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.735mBgOnyg85DdfzUco2uJUZ9GI260IYbq17Kkwr-yw', 0),
('d5af571a827d81b35d5c0993d1eb533d', 46, '2017-06-19 05:30:04', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDIwMywiaWF0IjoxNDk3ODUwMjAzLCJqdGkiOiJkNWFmNTcxYTgyN2Q4MWIzNWQ1YzA5OTNkMWViNTMzZCIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.vp28wg27WVxJ-nbl1kwIsWnRewozKaTkFacakypoo-E', 0),
('d652df3b3b51a589ce4bd036b80d3dcb', 45, '2017-06-19 05:29:44', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDE4MywiaWF0IjoxNDk3ODUwMTgzLCJqdGkiOiJkNjUyZGYzYjNiNTFhNTg5Y2U0YmQwMzZiODBkM2RjYiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.RSKI6GqT8-ftNHiS_jdkzSs5SE1PEjG7e9ppFyc_paA', 0),
('d6cdced550be6d3d86c6fbf97ad05fce', 45, '2017-06-18 01:02:05', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzg5MTcyNiwiaWF0IjoxNDk3NzQ3NzI2LCJqdGkiOiJkNmNkY2VkNTUwYmU2ZDNkODZjNmZiZjk3YWQwNWZjZSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.063YD6aRTcOXzTm0A99YhqnblFSFy6o_ctVuoE41vHo', 0),
('d9de32c4dc408d380742f3210cfc582f', 47, '2017-06-20 16:25:17', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDExOTkxNywiaWF0IjoxNDk3OTc1OTE3LCJqdGkiOiJkOWRlMzJjNGRjNDA4ZDM4MDc0MmYzMjEwY2ZjNTgyZiIsImlkVXRpbGl6YWRvciI6IjQ3Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.aZdF3JMwUCauB23JpVC6Z-yv2sfI-g-fSpxv_EC6hTQ', 0),
('da119d8db898119670c602cc6f3bf9e5', 45, '2017-06-18 23:15:09', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk3MTcxMSwiaWF0IjoxNDk3ODI3NzExLCJqdGkiOiJkYTExOWQ4ZGI4OTgxMTk2NzBjNjAyY2M2ZjNiZjllNSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.oKdDvX-aMKcTvqTg4ceFNUjIZqXHhB8393Btf4sDDto', 0),
('dda7e981203f734042e4cface9055b2a', 45, '2017-06-22 08:58:24', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDI2NTkwNiwiaWF0IjoxNDk4MTIxOTA2LCJqdGkiOiJkZGE3ZTk4MTIwM2Y3MzQwNDJlNGNmYWNlOTA1NWIyYSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.nMoFcq1CTbFmypO1JAs13SC234B0_Gnjs22LFSvFZXQ', 1),
('de20e81ff2be522fc49cc002125732c8', 45, '2017-06-22 03:22:03', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDI0NTcyNSwiaWF0IjoxNDk4MTAxNzI1LCJqdGkiOiJkZTIwZTgxZmYyYmU1MjJmYzQ5Y2MwMDIxMjU3MzJjOCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.qyetVECTdAQIE3YRDHr3-Vt_-hwhfvKzWpR3pLqx1ck', 0),
('e0904eabc3d49995f5530639002a3597', 45, '2017-06-19 06:28:44', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NzcyMywiaWF0IjoxNDk3ODUzNzIzLCJqdGkiOiJlMDkwNGVhYmMzZDQ5OTk1ZjU1MzA2MzkwMDJhMzU5NyIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.Hyit31CFlXevFQuVZxwXLG4yFry94dMBvDlvFa_k3fc', 0),
('e2b73efb702804f01aa550f315961a7a', 45, '2017-06-19 05:30:32', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDIzMSwiaWF0IjoxNDk3ODUwMjMxLCJqdGkiOiJlMmI3M2VmYjcwMjgwNGYwMWFhNTUwZjMxNTk2MWE3YSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.-dRDo8jaEK5kAWKjRGN8QCFKRHw9iplb9RRMO_LsD_c', 1),
('e8c765001acee5b1efb83c07c3dca8a2', 45, '2017-06-18 08:33:11', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxODc5MiwiaWF0IjoxNDk3Nzc0NzkyLCJqdGkiOiJlOGM3NjUwMDFhY2VlNWIxZWZiODNjMDdjM2RjYThhMiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.K2ij1e8LjhSktAT01FONbtBFLD5yA4vS0V8iV5gmJTM', 0),
('eb2a0ce221fcd6f45ec8566071db8105', 45, '2017-06-18 08:53:09', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxOTk5MCwiaWF0IjoxNDk3Nzc1OTkwLCJqdGkiOiJlYjJhMGNlMjIxZmNkNmY0NWVjODU2NjA3MWRiODEwNSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.ATD_Dw2wAOHFc7Ydbhxdj3lP5RK--LUrXubKXMtO_UI', 1),
('ecdb5db5b41316f89b3c7b9458c5b41b', 45, '2017-06-19 05:37:12', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDYzMiwiaWF0IjoxNDk3ODUwNjMyLCJqdGkiOiJlY2RiNWRiNWI0MTMxNmY4OWIzYzdiOTQ1OGM1YjQxYiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.CxIFte6F7cfam0BJndygRIQUlw0Opr1acE-hzEfRFO4', 0),
('ed33a27d2362069e1d70d5ccbd97619e', 45, '2017-06-18 08:23:40', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxODIyMSwiaWF0IjoxNDk3Nzc0MjIxLCJqdGkiOiJlZDMzYTI3ZDIzNjIwNjllMWQ3MGQ1Y2NiZDk3NjE5ZSIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.HQrVXg6mSp6ePkKLHrbTx0jk93-qqIOldsYavX4wjac', 1),
('ee1075c0d8d7485593fa39a031625bac', 46, '2017-06-19 05:31:26', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NDI4NiwiaWF0IjoxNDk3ODUwMjg2LCJqdGkiOiJlZTEwNzVjMGQ4ZDc0ODU1OTNmYTM5YTAzMTYyNWJhYyIsImlkVXRpbGl6YWRvciI6IjQ2Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCJdfQ.jQkZH4yE_iSxhgZ3crxzbgGrm8wkAxNySNf4hHA1-ug', 0),
('f05cd85903a90d999089494ab2ca7fd0', 45, '2017-06-19 06:54:36', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5OTI3NiwiaWF0IjoxNDk3ODU1Mjc2LCJqdGkiOiJmMDVjZDg1OTAzYTkwZDk5OTA4OTQ5NGFiMmNhN2ZkMCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.VvO7nf3DOyD41-KbUTiEui6zNZkrDOjXhyXTV_-OBPI', 0),
('f18eb3c04da098f359fe41da24a7d7a6', 45, '2017-06-20 23:11:14', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyNDE0NDI3NCwiaWF0IjoxNDk4MDAwMjc0LCJqdGkiOiJmMThlYjNjMDRkYTA5OGYzNTlmZTQxZGEyNGE3ZDdhNiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.SdKTp-mIu2L6zJHBu6LasPeFIRBXA-yWa5uzkIfQ2Kw', 0),
('f24e4b8ccea910293595a70bad7ade42', 45, '2017-06-18 08:22:42', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzkxODE2MywiaWF0IjoxNDk3Nzc0MTYzLCJqdGkiOiJmMjRlNGI4Y2NlYTkxMDI5MzU5NWE3MGJhZDdhZGU0MiIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.Ck33bhJfnp7-s6iUs4A0Ns_Igi9vP-Mgv6z7XsATfPM', 1),
('fd35473877044c0d00026e2b6178b01d', 45, '2017-06-19 06:30:18', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCaW9ldmVudG9zIiwiYXVkIjoiaHR0cDpcL1wvbGFibW0uY2xpZW50cy51YS5wdCIsImV4cCI6MTYyMzk5NzgxNywiaWF0IjoxNDk3ODUzODE3LCJqdGkiOiJmZDM1NDczODc3MDQ0YzBkMDAwMjZlMmI2MTc4YjAxZCIsImlkVXRpbGl6YWRvciI6IjQ1Iiwic2NvcGUiOlsicHVibGljbyIsIm5vcm1hbCIsInNvY2lvIiwiY29sYWJvcmFkb3IiLCJhZG1pbiJdfQ.mZrj6lqbe_np7Gv9ooKcNdLJUuXhzRZ1MKyC8VwQGOo', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `colaboradores`
--
ALTER TABLE `colaboradores`
  ADD PRIMARY KEY (`id_colaboradores`);

--
-- Indexes for table `estatutos`
--
ALTER TABLE `estatutos`
  ADD PRIMARY KEY (`id_estatutos`);

--
-- Indexes for table `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id_eventos`), ADD KEY `fk_eventos_localizacao1_idx` (`localizacao_localizacao`), ADD KEY `fk_eventos_tipo_evento1_idx` (`tipo_evento_id_tipo_evento`);

--
-- Indexes for table `eventos_fotos`
--
ALTER TABLE `eventos_fotos`
  ADD PRIMARY KEY (`id_eventos_fotos`), ADD KEY `eventos_id_eventos` (`eventos_id_eventos`);

--
-- Indexes for table `eventos_has_eventos_infos`
--
ALTER TABLE `eventos_has_eventos_infos`
  ADD PRIMARY KEY (`eventos_id_eventos`,`eventos_infos_id_extras`), ADD KEY `fk_eventos_has_eventos_infos_eventos_infos1_idx` (`eventos_infos_id_extras`), ADD KEY `fk_eventos_has_eventos_infos_eventos1_idx` (`eventos_id_eventos`);

--
-- Indexes for table `eventos_has_tags`
--
ALTER TABLE `eventos_has_tags`
  ADD PRIMARY KEY (`eventos_id_eventos`,`tags_id_tags`), ADD KEY `fk_eventos_has_tags_tags1_idx` (`tags_id_tags`), ADD KEY `fk_eventos_has_tags_eventos1_idx` (`eventos_id_eventos`);

--
-- Indexes for table `eventos_infos`
--
ALTER TABLE `eventos_infos`
  ADD PRIMARY KEY (`id_extras`), ADD KEY `fk_eventos_infos_icons1_idx` (`icons_id_icons`), ADD KEY `fk_eventos_infos_icons2_idx` (`icons_pequeno_icons_id`);

--
-- Indexes for table `fotos_eventos`
--
ALTER TABLE `fotos_eventos`
  ADD PRIMARY KEY (`id_foto`), ADD KEY `eventos_id_eventos` (`eventos_id_eventos`), ADD KEY `eventos_id_eventos_2` (`eventos_id_eventos`), ADD KEY `eventos_id_eventos_3` (`eventos_id_eventos`);

--
-- Indexes for table `icons`
--
ALTER TABLE `icons`
  ADD PRIMARY KEY (`id_icons`);

--
-- Indexes for table `interesses`
--
ALTER TABLE `interesses`
  ADD PRIMARY KEY (`eventos_id_eventos`,`utilizadores_id_utilizadores`), ADD KEY `eventos_id_eventos` (`eventos_id_eventos`), ADD KEY `utilizadores_id_utilizadores` (`utilizadores_id_utilizadores`);

--
-- Indexes for table `localizacao`
--
ALTER TABLE `localizacao`
  ADD PRIMARY KEY (`localizacao`);

--
-- Indexes for table `participantes`
--
ALTER TABLE `participantes`
  ADD PRIMARY KEY (`eventos_id_eventos`,`utilizadores_id_utilizadores`), ADD KEY `fk_eventos_has_utilizadores_utilizadores1_idx` (`utilizadores_id_utilizadores`), ADD KEY `fk_eventos_has_utilizadores_eventos1_idx` (`eventos_id_eventos`);

--
-- Indexes for table `participantes_colaboradores`
--
ALTER TABLE `participantes_colaboradores`
  ADD PRIMARY KEY (`eventos_id_eventos`,`colaboradores_id_colaboradores`), ADD KEY `fk_eventos_has_colaboradores_colaboradores1_idx` (`colaboradores_id_colaboradores`), ADD KEY `fk_eventos_has_colaboradores_eventos1_idx` (`eventos_id_eventos`);

--
-- Indexes for table `passwords_blacklist`
--
ALTER TABLE `passwords_blacklist`
  ADD PRIMARY KEY (`id_passwords`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id_tags`);

--
-- Indexes for table `tipo_evento`
--
ALTER TABLE `tipo_evento`
  ADD PRIMARY KEY (`id_tipo_evento`), ADD KEY `icons_id` (`icons_id`);

--
-- Indexes for table `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id_utilizadores`), ADD UNIQUE KEY `fk_utilizadores_localizacao_idx` (`id_utilizadores`), ADD UNIQUE KEY `email` (`email`), ADD KEY `fk_utilizadores_estatutos_idx` (`estatutos_id_estatutos`), ADD KEY `localizacao_id_localizacao` (`localizacao_id_localizacao`);

--
-- Indexes for table `utilizadores_tokens`
--
ALTER TABLE `utilizadores_tokens`
  ADD PRIMARY KEY (`id`), ADD KEY `utilizadores_id_utilizadores` (`utilizadores_id_utilizadores`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `colaboradores`
--
ALTER TABLE `colaboradores`
  MODIFY `id_colaboradores` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `estatutos`
--
ALTER TABLE `estatutos`
  MODIFY `id_estatutos` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id_eventos` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT for table `eventos_fotos`
--
ALTER TABLE `eventos_fotos`
  MODIFY `id_eventos_fotos` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventos_infos`
--
ALTER TABLE `eventos_infos`
  MODIFY `id_extras` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `fotos_eventos`
--
ALTER TABLE `fotos_eventos`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `icons`
--
ALTER TABLE `icons`
  MODIFY `id_icons` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=678;
--
-- AUTO_INCREMENT for table `localizacao`
--
ALTER TABLE `localizacao`
  MODIFY `localizacao` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=72;
--
-- AUTO_INCREMENT for table `passwords_blacklist`
--
ALTER TABLE `passwords_blacklist`
  MODIFY `id_passwords` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=139;
--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id_tags` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `tipo_evento`
--
ALTER TABLE `tipo_evento`
  MODIFY `id_tipo_evento` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id_utilizadores` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=48;
--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `eventos`
--
ALTER TABLE `eventos`
ADD CONSTRAINT `fk_eventos_localizacao1` FOREIGN KEY (`localizacao_localizacao`) REFERENCES `localizacao` (`localizacao`) ON UPDATE CASCADE,
ADD CONSTRAINT `fk_eventos_tipo_evento1` FOREIGN KEY (`tipo_evento_id_tipo_evento`) REFERENCES `tipo_evento` (`id_tipo_evento`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `eventos_fotos`
--
ALTER TABLE `eventos_fotos`
ADD CONSTRAINT `eventos_fotos_ibfk_1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `eventos_has_eventos_infos`
--
ALTER TABLE `eventos_has_eventos_infos`
ADD CONSTRAINT `fk_eventos_has_eventos_infos_eventos1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_has_eventos_infos_eventos_infos1` FOREIGN KEY (`eventos_infos_id_extras`) REFERENCES `eventos_infos` (`id_extras`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `eventos_has_tags`
--
ALTER TABLE `eventos_has_tags`
ADD CONSTRAINT `fk_eventos_has_tags_eventos1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_has_tags_tags1` FOREIGN KEY (`tags_id_tags`) REFERENCES `tags` (`id_tags`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `eventos_infos`
--
ALTER TABLE `eventos_infos`
ADD CONSTRAINT `fk_eventos_infos_icons1` FOREIGN KEY (`icons_id_icons`) REFERENCES `icons` (`id_icons`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_infos_icons2` FOREIGN KEY (`icons_pequeno_icons_id`) REFERENCES `icons` (`id_icons`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `fotos_eventos`
--
ALTER TABLE `fotos_eventos`
ADD CONSTRAINT `fk_id_eventos` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`);

--
-- Limitadores para a tabela `interesses`
--
ALTER TABLE `interesses`
ADD CONSTRAINT `eventos_id_eventos` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `utilizadores_id_utilizadores` FOREIGN KEY (`utilizadores_id_utilizadores`) REFERENCES `utilizadores` (`id_utilizadores`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `participantes`
--
ALTER TABLE `participantes`
ADD CONSTRAINT `fk_eventos_has_utilizadores_eventos1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_has_utilizadores_utilizadores1` FOREIGN KEY (`utilizadores_id_utilizadores`) REFERENCES `utilizadores` (`id_utilizadores`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `participantes_colaboradores`
--
ALTER TABLE `participantes_colaboradores`
ADD CONSTRAINT `fk_eventos_has_colaboradores_colaboradores1` FOREIGN KEY (`colaboradores_id_colaboradores`) REFERENCES `colaboradores` (`id_colaboradores`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_has_colaboradores_eventos1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `tipo_evento`
--
ALTER TABLE `tipo_evento`
ADD CONSTRAINT `fk_tipo_eventos_icon_class` FOREIGN KEY (`icons_id`) REFERENCES `icons` (`id_icons`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `utilizadores`
--
ALTER TABLE `utilizadores`
ADD CONSTRAINT `fk_utilizadores_estatutos` FOREIGN KEY (`estatutos_id_estatutos`) REFERENCES `estatutos` (`id_estatutos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_utilizadores_localizacao` FOREIGN KEY (`localizacao_id_localizacao`) REFERENCES `localizacao` (`localizacao`);

--
-- Limitadores para a tabela `utilizadores_tokens`
--
ALTER TABLE `utilizadores_tokens`
ADD CONSTRAINT `fk_tokens_utilizadores` FOREIGN KEY (`utilizadores_id_utilizadores`) REFERENCES `utilizadores` (`id_utilizadores`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
