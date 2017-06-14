-- phpMyAdmin SQL Dump
-- version 4.3.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 14-Jun-2017 às 04:06
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

-- --------------------------------------------------------

--
-- Estrutura da tabela `estatutos`
--

CREATE TABLE IF NOT EXISTS `estatutos` (
  `id_estatutos` int(11) NOT NULL,
  `nome_estatuto` varchar(45) DEFAULT NULL,
  `descricao` varchar(300) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos`
--

CREATE TABLE IF NOT EXISTS `eventos` (
  `id_eventos` int(11) NOT NULL,
  `nome_evento` varchar(65) NOT NULL,
  `data_registo_evento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_evento` datetime NOT NULL,
  `descricao_short` varchar(200) DEFAULT NULL,
  `descricao` varchar(6000) DEFAULT NULL,
  `max_participantes` int(6) DEFAULT NULL,
  `min_participantes` int(6) DEFAULT NULL,
  `idade_minima` int(2) DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `fotos` text,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `localizacao_localizacao` int(11) DEFAULT NULL,
  `tipo_evento_id_tipo_evento` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos_has_eventos_infos`
--

CREATE TABLE IF NOT EXISTS `eventos_has_eventos_infos` (
  `eventos_id_eventos` int(11) NOT NULL,
  `eventos_infos_id_extras` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos_has_tags`
--

CREATE TABLE IF NOT EXISTS `eventos_has_tags` (
  `eventos_id_eventos` int(11) NOT NULL,
  `tags_id_tags` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `icons`
--

CREATE TABLE IF NOT EXISTS `icons` (
  `id_icons` int(11) NOT NULL,
  `classe` varchar(45) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=677 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `localizacao`
--

CREATE TABLE IF NOT EXISTS `localizacao` (
  `localizacao` int(11) NOT NULL,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `nome` varchar(155) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `participantes`
--

CREATE TABLE IF NOT EXISTS `participantes` (
  `eventos_id_eventos` int(11) NOT NULL,
  `utilizadores_id_utilizadores` int(11) NOT NULL,
  `data_inscricao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `participantes_colaboradores`
--

CREATE TABLE IF NOT EXISTS `participantes_colaboradores` (
  `eventos_id_eventos` int(11) NOT NULL,
  `colaboradores_id_colaboradores` int(11) NOT NULL,
  `tipo_colaborador` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `passwords_blacklist`
--

CREATE TABLE IF NOT EXISTS `passwords_blacklist` (
  `id_passwords` int(11) NOT NULL,
  `passwords` varchar(255) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id_tags` int(11) NOT NULL,
  `tag_nome` varchar(45) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipo_evento`
--

CREATE TABLE IF NOT EXISTS `tipo_evento` (
  `id_tipo_evento` int(11) NOT NULL,
  `nome_tipo_evento` varchar(75) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE IF NOT EXISTS `utilizadores` (
  `id_utilizadores` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `apelido` varchar(50) NOT NULL,
  `genero` int(1) DEFAULT NULL,
  `data_nascimento` date NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores_tokens`
--

CREATE TABLE IF NOT EXISTS `utilizadores_tokens` (
  `id` int(11) NOT NULL,
  `utilizadores_id_utilizadores` int(11) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `refresh_token` text CHARACTER SET ascii COLLATE ascii_bin NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

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
-- Indexes for table `icons`
--
ALTER TABLE `icons`
  ADD PRIMARY KEY (`id_icons`);

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
  ADD PRIMARY KEY (`id_tipo_evento`);

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
  MODIFY `id_estatutos` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id_eventos` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=59;
--
-- AUTO_INCREMENT for table `eventos_infos`
--
ALTER TABLE `eventos_infos`
  MODIFY `id_extras` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `icons`
--
ALTER TABLE `icons`
  MODIFY `id_icons` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=677;
--
-- AUTO_INCREMENT for table `localizacao`
--
ALTER TABLE `localizacao`
  MODIFY `localizacao` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `passwords_blacklist`
--
ALTER TABLE `passwords_blacklist`
  MODIFY `id_passwords` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=139;
--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id_tags` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `tipo_evento`
--
ALTER TABLE `tipo_evento`
  MODIFY `id_tipo_evento` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id_utilizadores` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `utilizadores_tokens`
--
ALTER TABLE `utilizadores_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `eventos`
--
ALTER TABLE `eventos`
ADD CONSTRAINT `fk_eventos_localizacao1` FOREIGN KEY (`localizacao_localizacao`) REFERENCES `localizacao` (`localizacao`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_tipo_evento1` FOREIGN KEY (`tipo_evento_id_tipo_evento`) REFERENCES `tipo_evento` (`id_tipo_evento`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `eventos_has_eventos_infos`
--
ALTER TABLE `eventos_has_eventos_infos`
ADD CONSTRAINT `fk_eventos_has_eventos_infos_eventos1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_has_eventos_infos_eventos_infos1` FOREIGN KEY (`eventos_infos_id_extras`) REFERENCES `eventos_infos` (`id_extras`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `eventos_has_tags`
--
ALTER TABLE `eventos_has_tags`
ADD CONSTRAINT `fk_eventos_has_tags_eventos1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_has_tags_tags1` FOREIGN KEY (`tags_id_tags`) REFERENCES `tags` (`id_tags`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `eventos_infos`
--
ALTER TABLE `eventos_infos`
ADD CONSTRAINT `fk_eventos_infos_icons1` FOREIGN KEY (`icons_id_icons`) REFERENCES `icons` (`id_icons`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_infos_icons2` FOREIGN KEY (`icons_pequeno_icons_id`) REFERENCES `icons` (`id_icons`);

--
-- Limitadores para a tabela `participantes`
--
ALTER TABLE `participantes`
ADD CONSTRAINT `fk_eventos_has_utilizadores_eventos1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_has_utilizadores_utilizadores1` FOREIGN KEY (`utilizadores_id_utilizadores`) REFERENCES `utilizadores` (`id_utilizadores`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `participantes_colaboradores`
--
ALTER TABLE `participantes_colaboradores`
ADD CONSTRAINT `fk_eventos_has_colaboradores_colaboradores1` FOREIGN KEY (`colaboradores_id_colaboradores`) REFERENCES `colaboradores` (`id_colaboradores`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_eventos_has_colaboradores_eventos1` FOREIGN KEY (`eventos_id_eventos`) REFERENCES `eventos` (`id_eventos`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
ADD CONSTRAINT `fk_tokens_utilizadores` FOREIGN KEY (`utilizadores_id_utilizadores`) REFERENCES `utilizadores` (`id_utilizadores`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
